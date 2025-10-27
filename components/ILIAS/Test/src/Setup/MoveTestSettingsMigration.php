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

class MoveTestSettingsMigration implements Migration
{
    use TestSettingsSetup;

    private \ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Migrate test settings from tst_tests to tst_test_settings';
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
        $columns = implode(',', array_keys(self::SETTINGS_COLUMNS));
        $row = $this->db->fetchAssoc(
            $this->db->query("SELECT test_id, {$columns} FROM tst_tests WHERE settings_id IS NULL LIMIT 1")
        );

        $settings_id = $this->db->nextId('tst_test_settings');
        $setting_data = ['id' => [\ilDBConstants::T_INTEGER, $settings_id]];

        foreach ($row as $column_name => $value) {
            if (isset(self::SETTINGS_COLUMNS[$column_name])) {
                [$column_def] = self::SETTINGS_COLUMNS[$column_name];

                if ($column_name === 'reporting_date') {
                    $value = $this->convertLegacyDate($value);
                }

                // Convert legacy null values to 0
                if ($column_def['type'] === \ilDBConstants::T_INTEGER && !$this->columnIsNullable($column_def)) {
                    $value = (int) $value;
                }

                $setting_data[$column_name] = [$column_def['type'], $value];
            }
        }

        $this->db->insert('tst_test_settings', $setting_data);
        $this->db->update(
            'tst_tests',
            ['settings_id' => [\ilDBConstants::T_INTEGER, $settings_id]],
            ['test_id' => [\ilDBConstants::T_INTEGER, $row['test_id']]]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query("SELECT COUNT(test_id) AS cnt FROM tst_tests WHERE settings_id IS NULL");
        return (int) $this->db->fetchObject($result)->cnt;
    }
}
