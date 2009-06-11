<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* This class is a temporary class for DB changes needed due to the
* SCORM 2004 Editor development.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilScormEditorDBCreator
{

	function createTables()
	{
		global $ilDB, $ilSetting;
		
		$se_db = $ilSetting->get("se_db");

		// STEP 1: Add editable flag to sahs_lm
		if ($se_db <= 0)
		{
			$q = "ALTER TABLE sahs_lm ADD editable INT NOT NULL DEFAULT 0";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 1);
		}
		
		// STEP 2: Table for tree nodes
		if ($se_db <= 1)
		{
			$q = "CREATE TABLE `sahs_sc13_tree_node` (
				`obj_id` int(11) NOT NULL auto_increment,
				`title` varchar(200) NOT NULL default '',
				`type` char(4) NOT NULL default '',
				`slm_id` int(11) NOT NULL default '0',
				`import_id` varchar(50) NOT NULL default '',
				`create_date` datetime NOT NULL default '0000-00-00 00:00:00',
				`last_update` datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY  (`obj_id`),
				KEY `slm_id` (`slm_id`),
				KEY `type` (`type`)
				) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 2);
		}
		
		// STEP 3: Tree table itself
		if ($se_db <= 2)
		{
			$q = "CREATE TABLE `sahs_sc13_tree` (
				`slm_id` int(11) NOT NULL default '0',
				`child` int(11) NOT NULL default '0',
				`parent` int(11) default NULL,
				`lft` int(11) NOT NULL default '0',
				`rgt` int(11) NOT NULL default '0',
				`depth` smallint(5) NOT NULL default '0',
				KEY `child` (`child`),
				KEY `parent` (`parent`),
				KEY `jmp_lm` (`slm_id`)
				) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 3);
		}

		// STEP 4: Control Structure Reload
		if ($se_db <= 3)
		{
			$this->reloadControlStructure();
			
			$ilSetting->set("se_db", 4);
		}
		
		// STEP 5: Add stylesheet property to sahs_lm
		if ($se_db <= 4)
		{
			$q = "ALTER TABLE sahs_lm ADD stylesheet INT NOT NULL DEFAULT 0";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 5);
		}

		// STEP 6: Control Structure Reload
		// (in this case to be sure that
		// ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilObjStyleSheetGUI
		// is parsed)
		if ($se_db <= 5)
		{
			$this->reloadControlStructure();
			
			$ilSetting->set("se_db", 6);
		}

		// STEP 7: Add assigned glossary property to sahs_lm
		if ($se_db <= 6)
		{
			$q = "ALTER TABLE sahs_lm ADD glossary INT NOT NULL DEFAULT 0";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 7);
		}		
		
		// STEP 8: Add sequncing tables
		##TODO will be seperate steps when migrated to dbupdate02.php
		if ($se_db <= 7)
		{
			include_once("./Modules/Scorm2004/data/seq_editor/seqtemplates.sql.php");
			$ilSetting->set("se_db", 8);
		}		

		// STEP 9
		if ($se_db <= 8)
		{
			$this->reloadControlStructure();
			$ilSetting->set("se_db", 9);
		}		
		
		// STEP 10
		if ($se_db <= 9)
		{
			$this->reloadControlStructure();
			$ilSetting->set("se_db", 10);
		}		

		// STEP 11
		if ($se_db <= 10)
		{
			$q = "REPLACE INTO sahs_sc13_tree_node (obj_id, title, type, slm_id) VALUES (1, 'Dummy top node for all trees.', '', 0)";
			$ilDB->query($q);
			$ilSetting->set("se_db", 11);
		}	
		
		// STEP 12
		if ($se_db <= 11)
		{
			$q = "ALTER TABLE sahs_sc13_seq_objective ADD import_objective_id varchar(200)";
			$ilDB->query($q);
			$ilSetting->set("se_db", 12);
		}	
		
		// STEP 13
		if ($se_db <= 12)
		{
			$q = "INSERT INTO sahs_sc13_seq_templates (identifier,filename) values ('mandatoryoptions','mandatory_options.xml');";
			$ilDB->query($q);
			$ilSetting->set("se_db", 13);
		}	

		// STEP 14
		if ($se_db <= 13)
		{
			$q = "CREATE TABLE `page_question` (
				`page_parent_type` VARCHAR(4) NOT NULL,
				`page_id` INT(11) NOT NULL,
				`question_id` int(11) NOT NULL
				) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 14);
		}
		
		// STEP 15
		if ($se_db <= 14)
		{
			$this->reloadControlStructure();
			$ilSetting->set("se_db", 15);
		}		
		
		// STEP 16
		if ($se_db <= 15)
		{
			$q = "ALTER TABLE `cal_entries` ADD COLUMN `is_milestone` TINYINT
				NOT NULL DEFAULT 0";
			$ilDB->query($q);
			$q = "ALTER TABLE `cal_entries` ADD COLUMN `completion` INT
				NOT NULL DEFAULT 0";
			$ilDB->query($q);
			$q = "CREATE TABLE `cal_entry_responsible` (
				`cal_id` INT(11) NOT NULL,
				`user_id` INT(11) NOT NULL,
				INDEX `cal_id` (`cal_id`),
				INDEX `user_id` (`user_id`)
				) ENGINE=MyISAM;";
			$ilDB->query($q);

			$ilSetting->set("se_db", 16);
		}		

		// STEP 17
		if ($se_db <= 16)
		{
			$this->reloadControlStructure();
			$ilSetting->set("se_db", 17);
		}		
		
		// STEP 18
		if ($se_db <= 17)
		{
			$q = "CREATE TABLE `personal_pc_clipboard` (
				`user_id` INT(11) NOT NULL,
				`content` MEDIUMTEXT,
				`insert_time` DATETIME,
				`order_nr` INT(11),
				INDEX user_id (user_id)
				) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 18);
		}
		
		// STEP 19
		if ($se_db <= 18)
		{
			$this->reloadControlStructure();
			$ilSetting->set("se_db", 19);
		}


		// STEP 20
		if ($se_db <= 19)
		{
			$q = "CREATE TABLE `page_layout` (
			  	 `layout_id` int(11) NOT NULL auto_increment,
			  	 `content` mediumtext,
			     `title` varchar(128) default NULL,
			     `description` varchar(255) default NULL,
			     `active` tinyint(4) default '0',
			      PRIMARY KEY  (`layout_id`)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 20);
		}		
		
		// STEP 21: Import Page Layouts
		##Subjected to change
		if ($se_db <= 20)
		{
			include_once("./Modules/Scorm2004/data/seq_editor/examplelayouts.sql.php");
			$ilSetting->set("se_db", 21);
		}
		
		// STEP 22
		if ($se_db <= 21)
		{
			$this->reloadControlStructure();
			$ilSetting->set("se_db", 22);
		}
		
		// STEP 23
		if ($se_db <= 22)
		{
			//reimport templates
			include_once("./Modules/Scorm2004/data/seq_editor/examplelayouts.sql.php");
			$ilSetting->set("se_db", 23);
		}

		// STEP 24
		if ($se_db <= 23)
		{
			$this->reloadControlStructure();
			$ilSetting->set("se_db", 24);
		}
		
		// STEP 25
		if ($se_db <= 24)
		{
			$q = "CREATE TABLE `style_char` (
			  	 `style_id` int(11) NOT NULL,
			     `type` varchar(30) NOT NULL default '',
				 `characteristic` varchar(30) NOT NULL default '',
				  INDEX style_id (style_id),
			      PRIMARY KEY  (`style_id`, `type`, `characteristic`)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 25);
		}		

		// STEP 26
		if ($se_db <= 25)
		{
			$q = "ALTER TABLE `style_parameter` ADD COLUMN `type` varchar(30) NOT NULL default ''";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 26);
		}		

		// STEP 27
		if ($se_db <= 26)
		{
			include_once("./Services/Style/classes/class.ilStyleMigration.php");
			ilStyleMigration::addMissingStyleCharacteristics();
			
			$ilSetting->set("se_db", 27);
		}
		
		// STEP 28
		if ($se_db <= 27)
		{
			$q = "ALTER TABLE `qpl_questions` ADD COLUMN `nr_of_tries` int NOT NULL default '0'";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 28);
		}		
		
		// STEP 29
		if ($se_db <= 28)
		{
			$q = "ALTER TABLE `sahs_sc13_seq_item` ADD COLUMN `seqxml` mediumtext";
			$ilDB->query($q);
			
			$q = "ALTER TABLE `sahs_sc13_seq_item` DROP PRIMARY KEY";
			$ilDB->query($q);

			$q = "ALTER TABLE `sahs_sc13_seq_item` ADD PRIMARY KEY (`sahs_sc13_tree_node_id`)";
			$ilDB->query($q);
			
			
			$ilSetting->set("se_db", 29);
		}
		
		// STEP 30
		if ($se_db <= 29)
		{
			if (!$ilDB->tableColumnExists("file_usage", "usage_hist_nr"))
			{
				$q = "ALTER TABLE file_usage ADD COLUMN usage_hist_nr INT NOT NULL DEFAULT 0";
				$ilDB->query($q);
			}

			$q = "ALTER TABLE file_usage DROP PRIMARY KEY";
			$ilDB->query($q);

			$q = "ALTER TABLE file_usage ADD PRIMARY KEY (id, usage_type, usage_id, usage_hist_nr)";
			$ilDB->query($q);

			$ilSetting->set("se_db", 30);
		}
		
		// STEP 31
		if ($se_db <= 30)
		{
			$q = "ALTER TABLE `sahs_sc13_seq_item` ADD COLUMN `rootlevel` tinyint(4) NOT NULL default '0'";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 31);
		}

		// STEP 32
		if ($se_db <= 31)
		{
			$q = "UPDATE `style_char` SET type = 'media' WHERE `characteristic` = 'Media' OR `characteristic` = 'MediaCaption'";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 32);
		}

		// STEP 33
		if ($se_db <= 32)
		{
			$q = "UPDATE `style_parameter` SET type = 'media' WHERE `class` = 'Media' OR `class` = 'MediaCaption'";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 33);
		}

		// STEP 34
		if ($se_db <= 33)
		{
			$q = "UPDATE `style_parameter` SET type = 'media_caption' WHERE `class` = 'MediaCaption'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'media_caption' WHERE `characteristic` = 'MediaCaption'";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 34);
		}

		// STEP 35
		if ($se_db <= 34)
		{
			$q = "UPDATE `style_parameter` SET tag = 'div' WHERE `class` = 'MediaCaption'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'media_caption' WHERE `characteristic` = 'MediaCaption'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'media_cont', characteristic = 'MediaContainer' WHERE `characteristic` = 'Media'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'media_cont', class = 'MediaContainer' WHERE `class` = 'Media'";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 35);
		}

		// STEP 36
		if ($se_db <= 35)
		{
			$q = "UPDATE `style_char` SET type = 'page_fn' WHERE `characteristic` = 'Footnote'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'page_nav' WHERE `characteristic` = 'LMNavigation'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'page_title' WHERE `characteristic` = 'PageTitle'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'page_fn' WHERE `class` = 'Footnote'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'page_nav' WHERE `class` = 'LMNavigation'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'page_title' WHERE `class` = 'PageTitle'";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 36);
		}

		// STEP 37
		if ($se_db <= 36)
		{
			$q = "UPDATE `style_char` SET type = 'page_cont', characteristic = 'PageContainer' WHERE `characteristic` = 'Page'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET tag = 'table', type = 'page_cont', class = 'PageContainer' WHERE `class` = 'Page'";
			$ilDB->query($q);
			$ilSetting->set("se_db", 37);
		}

		// STEP 38
		if ($se_db <= 37)
		{
			$q = "UPDATE `style_char` SET type = 'sco_title' WHERE `characteristic` = 'Title' AND type = 'sco'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'sco_title' WHERE `class` = 'Title' AND type = 'sco'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'sco_desc' WHERE `characteristic` = 'Description' AND type = 'sco'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'sco_desc' WHERE `class` = 'Description' AND type = 'sco'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'sco_keyw' WHERE `characteristic` = 'Keywords' AND type = 'sco'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'sco_keyw' WHERE `class` = 'Keywords' AND type = 'sco'";
			$ilDB->query($q);
			$q = "UPDATE `style_char` SET type = 'sco_obj' WHERE `characteristic` = 'Objective' AND type = 'sco'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET type = 'sco_obj' WHERE `class` = 'Objective' AND type = 'sco'";
			$ilDB->query($q);
			$ilSetting->set("se_db", 38);
		}

		// STEP 39
		if ($se_db <= 38)
		{
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			ilObjStyleSheet::_addMissingStyleClassesToAllStyles();
			$ilSetting->set("se_db", 39);
		}

		// STEP 40
		if ($se_db <= 39)
		{
			$q = "UPDATE `style_char` SET characteristic = 'TextInput' WHERE type = 'qinput'";
			$ilDB->query($q);
			$q = "UPDATE `style_parameter` SET class = 'TextInput' WHERE type = 'qinput'";
			$ilDB->query($q);
			$ilSetting->set("se_db", 40);
		}

		// STEP 41
		if ($se_db <= 40)
		{
			// add LongTextInput
			$sts = $ilDB->prepare("SELECT * FROM object_data WHERE type = 'sty'");
			$sets = $ilDB->execute($sts);
			
			while ($recs = $ilDB->fetchAssoc($sets))
			{
				$id = $recs["obj_id"];
				$q = "INSERT INTO `style_char` (style_id, type, characteristic) VALUES ".
					"(".$ilDB->quote($id).",".$ilDB->quote("qlinput").",".$ilDB->quote("LongTextInput").")";
				$ilDB->query($q);
			}
			
			$ilSetting->set("se_db", 41);
		}
		
		// STEP 42
		if ($se_db <= 41)
		{
			$q = "ALTER TABLE sahs_lm ADD question_tries INT DEFAULT 3;";
			$ilDB->query($q);
			$ilSetting->set("se_db", 42);
		}

		// STEP 43
		if ($se_db <= 42)
		{
			$q = "CREATE TABLE `style_color` (
			  	 `style_id` int(11) NOT NULL,
			     `color_name` varchar(30) NOT NULL,
				 `color_code` char(10),
			      PRIMARY KEY  (`style_id`, `color_name`)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 43);
		}		

		// STEP 44
		if ($se_db <= 43)
		{
			$q = "CREATE TABLE `style_table_template` (
				 `id` int(11) NOT NULL AUTO_INCREMENT,
			  	 `style_id` int(11) NOT NULL,
			     `name` varchar(30) NOT NULL,
				 `table_class` char(30),
				 `first_row_class` char(30),
				 `last_row_class` char(30),
				 `first_col_class` char(30),
				 `last_col_class` char(30),
				 `odd_row_class` char(30),
				 `even_row_class` char(30),
				 `odd_col_class` char(30),
				 `even_col_class` char(30),
				 `first_row_header` tinyint NOT NULL DEFAULT 0,
				 `first_col_header` tinyint NOT NULL DEFAULT 0,
			      PRIMARY KEY  (`id`)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 44);
		}		

		// STEP 45
		if ($se_db <= 44)
		{
			$q = "DROP TABLE `style_table_template`";
			$ilDB->query($q);
			$q = "CREATE TABLE `style_table_template` (
				 `id` int(11) NOT NULL AUTO_INCREMENT,
			  	 `style_id` int(11) NOT NULL,
			     `name` varchar(30) NOT NULL,
				 `table_class` char(30),
				 `row_head_class` char(30),
				 `row_foot_class` char(30),
				 `col_head_class` char(30),
				 `col_foot_class` char(30),
				 `odd_row_class` char(30),
				 `even_row_class` char(30),
				 `odd_col_class` char(30),
				 `even_col_class` char(30),
			      PRIMARY KEY  (`id`)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 45);
		}		

		// STEP 46
		if ($se_db <= 45)
		{
			$q = "ALTER TABLE `style_table_template` ADD COLUMN preview VARCHAR(4000)";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 46);
		}		

		// STEP 47
		if ($se_db <= 46)
		{
			$q = "ALTER TABLE `style_table_template` ADD COLUMN `caption_class` char(30)";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 47);
		}		

		// STEP 48
		if ($se_db <= 47)
		{
			$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
			while ($rec = $ilDB->fetchAssoc($set))
			{
				$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
					"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
					"characteristic = ".$ilDB->quote("FileListItemLink", "text")." AND ".
					"type = ".$ilDB->quote("flist_a", "text"));
				if (!$ilDB->fetchAssoc($set2))
				{
					$ilDB->manipulate("INSERT INTO style_char (style_id, type, characteristic)".
						" VALUES (".
						$ilDB->quote($rec["obj_id"], "integer").",".
						$ilDB->quote("flist_a", "text").",".
						$ilDB->quote("FileListItemLink", "text").")");
				}
			}
			
			$ilSetting->set("se_db", 48);
		}		

		// STEP 49
		if ($se_db <= 48)
		{
			$ilDB->query("ALTER TABLE style_char ADD COLUMN hide TINYINT NOT NULL DEFAULT 0");
			$ilSetting->set("se_db", 49);
		}		

		// STEP 50
		if ($se_db <= 49)
		{
			$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
			while ($rec = $ilDB->fetchAssoc($set))
			{
				$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
					"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
					"characteristic = ".$ilDB->quote("Important", "text")." AND ".
					"type = ".$ilDB->quote("text_inline", "text"));
				if (!$ilDB->fetchAssoc($set2))
				{
					$ilDB->manipulate("INSERT INTO style_char (style_id, type, characteristic)".
						" VALUES (".
						$ilDB->quote($rec["obj_id"], "integer").",".
						$ilDB->quote("text_inline", "text").",".
						$ilDB->quote("Important", "text").")");
					$ilDB->manipulate("INSERT INTO style_parameter (style_id, type, class, tag, parameter, value)".
						" VALUES (".
						$ilDB->quote($rec["obj_id"], "integer").",".
						$ilDB->quote("text_inline", "text").",".
						$ilDB->quote("Important", "text").",".
						$ilDB->quote("span", "text").",".
						$ilDB->quote("text-decoration", "text").",".
						$ilDB->quote("underline", "text").
						")");
				}
				$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
					"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
					"characteristic = ".$ilDB->quote("Accent", "text")." AND ".
					"type = ".$ilDB->quote("text_inline", "text"));
				if (!$ilDB->fetchAssoc($set2))
				{
					$ilDB->manipulate("INSERT INTO style_char (style_id, type, characteristic)".
						" VALUES (".
						$ilDB->quote($rec["obj_id"], "integer").",".
						$ilDB->quote("text_inline", "text").",".
						$ilDB->quote("Accent", "text").")");
					$ilDB->manipulate("INSERT INTO style_parameter (style_id, type, class, tag, parameter, value)".
						" VALUES (".
						$ilDB->quote($rec["obj_id"], "integer").",".
						$ilDB->quote("text_inline", "text").",".
						$ilDB->quote("Accent", "text").",".
						$ilDB->quote("span", "text").",".
						$ilDB->quote("color", "text").",".
						$ilDB->quote("#E000E0", "text").
						")");
				}
			}
			
			$ilSetting->set("se_db", 50);
		}		

		// STEP 51
		if ($se_db <= 50)
		{
			$q = "CREATE TABLE `style_template_class` (
			  	 `template_id` int(11) NOT NULL,
				 `class_type` char(30),
				 `class` char(30)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$set = $ilDB->query("SELECT * FROM style_table_template");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$ts = array("table", "row_head", "row_foot", "col_head", "col_foot",
					"odd_row", "even_row", "odd_col", "even_col", "caption");
				foreach ($ts as $t)
				{
					$class = $rec[$t."_class"];
					if ($class != "")
					{
						$ilDB->manipulate("INSERT INTO style_template_class ".
							"(template_id, class_type, class) VALUES (".
							$ilDB->quote($rec["id"], "integer").",".
							$ilDB->quote($t, "text").",".
							$ilDB->quote($class, "text").
							")");
					}
				}
			}
			
			$ilSetting->set("se_db", 51);
		}		

		// STEP 52
		if ($se_db <= 51)
		{
			$q = "ALTER TABLE `style_table_template` RENAME `style_template`";
			$ilDB->query($q);
			$ilSetting->set("se_db", 52);
		}		

		// STEP 53
		if ($se_db <= 52)
		{
			$ilDB->query("ALTER TABLE style_template ADD COLUMN temp_type VARCHAR(30);");
			$ilDB->query("UPDATE style_template SET temp_type = 'table'");
			$ilSetting->set("se_db", 53);
		}		

		// STEP 54
		if ($se_db <= 53)
		{
			$ilDB->query("ALTER TABLE style_template DROP COLUMN table_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN row_head_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN row_foot_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN col_head_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN col_foot_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN odd_row_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN even_row_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN odd_col_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN even_col_class");
			$ilDB->query("ALTER TABLE style_template DROP COLUMN caption_class");
			$ilSetting->set("se_db", 54);
		}		

		// STEP 55
		if ($se_db <= 54)
		{
			$q = "CREATE TABLE `page_style_usage` (
				 `page_id` int NOT NULL,
			  	 `page_type` char(10) NOT NULL,
				 `page_nr` int NOT NULL,
				 `template` tinyint NOT NULL DEFAULT 0,
				 `stype` varchar(30),
				 `sname` char(30),
			      PRIMARY KEY  (page_id, page_type, page_nr)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 55);
		}		

		// STEP 56
		if ($se_db <= 55)
		{
			$q = "ALTER TABLE page_style_usage DROP PRIMARY KEY";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 56);
		}		

		// STEP 57
		if ($se_db <= 56)
		{
			$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
			while ($rec = $ilDB->fetchAssoc($set))	// all styles
			{
				$ast = array(
					array("tag" => "div", "type" => "va_cntr", "class" => "VAccordCntr",
						"par" => array(
							array("name" => "margin-top", "value" => "5px")
							)),
					array("tag" => "div", "type" => "va_icntr", "class" => "VAccordICntr",
						"par" => array(
							array("name" => "background-color", "value" => "#FFFFFF"),
							array("name" => "margin-bottom", "value" => "5px"),
							array("name" => "border-width", "value" => "1px"),
							array("name" => "border-color", "value" => "#9EADBA"),
							array("name" => "border-style", "value" => "solid")
							)),
					array("tag" => "div", "type" => "va_ihead", "class" => "VAccordIHead",
						"par" => array(
							array("name" => "padding-left", "value" => "24px"),
							array("name" => "padding-right", "value" => "3px"),
							array("name" => "padding-bottom", "value" => "3px"),
							array("name" => "padding-top", "value" => "3px"),
							array("name" => "background-color", "value" => "#E2EAF4"),
							array("name" => "text-align", "value" => "left"),
							array("name" => "cursor", "value" => "pointer"),
							array("name" => "background-image", "value" => "accordion_arrow.gif"),
							array("name" => "background-repeat", "value" => "no-repeat"),
							array("name" => "background-position", "value" => "3px 4px"),
							)),
					array("tag" => "div", "type" => "va_ihead", "class" => "VAccordIHead:hover",
						"par" => array(
							array("name" => "background-color", "value" => "#D2D8E2")
							)),
					array("tag" => "div", "type" => "va_icont", "class" => "VAccordICont",
						"par" => array(
							array("name" => "background-color", "value" => "#FFFFFF"),
							array("name" => "padding", "value" => "3px")
							)),
							
					array("tag" => "div", "type" => "ha_cntr", "class" => "HAccordCntr",
						"par" => array(
							)),
					array("tag" => "div", "type" => "ha_icntr", "class" => "HAccordICntr",
						"par" => array(
							array("name" => "background-color", "value" => "#FFFFFF"),
							array("name" => "margin-right", "value" => "5px"),
							array("name" => "border-width", "value" => "1px"),
							array("name" => "border-color", "value" => "#9EADBA"),
							array("name" => "border-style", "value" => "solid")
							)),
					array("tag" => "div", "type" => "ha_ihead", "class" => "HAccordIHead",
						"par" => array(
							array("name" => "padding-left", "value" => "20px"),
							array("name" => "padding-right", "value" => "10px"),
							array("name" => "padding-bottom", "value" => "3px"),
							array("name" => "padding-top", "value" => "3px"),
							array("name" => "background-color", "value" => "#E2EAF4"),
							array("name" => "text-align", "value" => "left"),
							array("name" => "cursor", "value" => "pointer"),
							array("name" => "background-image", "value" => "haccordion_arrow.gif"),
							array("name" => "background-repeat", "value" => "no-repeat"),
							array("name" => "background-position", "value" => "3px 4px"),
							)),
					array("tag" => "div", "type" => "ha_ihead", "class" => "HAccordIHead:hover",
						"par" => array(
							array("name" => "background-color", "value" => "#D2D8E2")
							)),
					array("tag" => "div", "type" => "ha_icont", "class" => "HAccordICont",
						"par" => array(
							array("name" => "background-color", "value" => "#FFFFFF"),
							array("name" => "padding", "value" => "3px")
							)),
							);
							
				foreach($ast as $st)
				{
						
					$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
						"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
						"characteristic = ".$ilDB->quote($st["class"], "text")." AND ".
						"type = ".$ilDB->quote($st["type"], "text"));
					if (!$ilDB->fetchAssoc($set2))
					{
						$q = "INSERT INTO style_char (style_id, type, characteristic)".
							" VALUES (".
							$ilDB->quote($rec["obj_id"], "integer").",".
							$ilDB->quote($st["type"], "text").",".
							$ilDB->quote($st["class"], "text").")";
//echo "<br>-$q-";
						$ilDB->manipulate($q);
						foreach ($st["par"] as $par)
						{
							$q = "INSERT INTO style_parameter (style_id, type, class, tag, parameter, value)".
								" VALUES (".
								$ilDB->quote($rec["obj_id"], "integer").",".
								$ilDB->quote($st["type"], "text").",".
								$ilDB->quote($st["class"], "text").",".
								$ilDB->quote($st["tag"], "text").",".
								$ilDB->quote($par["name"], "text").",".
								$ilDB->quote($par["value"], "text").
								")";
//echo "<br>-$q-";
						$ilDB->manipulate($q);
						}
					}
				}
			}
			$ilSetting->set("se_db", 57);
		}		

		
		// STEP 58
		if ($se_db <= 57)
		{
			$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
			while ($rec = $ilDB->fetchAssoc($set))	// all styles
			{
				$ast = array(
					array("type" => "vaccordion", "name" => "VerticalAccordion",
						"class" => array(
							array("class_type" => "va_cntr", "class" => "VAccordCntr"),
							array("class_type" => "va_icntr", "class" => "VAccordICntr"),
							array("class_type" => "va_ihead", "class" => "VAccordIHead"),
							array("class_type" => "va_icont", "class" => "VAccordICont")
							)),
					array("type" => "haccordion", "name" => "HorizontalAccordion",
						"class" => array(
							array("class_type" => "ha_cntr", "class" => "HAccordCntr"),
							array("class_type" => "ha_icntr", "class" => "HAccordICntr"),
							array("class_type" => "ha_ihead", "class" => "HAccordIHead"),
							array("class_type" => "ha_icont", "class" => "HAccordICont")
							))
							);
							
				foreach($ast as $st)
				{
						
					$set2 = $ilDB->query("SELECT * FROM style_template WHERE ".
						"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
						"temp_type = ".$ilDB->quote($st["type"], "text")." AND ".
						"name = ".$ilDB->quote($st["name"], "text"));
					if (!$ilDB->fetchAssoc($set2))
					{
						$q = "INSERT INTO style_template (style_id, name, temp_type)".
							" VALUES (".
							$ilDB->quote($rec["obj_id"], "integer").",".
							$ilDB->quote($st["name"], "text").",".
							$ilDB->quote($st["type"], "text").")";
//echo "<br>-$q-";
						$ilDB->manipulate($q);
						$tid = $ilDB->getLastInsertId();
						
						foreach ($st["class"] as $c)
						{
							$q = "INSERT INTO style_template_class (template_id, class_type, class)".
								" VALUES (".
								$ilDB->quote($tid, "integer").",".
								$ilDB->quote($c["class_type"], "text").",".
								$ilDB->quote($c["class"], "text").
								")";
//echo "<br>-$q-";
						$ilDB->manipulate($q);
						}
					}
				}
			}
			$ilSetting->set("se_db", 58);
		}		
		
		// STEP 59
		if ($se_db <= 58)
		{
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
			while ($rec = $ilDB->fetchAssoc($set))	// all styles
			{
				$imgs = array("accordion_arrow.gif", "haccordion_arrow.gif");
				ilObjStyleSheet::_createImagesDirectory($rec["obj_id"]);
				$imdir = ilObjStyleSheet::_getImagesDirectory($rec["obj_id"]);
				foreach($imgs as $cim)
				{
					if (!is_file($imdir."/".$cim))
					{
						copy("./Services/Style/basic_style/images/".$cim, $imdir."/".$cim);
					}
				}
			}
			$ilSetting->set("se_db", 59);
		}

		// STEP 60
		if ($se_db <= 59)
		{
			$q = "CREATE TABLE `style_setting` (
				 `style_id` int NOT NULL,
			  	 `name` varchar(30),
				 `value` varchar(30),
			      PRIMARY KEY  (style_id, name)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 60);
		}		

		// STEP 61
		if ($se_db <= 60)
		{
			$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
			while ($rec = $ilDB->fetchAssoc($set))	// all styles
			{
				$ast = array(
					array("tag" => "div", "type" => "sco_desct", "class" => "DescriptionTop",
						"par" => array()),
					array("tag" => "div", "type" => "sco_objt", "class" => "ObjectiveTop",
						"par" => array())
							);
							
				foreach($ast as $st)
				{
						
					$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
						"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
						"characteristic = ".$ilDB->quote($st["class"], "text")." AND ".
						"type = ".$ilDB->quote($st["type"], "text"));
					if (!$ilDB->fetchAssoc($set2))
					{
						$q = "INSERT INTO style_char (style_id, type, characteristic)".
							" VALUES (".
							$ilDB->quote($rec["obj_id"], "integer").",".
							$ilDB->quote($st["type"], "text").",".
							$ilDB->quote($st["class"], "text").")";
//echo "<br>-$q-";
						$ilDB->manipulate($q);
						foreach ($st["par"] as $par)
						{
							$q = "INSERT INTO style_parameter (style_id, type, class, tag, parameter, value)".
								" VALUES (".
								$ilDB->quote($rec["obj_id"], "integer").",".
								$ilDB->quote($st["type"], "text").",".
								$ilDB->quote($st["class"], "text").",".
								$ilDB->quote($st["tag"], "text").",".
								$ilDB->quote($par["name"], "text").",".
								$ilDB->quote($par["value"], "text").
								")";
//echo "<br>-$q-";
						$ilDB->manipulate($q);
						}
					}
				}
			}
			$ilSetting->set("se_db", 61);
		}		

		// STEP 62
		if ($se_db <= 61)
		{
			$q = "CREATE TABLE `page_editor_settings` (
				 `settings_grp` varchar(10),
			  	 `name` varchar(30),
				 `value` varchar(30),
			      PRIMARY KEY  (settings_grp, name)
			     ) ENGINE=MyISAM;";
			$ilDB->query($q);
			
			$ilSetting->set("se_db", 62);
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
		global $ilDB;
		
		if ($this->reload_control_structure)
		{
			include_once("./classes/class.ilDBUpdate.php");
			chdir("./setup");
			include_once("./classes/class.ilCtrlStructureReader.php");
			$GLOBALS["ilCtrlStructureReader"] = new ilCtrlStructureReader();
			$GLOBALS["ilCtrlStructureReader"]->getStructure();
			$update = new ilDBUpdate($ilDB);
			$update->loadXMLInfo();
			chdir("..");
		}
	}
}
