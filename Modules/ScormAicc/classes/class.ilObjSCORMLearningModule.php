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


require_once "classes/class.ilObject.php";
require_once "./Modules/ScormAicc/classes/class.ilObjSCORMValidator.php";
require_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";

/**
* Class ilObjSCORMLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMLearningModule extends ilObjSAHSLearningModule
{
	var $validator;
//	var $meta_data;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSCORMLearningModule($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "sahs";
		parent::ilObject($a_id,$a_call_by_reference);
	}


	/**
	* Validate all XML-Files in a SCOM-Directory
	*
	* @access       public
	* @return       boolean true if all XML-Files are wellfomred and valid
	*/
	function validate($directory)
	{
		$this->validator =& new ilObjSCORMValidator($directory);
		$returnValue = $this->validator->validate();
		return $returnValue;
	}

	function getValidationSummary()
	{
		if(is_object($this->validator))
		{
			return $this->validator->getSummary();
		}
		return "";
	}

	function getTrackingItems()
	{
		return ilObjSCORMLearningModule::_getTrackingItems($this->getId());
	}


	/**
	* get all tracking items of scorm object
	* @access static
	*/
	function _getTrackingItems($a_obj_id)
	{
		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");
		$tree = new ilSCORMTree($a_obj_id);
		$root_id = $tree->readRootId();

		$items = array();
		$childs = $tree->getSubTree($tree->getNodeData($root_id));
		foreach($childs as $child)
		{
			if($child["type"] == "sit")
			{
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
				$sc_item =& new ilSCORMItem($child["obj_id"]);
				if ($sc_item->getIdentifierRef() != "")
				{
					$items[count($items)] =& $sc_item;
				}
			}
		}

		return $items;
	}

	/**
	* read manifest file
	* @access	public
	*/
	function readObject()
	{
		// the seems_utf8($str) function
		include_once("include/inc.utf8checker.php");
		$needs_convert = false;

		// convert imsmanifest.xml file in iso to utf8 if needed
		// include_once("include/inc.convertcharset.php");
		$manifest_file = $this->getDataDirectory()."/imsmanifest.xml";

		// check if manifestfile exists and space left on device...
		$check_for_manifest_file = is_file($manifest_file);

		// if no manifestfile
		if (!$check_for_manifest_file)
		{
			$this->ilias->raiseError($this->lng->txt("Manifestfile $manifest_file not found!"), $this->ilias->error_obj->MESSAGE);
			return;
		}

		if ($check_for_manifest_file)
		{
			$manifest_file_array = file($manifest_file);
			foreach($manifest_file_array as $mfa)
			{
				if (seems_not_utf8($mfa))
				{
					$needs_convert = true;
					break;
				}
			}

			// to copy the file we need some extraspace, counted in bytes *2 ... we need 2 copies....
			$estimated_manifest_filesize = filesize($manifest_file) * 2;
			
			// i deactivated this, because it seems to fail on some windows systems (see bug #1795)
			//$check_disc_free = disk_free_space($this->getDataDirectory()) - $estimated_manifest_filesize;
			$check_disc_free = 2;
		}

		// if $manifest_file needs to be converted to UTF8
		if ($needs_convert)
		{
			// if file exists and enough space left on device
			if ($check_for_manifest_file && ($check_disc_free > 1))
			{

				// create backup from original
				if (!copy($manifest_file, $manifest_file.".old"))
				{
					echo "Failed to copy $manifest_file...<br>\n";
				}

				// read backupfile, convert each line to utf8, write line to new file
				// php < 4.3 style
				$f_write_handler = fopen($manifest_file.".new", "w");
				$f_read_handler = fopen($manifest_file.".old", "r");
				while (!feof($f_read_handler))
				{
					$zeile = fgets($f_read_handler);
					//echo mb_detect_encoding($zeile);
					fputs($f_write_handler, utf8_encode($zeile));
				}
				fclose($f_read_handler);
				fclose($f_write_handler);

				// copy new utf8-file to imsmanifest.xml
				if (!copy($manifest_file.".new", $manifest_file))
				{
					echo "Failed to copy $manifest_file...<br>\n";
				}

				if (!@is_file($manifest_file))
				{
					$this->ilias->raiseError($this->lng->txt("cont_no_manifest"),
					$this->ilias->error_obj->WARNING);
				}
			}
			else
			{
				// gives out the specific error

				if (!($check_disc_free > 1))
					$this->ilias->raiseError($this->lng->txt("Not enough space left on device!"),$this->ilias->error_obj->MESSAGE);
					return;
			}

		}
		else
		{
			// check whether file starts with BOM (that confuses some sax parsers, see bug #1795)
			$hmani = fopen($manifest_file, "r");
			$start = fread($hmani, 3);
			if (strtolower(bin2hex($start)) == "efbbbf")
			{
				$f_write_handler = fopen($manifest_file.".new", "w");
				while (!feof($hmani))
				{
					$n = fread($hmani, 900);
					fputs($f_write_handler, $n);
				}
				fclose($f_write_handler);
				fclose($hmani);

				// copy new utf8-file to imsmanifest.xml
				if (!copy($manifest_file.".new", $manifest_file))
				{
					echo "Failed to copy $manifest_file...<br>\n";
				}
			}
			else
			{
				fclose($hmani);
			}
		}

		//validate the XML-Files in the SCORM-Package
		if ($_POST["validate"] == "y")
		{
			if (!$this->validate($this->getDataDirectory()))
			{
				$this->ilias->raiseError("<b>Validation Error(s):</b><br>".$this->getValidationSummary(),
					$this->ilias->error_obj->WARNING);
			}
		}

		// start SCORM package parser
		include_once ("./Modules/ScormAicc/classes/SCORM/class.ilSCORMPackageParser.php");
		// todo determine imsmanifest.xml path here...
		$slmParser = new ilSCORMPackageParser($this, $manifest_file);
		$slmParser->startParsing();
		return $slmParser->getPackageTitle();
	}


	/**
	* get all tracked items of current user
	*/
	function getTrackedItems()
	{
		global $ilDB, $ilUser;
		
		$sco_set = $ilDB->queryF('
		SELECT DISTINCT sco_id FROM scorm_tracking WHERE obj_id = %s', 
		array('integer'),array($this->getId()));

		$items = array();
		while($sco_rec = $ilDB->fetchAssoc($sco_set))
		{
			include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
			$sc_item =& new ilSCORMItem($sco_rec["sco_id"]);
			if ($sc_item->getIdentifierRef() != "")
			{
				$items[count($items)] =& $sc_item;
			}
		}

		return $items;
	}
	
	/**
	* Return the last access timestamp for a given user
	*
	* @param	int		$a_obj_id		object id
	* @param	int		$user_id		user id
	* @return timestamp
	*/
	public static function _lookupLastAccess($a_obj_id, $a_usr_id)
	{
		global $ilDB;

		$result = $ilDB->queryF('
		SELECT user_id,c_timestamp last_access FROM scorm_tracking 
		WHERE  obj_id = %s
		AND user_id = %s
		ORDER BY c_timestamp DESC',
		array('integer','integer'), array($a_obj_id,$a_usr_id));
		
		if ($ilDB->numRows($result))
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["last_access"];
		}
		return "";		
	}

	function getTrackedUsers($a_search)
	{
		global $ilUser, $ilDB, $ilUser;

		$sco_set =  $ilDB->queryF('
		SELECT user_id,MAX(c_timestamp) last_access FROM scorm_tracking 
		WHERE  obj_id = %s
		GROUP BY user_id',
		array('integer'), array($this->getId()));
		
		$items = array();
		while($sco_rec = $ilDB->fetchAssoc($sco_set))
		{	
			if ($sco_rec['last_access'] != 0) {
				$sco_rec['last_access'] = ilDatePresentation::formatDate(new ilDateTime($sco_rec['last_access'],IL_CAL_DATETIME));
			} else {
				$sco_rec['last_access'] = "";
			}	
				
			if (ilObject::_exists($sco_rec['user_id']) && ilObject::_lookUpType($sco_rec["user_id"])=="usr" ) {	
				$user = new ilObjUser($sco_rec['user_id']);
				//$sco_rec['status'] = $this->getStatusForUser($sco_rec["user_id"]);
				$sco_rec['version'] = $this->getModuleVersionForUser($sco_rec["user_id"]);
				$sco_rec['attempts'] = $this->getAttemptsForUser($sco_rec["user_id"]);
				$sco_rec['username'] =  $user->getLastname().", ".$user->getFirstname();
				if ($a_search != "" && (strpos(strtolower($user->getLastname()), strtolower($a_search)) !== false || strpos(strtolower($user->getFirstname()), strtolower($a_search)) !== false ) ) {
					$items[] = $sco_rec;
				} else if ($a_search == "") {
					$items[] = $sco_rec;
				}
				
			}
		}

		return $items;
	}
	
	/**
	* get number of atttempts for a certain user and package
	*/
	function getAttemptsForUser($a_user_id){
		global $ilDB;

		$val_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE user_id = %s
		AND sco_id = %s
		AND lvalue = %s
		AND obj_id = %s',
		array('integer','integer','text','integer'),
		array($a_user_id,0,'package_attempts',$this->getId()));
		
		$val_rec = $ilDB->fetchAssoc($val_set);
		
		$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
		if ($val_rec["rvalue"] == null) {
			$val_rec["rvalue"]="";
		}
		return $val_rec["rvalue"];
	}
	
	
	/**
	* get module version that tracking data for a user was recorded on
	*/
	function getModuleVersionForUser($a_user_id){
		global $ilDB;
		
		$val_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE user_id = %s
		AND sco_id = %s
		AND lvalue = %s
		AND obj_id = %s',
		array('integer','integer','text','integer'),
		array($a_user_id,0,'module_version',$this->getId()));
		
		$val_rec = $ilDB->fetchAssoc($val_set);		
		
		
		$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
		if ($val_rec["rvalue"] == null) {
			$val_rec["rvalue"]="";
		}
		return $val_rec["rvalue"];
	}
	
	function getTrackingDataPerUser($a_sco_id, $a_user_id)
	{
		global $ilDB;

		$data_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE user_id = %s
		AND sco_id = %s
		AND obj_id = %s
		ORDER BY lvalue',
		array('integer','integer','integer'),
		array($a_user_id,$a_sco_id,$this->getId()));
			
		$data = array();
		while($data_rec = $ilDB->fetchAssoc($data_set))	
		
		{
			$data[] = $data_rec;
		}

		return $data;
	}

	function getTrackingDataAgg($a_user_id)
	{
		global $ilDB;

		// get all users with any tracking data
		$sco_set = $ilDB->queryF('
		SELECT DISTINCT sco_id FROM scorm_tracking 
		WHERE obj_id = %s
		AND user_id = %s
		AND sco_id <> %s',
		array('integer','integer','integer'),
		array($this->getId(),$a_user_id,0));

		$data = array();
		while($sco_rec = $ilDB->fetchAssoc($sco_set))		
		{
			$data_set = $ilDB->queryF('
			SELECT * FROM scorm_tracking 
			WHERE  obj_id = %s
			AND sco_id = %s
			AND user_id = %s 
			AND lvalue <> %s
			AND (lvalue = %s
				OR lvalue = %s
				OR lvalue = %s)',
			array('integer','integer','integer','text','text','text','text'),
			array($this->getId(),
				$sco_rec["sco_id"],
				$a_user_id,
				"package_attempts",
				"cmi.core.lesson_status",
				"cmi.core.total_time",
				"cmi.core.score.raw")
			);
			
			$score = $time = $status = "";
			
			while($data_rec = $ilDB->fetchAssoc($data_set))
			{
				switch($data_rec["lvalue"])
				{
					case "cmi.core.lesson_status":
						$status = $data_rec["rvalue"];
						break;

					case "cmi.core.total_time":
						$time = $data_rec["rvalue"];
						break;

					case "cmi.core.score.raw":
						$score = $data_rec["rvalue"];
						break;
				}
			}
			//create sco_object
			$sc_item =& new ilSCORMItem($sco_rec["sco_id"]);
			$data[] = array("sco_id"=>$sco_rec["sco_id"], "title" => $sc_item->getTitle(),
			"score" => $score, "time" => $time, "status" => $status);
				
		}

		return $data;
	}

	function getTrackingDataAggSco($a_sco_id)
	    {
	        global $ilDB;

	        // get all users with any tracking data
	        $user_set = $ilDB->queryF('
	        SELECT DISTINCT user_id FROM scorm_tracking 
	        WHERE obj_id = %s
	        AND sco_id = %s',
	        array('integer','integer'),
	        array($this->getId(),$a_sco_id));

	        $data = array();
	        while($user_rec = $ilDB->fetchAssoc($user_set))
	        {

	            $data_set = $ilDB->queryF('
	            SELECT * FROM scorm_tracking 
	            WHERE obj_id = %s
	            AND sco_id = %s
	            AND user_id = %s
	            AND (lvalue = %s
	            OR lvalue = %s
	            OR lvalue = %s)',
				array('integer','integer','integer','text','text','text'),
				array($this->getId(),
					$a_sco_id,
					$user_rec["user_id"],
					"cmi.core.lesson_status",
					"cmi.core.total_time",
					"cmi.core.score.raw")
				);
				
	      	  	$score = $time = $status = "";
	      	  	
	            while($data_rec = $ilDB->fetchAssoc($data_set))
	            {
	                switch($data_rec["lvalue"])
	                {
	                    case "cmi.core.lesson_status":
	                        $status = $data_rec["rvalue"];
	                        break;

	                    case "cmi.core.total_time":
	                        $time = $data_rec["rvalue"];
	                        break;

	                    case "cmi.core.score.raw":
	                        $score = $data_rec["rvalue"];
	                        break;
	                }
	            }

	            $data[] = array("user_id" => $user_rec["user_id"],
	                "score" => $score, "time" => $time, "status" => $status);
	        }

	        return $data;
	    }	
	
	function exportSelectedRaw($a_exportall=0, $a_user = array())  {
		
		global $ilDB, $ilUser;
		
		$csv = null;
		
		$user_array = array();
		
		if(!isset($_POST['user']) && $a_exportall == 0)
		{
			$this->ilias->raiseError($this->lng->txt('no_checkbox'), $this->ilias->error_obj->MESSAGE);
		}
		
		if($a_exportall == 1)
		{
			$res = $ilDB->queryF(
				'SELECT user_id FROM scorm_tracking WHERE obj_id = %s GROUP BY user_id',
				array('integer'),
				array($this->getId())
			);
			while($row = $ilDB->fetchAssoc($res))
			{
			 	$user_array[] = $row['user_id'];
			}			
		}
		else
		{
			$user_array = $a_user;
		}
		
		//loop through users and get all data
		foreach($user_array as $user)
		{
			//get user e-mail
			if(ilObject::_exists($user) && ilObject::_lookUpType($user) == 'usr')
			{
				$e_user = new ilObjUser($user);
				$email = $e_user->getEmail();
				
				//get sco related information
				$query = 'SELECT rvalue, lvalue, identifierref, c_timestamp '
				       . 'FROM scorm_tracking ' 
				       . 'INNER JOIN sc_item ON sc_item.obj_id = scorm_tracking.sco_id ' 
					   . 'WHERE scorm_tracking.sco_id <> 0 AND user_id = %s AND scorm_tracking.obj_id = %s';
				$res = $ilDB->queryF(
					$query,
					array('integer', 'integer'),
					array($user, $this->getId())
				);
				while($row = $ilDB->fetchAssoc($res))
				{
					//get mail address for user-id
					$sco_id = $row['identifierref'];
					$key = $row['lvalue'];
					$value = $row['rvalue'];
					$timestamp = $row['c_timestamp'];
					$csv = $csv. "$sco_id;$key;$value;$email;$timestamp;$user\n";				
				}
				
				//get sco unrelated information
				$query = 'SELECT rvalue, lvalue, c_timestamp FROM scorm_tracking ' 
					   . 'WHERE sco_id = 0 AND user_id = %s AND obj_id = %s';
				$res = $ilDB->queryF(
					$query,
					array('integer', 'integer'),
					array($user, $this->getId())
				);
				while($row = $ilDB->fetchAssoc($res))
				{
					$key = $row['lvalue'];
					$value = $row['rvalue'];
					$timestamp = $row['c_timestamp'];
					$csv = $csv. "0;$key;$value;$email;$timestamp;$user\n";	
				}
		 	}
		}
		$header = "Scoid;Key;Value;Email;Timestamp;Userid\n";
		$this->sendExportFile($header, $csv);
	}
	
	
	function exportSelected($a_exportall = 0, $a_user = array())
	{
		global $ilDB, $ilUser;
			
		$scos = array();
		
		//get all SCO's of this object
		$query = 'SELECT scorm_object.obj_id, scorm_object.title, '
			   . 'scorm_object.c_type, scorm_object.slm_id, scorm_object.obj_id scoid  '
			   . 'FROM scorm_object, sc_item, sc_resource '
			   . 'WHERE (scorm_object.slm_id = %s '
			   . 'AND scorm_object.obj_id = sc_item.obj_id '
			   . 'AND sc_item.identifierref = sc_resource.import_id '
			   . 'AND sc_resource.scormtype = %s) '
			   . 'GROUP BY scorm_object.obj_id, scorm_object.title, scorm_object.c_type,  '
			   . 'scorm_object.slm_id, scorm_object.obj_id ';		
		$res = $ilDB->queryF(
			$query,
			array('integer', 'text'),
			array($this->getId(), 'sco')
		);				
		while($row = $ilDB->fetchAssoc($res))
		{
			$scos[] = $row['scoid'];
		}
		
		$csv = null;
		
		//a module is completed when all SCO's are completed
		$user_array = array();
		
		if($a_exportall == 1)
		{
			$query = 'SELECT user_id FROM scorm_tracking WHERE obj_id = %s GROUP BY user_id';
			$res = $ilDB->queryF(
				$query,
				array('integer'),
				array($this->getId())
			);
			while($row = $ilDB->fetchAssoc($res))
			{
			 	$user_array[] = $row['user_id'];
			}
		}
		else
		{
			$user_array = $a_user;
		}
			
		foreach($user_array as $user)
		{
			$scos_c = $scos;
			//copy SCO_array
			//check if all SCO's are completed
			for($i = 0; $i < count($scos); $i++)
			{
				$query = 'SELECT * FROM scorm_tracking '
					   . 'WHERE (user_id = %s AND obj_id = %s AND sco_id = %s '
					   . 'AND (	(lvalue = %s AND rvalue = %s) OR (lvalue = %s AND rvalue = %s) ) )';
				$res = $ilDB->queryF(
					$query,
					array('integer', 'integer', 'integer', 'text', 'text', 'text', 'text'),
					array($user, $this->getId(), $scos[$i], 'cmi.core.lesson_status', 'completed', 'cmi.core.lesson_status', 'passed')
				);
				$data = $ilDB->fetchAssoc($res);
				if(is_array($data) && count($data))
				{
					//delete from array
					$key = array_search($scos[$i], $scos_c); 
					unset($scos_c[$key]);
				}
			}
			
			//check for completion
			if(count($scos_c) == 0)
			{
				$completion = 1;
			}
			else
			{
				$completion = 0;
			}
			
			if(ilObject::_exists($user) && ilObject::_lookUpType($user) == 'usr')
			{	
				//write export entry
				$e_user = new ilObjUser($user);
				$login = $e_user->getLogin();
				$firstname = $e_user->getFirstname();
				$lastname = $e_user->getLastname();
				$email = $e_user->getEmail();
				$department = $e_user->getDepartment();
						
				//get the date for csv export		
				$query = 'SELECT MAX(c_timestamp) exp_date '
					   . 'FROM scorm_tracking ' 
					   . 'WHERE user_id = %s AND obj_id = %s';
				$res = $ilDB->queryF(
					$query,
					array('integer', 'integer'),
					array($user, $this->getId())
				);
				$data = $ilDB->fetchAssoc($res);
				if(is_array($data) && count($data))
				{
					$validDate = false;
					
					$datetime = explode(' ', $data['exp_date']);
					if(count($datetime) == 2)
					{						
						$date = explode('-', $datetime[0]);
						if(count($date) == 3 && checkdate($date[1], $date[2], $date[0]))
							$validDate = true;			
					}
					
					if($validDate)
						$date = date('d.m.Y', strtotime($data['exp_date']));
					else
						$date = '';
				}
				else
				{
					$date = '';
				}	
				$csv = $csv. "$department;$login;$lastname;$firstname;$email;$date;$completion\n";
			}		
		}
		$header = "Department;Login;Lastname;Firstname;Email;Date;Status\n";
		$this->sendExportFile($header, $csv);		
	}
	
	
	function importTrackingData($a_file)
	{
		global $ilDB, $ilUser;
		
		$error = 0;
		//echo file_get_contents($a_file);
		$method = null;
		
		//lets import
		$fhandle = fopen($a_file, "r");
		
		//the top line is the field names
		$fields = fgetcsv($fhandle, 4096, ';');
		//lets check the import method
		fclose($fhandle);
	   
		switch($fields[0])
		{
			case "Scoid": 
				$error = $this->importRaw($a_file);
				break;
			case "Department":
				$error = $this->importSuccess($a_file);
				break;
			default:
				return -1;
				break;
		}
		return $error;
	}
	
		function importSuccess($a_file) {
		
		global $ilDB, $ilUser;
		
		$scos = array();
		//get all SCO's of this object

	    $val_set = $ilDB->queryF('
		    SELECT 	scorm_object.obj_id, 
		    		scorm_object.title, 
		    		scorm_object.c_type,
		    		scorm_object.slm_id, 
		    		scorm_object.obj_id scoid 
		    FROM scorm_object,sc_item,sc_resource
		    WHERE(scorm_object.slm_id = %s
		    AND scorm_object.obj_id=sc_item.obj_id 
		    AND sc_item.identifierref = sc_resource.import_id 
		    AND sc_resource.scormtype = %s
		    GROUP BY scorm_object.obj_id,
		    		scorm_object.title,
		    		scorm_object.c_type,
		    		scorm_object.slm_id,
		    		scorm_object.obj_id ',
		    array('integer','text'),
		    array($this->getId(),'sco')
	    );
	    
 		if (count($val_set)<1)
		{
			return -1;
		}			
		while($rows_sco = $ilDB->fetchAssoc($val_set))
		{
			array_push($scos,$rows_sco['scoid']);
		}
		
		$fhandle = fopen($a_file, "r");

		$obj_id = $ilDB->quote($this->getID());

		$fields = fgetcsv($fhandle, 4096, ';');

		while(($csv_rows = fgetcsv($fhandle, 4096, ";")) !== FALSE) {
			$data = array_combine($fields, $csv_rows);
			  //check the format
			  $statuscheck = 0;
			  if (count($csv_rows) == 6) {$statuscheck = 1;}
			
			  if ($this->get_user_id($data["Login"])>0) {
					
				$user_id = $ilDB->quote($this->get_user_id($data["Login"]));
				$import = $data["Status"];
				if ($import == "") {$import = 1;}
					//iterate over all SCO's
					if ($import == 1) {
						foreach ($scos as $sco) 
						{
							$sco_id = $ilDB->quote($sco);

							$date_ex = explode('.', $data['Date']);
							$date = implode('-',$date_ex[2],$date_ex[1],$date_ex[0]);
							
							$statement = $ilDB->queryF('
								SELECT * FROM scorm_tracking 
								WHERE user_id = %s
								AND sco_id = %s 
								AND lvalue = %s
								AND obj_id = %s',
								array('integer','integer','text','integer'),
								array($user_id, $sco_id, 'cmi.core.lesson_status',$obj_id)
							);
							if($ilDB->numRows($statement) > 0)
							{
								$val_set = $ilDB->manipulateF('
								UPDATE scorm_tracking
								SET rvalue = %s,
									c_timestamp = %s
								WHERE user_id = %s
								AND sco_id = %s 
								AND lvalue = %s
								AND obj_id = %s',
								array('text','timestamp','integer','integer','text','integer'),
								array('completed',$date ,$user_id, $sco_id, 'cmi.core.lesson_status',$obj_id)
								);
							}
							else
							{
								$val_set = $ilDB->manipulateF('
								INSERT INTO scorm_tracking
								(obj_id,user_id,sco_id,lvalue,rvalue,c_timestamp)
								VALUES (%s,%s,%s,%s,%s,%s)',
								array('integer','integer','integer','text','text','timestamp'),
								array($obj_id,$user_id,$sco,'cmi.core.lesson_status','completed',$date));
							}
							
						
							$statement = $ilDB->queryF('
								SELECT * FROM scorm_tracking 
								WHERE user_id = %s
								AND sco_id = %s 
								AND lvalue = %s
								AND obj_id = %s',
								array('integer','integer','text','integer'),
								array($user_id, $sco_id, 'cmi.core.entry',$obj_id)
							);
							if($ilDB->numRows($statement) > 0)
							{
								$val_set = $ilDB->manipulateF('
								UPDATE scorm_tracking
								SET rvalue = %s,
									c_timestamp = %s
								WHERE user_id = %s
								AND sco_id = %s 
								AND lvalue = %s
								AND obj_id = %s',
								array('text','timestamp','integer','integer','text','integer'),
								array('completed',$date ,$user_id, $sco_id, 'cmi.core.entry',$obj_id)
								);
							}
							else
							{
								$val_set = $ilDB->manipulateF('
								INSERT INTO scorm_tracking
								(obj_id,user_id,sco_id,lvalue,rvalue,c_timestamp)
								VALUES (%s,%s,%s,%s,%s,%s)',
								array('integer','integer','integer','text','text','timestamp'),
								array($obj_id,$user_id,$sco,'cmi.core.entry','completed',$date));
							}
							
						}
					}
			  	} else {
					//echo "Warning! User $csv_rows[0] does not exist in ILIAS. Data for this user was skipped.\n";
				}
		}
		return 0;
	}
	
	private function importRaw($a_file)
	{
		global $ilDB, $ilUser;
		
		$fhandle = fopen($a_file, "r");
		
		$fields = fgetcsv($fhandle, 4096, ';');
		
		while(($csv_rows = fgetcsv($fhandle, 4096, ";")) !== FALSE) 
		{
			$data = array_combine($fields, $csv_rows);
	   		$il_sco_id = $this->lookupSCOId($data['Scoid']);
	   		//look for required data for an import
	   		$user_id = $data['Userid'];
	   		if ($user_id == "" || $user_id == null) {
	   			//look for Email
	   			$user_id = $this->getUserIdEmail($data['Email']);
	   		}
	   		//do the actual import
	   		if ($user_id != "" && $il_sco_id>=0)
	   		{
      
	   			$statement = $ilDB->queryF('
					SELECT * FROM scorm_tracking 
					WHERE user_id = %s
					AND sco_id = %s 
					AND lvalue = %s
					AND obj_id = %s',
					array('integer','integer','text','integer'),
					array($user_id, $il_sco_id, $data['Key'],$this->getID())
				);
				if($ilDB->numRows($statement) > 0)
				{
   					$val_set = $ilDB->manipulateF('
					UPDATE scorm_tracking
					SET rvalue = %s,
						c_timestamp = %s
					WHERE user_id = %s
					AND sco_id = %s 
					AND lvalue = %s
					AND obj_id = %s',
					array('text','timestamp','integer','integer','text','integer'),
					array($data['Value'],$data['Timestamp'] ,$user_id, $il_sco_id, $data['Key'],$this->getID())
					);
				}
				else
				{
					$val_set = $ilDB->manipulateF('
					INSERT INTO scorm_tracking
					(obj_id,user_id,sco_id,lvalue,rvalue,c_timestamp)
					VALUES (%s,%s,%s,%s,%s,%s)',
					array('integer','integer','integer','text','text','timestamp'),
					array($this->getID(),$user_id,$il_sco_id,$data['Key'],$data['Value'],$data['Timestamp']));
				}				
	   		}
	   }
	   fclose($fhandle);
	   return 0;
	}
	
	//helper function
	function get_user_id($a_login) {
		global $ilDB, $ilUser;
		
		$val_set = $ilDB->queryF('SELECT * FROM usr_data WHERE(login=%s)',
		array('text'),array($a_login));
		$val_rec = $ilDB->fetchAssoc($val_set);
		
		if (count($val_rec)>0) {
			return $val_rec['usr_id'];
		} else {
			return null;
		}
	}
	
	
	/**
	* resolves manifest SCOID to internal ILIAS SCO ID
	*/
	private function lookupSCOId($a_referrer){
		global $ilDB, $ilUser;
		
		//non specific SCO entries
		if ($a_referrer=="0") {
			return 0;
		}

		$val_set = $ilDB->queryF('
		SELECT obj_id FROM sc_item,scorm_tree 
		WHERE (obj_id = child 
		AND identifierref = %s 
		AND slm_id = %s)',
		array('text','integer'), array($a_referrer,$this->getID()));
		$val_rec = $ilDB->fetchAssoc($val_set);
		
		return $val_rec["obj_id"];
	}
	
	/**
	* assumes that only one account exists for a mailadress
	*/
	function getUserIdEmail($a_mail)
	{
		global $ilDB, $ilUser;
		
		$val_set = $ilDB->queryF('SELECT usr_id FROM usr_data WHERE(email=%s)',
		array('text'),array($a_mail));
		$val_rec = $ilDB->fetchAssoc($val_set);
				
		
		return $val_rec["usr_id"];
	}
	
	
	/**
	* send export file to browser
	*/
	function sendExportFile($a_header,$a_content)
	{
	   	$timestamp = time();
		$refid = $this->getRefId();
		$filename = "scorm_tracking_".$refid."_".$timestamp.".csv";
		//Header
		header("Expires: 0");
		header("Cache-control: private");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Description: File Transfer");
		header("Content-Type: application/octet-stream");
		header("Content-disposition: attachment; filename=$filename");
		echo $a_header.$a_content;
		exit;	
	}
	
	/**
	* Get an array of id's for all Sco's in the module
	* @param int $a_id Object id
	* @return array Sco id's
	*/
	public static function _getAllScoIds($a_id)
	{
		global $ilDB;
		
		$scos = array();

		$val_set = $ilDB->queryF('
		SELECT scorm_object.obj_id,
				scorm_object.title,
				scorm_object.c_type,
				scorm_object.slm_id,
				scorm_object.obj_id scoid 
		FROM scorm_object,sc_item,sc_resource 
		WHERE(scorm_object.slm_id = %s
		AND scorm_object.obj_id = sc_item.obj_id 
		AND sc_item.identifierref = sc_resource.import_id 
		AND sc_resource.scormtype = %s
		GROUP BY scorm_object.obj_id,
				scorm_object.title,
				scorm_object.c_type,
				scorm_object.slm_id,
				scorm_object.obj_id ',
		array('integer', 'text'),
		array($a_id,'sco'));

		while ($val_rec = $ilDB->fetchAssoc($val_set)) 
		{
			array_push($scos,$val_rec['scoid']);
		}
		return $scos;
	}
	
	/**
	* Get the status of a SCORM module for a given user
	* @param int $a_id Object id
	* @param int $a_user User id
	* @param array $a_allScoIds Array of Sco id's in this module
	* @param boolean $a_numerical Text (false) or boolean result (true)
	* @return mixed Status result
	*/
	public static function _getStatusForUser($a_id, $a_user,$a_allScoIds,$a_numerical=false)
	{
		global $ilDB, $lng;
		
		$scos = $a_allScoIds;
		//check if all SCO's are completed
		$scos_c = implode(',',$scos);

		$val_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE (user_id = %s
		AND obj_id = %s
		AND sco_id IN ('.$scos_c.')
		AND ((lvalue = %s AND rvalue = %s) 
			OR (lvalue = %s AND rvalue = %s)))',
		array('integer','integer','text','text','text','text'),
		array($a_user,$a_id,'cmi.core.lesson_status', 'completed', 'cmi.core.lesson_status', 'passed'));	
		while ($val_rec = $ilDB->fetchAssoc($val_set))
		{
			$key = array_search($val_rec['sco_id'], $scos); 
			unset ($scos[$key]);
		}
		//check for completion
		if (count($scos) == 0) {
			$completion = ($a_numerical===true)  ? true: $lng->txt("cont_complete");
		}	
		if (count($scos) > 0) {
			$completion = ($a_numerical===true)  ? false: $lng->txt("cont_incomplete");
		}
		return $completion;
	}

	/**
	* Get the completion of a SCORM module for a given user
	* @param int $a_id Object id
	* @param int $a_user User id
	* @return boolean Completion status
	*/
	public static function _getCourseCompletionForUser($a_id, $a_user) 
	{
		return ilObjSCORMLearningModule::_getStatusForUser($a_id, $a_user, ilObjSCORMLearningModule::_getAllScoIds($a_id), true);
	}

	function getAllScoIds(){
		global $ilDB;
		
		$scos = array();
		//get all SCO's of this object
		$val_set = $ilDB->queryF('
		SELECT scorm_object.obj_id,
				scorm_object.title,
				scorm_object.c_type,
				scorm_object.slm_id,
				scorm_object.obj_id scoid 
		FROM scorm_object, sc_item,sc_resource 
		WHERE(scorm_object.slm_id = %s 
			AND scorm_object.obj_id = sc_item.obj_id 
			AND sc_item.identifierref = sc_resource.import_id 
			AND sc_resource.scormtype = %s )
		GROUP BY scorm_object.obj_id,
		scorm_object.title,
		scorm_object.c_type,
		scorm_object.slm_id,
		scorm_object.obj_id',
		array('integer','text'), 
		array($this->getID(),'sco'));

		while ($val_rec = $ilDB->fetchAssoc($val_set))
		{
			array_push($scos,$val_rec['scoid']);
		}
		return $scos;
	}
	
	function getStatusForUser($a_user,$a_allScoIds,$a_numerical=false){
		global $ilDB;
		$scos = $a_allScoIds;
		//loook up status
		//check if all SCO's are completed
		$scos_c = implode(',',$scos); 

		$val_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE (user_id = %s
			AND obj_id = %s
			AND sco_id IN ('.$scos_c.')
		 AND ((lvalue = %s AND rvalue =  %s) OR (lvalue =  %s AND rvalue =  %s) ) )',
		array('integer','integer','text','text','text','text'), 
		array($a_user,$this->getID(),'cmi.core.lesson_status','completed','cmi.core.lesson_status','passed'));	
		while ($val_rec = $ilDB->fetchAssoc($val_set))	
		{
			$key = array_search($val_rec['sco_id'], $scos); 
			unset ($scos[$key]);
		}		
		//check for completion
		if (count($scos) == 0) {
			$completion = ($a_numerical===true)  ? true: $this->lng->txt("cont_complete");
		}	
		if (count($scos) > 0) {
			$completion = ($a_numerical===true)  ? false: $this->lng->txt("cont_incomplete");
		}
		return $completion;
	}
	
	function getCourseCompletionForUser($a_user) {
		return $this->getStatusForUser($a_user,$this->getAllScoIds,true);
	}
	
} // END class.ilObjSCORMLearningModule
?>
