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

use ILIAS\Test\Certificate\TestPlaceholderValues;

class ilTest10DBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('tst_tests', 'ip_range_from')) {
            $this->db->addTableColumn(
                'tst_tests',
                'ip_range_from',
                [
                    'type' => 'text',
                    'length' => 39
                ]
            );
        }
        if (!$this->db->tableColumnExists('tst_tests', 'ip_range_to')) {
            $this->db->addTableColumn(
                'tst_tests',
                'ip_range_to',
                [
                    'type' => 'text',
                    'length' => 39
                ]
            );
        }
    }

    public function step_2(): void
    {
        $this->db->update(
            'il_cert_cron_queue',
            ['adapter_class' => [ilDBConstants::T_TEXT, TestPlaceholderValues::class]],
            ['adapter_class' => [ilDBConstants::T_TEXT, 'ilTestPlaceholderValues']]
        );
    }
}
