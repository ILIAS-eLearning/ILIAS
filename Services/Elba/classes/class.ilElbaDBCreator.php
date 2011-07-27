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

                // STEP 9
		if ($elb_db <= 8)
		{
			$ilDB->addTableColumn("svy_svy", "template_id", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 4
			));

			$ilSetting->set("elb_db", 9);
		}

                // STEP 10
                /** @author jposselt at databay.de **/
                if ($elb_db <= 9)
                {
			$ilDB->addTableColumn("tst_tests", "express_qpool_allowed", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 1,
                                "default" => 0
			));


			$ilSetting->set("elb_db", 10);
                }

                // STEP 11
                /** @author jposselt at databay.de **/
                if ($elb_db <= 10)
                {
			$ilDB->addTableColumn("tst_tests", "enabled_view_mode", array(
				"type" => "text",
				"notnull" => false,
				"length" => 20,
                                "default" => 0
			));


			$ilSetting->set("elb_db", 11);
                }

                // STEP 12
                /** @author jposselt at databay.de **/
                if ($elb_db <= 11)
                {
			$ilDB->addTableColumn("tst_tests", "template_id", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 4
			));


			$ilSetting->set("elb_db", 12);
                }

	    // STEP 13
		if ($elb_db <= 12)
		{
			$ilDB->addTableColumn("svy_svy", "pool_usage", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 1
			));

			$ilSetting->set("elb_db", 13);
		}

	    // STEP 14
		if ($elb_db <= 13)
		{
			$ilDB->addTableColumn("svy_qblk", "show_blocktitle", array(
				"type" => "text",
				"notnull" => false,
				"length" => 1
			));

			$ilSetting->set("elb_db", 14);
		}

		// STEP 15
		if ($elb_db <= 14)
		{
			$ilDB->addTableColumn("tst_tests", "pool_usage", array(
				"type" => "integer",
				"notnull" => false,
				"length" => 1
			));

			$ilSetting->set("elb_db", 15);
		}

		// STEP 16
		if ($elb_db <= 15)
		{
			$ilDB->dropTableColumn("tst_tests", "express_qpool_allowed");

			$ilSetting->set("elb_db", 16);
		}


		// STEP 17
		if ($elb_db <= 16)
		{
			$ilDB->addTableColumn("il_news_item", "content_text_is_lang_var", array(
				"type" => "integer",
				"length" => 1,
				"notnull" => true,
				"default" => 0
				));

			$ilSetting->set("elb_db", 17);
		}

		// STEP 18
		if ($elb_db <= 17)
		{
			include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
			ilDBUpdate3136::addStyleClass("Page", "page", "div",
						array());
			$ilSetting->set("elb_db", 18);
		}


		// STEP 20
		if ($elb_db <= 19)
		{
			$this->reloadControlStructure();
			$ilSetting->set("elb_db", 20);
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