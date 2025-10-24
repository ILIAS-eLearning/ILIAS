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

class RemoveLegacyTestSettingsMigration implements Migration
{
    use TestSettingsSetup;

    private const array UNUSED_LEGACY_COLUMNS = [
        'ects_output',
        'ects_fx',
        'ects_a',
        'ects_b',
        'ects_c',
        'ects_d',
        'ects_e',
        'keep_questions',
        'mc_scoring',
        'show_question_titles',
        'certificate_visibility',
        'resultoutput',
        'pool_usage',
        'info_screen',
    ];

    private \ilDBInterface $db;
    private bool $data_loss_detected = false;

    public function getLabel(): string
    {
        return 'Remove columns from tst_tests and tst_test_defaults that are now in tst_test_settings';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        $move_settings = new MoveTestSettingsMigration();
        $move_settings->prepare($environment);
        if ($move_settings->getRemainingAmountOfSteps() > 0) {
            $this->data_loss_detected = true;
            return;
        }

        $move_templates = new MoveSettingsTemplatesMigration();
        $move_templates->prepare($environment);
        if ($move_templates->getRemainingAmountOfSteps() > 0) {
            $this->data_loss_detected = true;
            return;
        }
    }

    public function step(Environment $environment): void
    {
        if ($this->data_loss_detected) {
            throw new Setup\UnachievableException(
                'Failed to remove legacy test settings. Please run MoveTestSettingsMigration and MoveSettingsTemplatesMigration first.'
            );
        }

        $test_columns = array_merge(array_keys(self::SETTINGS_COLUMNS), self::UNUSED_LEGACY_COLUMNS);
        foreach ($test_columns as $column) {
            $this->dropColumn('tst_tests', $column);
        }

        $this->dropColumn('tst_test_defaults', 'marks');
        $this->dropColumn('tst_test_defaults', 'defaults');
    }

    public function getRemainingAmountOfSteps(): int
    {
        return (int) ($this->db->tableColumnExists('tst_tests', array_key_first(self::SETTINGS_COLUMNS))
            || $this->db->tableColumnExists('tst_tests', self::UNUSED_LEGACY_COLUMNS[0])
            || $this->db->tableColumnExists('tst_test_defaults', 'defaults'));
    }

    private function dropColumn(string $table, string $column): void
    {
        if ($this->db->tableColumnExists($table, $column)) {
            $this->db->dropTableColumn($table, $column);
        }
    }
}
