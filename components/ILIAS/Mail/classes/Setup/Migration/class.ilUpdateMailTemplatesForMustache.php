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
        return 'ilUpdateMailTemplatesForMustache';
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
        if ($tpl_values === null) {
            return;
        }

        [$tpl_id, $lang] = $tpl_values;

        $this->replace($tpl_id, $lang);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $q = 'SELECT COUNT(tpl_id) AS open FROM mail_man_tpl ' . PHP_EOL . $this->getWhere();
        $res = $this->db->query($q);
        $row = $this->db->fetchAssoc($res);

        return (int) $row['open'];
    }

    /**
     * @return array{0: int, 1: string}|null
     */
    protected function getNextTemplateToBeUpdated(): ?array
    {
        $this->db->setLimit(1);
        $q = 'SELECT tpl_id, lang FROM mail_man_tpl ' . PHP_EOL . $this->getWhere();
        $res = $this->db->query($q);

        if ($this->db->numRows($res) === 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);

        return [
            (int) $row['tpl_id'],
            $row['lang']
        ];
    }

    protected function getWhere(): string
    {
        return " WHERE (m_subject REGEXP '\[[A-Z_]+?\]' OR m_message REGEXP '\[[A-Z_]+?\]')" . PHP_EOL;
    }

    protected function replace(int $tpl_id, string $lang): void
    {
        $res = $this->db->queryF(
            'SELECT m_subject, m_message FROM mail_man_tpl WHERE tpl_id = %s AND lang = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT],
            [$tpl_id, $lang]
        );
        if ($this->db->numRows($res) === 1) {
            $row = $this->db->fetchAssoc($res);

            $subject = isset($row['m_subject']) ? preg_replace(
                '/\[([A-Z_]+?)\]/',
                '{{$1}}',
                $row['m_subject']
            ) : null;
            $message = isset($row['m_message']) ? preg_replace(
                '/\[([A-Z_]+?)\]/',
                '{{$1}}',
                $row['m_message']
            ) : null;

            $this->db->manipulateF(
                'UPDATE mail_man_tpl SET m_subject = %s, m_message = %s WHERE tpl_id = %s AND lang = %s',
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT],
                [$subject, $message, $tpl_id, $lang]
            );
        }
    }
}
