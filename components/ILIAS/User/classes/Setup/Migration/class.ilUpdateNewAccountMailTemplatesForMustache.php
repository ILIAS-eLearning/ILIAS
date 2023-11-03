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

class ilUpdateNewAccountMailTemplatesForMustache implements Migration
{
    public const NUMBER_OF_STEPS = 10000;

    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return 'ilUpdateNewAccountMailTemplatesForMustache';
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
        if ($lang === null) {
            return;
        }

        $this->replace($lang, '/\[IF_PASSWORD\]/', '{{#IF_PASSWORD}}');
        $this->replace($lang, '/\[IF_NO_PASSWORD\]/', '{{#IF_NO_PASSWORD}}');
        $this->replace($lang, '/\[IF_TARGET\]/', '{{#IF_TARGET}}');
        $this->replace($lang, '/\[IF_TIMELIMIT\]/', '{{#IF_TIMELIMIT}}');
        $this->replaceRemainingBrackets($lang);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $q = 'SELECT COUNT(*) AS open FROM mail_template ' . PHP_EOL . $this->getWhere();
        $res = $this->db->query($q);
        $row = $this->db->fetchAssoc($res);

        return (int) $row['open'];
    }

    protected function getNextLangToBeUpdated(): ?string
    {
        $this->db->setLimit(1);
        $q = 'SELECT lang FROM mail_template ' . PHP_EOL . $this->getWhere();
        $res = $this->db->query($q);

        if ($this->db->numRows($res) === 0) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);

        return $row['lang'];
    }

    protected function getWhere(): string
    {
        return ' WHERE (' . PHP_EOL
            . $this->db->like('body', ilDBConstants::T_TEXT, '%[%') . ' OR ' . PHP_EOL
            . $this->db->like('body', ilDBConstants::T_TEXT, '%]%') . ' OR ' . PHP_EOL
            . $this->db->like('subject', ilDBConstants::T_TEXT, '%[%') . ' OR ' . PHP_EOL
            . $this->db->like('subject', ilDBConstants::T_TEXT, '%]%') . PHP_EOL
            . ') AND type = ' . $this->db->quote('nacc', ilDBConstants::T_TEXT);
    }

    protected function replace(string $lang, string $regex_search, string $replacement): void
    {
        $res = $this->db->queryF(
            'SELECT subject, body FROM mail_template WHERE lang = %s AND type = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$lang, 'nacc']
        );
        if ($this->db->numRows($res) === 1) {
            $row = $this->db->fetchAssoc($res);

            $subject = $this->replaceInText(
                $row['subject'],
                $regex_search,
                $replacement
            );
            $body = $this->replaceInText(
                $row['body'],
                $regex_search,
                $replacement
            );

            $this->db->manipulateF(
                'UPDATE mail_template SET subject = %s, body = %s WHERE lang = %s AND type = %s',
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                [$subject, $body, $lang, 'nacc']
            );
        }
    }

    protected function replaceRemainingBrackets(string $lang): void
    {
        $res = $this->db->queryF(
            'SELECT subject, body FROM mail_template WHERE lang = %s AND type = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$lang, 'nacc']
        );
        if ($this->db->numRows($res) === 1) {
            $row = $this->db->fetchAssoc($res);

            $subject = $this->replaceInText(
                $row['subject'],
                '/\[([A-Z_\/]+?)\]/',
                '{{$1}}'
            );
            $body = preg_replace(
                $row['body'],
                '/\[([A-Z_\/]+?)\]/',
                '{{$1}}'
            );

            $this->db->manipulateF(
                'UPDATE mail_template SET subject = %s, body = %s WHERE lang = %s AND type = %s',
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                [$subject, $body, $lang, 'nacc']
            );
        }
    }

    protected function replaceInText(
        ?string $text,
        string $regex_search,
        string $replacement
    ): ?string {
        if ($text === null) {
            return null;
        }

        return preg_replace(
            $regex_search,
            $replacement,
            $text
        );
    }
}
