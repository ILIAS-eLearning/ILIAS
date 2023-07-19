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

class ilLTIConsumerDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'instructor_send_name')) {
            $this->db->addTableColumn('lti_ext_provider', 'instructor_send_name', [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => '0'
            ]);
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'instructor_send_email')) {
            $this->db->addTableColumn('lti_ext_provider', 'instructor_send_email', [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => '0'
            ]);
        }
    }

    public function step_3(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'client_id')) {
            $this->db->addTableColumn('lti_ext_provider', 'client_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'enabled_capability')) {
            $this->db->addTableColumn('lti_ext_provider', 'enabled_capability', [
                'type' => 'clob'
            ]);
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'key_type')) {
            $this->db->addTableColumn('lti_ext_provider', 'key_type', [
                'type' => 'text',
                'length' => 16,
                'notnull' => false
            ]);
        }
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'public_key')) {
            $this->db->addTableColumn('lti_ext_provider', 'public_key', [
                'type' => 'clob'
            ]);
        }
    }

    public function step_7(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'public_keyset')) {
            $this->db->addTableColumn('lti_ext_provider', 'public_keyset', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_8(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'initiate_login')) {
            $this->db->addTableColumn('lti_ext_provider', 'initiate_login', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_9(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'redirection_uris')) {
            $this->db->addTableColumn('lti_ext_provider', 'redirection_uris', [
                'type' => 'text',
                'length' => 510,
                'notnull' => false
            ]);
        }
    }

    public function step_10(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'content_item')) {
            $this->db->addTableColumn('lti_ext_provider', 'content_item', [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => '0'
            ]);
        }
    }

    public function step_11(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'content_item_url')) {
            $this->db->addTableColumn('lti_ext_provider', 'content_item_url', [
                'type' => 'text',
                'length' => 510,
                'notnull' => false
            ]);
        }
    }

    public function step_12(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'grade_synchronization')) {
            $this->db->addTableColumn('lti_ext_provider', 'grade_synchronization', [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => '0'
            ]);
        }
    }

    public function step_13(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'lti_version')) {
            $this->db->addTableColumn('lti_ext_provider', 'lti_version', [
                'type' => 'text',
                'length' => 10,
                'notnull' => true,
                'default' => 'LTI-1p0'
            ]);
        }
    }

    public function step_14(): void
    {
        if (!$this->db->tableColumnExists('lti_consumer_settings', 'custom_params')) {
            $this->db->addTableColumn('lti_consumer_settings', 'custom_params', [
                'type' => 'text',
                'length' => 255,
                'notnull' => true,
                'default' => ''
            ]);
        }
    }

    public function step_15(): void
    {
        if (!$this->db->tableExists('lti_consumer_grades')) {
            $values = array(
                'id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'obj_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'usr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'score_given' => array(
                    'type' => 'float',
                    'notnull' => false
                ),
                'score_maximum' => array(
                    'type' => 'float',
                    'notnull' => false
                ),
                'activity_progress' => array(
                    'type' => 'text',
                    'length' => 20,
                    'notnull' => true
                ),
                'grading_progress' => array(
                    'type' => 'text',
                    'length' => 20,
                    'notnull' => true
                ),
                'lti_timestamp' => array(
                    'type' => 'timestamp',
                    'notnull' => false,
                    'default' => null
                ),
                'stored' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                )
            );
            $this->db->createTable("lti_consumer_grades", $values);
            $this->db->addPrimaryKey("lti_consumer_grades", array("id"));
            $this->db->createSequence("lti_consumer_grades");
            $this->db->addIndex("lti_consumer_grades", array("obj_id","usr_id"), 'i1');
        }
    }
}
