<?php
    /* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
    
    /**
     * Data loader for load tests. Initializes an ILIAS client
     * with a number of users, objects etc.
     *
     * @author Alex Killing <alex.killing@gmx.de>
     * @version $Id$
     * @ingroup ingroup	ServicesLoadTest
     */
    class ilDataLoader
    {
    	
		/**
		 * Set enable log (currently echoes directly to the screen)
		 *
		 * @param	boolean	enable log
		 */
		function setEnableLog($a_val)
		{
			$this->enable_log = $a_val;
		}
		
		/**
		 * Get enable log
		 *
		 * @return	boolean	enable log
		 */
		function getEnableLog()
		{
			return $this->enable_log;
		}
		
		/**
		 * Log
		 *
		 * @param
		 * @return
		 */
		function log($a_str)
		{
			if ($this->getEnableLog())
			{
				echo "<br>".$a_str; flush();
			}
		}
		
		/**
		 * Generate Users
		 *
		 * @param
		 * @return
		 */
		function generateUsers($a_login_base = "learner", $a_start = 1,
			$a_end = 1000, $a_firstname = "John", $a_lastname_base = "Learner", $a_pw = "learnerpw",
			$a_email = "de@de.de", $a_gender = "m", $a_lang = "en")
		{
			global $rbacadmin;
			
			// new users
			$this->log("Creating Users");
			for ($i = $a_start; $i <= $a_end; $i++)
			{
				$this->log($a_login_base.$i);
				$user = new ilObjUser();
				$user->setLogin($a_login_base.$i);
				$user->setFirstname($a_firstname);
				$user->setLastname($a_lastname_base." ".$i);
				$user->setGender($a_gender);
				$user->setEmail($a_email);
				$user->setAgreeDate(ilUtil::now());
				$user->setPasswd($a_pw, IL_PASSWD_PLAIN);
				$user->setTitle($user->getFullname());
				$user->setDescription($user->getEmail());
				$user->setLastPasswordChangeTS( time() );
				$user->setActive(true);
				$user->setTimeLimitUnlimited(true);
				$user->create();
				$user->setLanguage($a_lang);
				$user->saveAsNew(false);
				$user->writePrefs();
				$rbacadmin->assignUser(4, $user->getId(),true);
			}
		}
		
		/**
		 * Generate Categories
		 *
		 * @param
		 * @return
		 */
		function generateCategories($a_start = 1, $a_end = 500, $a_sub_cats_per_cat = 10, $a_title_base = "Category",
			$a_node = "", $a_init_cnt = true, $a_depth = 1)
		{
			global $tree;
						
			include_once("./Modules/Category/classes/class.ilObjCategory.php");
			
			if ($a_init_cnt)
			{
				$this->item_cnt = $a_start;
				$this->max_depth = ceil(log($a_end - $a_start, $a_sub_cats_per_cat));
			}
			
			if ($a_depth > $this->max_depth)
			{
				return;
			}

			if ($a_node == "")
			{
				$a_node = $tree->getRootId();
				$this->log("Creating Categories");
			}

			$sub_cat_cnt = 0;
			$sub_cats = array();
			while ($sub_cat_cnt < $a_sub_cats_per_cat && $this->item_cnt <= $a_end)
			{
				if ($this->item_cnt <= $a_end)
				{
					$this->log($a_title_base." ".$this->item_cnt);
					$new_cat = new ilObjCategory();
					$new_cat->setTitle($a_title_base." ".$this->item_cnt);
					$new_cat->create();
					$new_cat->createReference();
					$new_cat->putInTree($a_node);
					$new_cat->setPermissions($a_node);
					$sub_cats[] = $new_cat;
					$sub_cat_cnt++;
					$this->item_cnt++;
				}
			}
			
			foreach ($sub_cats as $sub_cat)
			{
				$this->generateCategories($this->item_cnt, $a_end, $a_sub_cats_per_cat, $a_title_base,
					$sub_cat->getRefId(), false, $a_depth + 1);
			}
		}
		
		/**
		 * Generate courses
		 *
		 * @param
		 * @return
		 */
		function generateCourses($a_start = 1, $a_end = 500, $a_course_per_cat = 10,
			$a_title_base = "Course")
		{
			global $tree;
			
			include_once("./Modules/Course/classes/class.ilObjCourse.php");

			$this->log("Creating Courses");
			
			$a_current = $a_start;
			
			// how many categories do we need?
			$needed_cats = ceil(($a_end - $a_start + 1) / $a_course_per_cat);
		
			// get all categories and sort them by depth
			$nodes = $tree->getFilteredSubTree($tree->getRootId(), array("adm", "crs", "fold", "grp"));
			$nodes = ilUtil::sortArray($nodes, "depth", "desc");
			
			foreach ($nodes as $node)
			{
				if ($node["type"] == "cat" && $a_current <= $a_end)
				{
					for ($i = 1; $i <= $a_course_per_cat; $i++)
					{
						if ($a_current <= $a_end)
						{
							$this->log($a_title_base." ".$a_current);
							$new_crs = new ilObjCourse();
							$new_crs->setTitle($a_title_base." ".$a_current);
							$new_crs->create();
							$new_crs->createReference();
							$new_crs->putInTree($node["child"]);
							$new_crs->setPermissions($node["child"]);
							
							$a_current++;
						}
					}
				}
			}			
		}
		
		/**
		 * Generate files
		 *
		 * @param
		 * @return
		 */
		function generateFiles($a_test_file, $a_files_per_course = 10, $a_title_base = "File")
		{
			global $tree;
			
			include_once("./Modules/File/classes/class.ilObjFile.php");

			$this->log("Creating Files");
			
			$a_current = $a_start;
					
			// get all categories and sort them by depth
			$crs_ref_ids = ilUtil::_getObjectsByOperations("crs", "read",
				0, $limit = 1000000);
			$cnt = 1;
			foreach ($crs_ref_ids as $rid)
			{
				for ($i = 1; $i <= $a_files_per_course; $i++)
				{
					$this->log($a_title_base." ".$cnt);
					$fileObj = new ilObjFile();
					$fileObj->setTitle($a_title_base." ".$cnt);
					$fileObj->setFileName("file_".$cnt.".txt");
					$fileObj->create();
					$fileObj->createReference();
					$fileObj->putInTree($rid);
					$fileObj->setPermissions($rid);
					$fileObj->createDirectory();
					$fileObj->getUploadFile($a_test_file,
						"file_".$cnt.".txt");
					$cnt++;

				}
			}
		}
		
		/**
		 * Generate Calendar Entries
		 *
		 * @param
		 * @return
		 */
		function generateCalendarEntries($a_num_per_course = 10)
		{	
			include_once("./Services/Calendar/classes/class.ilDateTime.php");
			include_once("./Services/Calendar/classes/class.ilCalendarEntry.php");
			include_once("./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php");
			include_once("./Services/Calendar/classes/class.ilCalendarCategories.php");
			
			$this->log("Creating Calendar Entries");
		
			$crs_ref_ids = ilUtil::_getObjectsByOperations("crs", "read",
				0, $limit = 1000000);
			$cnt = 1;
			foreach ($crs_ref_ids as $rid)
			{
				$obj_id = ilObject::_lookupObjId($rid);
				$cat_id = ilCalendarCategories::_lookupCategoryIdByObjId($obj_id);
				
				$start = new ilDate(time(),IL_CAL_UNIX);
				$end = new ilDate(time(),IL_CAL_UNIX);
				$end->increment(IL_CAL_HOUR, 2);
				
				for ($i = 1; $i <= $a_num_per_course; $i++)
				{
					$this->log("Event ".$cnt);
					$entry = new ilCalendarEntry();
					$entry->setStart($start);		//ilDateTiem
					$entry->setEnd($end);		//ilDateTiem
					$entry->setFullday(false);		//ilDateTiem
					$entry->setTitle("Event ".$cnt);		//ilDateTiem
					$entry->save();
					$id = $entry->getEntryId();
					$ass = new ilCalendarCategoryAssignments($id);
					$ass->addAssignment($cat_id);
					$start->increment(IL_CAL_DAY, 1);
					$end->increment(IL_CAL_DAY, 1);
//echo "-$cat_id-";
//echo "+".ilDatePresentation::formatDate($start)."+";
					
					$cnt++;
				}
			}
		}
		
		/**
		 * Load SQL Template
		 *
		 * @param
		 * @return
		 */
		function loadSqlTemplate($file)
		{
			global $ilDB;

			// mysql
			if ($ilDB->getDBType() == "mysql")
			{
				$fp = fopen($file, 'r');
				if (!$fp)
				{
					$this->log("Error reading file $file.");
					return;
				}
				$this->log("Dropping Tables.");
				$set = $ilDB->query("SHOW tables");
				while ($rec = $ilDB->fetchAssoc($set))
				{
					foreach ($rec as $v)
					{
						$ilDB->query("DROP TABLE ".$v);
					}
				}
		
				$this->log("Read Dump.");
				while(!feof($fp))
				{
					//$line = trim(fgets($fp, 200000));
					$line = trim($this->getline($fp, "\n"));
		
					if ($line != "" && substr($line,0,1)!="#"
						&& substr($line,0,1)!="-")
					{
						//take line per line, until last char is ";"
						if (substr($line,-1)==";")
						{
							//query is complete
							$q .= " ".substr($line,0,-1);
							$r = $ilDB->query($q);
							if (mysql_errno() > 0)
							{
								echo "<br />ERROR: ".mysql_error().
									"<br />SQL: $q";
								return false;
							}
							unset($q);
							unset($line);
						} //if
						else
						{
							$q .= " ".$line;
						} //else
					} //if
				} //for
				
				fclose($fp);
			}
			
			if ($ilDB->getDBType() == "oracle")
			{
				include_once("./setup/sql/ilDBTemplate.php");
				setupILIASDatabase();
			}
		}

		/**
		 * Get line from file
		 */
		function getline( $fp, $delim )
		{
			$result = "";
			while( !feof( $fp ) )
			{
				$tmp = fgetc( $fp );
				if( $tmp == $delim )
					return $result;
				$result .= $tmp;
			}
			return $result;
		}

		/**
		 * Create dump
		 *
		 * @param
		 */
		function createDump($a_target_file, $a_dump_cmd, $a_db_user = "root",
			$a_db_pw = "", $a_db_name = "loadtest")
		{
			$this->log("Writing Dump");
			$a_pw = ($a_pwd == "")
				? ""
				: " -p".$a_pw;
			$cmd = $a_dump_cmd." -u".$a_db_user." ".$a_pw." --skip-lock-tables -Q --extended-insert=FALSE --default-character-set=utf8".
				" --add-drop-table=FALSE --add-locks=FALSE  --compatible=mysql40 ".$a_db_name." > ".$a_target_file;
			if (!is_file($a_target_file))
			{
				$this->log("Something went wrong when writing the dump:<br>".$cmd);
			}
		}
		
		/**
		 * Write User CSV
		 *
		 * @param
		 * @return
		 */
		function writeUserCsv($a_target_file, $a_user_base_name = "learner")
		{
			global $ilDB;
			
			$this->log("Writing User CSV");
			$set = $ilDB->query("SELECT * FROM usr_data");
			$file = fopen($a_target_file, "w");
			if ($file)
			{
				while ($rec  = $ilDB->fetchAssoc($set))
				{
					if (substr($rec["login"], 0, strlen($a_user_base_name)) == $a_user_base_name)
					{
						fwrite($file, $rec["login"]."\n");
					}
				}
				fclose($file);
			}
			else
			{
				$this->log("Could not write USer CSV.");
			}
				
		}
		
		/**
		 * Write Course CSV
		 *
		 * @param
		 * @return
		 */
		function writeCourseCsv($a_target_file)
		{
			global $ilDB;
			
			$this->log("Writing Course CSV");
			$crs_ref_ids = ilUtil::_getObjectsByOperations("crs", "read",
				0, $limit = 1000000);
			$file = fopen($a_target_file, "w");
			if ($file)
			{
				foreach ($crs_ref_ids as $r)
				{
					fwrite($file, $r."\n");
				}
				fclose($file);
			}
			else
			{
				$this->log("Could not write Course CSV.");
			}
				
		}

		/**
		 * Write Category CSV
		 *
		 * @param
		 * @return
		 */
		function writeCategoryCsv($a_target_file)
		{
			global $ilDB;
			
			$this->log("Writing Category CSV");
			$cat_ref_ids = ilUtil::_getObjectsByOperations("cat", "read",
				0, $limit = 1000000);
			$file = fopen($a_target_file, "w");
			if ($file)
			{
				foreach ($cat_ref_ids as $r)
				{
					fwrite($file, $r."\n");
				}
				fclose($file);
			}
			else
			{
				$this->log("Could not write Category CSV.");
			}
				
		}
		
		/**
		 * Assign users as course members
		 *
		 */
		function assignUsersAsCourseMembers($a_user_login_base = "learner",
			$a_start = 1, $a_end = 100)
		{
			global $ilDB;
			
			$this->log("Assigning Course Members");
			$set = $ilDB->query("SELECT usr_id, login FROM usr_data WHERE ".
				" login LIKE ".$ilDB->quote($a_user_login_base."%", "text")
				);
			$user_ids = array();
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$rest = substr($rec["login"], strlen($a_user_login_base));
				if (is_numeric($rest) &&
					((int) $rest >= $a_start && (int) $rest <= $a_end))
				{
					$user_ids[] = $rec["usr_id"];
				}
			}
			$cnt = 1;
			$crs_ref_ids = ilUtil::_getObjectsByOperations("crs", "read",
				0, $limit = 1000000);
			include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
			foreach ($crs_ref_ids as $r)
			{
				$crs_id = ilObject::_lookupObjId($r);
				$mem_obj = ilCourseParticipants::_getInstanceByObjId($crs_id);
				foreach ($user_ids as $u)
				{
					$this->log("$cnt: add user $u as member to course ".$crs_id);				
					$mem_obj->add($u, 1);
					$cnt++;
				}
			}
		}
		
		/**
		* Remove all desktop items
		*/
		function removeAllDesktopItems()
		{
			global $ilDB;
			
			$this->log("Remove Desktop Items.");
			$ilDB->manipulate("DELETE FROM desktop_item");
		}
		
		/**
		* Deactivate calendars on personal desktops
		*/
		function deactivateCalendarsOnPersonalDesktop()
		{
			global $ilDB;
			
			$this->log("Deactivate calendars on personal desktop.");
			$set = $ilDB->query("SELECT * FROM usr_data");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				$ilDB->manipulate("REPLACE INTO il_block_setting ".
					"(type, user_id, block_id, setting, value) VALUES (".
					$ilDB->quote("pdcal", "text").",".
					$ilDB->quote($rec["usr_id"], "integer").",".
					$ilDB->quote(0, "integer").",".
					$ilDB->quote("detail", "text").",".
					$ilDB->quote(0, "integer").
					")");
			}
		}
		
		
	}
?>