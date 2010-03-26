<?php

/**
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilUniStuttgartDBCreator
{

	function updateTables()
	{
		global $ilDB, $ilSetting;
		
		$db = $ilSetting->get("patch_stex_db");

		// STEP 1:
		if ($db <= 0)
		{
			$ilDB->createTable("exc_assignment",
				array(
					"id" => array(
						"type" => "integer", "length" => 4, "notnull" => true
					),
					"exc_id" => array(
						"type" => "integer", "length" => 4, "notnull" => true
					),
					"time_stamp" => array(
						"type" => "integer", "length" => 4, "notnull" => false
					),
					"instruction" => array(
						"type" => "clob"
					)
				)
			);

			$ilDB->addPrimaryKey("exc_assignment", array("id"));
			
			$ilDB->createSequence("exc_assignment");
			
			$ilSetting->set("patch_stex_db", 1);
		}
		
		// STEP 2:
		if ($db <= 1)
		{
			$ilDB->createTable("exc_mem_ass_status",
				array(
					"ass_id" => array(
						"type" => "integer", "length" => 4, "notnull" => true
					),
					"usr_id" => array(
						"type" => "integer", "length" => 4, "notnull" => true
					),
					"notice" => array(
						"type" => "text", "length" => 4000, "notnull" => false
					),
					"returned" => array(
						"type" => "integer", "length" => 1, "notnull" => true, "default" => 0
					),
					"solved" => array(
						"type" => "integer", "length" => 1, "notnull" => false
					),
					"status_time" => array(
						"type" => "timestamp", "notnull" => false
					),
					"sent" => array(
						"type" => "integer", "length" => 1, "notnull" => false
					),
					"sent_time" => array(
						"type" => "timestamp", "notnull" => false
					),
					"feedback_time" => array(
						"type" => "timestamp", "notnull" => false
					),
					"feedback" => array(
						"type" => "integer", "length" => 1, "notnull" => true, "default" => 0
					),
					"status" => array(
						"type" => "text", "length" => 9, "fixed" => true, "default" => "notgraded", "notnull" => false
					)
				)
			);
				
			$ilDB->addPrimaryKey("exc_mem_ass_status", array("ass_id", "usr_id"));
			
			$ilSetting->set("patch_stex_db", 2);
		}

		
		
		// STEP 3:
		if ($db <= 2)
		{
			$ilDB->addTableColumn("exc_returned",
				"ass_id",
				array("type" => "integer", "length" => 4, "notnull" => false));
			
			$ilSetting->set("patch_stex_db", 3);
		}
		
		// STEP 4:
		if ($db <= 3)
		{
			$ilDB->createTable("exc_mem_tut_status",
				array (
					"ass_id" => array(
						"type" => "integer", "length" => 4, "notnull" => true
					),
					"mem_id" => array(
						"type" => "integer", "length" => 4, "notnull" => true
					),
					"tut_id" => array(
						"type" => "integer", "length" => 4, "notnull" => true
					),
					"download_time" => array(
						"type" => "timestamp"
					)
				)
			);
			
			$ilSetting->set("patch_stex_db", 4);
		}
		
		// STEP 5:
		if ($db <= 4)
		{
			$ilDB->addPrimaryKey("exc_mem_tut_status", array("ass_id", "mem_id", "tut_id"));

			$ilSetting->set("patch_stex_db", 5);
		}

		// STEP 6: 
		if ($db <= 5)
		{
			$set = $ilDB->query("SELECT * FROM exc_data");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				// Create exc_assignment records for all existing exercises
				// -> instruction and time_stamp fields in exc_data are obsolete
				$next_id = $ilDB->nextId("exc_assignment");
				$ilDB->insert("exc_assignment", array(
					"id" => array("integer", $next_id),
					"exc_id" => array("integer", $rec["obj_id"]),
					"time_stamp" => array("integer", $rec["time_stamp"]),
					"instruction" => array("clob", $rec["instruction"])
					));
			}
			
			$ilSetting->set("patch_stex_db", 6);
		}
		
		// STEP 7: 
		if ($db <= 6)
		{
			$ilDB->addIndex("exc_members", array("obj_id"), "ob");
			$ilSetting->set("patch_stex_db", 7);
		}

		// STEP 8: 
		if ($db <= 7)
		{
			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$set2 = $ilDB->query("SELECT * FROM exc_members ".
					" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
					);
				while ($rec2  = $ilDB->fetchAssoc($set2))
				{
					$ilDB->manipulate("INSERT INTO exc_mem_ass_status ".
						"(ass_id, usr_id, notice, returned, solved, status_time, sent, sent_time,".
						"feedback_time, feedback, status) VALUES (".
						$ilDB->quote($rec["id"], "integer").",".
						$ilDB->quote($rec2["usr_id"], "integer").",".
						$ilDB->quote($rec2["notice"], "text").",".
						$ilDB->quote($rec2["returned"], "integer").",".
						$ilDB->quote($rec2["solved"], "integer").",".
						$ilDB->quote($rec2["status_time"], "timestamp").",".
						$ilDB->quote($rec2["sent"], "integer").",".
						$ilDB->quote($rec2["sent_time"], "timestamp").",".
						$ilDB->quote($rec2["feedback_time"], "timestamp").",".
						$ilDB->quote($rec2["feedback"], "integer").",".
						$ilDB->quote($rec2["status"], "text").
						")");
				}
			}
			$ilSetting->set("patch_stex_db", 8);
		}

		// STEP 9: 
		if ($db <= 8)
		{
			$ilDB->addIndex("exc_usr_tutor", array("obj_id"), "ob");
			$ilSetting->set("patch_stex_db", 9);
		}

		// STEP 10: 
		if ($db <= 9)
		{
			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$set2 = $ilDB->query("SELECT * FROM exc_usr_tutor ".
					" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
					);
				while ($rec2  = $ilDB->fetchAssoc($set2))
				{
					$ilDB->manipulate("INSERT INTO exc_mem_tut_status ".
						"(ass_id, mem_id, tut_id, download_time) VALUES (".
						$ilDB->quote($rec["id"], "integer").",".
						$ilDB->quote($rec2["usr_id"], "integer").",".
						$ilDB->quote($rec2["tutor_id"], "integer").",".
						$ilDB->quote($rec2["download_time"], "timestamp").
						")");
				}
			}
			$ilSetting->set("patch_stex_db", 10);
		}
		
		// STEP 11: 
		if ($db <= 10)
		{
			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$ilDB->manipulate("UPDATE exc_returned SET ".
					" ass_id = ".$ilDB->quote($rec["id"], "integer").
					" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
					);
			}
			$ilSetting->set("patch_stex_db", 11);
		}

		// STEP 12: 
		if ($db <= 11)
		{
			$ilDB->addTableColumn("exc_assignment",
				"title",
				array("type" => "text", "length" => 200, "notnull" => false));

			$ilDB->addTableColumn("exc_assignment",
				"start_time",
				array("type" => "integer", "length" => 4, "notnull" => false));

			$ilDB->addTableColumn("exc_assignment",
				"mandatory",
				array("type" => "integer", "length" => 1, "notnull" => false, "default" => 0));

			$ilSetting->set("patch_stex_db", 12);
		}

		// STEP 13: 
		if ($db <= 12)
		{
			$ilDB->addTableColumn("exc_data",
				"pass_mode",
				array("type" => "text", "length" => 8, "fixed" => false,
					"notnull" => true, "default" => "all"));

			$ilDB->addTableColumn("exc_data",
				"pass_nr",
				array("type" => "integer", "length" => 4, "notnull" => false));

			$ilSetting->set("patch_stex_db", 13);
		}

		// STEP 14: 
		if ($db <= 13)
		{
			$ilDB->addTableColumn("exc_assignment",
				"order_nr",
				array("type" => "integer", "length" => 4, "notnull" => true, "default" => 0));
			
			$ilSetting->set("patch_stex_db", 14);
		}

		// STEP 15: 
		if ($db <= 14)
		{
			$ilDB->addTableColumn("exc_data",
				"show_submissions",
				array("type" => "integer", "length" => 1, "notnull" => true, "default" => 0));
			
			$ilSetting->set("patch_stex_db", 15);
		}

		// STEP 16: 
		if ($db <= 15)
		{
			$new_ex_path = CLIENT_DATA_DIR."/ilExercise";
			
			$old_ex_path = CLIENT_DATA_DIR."/exercise";
			
			$old_ex_files = array();
			
			if (is_dir($old_ex_path))
			{
				$dh_old_ex_path = opendir($old_ex_path);
				
				// old exercise files into an assoc array to
				// avoid reading of all files each time
				
				while($file = readdir($dh_old_ex_path))
				{
					if(is_dir($old_ex_path."/".$file))
					{
						continue;
					}
					list($obj_id,$rest) = split('_',$file,2);
					$old_ex_files[$obj_id][] = array("full" => $file,
						"rest" => $rest);
				}
			}
//var_dump($old_ex_files);
			
			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				// move exercise files to assignment directories
				if (is_array($old_ex_files[$rec["exc_id"]]))
				{
					foreach ($old_ex_files[$rec["exc_id"]] as $file)
					{
						$old = $old_ex_path."/".$file["full"];
						$new = $new_ex_path."/".$this->createPathFromId($rec["exc_id"], "exc").
							"/ass_".$rec["id"]."/".$file["rest"];
							
						if (is_file($old))
						{
							ilUtil::makeDirParents(dirname($new));
							rename($old, $new);
//echo "<br><br>move: ".$old.
//	"<br>to: ".$new;
						}
					}
				}

				// move submitted files to assignment directories
				if (is_dir($old_ex_path."/".$rec["exc_id"]))
				{
					$old = $old_ex_path."/".$rec["exc_id"];
					$new = $new_ex_path."/".$this->createPathFromId($rec["exc_id"], "exc").
						"/subm_".$rec["id"];
					ilUtil::makeDirParents(dirname($new));
					rename($old, $new);
//echo "<br><br>move: ".$old.
//	"<br>to: ".$new;
				}
				
				//echo "<br>-".$rec["exc_id"]."-".$rec["id"]."-";
			}
			$ilSetting->set("patch_stex_db", 16);
		}
		
		
		// STEP 17:
		if ($db <= 16)
		{
			$ilDB->addTableColumn("exc_usr_tutor",
				"ass_id",
				array("type" => "integer", "length" => 4, "notnull" => false));
			
			$ilSetting->set("patch_stex_db", 17);
		}

		// STEP 18: 
		if ($db <= 17)
		{
			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$ilDB->manipulate("UPDATE exc_usr_tutor SET ".
					" ass_id = ".$ilDB->quote($rec["id"], "integer").
					" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
					);
			}
			$ilSetting->set("patch_stex_db", 18);
		}
		
		// STEP 19: 
		if ($db <= 18)
		{
			$this->reloadControlStructure();
			$ilSetting->set("patch_stex_db", 19);
		}
		
		// STEP 20: 
		if ($db <= 19)
		{
			$ilDB->addTableColumn("exc_mem_ass_status",
				"mark",
				array("type" => "text", "length" => 32, "notnull" => false));
			$ilDB->addTableColumn("exc_mem_ass_status",
				"u_comment",
				array("type" => "text", "length" => 1000, "notnull" => false));

			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$set2 = $ilDB->query("SELECT * FROM ut_lp_marks WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer"));
				while ($rec2 = $ilDB->fetchAssoc($set2))
				{
					$set3 = $ilDB->query("SELECT ass_id FROM exc_mem_ass_status WHERE ".
						"ass_id = ".$ilDB->quote($rec["id"], "integer").
						" AND usr_id = ".$ilDB->quote($rec2["usr_id"], "integer"));
					if ($rec3 = $ilDB->fetchAssoc($set3))
					{
						$ilDB->manipulate("UPDATE exc_mem_ass_status SET ".
							" mark = ".$ilDB->quote($rec2["mark"], "text").",".
							" u_comment = ".$ilDB->quote($rec2["u_comment"], "text").
							" WHERE ass_id = ".$ilDB->quote($rec["id"], "integer").
							" AND usr_id = ".$ilDB->quote($rec2["usr_id"], "integer")
							);
					}
					else
					{
						$ilDB->manipulate("INSERT INTO exc_mem_ass_status (ass_id, usr_id, mark, u_comment) VALUES (".
							$ilDB->quote($rec["id"], "integer").", ".
							$ilDB->quote($rec2["usr_id"], "integer").", ".
							$ilDB->quote($rec2["mark"], "text").", ".
							$ilDB->quote($rec2["u_comment"], "text").")"
							);
					}
				}
			}
			
			$ilSetting->set("patch_stex_db", 20);
		}

		// STEP 21: 
		if ($db <= 20)
		{
			$ilDB->dropPrimaryKey("exc_usr_tutor");
			$ilDB->addPrimaryKey("exc_usr_tutor",
				array("ass_id", "usr_id", "tutor_id"));
			$ilSetting->set("patch_stex_db", 21);
		}
		
		// STEP 22: 
		if ($db <= 21)
		{
			$ilDB->dropTable("exc_mem_tut_status");
			$ilSetting->set("patch_stex_db", 22);
		}
		
		// STEP 23: 
		if ($db <= 22)
		{
			$this->reloadControlStructure();
			$ilSetting->set("patch_stex_db", 23);
		}

		// keep this line at the end of the method
		$this->finalProcessing();
	}
	
	function createPathFromId($a_container_id,$a_name)
	{
		$max_exponent = 3;
		$st_factor = 100;
		
		$path = array();
		$found = false;
		$num = $a_container_id;
		for($i = $max_exponent; $i > 0;$i--)
		{
			$factor = pow($st_factor, $i);
			if(($tmp = (int) ($num / $factor)) or $found)
			{
				$path[] = $tmp;
				$num = $num % $factor;
				$found = true;
			}	
		}

		if(count($path))
		{
			$path_string = (implode('/',$path).'/');
		}
		return $path_string.$a_name.'_'.$a_container_id;
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
