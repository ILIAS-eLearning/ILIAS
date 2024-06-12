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

class ilCourseDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $ilDB = $this->db;


        if ($ilDB->tableColumnExists('crs_archives', 'archive_date')) {
            $ilDB->modifyTableColumn('crs_archives', 'archive_date', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_items', 'timing_start')) {
            $ilDB->modifyTableColumn('crs_items', 'timing_start', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_items', 'timing_end')) {
            $ilDB->modifyTableColumn('crs_items', 'timing_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_items', 'suggestion_start')) {
            $ilDB->modifyTableColumn('crs_items', 'suggestion_start', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_items', 'suggestion_end')) {
            $ilDB->modifyTableColumn('crs_items', 'suggestion_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_items', 'suggestion_start_rel')) {
            $ilDB->modifyTableColumn('crs_items', 'suggestion_start_rel', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_items', 'suggestion_end_rel')) {
            $ilDB->modifyTableColumn('crs_items', 'suggestion_end_rel', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_lm_history', 'last_access')) {
            $ilDB->modifyTableColumn('crs_lm_history', 'last_access', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'activation_start')) {
            $ilDB->modifyTableColumn('crs_settings', 'activation_start', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'activation_end')) {
            $ilDB->modifyTableColumn('crs_settings', 'activation_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'sub_start')) {
            $ilDB->modifyTableColumn('crs_settings', 'sub_start', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'sub_end')) {
            $ilDB->modifyTableColumn('crs_settings', 'sub_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'archive_start')) {
            $ilDB->modifyTableColumn('crs_settings', 'archive_start', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'archive_end')) {
            $ilDB->modifyTableColumn('crs_settings', 'archive_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'crs_start')) {
            $ilDB->modifyTableColumn('crs_settings', 'crs_start', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'crs_end')) {
            $ilDB->modifyTableColumn('crs_settings', 'crs_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_settings', 'leave_end')) {
            $ilDB->modifyTableColumn('crs_settings', 'leave_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => false,
                'default' => null
            ]);
        }
        if ($ilDB->tableColumnExists('crs_timings_planed', 'planed_start')) {
            $ilDB->modifyTableColumn('crs_timings_planed', 'planed_start', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_timings_planed', 'planed_end')) {
            $ilDB->modifyTableColumn('crs_timings_planed', 'planed_end', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_timings_user', 'sstart')) {
            $ilDB->modifyTableColumn('crs_timings_user', 'sstart', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_timings_user', 'ssend')) {
            $ilDB->modifyTableColumn('crs_timings_user', 'ssend', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
        if ($ilDB->tableColumnExists('crs_waiting_list', 'sub_time')) {
            $ilDB->modifyTableColumn('crs_waiting_list', 'sub_time', [
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ]);
        }
    }

}
