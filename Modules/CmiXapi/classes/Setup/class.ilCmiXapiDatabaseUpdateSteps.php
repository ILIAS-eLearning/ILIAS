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

class ilCmiXapiDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('cmix_users', 'registration')) {
            $this->db->addTableColumn('cmix_users', 'registration', array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true,
                'default' => ''
            ));
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('cmix_settings', 'publisher_id')) {
            $this->db->addTableColumn('cmix_settings', 'publisher_id', array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true,
                'default' => ''
            ));
        }
    }

    public function step_3(): void
    {
        if (!$this->db->tableColumnExists('cmix_settings', 'anonymous_homepage')) {
            $this->db->addTableColumn('cmix_settings', 'anonymous_homepage', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 1
            ));
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('cmix_settings', 'moveon')) {
            $this->db->addTableColumn('cmix_settings', 'moveon', array(
                'type' => 'text',
                'length' => 32,
                'notnull' => true,
                'default' => ''
            ));
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('cmix_token', 'cmi5_session')) {
            $this->db->addTableColumn("cmix_token", "cmi5_session", [
                'type' => 'text',
                'length' => 255,
                'notnull' => true,
                'default' => ''
            ]);
        }
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('cmix_token', 'returned_for_cmi5_session')) {
            $this->db->addTableColumn("cmix_token", "returned_for_cmi5_session", [
                'type' => 'text',
                'length' => 255,
                'notnull' => true,
                'default' => ''
            ]);
        }
    }

    public function step_7(): void
    {
        if (!$this->db->tableColumnExists('cmix_settings', 'launch_parameters')) {
            $this->db->addTableColumn('cmix_settings', 'launch_parameters', array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true,
                'default' => ''
            ));
        }
    }

    public function step_8(): void
    {
        if (!$this->db->tableColumnExists('cmix_settings', 'entitlement_key')) {
            $this->db->addTableColumn('cmix_settings', 'entitlement_key', array(
                'type' => 'text',
                'length' => 255,
                'notnull' => true,
                'default' => ''
            ));
        }
    }

    public function step_9(): void
    {
        if (!$this->db->tableColumnExists('cmix_token', 'cmi5_session_data')) {
            $this->db->addTableColumn("cmix_token", "cmi5_session_data", [
                'type' => 'clob'
            ]);
        }
    }

    public function step_10(): void
    {
        if (!$this->db->tableColumnExists('cmix_users', 'satisfied')) {
            $this->db->addTableColumn('cmix_users', 'satisfied', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_11(): void
    {
        if (!$this->db->tableColumnExists('cmix_settings', 'switch_to_review')) {
            $this->db->addTableColumn('cmix_settings', 'switch_to_review', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 1
            ));
        }
    }
}
