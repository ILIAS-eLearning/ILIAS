<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;

class ilUpdateMailTemplatesForMustache implements Migration
{
    public const NUMBER_OF_STEPS = 10000;

    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "ilUpdateMailTemplatesForMustache";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::NUMBER_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $tpl_values = $this->getNextTemplateToBeUpdated();
        if (is_null($tpl_values)) {
            return;
        }

        list($tpl_id, $lang) = $tpl_values;
        $this->replace($tpl_id, $lang);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $q = "SELECT COUNT(tpl_id) AS open FROM mail_man_tpl" . PHP_EOL
            . $this->getWhere()
        ;
        $res = $this->db->query($q);
        $row = $this->db->fetchAssoc($res);
        return (int) $row["open"];
    }

    protected function getNextTemplateToBeUpdated(): ?array
    {
        $q = "SELECT tpl_id, lang FROM mail_man_tpl" . PHP_EOL
            . $this->getWhere() . PHP_EOL
            . " LIMIT 1"
        ;
        $res = $this->db->query($q);

        if ($this->db->numRows($res) == 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);
        return [
            (int) $row["tpl_id"],
            $row["lang"]
        ];
    }

    protected function getWhere(): string
    {
        return " WHERE " . PHP_EOL
            . $this->db->like("m_message", ilDBConstants::T_TEXT, "[") . " OR " . PHP_EOL
            . $this->db->like("m_message", ilDBConstants::T_TEXT, "]") . " OR " . PHP_EOL
            . $this->db->like("m_subject", ilDBConstants::T_TEXT, "[") . " OR " . PHP_EOL
            . $this->db->like("m_subject", ilDBConstants::T_TEXT, "]")
        ;
    }

    protected function replace(int $tpl_id, string $lang): void
    {
        $q = 'UPDATE mail_man_tpl' . PHP_EOL
            . ' SET m_subject = REGEXP_REPLACE(m_subject, "\\[([[:upper:]]+)\\]", "{{$1}}"),' . PHP_EOL
            . ' m_message = REGEXP_REPLACE(m_message, "\\[([[:upper:]]+)\\]", "{{$1}}"),' . PHP_EOL
            . ' WHERE tpl_id = ' . $this->db->quote($tpl_id, ilDBConstants::T_INTEGER) . PHP_EOL
            . '    AND lang = ' . $this->db->quote($lang, ilDBConstants::T_TEXT)
        ;
        $this->db->manipulate($q);
    }
}
