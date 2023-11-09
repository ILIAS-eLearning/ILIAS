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

    public function step_12(): void
    {
        $this->db->manipulateF('DELETE FROM cmix_users WHERE usr_id = %s', ['integer'], [13]);
    }

    public function step_13(): void
    {
        if (!$this->db->tableColumnExists('cmix_lrs_types', 'delete_data')) {
            $this->db->addTableColumn('cmix_lrs_types', 'delete_data', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_14(): void
    {
        if (!$this->db->tableColumnExists('cmix_settings', 'delete_data')) {
            $this->db->addTableColumn('cmix_settings', 'delete_data', array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_15(): void
    {
        if (!$this->db->tableExists('cmix_del_user')) {
            $fields_data = array(
                'usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'added' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                ),
                'updated' => array(
                    'type' => 'timestamp',
                    'notnull' => false,
                    'default' => null
                ),
            );
            $this->db->createTable("cmix_del_user", $fields_data);
            $this->db->addPrimaryKey("cmix_del_user", array("usr_id"));
        }
    }

    public function step_16(): void
    {
        if (!$this->db->tableExists('cmix_del_object')) {
            $fields_data = array(
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'type_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'activity_id' => array(
                    'type' => 'text',
                    'length' => 128,
                    'notnull' => true,
                ),
                'added' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                ),
                'updated' => array(
                    'type' => 'timestamp',
                    'notnull' => false,
                    'default' => null
                ),
            );
            $this->db->createTable("cmix_del_object", $fields_data);
            $this->db->addPrimaryKey("cmix_del_object", array("obj_id", "type_id", "activity_id"));
        }
    }
}
