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

/**
 * Database update steps of the whole LTI component.
 *
 * Steps 1-9 were written for LTIProvider and must keep their class name and numbers:
 * il_db_steps records executed steps per class, and existing installations already ran them.
 * Steps 10-29 are the former ilLTIConsumerDatabaseUpdateSteps 1-20, step 30 is the first LTIAdvantage step.
 * Every step checks the current schema first, so running one again changes nothing.
 *
 * New steps are only appended at the end and must check the schema as well.
 * Never renumber the steps or rename this class.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        // Only while consumer_key256 still exists: afterwards consumer_key holds the renamed keys (step_2).
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key')
            && $this->db->tableColumnExists('lti2_consumer', 'consumer_key256')) {
            $this->db->dropTableColumn('lti2_consumer', 'consumer_key');
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key256')
            && !$this->db->tableColumnExists('lti2_consumer', 'consumer_key')) {
            $this->db->renameTableColumn('lti2_consumer', 'consumer_key256', 'consumer_key');
        }
    }

    public function step_3(): void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key')) {
            $this->db->modifyTableColumn('lti2_consumer', 'consumer_key', array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ));
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'platform_id')) {
            $this->db->addTableColumn('lti2_consumer', 'platform_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'client_id')) {
            $this->db->addTableColumn('lti2_consumer', 'client_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'deployment_id')) {
            $this->db->addTableColumn('lti2_consumer', 'deployment_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_7(): void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'public_key')) {
            $this->db->addTableColumn('lti2_consumer', 'public_key', [
                'type' => 'clob',
                'notnull' => false
            ]);
        }
    }

    public function step_8(): void
    {
        if (!$this->db->tableExists('lti2_access_token')) {
            $values = array(
                'consumer_pk' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'scopes' => array(
                    'type' => 'clob',
                    'default' => '',
                    'notnull' => true
                ),
                'token' => array(
                    'type' => 'text',
                    'length' => 2000,
                    'default' => '',
                    'notnull' => true
                ),
                'expires' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                ),
                'created' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                ),
                'updated' => array(
                    'type' => 'timestamp',
                    'notnull' => true
                )
           );
            $this->db->createTable("lti2_access_token", $values);
            $this->db->addPrimaryKey("lti2_access_token", array("consumer_pk"));
        }
    }

    public function step_9(): void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'settings')) {
            $this->db->modifyTableColumn("lti2_consumer", "settings", array("type" => "clob", "notnull" => false));
        }
    }

    public function step_10(): void
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

    public function step_11(): void
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

    public function step_12(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'client_id')) {
            $this->db->addTableColumn('lti_ext_provider', 'client_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_13(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'enabled_capability')) {
            $this->db->addTableColumn('lti_ext_provider', 'enabled_capability', [
                'type' => 'clob'
            ]);
        }
    }

    public function step_14(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'key_type')) {
            $this->db->addTableColumn('lti_ext_provider', 'key_type', [
                'type' => 'text',
                'length' => 16,
                'notnull' => false
            ]);
        }
    }

    public function step_15(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'public_key')) {
            $this->db->addTableColumn('lti_ext_provider', 'public_key', [
                'type' => 'clob'
            ]);
        }
    }

    public function step_16(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'public_keyset')) {
            $this->db->addTableColumn('lti_ext_provider', 'public_keyset', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_17(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'initiate_login')) {
            $this->db->addTableColumn('lti_ext_provider', 'initiate_login', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_18(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'redirection_uris')) {
            $this->db->addTableColumn('lti_ext_provider', 'redirection_uris', [
                'type' => 'text',
                'length' => 510,
                'notnull' => false
            ]);
        }
    }

    public function step_19(): void
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

    public function step_20(): void
    {
        if (!$this->db->tableColumnExists('lti_ext_provider', 'content_item_url')) {
            $this->db->addTableColumn('lti_ext_provider', 'content_item_url', [
                'type' => 'text',
                'length' => 510,
                'notnull' => false
            ]);
        }
    }

    public function step_21(): void
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

    public function step_22(): void
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

    public function step_23(): void
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

    public function step_24(): void
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

    public function step_25(): void
    {
        if (!$this->db->tableColumnExists('lti_consumer_results', 'attended')) {
            $this->db->addTableColumn('lti_consumer_results', 'attended', [
                'type' => 'integer',
                'notnull' => true,
                'length' => 1,
                'default' => 0
            ]);

            $query = /** @lang sql */
                "
                UPDATE lti_consumer_results
                SET attended = 1
                WHERE result IS NOT NULL AND result > 0
            ";
            $this->db->manipulate($query);
        }
    }

    public function step_26(): void
    {
        if (!$this->db->tableColumnExists('lti_consumer_settings', 'score_maximum')) {
            $this->db->addTableColumn('lti_consumer_settings', 'score_maximum', [
                'type' => 'float',
                'notnull' => false,
                'default' => 1
            ]);
        }
    }

    public function step_27(): void
    {
        if (!$this->db->tableExists('lti_consumer_lineitems')) {
            $values = [
                'id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'context_id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'obj_id' => ['type' => 'integer', 'length' => 4, 'notnull' => false],
                'client_id' => ['type' => 'text', 'length' => 255, 'notnull' => false],
                'label' => ['type' => 'text', 'length' => 255, 'notnull' => false],
                'score_maximum' => ['type' => 'float', 'notnull' => false, 'default' => 1],
                'resource_id' => ['type' => 'text', 'length' => 255, 'notnull' => false],
                'resource_link_id' => ['type' => 'text', 'length' => 255, 'notnull' => false],
                'tag' => ['type' => 'text', 'length' => 255, 'notnull' => false],
                'enabled' => ['type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 1]
            ];
            $this->db->createTable('lti_consumer_lineitems', $values);
            $this->db->addPrimaryKey('lti_consumer_lineitems', ['id']);
            $this->db->createSequence('lti_consumer_lineitems');
        }
    }

    public function step_28(): void
    {
        if ($this->db->tableExists('lti_consumer_lineitems') &&
            !$this->db->tableColumnExists('lti_consumer_lineitems', 'resource_link_id')) {
            $this->db->addTableColumn('lti_consumer_lineitems', 'resource_link_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_29(): void
    {
        if ($this->db->tableExists('lti_consumer_lineitems') &&
            !$this->db->tableColumnExists('lti_consumer_lineitems', 'client_id')) {
            $this->db->addTableColumn('lti_consumer_lineitems', 'client_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_30(): void
    {
        if ($this->db->tableExists('lti2_tool')) {
            return;
        }

        $this->db->createTable('lti2_tool', [
            'tool_pk' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            'name' => ['type' => 'text', 'length' => 255, 'notnull' => true],
            'consumer_key' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'secret' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'message_url' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'initiate_login_url' => ['type' => 'text', 'length' => 255, 'notnull' => false],
            'redirection_uris' => ['type' => 'clob', 'notnull' => false],
            'public_key' => ['type' => 'clob', 'notnull' => false],
            'lti_version' => ['type' => 'text', 'length' => 10, 'notnull' => false],
            'signature_method' => ['type' => 'text', 'length' => 15, 'notnull' => false],
            'settings' => ['type' => 'clob', 'notnull' => false],
            'enabled' => ['type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 0],
            'enable_from' => ['type' => 'timestamp', 'notnull' => false],
            'enable_until' => ['type' => 'timestamp', 'notnull' => false],
            'last_access' => ['type' => 'date', 'notnull' => false],
            'created' => ['type' => 'timestamp', 'notnull' => true],
            'updated' => ['type' => 'timestamp', 'notnull' => true],
        ]);
        $this->db->addPrimaryKey('lti2_tool', ['tool_pk']);
        $this->db->createSequence('lti2_tool');
    }
}
