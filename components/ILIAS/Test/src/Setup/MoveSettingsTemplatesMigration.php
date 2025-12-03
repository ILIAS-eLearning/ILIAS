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

namespace ILIAS\Test\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class MoveSettingsTemplatesMigration implements Migration
{
    use TestSettingsSetup;

    private \ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Migrate personal test template settings from tst_test_defaults to tst_test_settings';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 100;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [new \ilDatabaseInitializedObjective()];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $row = $this->db->fetchAssoc(
            $this->db->query("SELECT * FROM tst_test_defaults WHERE settings_id IS NULL LIMIT 1")
        );

        $settings_id = $this->db->nextId('tst_test_settings');
        $setting_data = ['id' => [\ilDBConstants::T_INTEGER, $settings_id]];

        // Migrate the legacy serialized column to a row in 'tst_test_settings'
        $raw_settings = unserialize($row['defaults'], ['allowed_classes' => [\DateTimeImmutable::class]]);
        foreach (self::SETTINGS_COLUMNS as $column_name => $column) {
            [$column_def, $raw_name] = $column;
            if (isset($raw_settings[$raw_name])) {
                $value = $raw_settings[$raw_name];

                if ($column_name === 'reporting_date') {
                    $value = $this->convertLegacyDate($value);
                }

                $setting_data[$column_name] = [$column_def['type'], $value];
            }
        }

        // Insert the new row
        $this->db->insert('tst_test_settings', $setting_data);
        $this->db->update(
            'tst_test_defaults',
            ['settings_id' => [\ilDBConstants::T_INTEGER, $settings_id]],
            ['test_defaults_id' => [\ilDBConstants::T_INTEGER, $row['test_defaults_id']]]
        );

        // Migrate the legacy json decoded to a row in 'tst_mark'
        $raw_marks = json_decode($row['marks'] ?? '[]', true) ?? [];
        foreach ($raw_marks as $mark_data) {
            $mark_id = $this->db->nextId('tst_mark');
            $this->db->insert(
                'tst_mark',
                [
                    'mark_id' => [\ilDBConstants::T_INTEGER, $mark_id],
                    'test_fi' => [\ilDBConstants::T_INTEGER, 0],
                    'short_name' => [\ilDBConstants::T_TEXT, $mark_data['short_name']],
                    'official_name' => [\ilDBConstants::T_TEXT, $mark_data['official_name']],
                    'minimum_level' => [\ilDBConstants::T_FLOAT, $mark_data['minimum_level']],
                    'passed' => [\ilDBConstants::T_FLOAT, (int) $mark_data['passed']],
                    'tstamp' => [\ilDBConstants::T_INTEGER, $row['tstamp']],
                ]
            );

            $this->db->insert(
                'tst_defaults_marks',
                [
                    'defaults_id' => [\ilDBConstants::T_INTEGER, $row['test_defaults_id']],
                    'mark_id' => [\ilDBConstants::T_INTEGER, $mark_id],
                ]
            );
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query("SELECT COUNT(test_defaults_id) AS cnt FROM tst_test_defaults WHERE settings_id IS NULL");
        return (int) $this->db->fetchObject($result)->cnt;
    }
}
