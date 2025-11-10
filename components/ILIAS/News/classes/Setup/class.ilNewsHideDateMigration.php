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

use ILIAS\Setup;
use ILIAS\Setup\Environment;

/**
 * Rewrites stored hide_news_date values to "Y-m-d H:i:s".
 *
 * Older rows can be a date without a time, or the native datetime-local
 * value "Y-m-d\TH:i" (see https://mantis.ilias.de/view.php?id=43255).
 */
class ilNewsHideDateMigration implements Setup\Migration
{
    /**
     * Each "_" matches exactly one character, so values already stored as
     * "Y-m-d H:i:s" never match.
     */
    private const LEGACY_FORMATS = [
        "____-__-__",
        "____-__-__ __:__",
        "____-__-__T__:__",
        "____-__-__T__:__:__"
    ];

    private ilDBInterface $db;

    public function getLabel(): string
    {
        return "Normalize stored news hide dates to Y-m-d H:i:s";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $this->db->setLimit(1);
        $set = $this->db->query(
            "SELECT block_id, value FROM il_block_setting WHERE " . $this->legacyHideDateCondition()
        );
        $row = $this->db->fetchAssoc($set);
        if ($row === null) {
            return;
        }

        $value = str_replace("T", " ", (string) $row["value"]);
        $value .= match (strlen($value)) {
            10 => " 12:00:00",
            16 => ":00",
            default => ""
        };

        $this->db->update(
            "il_block_setting",
            [
                "value" => [ilDBConstants::T_TEXT, $value]
            ],
            [
                "type" => [ilDBConstants::T_TEXT, "news"],
                "user_id" => [ilDBConstants::T_INTEGER, 0],
                "block_id" => [ilDBConstants::T_INTEGER, (int) $row["block_id"]],
                "setting" => [ilDBConstants::T_TEXT, "hide_news_date"]
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $set = $this->db->query(
            "SELECT COUNT(*) cnt FROM il_block_setting WHERE " . $this->legacyHideDateCondition()
        );

        return (int) ($this->db->fetchAssoc($set)["cnt"] ?? 0);
    }

    /**
     * type and user_id are the leading primary key columns of il_block_setting,
     * so the lookup is a range scan over the news block settings only.
     */
    private function legacyHideDateCondition(): string
    {
        $formats = array_map(
            fn(string $format): string => "value LIKE " . $this->db->quote($format, ilDBConstants::T_TEXT),
            self::LEGACY_FORMATS
        );

        return "type = " . $this->db->quote("news", ilDBConstants::T_TEXT) .
            " AND user_id = " . $this->db->quote(0, ilDBConstants::T_INTEGER) .
            " AND setting = " . $this->db->quote("hide_news_date", ilDBConstants::T_TEXT) .
            " AND (" . implode(" OR ", $formats) . ")";
    }
}
