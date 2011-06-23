<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class is a temporary class for DB changes needed due to the
* Skill editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilSkillDBCreator
{

	function createTables()
	{
		global $ilDB, $ilSetting;

		$sk_db = $ilSetting->get("sk_db");

		// STEP 1
		if ($sk_db <= 0)
		{
			// skill tree
			$fields = array(
					'skl_tree_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true,
							'default' => 0
					),
					'child' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true,
							'default' => 0
					),
					'parent' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => false,
							'default' => null
					),
					'lft' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true,
							'default' => 0
					),
					'rgt' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true,
							'default' => 0
					),
					'depth' => array(
							'type' => 'integer',
							'length' => 2,
							'notnull' => true,
							'default' => 0
					)
			);
			$ilDB->createTable('skl_tree', $fields);
			$ilSetting->set("sk_db", 1);
		}

		// STEP 2
		if ($sk_db <= 1)
		{
			// skill tree nodes
			$fields = array(
					'obj_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true,
							'default' => 0

					),
					'title' => array(
							'type' => 'text',
							'length' => 200,
							'notnull' => false
					),
					'type' => array(
							'type' => 'text',
							'length' => 4,
							'fixed' => true,
							'notnull' => false
					),
					'create_date' => array(
							'type' => 'timestamp',
							'notnull' => false
					),
					'last_update' => array(
							'type' => 'timestamp',
							'notnull' => false
					)
			);
			$ilDB->createTable('skl_tree_node', $fields);
			$ilDB->createSequence('skl_tree_node');
			$ilDB->addPrimaryKey("skl_tree_node", array("obj_id"));

			$ilSetting->set("sk_db", 2);
		}

		// STEP 3
		if ($sk_db <= 2)
		{
			// add new type "skmg" for skill management
			$nid = $ilDB->nextId("object_data");
			$ilDB->manipulate("INSERT INTO object_data ".
				"(obj_id, type, title, description, owner, create_date, last_update) VALUES (".
				$ilDB->quote($nid, "integer").",".
				$ilDB->quote("typ", "text").",".
				$ilDB->quote("skmg", "text").",".
				$ilDB->quote("Skill Management", "text").",".
				$ilDB->quote(-1, "integer").",".
				$ilDB->now().",".
				$ilDB->now().
				")");
			$typ_id = $nid;

			// add skill management node in settings folder
			$nid = $ilDB->nextId("object_data");
			$ilDB->manipulate("INSERT INTO object_data ".
				"(obj_id, type, title, description, owner, create_date, last_update) VALUES (".
				$ilDB->quote($nid, "integer").",".
				$ilDB->quote("skmg", "text").",".
				$ilDB->quote("__SkillManagement", "text").",".
				$ilDB->quote("Skill Management", "text").",".
				$ilDB->quote(-1, "integer").",".
				$ilDB->now().",".
				$ilDB->now().
				")");

			$nrid = $ilDB->nextId("object_reference");
			$ilDB->manipulate("INSERT INTO object_reference ".
				"(ref_id, obj_id) VALUES (".
				$ilDB->quote($nrid, "integer").",".
				$ilDB->quote($nid, "integer").
				")");

			// put in tree
			$tree = new ilTree(ROOT_FOLDER_ID);
			$tree->insertNode($nrid, SYSTEM_FOLDER_ID);


			$set = $ilDB->query("SELECT obj_id FROM object_data WHERE ".
				" type = ".$ilDB->quote("typ", "text")." AND ".
				" title = ".$ilDB->quote("skmg", "text")
				);
			$rec = $ilDB->fetchAssoc($set);
			$typ_id = $rec["obj_id"];

			// add rbac operations
			// 1: edit_permissions, 2: visible, 3: read, 4:write
			$ilDB->manipulate("INSERT INTO rbac_ta ".
				"(typ_id, ops_id) VALUES (".
				$ilDB->quote($typ_id, "integer").",".
				$ilDB->quote(1, "integer").
				")");
			$ilDB->manipulate("INSERT INTO rbac_ta ".
				"(typ_id, ops_id) VALUES (".
				$ilDB->quote($typ_id, "integer").",".
				$ilDB->quote(2, "integer").
				")");
			$ilDB->manipulate("INSERT INTO rbac_ta ".
				"(typ_id, ops_id) VALUES (".
				$ilDB->quote($typ_id, "integer").",".
				$ilDB->quote(3, "integer").
				")");
			$ilDB->manipulate("INSERT INTO rbac_ta ".
				"(typ_id, ops_id) VALUES (".
				$ilDB->quote($typ_id, "integer").",".
				$ilDB->quote(4, "integer").
				")");

			$ilSetting->set("sk_db", 3);
		}

		// STEP 4
		if ($sk_db <= 3)
		{
			$this->reloadControlStructure();
			
			$ilSetting->set("sk_db", 4);
		}

		// STEP 5
		if ($sk_db <= 4)
		{
			// add skill tree and root node
			$nid = $ilDB->nextId("skl_tree_node");
			$ilDB->manipulate("INSERT INTO skl_tree_node ".
				"(obj_id, type, title, create_date) VALUES (".
				$ilDB->quote($nid, "integer").",".
				$ilDB->quote("skrt", "text").",".
				$ilDB->quote("Skill Tree Root Node", "text").",".
				$ilDB->now().
				")");

			$skill_tree = new ilTree(1);
			$skill_tree->setTreeTablePK("skl_tree_id");
			$skill_tree->setTableNames('skl_tree', 'skl_tree_node');
			$skill_tree->addTree(1, $nid);

			$ilSetting->set("sk_db", 5);
		}

		// STEP 6
		if ($sk_db <= 5)
		{
			$this->reloadControlStructure();

			$ilSetting->set("sk_db", 6);
		}

		// STEP 7
		if ($sk_db <= 6)
		{
			$fields = array(
					'id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'skill_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'nr' => array(
							'type' => 'integer',
							'length' => 2,
							'notnull' => true
					),
					'title' => array(
							'type' => 'text',
							'length' => 200,
							'notnull' => false
					),
					'description' => array(
							'type' => 'clob'
					)
				);
			$ilDB->createTable('skl_level', $fields);
			$ilDB->createSequence('skl_level');
			$ilDB->addPrimaryKey("skl_level", array("id"));

			$ilSetting->set("sk_db", 7);
		}

		// STEP 8
		if ($sk_db <= 7)
		{
			$this->reloadControlStructure();

			$ilSetting->set("sk_db", 8);
		}

		// STEP 9
		if ($sk_db <= 8)
		{
			$ilDB->addTableColumn("skl_level", "trigger_ref_id", array(
				"type" => "integer",
				"notnull" => true,
				"length" => 4,
				"default" => 0
				));
			$ilDB->addTableColumn("skl_level", "trigger_obj_id", array(
				"type" => "integer",
				"notnull" => true,
				"length" => 4,
				"default" => 0
				));

			$ilSetting->set("sk_db", 9);
		}

		// STEP 10
		if ($sk_db <= 9)
		{
			$fields = array(
					'level_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'user_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'status_date' => array(
							'type' => 'timestamp',
							'notnull' => true
					),
					'skill_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'status' => array(
							'type' => 'integer',
							'length' => 1,
							'notnull' => true
					),
					'valid' => array(
							'type' => 'integer',
							'length' => 1,
							'notnull' => true,
							'default' => 0
					),
					'trigger_ref_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'trigger_obj_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'trigger_title' => array(
							'type' => 'text',
							'length' => 200,
							'notnull' => false
					)
				);
			$ilDB->createTable('skl_user_skill_level', $fields);
			$ilDB->addIndex("skl_user_skill_level", array("skill_id"), "isk");
			$ilDB->addIndex("skl_user_skill_level", array("level_id"), "ilv");
			$ilDB->addIndex("skl_user_skill_level", array("user_id"), "ius");
			$ilDB->addIndex("skl_user_skill_level", array("status_date"), "isd");
			$ilDB->addIndex("skl_user_skill_level", array("status"), "ist");
			$ilDB->addIndex("skl_user_skill_level", array("valid"), "ivl");

			$ilSetting->set("sk_db", 10);
		}

		// STEP 11
		if ($sk_db <= 10)
		{
			$fields = array(
					'level_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'user_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'status_date' => array(
							'type' => 'timestamp',
							'notnull' => true
					),
					'skill_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'trigger_ref_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'trigger_obj_id' => array(
							'type' => 'integer',
							'length' => 4,
							'notnull' => true
					),
					'trigger_title' => array(
							'type' => 'text',
							'length' => 200,
							'notnull' => false
					)
				);
			$ilDB->createTable('skl_user_has_level', $fields);
			$ilDB->addPrimaryKey('skl_user_has_level',
				array("level_id", "user_id"));

			$ilSetting->set("sk_db", 11);
		}


		// keep this line at the end of the method
		$this->finalProcessing();
	}

	function reloadControlStructure()
	{
		$this->reload_control_structure = true;
	}

	function finalProcessing()
	{
		global $ilDB, $ilClientIniFile;

		if ($this->reload_control_structure)
		{
			include_once("./Services/Database/classes/class.ilDBUpdate.php");
//			chdir("./setup");
			include_once("./setup/classes/class.ilCtrlStructureReader.php");
			$GLOBALS["ilCtrlStructureReader"] = new ilCtrlStructureReader();
			$GLOBALS["ilCtrlStructureReader"]->setIniFile($ilClientIniFile);
			$GLOBALS["ilCtrlStructureReader"]->getStructure();
			$update = new ilDBUpdate($ilDB);
			$update->loadXMLInfo();
//			chdir("..");
		}
	}
}
?>