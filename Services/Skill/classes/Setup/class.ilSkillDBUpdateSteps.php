<?php declare(strict_types = 1);

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
 ********************************************************************
 */

class ilSkillDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        if ($this->db->sequenceExists('skl_self_eval')) {
            $this->db->dropSequence('skl_self_eval');
        }

        if ($this->db->tableExists('skl_self_eval')) {
            $this->db->dropTable('skl_self_eval');
        }

        if ($this->db->tableExists('skl_self_eval_level')) {
            $this->db->dropTable('skl_self_eval_level');
        }
    }

    public function step_2() : void
    {
        if (!$this->db->tableColumnExists('skl_user_skill_level', 'trigger_user_id')) {
            $this->db->addTableColumn(
                'skl_user_skill_level',
                'trigger_user_id',
                array(
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 20,
                    'default' => "-"
                )
            );
        }
    }

    public function step_3() : void
    {
        if (!$this->db->tableColumnExists('skl_user_has_level', 'trigger_user_id')) {
            $this->db->addTableColumn(
                'skl_user_has_level',
                'trigger_user_id',
                array(
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 20,
                    'default' => "-"
                )
            );
        }
    }

    public function step_4() : void
    {
        include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

        $skill_tree_type_id = ilDBUpdateNewObjectType::getObjectTypeId('skee');

        if (!$skill_tree_type_id) {
            // add basic object type
            $skill_tree_type_id = ilDBUpdateNewObjectType::addNewType('skee', 'Skill Tree');

            $opsId = [];
            $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
                'read_comp',
                'Read Competences',
                'object',
                6500
            );

            $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
                'read_profiles',
                'Read Competence Profiles',
                'object',
                6510
            );

            $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
                'manage_comp',
                'Manage Competences',
                'object',
                8500
            );

            $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
                'manage_comp_temp',
                'Manage Competence Templates',
                'object',
                8510
            );

            $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
                'manage_profiles',
                'Manage Competence Profiles',
                'object',
                8520
            );

            // common rbac operations
            $rbacOperations = array(
                ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
                ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
                ilDBUpdateNewObjectType::RBAC_OP_READ,
                ilDBUpdateNewObjectType::RBAC_OP_WRITE,
                ilDBUpdateNewObjectType::RBAC_OP_DELETE,
                ilDBUpdateNewObjectType::RBAC_OP_COPY
            );

            ilDBUpdateNewObjectType::addRBACOperations($skill_tree_type_id, $rbacOperations);

            // add create operation for relevant container types

            $parentTypes = array('skmg');
            ilDBUpdateNewObjectType::addRBACCreate('create_skee', 'Create Skill Tree', $parentTypes);

            //ilDBUpdateNewObjectType::applyInitialPermissionGuideline('skee', false);
        }
    }

    public function step_5() : void
    {
        include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
        $skill_tree_type_id = ilDBUpdateNewObjectType::getObjectTypeId('skee');
        $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_comp');
        ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
        $skill_tree_type_id = ilDBUpdateNewObjectType::getObjectTypeId('skee');
        $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_profiles');
        ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
        $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_comp');
        ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
        $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_comp_temp');
        ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
        $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_profiles');
        ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
    }

    public function step_6() : void
    {
        // get skill managemenet object id
        $set = $this->db->queryF(
            "SELECT * FROM object_data " .
            " WHERE type = %s ",
            ["text"],
            ["skmg"]
        );
        $rec = $this->db->fetchAssoc($set);

        // get skill management ref id
        $set = $this->db->queryF(
            "SELECT * FROM object_reference " .
            " WHERE obj_id = %s ",
            ["integer"],
            [$rec["obj_id"]]
        );
        $rec = $this->db->fetchAssoc($set);
        $skmg_ref_id = $rec["ref_id"];

        // create default tree object entry
        $obj_id = $this->db->nextId('object_data');
        $this->db->manipulate("INSERT INTO object_data " .
            "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
            $this->db->quote($obj_id, "integer") . "," .
            $this->db->quote("skee", "text") . "," .
            $this->db->quote("Default", "text") . "," .
            $this->db->quote("", "text") . "," .
            $this->db->quote(-1, "integer") . "," .
            $this->db->now() . "," .
            $this->db->now() .
            ")");

        // get ref id for default tree object
        $ref_id = $this->db->nextId('object_reference');
        $this->db->manipulate("INSERT INTO object_reference " .
            "(obj_id, ref_id) VALUES (" .
            $this->db->quote($obj_id, "integer") . "," .
            $this->db->quote($ref_id, "integer") .
            ")");

        // put in tree
        require_once("Services/Tree/classes/class.ilTree.php");
        $tree = new ilTree(ROOT_FOLDER_ID);
        $tree->insertNode($ref_id, (int) $skmg_ref_id);
    }

    public function step_7() : void
    {
        $set = $this->db->queryF(
            "SELECT * FROM object_data " .
            " WHERE type = %s AND title = %s",
            ["string", "string"],
            ["skee", "Default"]
        );
        $rec = $this->db->fetchAssoc($set);

        $this->db->update(
            "skl_tree",
            [
            "skl_tree_id" => ["integer", $rec["obj_id"]]
        ],
            [    // where
                "skl_tree_id" => ["integer", 1]
            ]
        );
    }

    public function step_8() : void
    {
        if (!$this->db->tableColumnExists("skl_profile", "skill_tree_id")) {
            $this->db->addTableColumn("skl_profile", "skill_tree_id", array(
                "type" => "integer",
                "notnull" => true,
                "default" => 0,
                "length" => 4
            ));
        }
    }

    public function step_9() : void
    {
        $set = $this->db->queryF(
            "SELECT * FROM object_data " .
            " WHERE type = %s AND title = %s",
            ["string", "string"],
            ["skee", "Default"]
        );
        $rec = $this->db->fetchAssoc($set);

        $this->db->update(
            "skl_profile",
            [
            "skill_tree_id" => ["integer", $rec["obj_id"]]
        ],
            [    // where
                "skill_tree_id" => ["integer", 0]
            ]
        );
    }

    public function step_10() : void
    {
        if (!$this->db->tableColumnExists("skl_profile", "image_id")) {
            $this->db->addTableColumn("skl_profile", "image_id", array(
                "type" => "text",
                "notnull" => true,
                "length" => 4000
            ));
        }
    }

    public function step_11() : void
    {
        if (!$this->db->tableExists("skl_profile_completion")) {
            $fields = [
                "profile_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "user_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "date" => [
                    "type" => "timestamp",
                    "notnull" => true
                ],
                "fulfilled" => [
                    "type" => "integer",
                    "length" => 1,
                    "notnull" => true
                ]
            ];
            $this->db->createTable("skl_profile_completion", $fields);
            $this->db->addPrimaryKey("skl_profile_completion", ["profile_id", "user_id", "date"]);
        }
    }
}
