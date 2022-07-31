<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilLTIDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key')) {
            $this->db->dropTableColumn('lti2_consumer', 'consumer_key');
        }
    }

    public function step_2() : void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key256')) {
            $this->db->renameTableColumn('lti2_consumer', 'consumer_key256', 'consumer_key');
        }
    }

    public function step_3() : void
    {
        if ($this->db->tableColumnExists('lti2_consumer', 'consumer_key')) {
            $this->db->modifyTableColumn('lti2_consumer', 'consumer_key', array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ));
        }
    }

    public function step_4() : void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'platform_id')) {
            $this->db->addTableColumn('lti2_consumer', 'platform_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_5() : void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'client_id')) {
            $this->db->addTableColumn('lti2_consumer', 'client_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_6() : void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'deployment_id')) {
            $this->db->addTableColumn('lti2_consumer', 'deployment_id', [
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            ]);
        }
    }

    public function step_7() : void
    {
        if (!$this->db->tableColumnExists('lti2_consumer', 'public_key')) {
            $this->db->addTableColumn('lti2_consumer', 'public_key', [
                'type' => 'clob',
                'notnull' => false
            ]);
        }
    }

    public function step_8() : void
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

    public function step_9() : void
    {
        $this->db->modifyTableColumn("lti2_consumer", "settings", array("type" => "clob", "notnull" => false));
    }
}
