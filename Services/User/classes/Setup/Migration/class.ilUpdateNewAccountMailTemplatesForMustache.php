<?php

declare(strict_types=1);

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

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;

class ilUpdateNewAccountMailTemplatesForMustache implements Migration
{
    public const NUMBER_OF_STEPS = 10000;

    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "ilUpdateNewAccountMailTemplatesForMustache";
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
        $lang = $this->getNextLangToBeUpdated();
        if (is_null($lang)) {
            return;
        }
        $this->replace($lang, '[IF_PASSWORD]', '{{#IF_PASSWORD}}');
        $this->replace($lang, '[IF_NO_PASSWORD]', '{{#IF_NO_PASSWORD}}');
        $this->replace($lang, '[IF_TARGET]', '{{#IF_TARGET}}');
        $this->replace($lang, '[IF_TIMELIMIT]', '{{#IF_TIMELIMIT}}');
        $this->replace($lang, '[', '{{');
        $this->replace($lang, ']', '}}');
    }

    public function getRemainingAmountOfSteps(): int
    {
        $q = "SELECT COUNT(lang) AS open FROM mail_template" . PHP_EOL
            . " WHERE body LIKE '%[%' OR body LIKE '%]%' OR subject LIKE '%[%' OR subject LIKE '%]%'" . PHP_EOL
            . "    AND type = 'nacc'" . PHP_EOL
        ;
        $res = $this->db->query($q);
        $row = $this->db->fetchAssoc($res);
        return (int) $row["open"];
    }

    protected function getNextLangToBeUpdated(): ?string
    {
        $q = "SELECT lang FROM mail_template" . PHP_EOL
            . " WHERE body LIKE '%[%' OR body LIKE '%]%' OR subject LIKE '%[%' OR subject LIKE '%]%'" . PHP_EOL
            . "    AND type = 'nacc'" . PHP_EOL
            . " LIMIT 1"
        ;
        $res = $this->db->query($q);

        if ($this->db->numRows($res) == 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);
        return $row["lang"];
    }

    protected function replace(string $lang, string $search, string $replacement): void
    {
        $q = "UPDATE mail_template" . PHP_EOL
            . " SET subject = REPLACE(subject, '" . $search . "', '" . $replacement . "')," . PHP_EOL
            . " body = REPLACE(body, '" . $search . "', '" . $replacement . "')" . PHP_EOL
            . " WHERE lang = " . $this->db->quote($lang, 'text') . PHP_EOL
            . "    AND type = 'nacc'"
        ;
        $this->db->manipulate($q);
    }
}
