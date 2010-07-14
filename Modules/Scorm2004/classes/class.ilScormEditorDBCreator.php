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

		// continue with step 100
		//
		// IMPORTANT ONLY USE ABSTRACTED METHOD OF ilDB
		// TO MAKE CHANGES, SEE
		// http://www.ilias.de/docu/goto.php?target=pg_25354_42
		//
		
		// STEP 100: ..
		if ($se_db <= 99)
		{
			// OpenId table structure
			
			if(!$ilDB->tableExists('openid_provider'))
			{
				$fields = array(
					'provider_id'	=> array(
						'type'		=> 'integer',
						'length'	=> 4,
					),
					'enabled' 		=> array(
						'type' 			=> 'integer',
						'length' 		=> 1,
					),
					'name' 			=> array(
						'type' 			=> 'text',
						'length' 		=> 128,
						'fixed'			=> false,
						'notnull'		=> false
					),
					'url'			=> array(
						'type'			=> 'text',
						'length'		=> 512,
						'fixed'			=> false,
						'notnull'		=> false
					),
					'image'			=> array(
						'type'			=> 'integer',
						'length'		=> 2
					)
				);
				$ilDB->createTable('openid_provider',$fields);
				$ilDB->addPrimaryKey('openid_provider',array('provider_id'));
				$ilDB->createSequence('openid_provider');
				
			}
			$ilSetting->set("se_db", 100);
		}
		
		if($se_db <= 100)
		{
			$query = "INSERT INTO openid_provider (provider_id,enabled,name,url,image) ".
				"VALUES ( ".
				$ilDB->quote($ilDB->nextId('openid_provider'),'integer').','.
				$ilDB->quote(1,'integer').','.
				$ilDB->quote('MyOpenID','text').','.
				$ilDB->quote('http://%s.myopenid.com').','.
				$ilDB->quote(1,'integer').
				")";
			$res = $ilDB->query($query);

			$ilSetting->set("se_db", 101);
		}
		
		
		if($se_db <= 101)
		{
			$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
			while ($rec = $ilDB->fetchAssoc($set))	// all styles
			{
				$ast = array(
					array("tag" => "a", "type" => "link", "class" => "FileLink",
						"par" => array(
							array("name" => "text-decoration", "value" => "undeline"),
							array("name" => "font-weight", "value" => "normal"),
							array("name" => "color", "value" => "blue")
							)),
					array("tag" => "div", "type" => "glo_overlay", "class" => "GlossaryOverlay",
						"par" => array(
							array("name" => "background-color", "value" => "#FFFFFF"),
							array("name" => "border-color", "value" => "#A0A0A0"),
							array("name" => "border-style", "value" => "solid"),
							array("name" => "border-width", "value" => "2px"),
							array("name" => "padding-top", "value" => "5px"),
							array("name" => "padding-bottom", "value" => "5px"),
							array("name" => "padding-left", "value" => "5px"),
							array("name" => "padding-right", "value" => "5px")
							))
					);
							
				foreach($ast as $st)
				{
					$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
						"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
						"characteristic = ".$ilDB->quote($st["class"], "text")." AND ".
						"type = ".$ilDB->quote($st["type"], "text"));
					if (!$ilDB->fetchAssoc($set2))
					{
						$q = "INSERT INTO style_char (style_id, type, characteristic, hide)".
							" VALUES (".
							$ilDB->quote($rec["obj_id"], "integer").",".
							$ilDB->quote($st["type"], "text").",".
							$ilDB->quote($st["class"], "text").",".
							$ilDB->quote(0, "integer").")";
//echo "<br>-$q-";
						$ilDB->manipulate($q);
						foreach ($st["par"] as $par)
						{
							$spid = $ilDB->nextId("style_parameter");
							$q = "INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value)".
								" VALUES (".
								$ilDB->quote($spid, "integer").",".
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
		
			$ilSetting->set("se_db", 102);
		}
		
		if($se_db <= 102)
		{
			include_once("./Modules/Scorm2004/classes/class.ilScormEditorDBMigrationUtil.php");
			ilScormEditorDBMigrationUtil::copyStyleClass("IntLink", "GlossaryLink", "link", "a");
			
			$ilSetting->set("se_db", 103);
		}

		if($se_db <= 103)
		{
			include_once("./Modules/Scorm2004/classes/class.ilScormEditorDBMigrationUtil.php");
			ilScormEditorDBMigrationUtil::addStyleClass("GlossaryOvTitle", "glo_ovtitle", "h1",
						array("font-size" => "120%",
							  "margin-bottom" => "10px",
							  "margin-top" => "10px",
							  "font-weight" => "normal"
							  ));
			
			$ilSetting->set("se_db", 104);
		}

		if($se_db <= 104)
		{
			include_once("./Modules/Scorm2004/classes/class.ilScormEditorDBMigrationUtil.php");
			ilScormEditorDBMigrationUtil::addStyleClass("GlossaryOvCloseLink", "glo_ovclink", "a",
						array("text-decoration" => "underline",
							  "font-weight" => "normal",
							  "color" => "blue"
							  ));
			ilScormEditorDBMigrationUtil::addStyleClass("GlossaryOvUnitGloLink", "glo_ovuglink", "a",
						array("text-decoration" => "underline",
							  "font-weight" => "normal",
							  "color" => "blue"
							  ));
			ilScormEditorDBMigrationUtil::addStyleClass("GlossaryOvUGListLink", "glo_ovuglistlink", "a",
						array("text-decoration" => "underline",
							  "font-weight" => "normal",
							  "color" => "blue"
							  ));
			
			$ilSetting->set("se_db", 105);
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
?>