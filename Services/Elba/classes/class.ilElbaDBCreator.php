<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class handles all DB changes necessary for Carl Duisberg
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilElbaDBCreator
{

	function createTables()
	{
		global $ilDB, $ilSetting;

		$elb_db = $ilSetting->get("elb_db");

		// STEP 1
		if ($elb_db <= 0)
		{
			$ilDB->addTableColumn("il_wiki_data", "imp_pages", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 1
			));


			$ilSetting->set("elb_db", 1);
		}

		// STEP 2
		if ($elb_db <= 1)
		{
			$fields = array(
				'wiki_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
					),
				'ord' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
					),
				'indent' => array(
					'type' => 'integer',
					'length' => 1,
					'notnull' => true
					),
				'page_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
					)
			);
			$ilDB->createTable('il_wiki_imp_pages', $fields);



			$ilSetting->set("elb_db", 2);
		}

		// STEP 3
		if ($elb_db <= 2)
		{
			$fields = array(
				'id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
					),
				'type' => array(
					'type' => 'text',
					'length' => 5,
					'notnull' => true
					),
				'title' => array(
					'type' => 'text',
					'length' => 100,
					'notnull' => true
					),
				'description' => array(
					'type' => 'clob'
					)
			);
			$ilDB->createTable('adm_settings_template', $fields);
			$ilDB->createSequence('adm_settings_template');


			$ilSetting->set("elb_db", 3);
		}

		// STEP 4
		if ($elb_db <= 3)
		{
			$fields = array(
				'template_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
					),
				'setting' => array(
					'type' => 'text',
					'length' => 40,
					'notnull' => true
					),
				'value' => array(
					'type' => 'text',
					'length' => 4000,
					'notnull' => false
					),
				'hide' => array(
					'type' => 'integer',
					'length' => 1,
					'notnull' => false
					),
			);
			$ilDB->createTable('adm_set_templ_value', $fields);
			
			$ilSetting->set("elb_db", 4);
		}

		// STEP 5
		if ($elb_db <= 4)
		{
			$fields = array(
				'template_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
					),
				'tab_id' => array(
					'type' => 'text',
					'length' => 80,
					'notnull' => true
					)
			);
			$ilDB->createTable('adm_set_templ_hide_tab', $fields);

			$ilSetting->set("elb_db", 5);
		}

		// STEP 6
		if ($elb_db <= 5)
		{
			$this->reloadControlStructure();

			$ilSetting->set("elb_db", 6);
		}

		// STEP 7
		if ($elb_db <= 6)
		{
			$ilDB->addTableColumn("il_wiki_data", "page_toc", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 1
			));


			$ilSetting->set("elb_db", 7);
		}

		// STEP 8
		if ($elb_db <= 7)
		{
			$ilDB->addTableColumn("il_wiki_page", "blocked", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 1
			));


			$ilSetting->set("elb_db", 8);
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