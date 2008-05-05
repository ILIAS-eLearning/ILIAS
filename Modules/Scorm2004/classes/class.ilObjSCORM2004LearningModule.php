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


require_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";

/**
* Class ilObjSCORM2004LearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjSCORMLearningModule.php 13123 2007-01-29 13:57:16Z smeyer $
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORM2004LearningModule extends ilObjSCORMLearningModule
{
	var $validator;
//	var $meta_data;
	
	const CONVERT_XSL   = './Modules/Scorm2004/templates/xsl/op/scorm12To2004.xsl';
	const WRAPPER_HTML  = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/GenericRunTimeWrapper.htm';
	const WRAPPER_JS  	= './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/SCOPlayerWrapper.js';

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSCORM2004LearningModule($a_id = 0, $a_call_by_reference = true)
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
		//$this->validator =& new ilObjSCORMValidator($directory);
		//$returnValue = $this->validator->validate();
		return true;
	}


	/**
	* read manifest file
	* @access	public
	*/
	function readObject()
	{	
		global $ilias, $lng ,$ilDB;
		
		//check for MYSQL 4.1 and json_encode,json_decode 
		if (!function_exists('json_encode') ||  !function_exists('json_decode') || !$ilDB->isMysql4_1OrHigher()) {
			$ilias->raiseError($lng->txt('scplayer_phpmysqlcheck'),$ilias->error_obj->WARNING);
		}
		
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
			
		
		//check for SCORM 1.2 
		$this->convert_1_2_to_2004($manifest_file);
		
		// start SCORM 2004 package parser/importer
		include_once ("./Modules/Scorm2004/classes/ilSCORM13Package.php");
		$newPack = new ilSCORM13Package();
		return $newPack->il_import($this->getDataDirectory(),$this->getId(),$this->ilias,$_POST["validate"]);
	}


	public function fixReload() {		
		$out = file_get_contents($this->imsmanifestFile);
		$check ='/xmlns="http:\/\/www.imsglobal.org\/xsd\/imscp_v1p1"/';
		$replace="xmlns=\"http://www.imsproject.org/xsd/imscp_rootv1p1p2\"";
		$out=preg_replace($check, $replace, $out);
		file_put_contents($this->imsmanifestFile, $out);
	}
	
	
	public function convert_1_2_to_2004($manifest) {
		global $ilDB, $ilLog;
		
		##check manifest-file for version. Check for schemaversion as this is a required element for SCORM 2004
		##accept 2004 3rd Edition an CAM 1.3 as valid schemas
		
		//set variables
		$this->packageFolder=$this->getDataDirectory();
		$this->imsmanifestFile=$manifest;
		$doc = new DomDocument();
		
		//fix reload errors before loading
		//$this->fixReload();
	  	$doc->load($this->imsmanifestFile);
	  	$elements = $doc->getElementsByTagName("schemaversion");
		$schema=$elements->item(0)->nodeValue;
		if (strtolower($schema)=="cam 1.3" || strtolower($schema)=="2004 3rd edition") {
			//no conversion
			$this->converted=false;
			return true;
			
		} else {
			$this->converted=true;
			//convert to SCORM 2004
			
			//check for broken SCORM 1.2 manifest file (missing organization default-common error in a lot of manifest files)
			$organizations = $doc->getElementsByTagName("organizations");
			$default=$organizations->item(0)->getAttribute("default");
		  	if ($default=="" || $default==null) {
				//lookup identifier
			  	$organization = $doc->getElementsByTagName("organization");
				$ident=$organization->item(0)->getAttribute("identifier");
				$organizations->item(0)->setAttribute("default",$ident);
			}
			
			//validate the fixed mainfest. If it's still not valid, don't transform an throw error
			
					
			//first copy wrappers
			$wrapperdir=$this->packageFolder."/GenericRunTimeWrapper1.0_aadlc";
			mkdir($wrapperdir);
			copy(self::WRAPPER_HTML,$wrapperdir."/GenericRunTimeWrapper.htm");
			copy(self::WRAPPER_JS,$wrapperdir."/SCOPlayerWrapper.js");
			
			//backup manifestfile
			$this->backupManifest=$this->packageFolder."/imsmanifest.xml.back";
			$ret=copy($this->imsmanifestFile,$this->backupManifest);
			
			//transform manifest file
			$this->totransform = $doc;
			$ilLog->write("SCORM: about to transform to SCORM 2004");
			
			$xsl = new DOMDocument;
			$xsl->async = false;
			$xsl->load(self::CONVERT_XSL);
			$prc = new XSLTProcessor;
			$r = @$prc->importStyleSheet($xsl);
			
			file_put_contents($this->imsmanifestFile, $prc->transformToXML($this->totransform));

			$ilLog->write("SCORM: Transoformation completed");
			return true;
		}
		
	}
	

	/**
	* get all tracked items of current user
	*/
	function getTrackedUsers($a_search)
	{
		global $ilUser, $ilDB, $ilUser;

		$query = "SELECT DISTINCT user_id,UNIX_TIMESTAMP(MAX(TIMESTAMP)) AS last_access FROM cmi_node, cp_node WHERE".
			" cmi_node.cp_node_id = cp_node.cp_node_id ".
			" AND cp_node.slm_id = ".$ilDB->quote($this->getId())."GROUP BY user_id";

		$sco_set = $ilDB->query($query);

		$items = array();
		$temp = array();
		
		while($sco_rec = $sco_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$name = ilObjUser::_lookupName($sco_rec["user_id"]);
			if ($sco_rec['last_access'] != 0) {
				$sco_rec['last_access'] = ilFormat::formatDate(date("Y-m-d H:i:s", $sco_rec['last_access']));
			} else {
				$sco_rec['last_access'] = "";
			}
			if (ilObject::_exists($sco_rec['user_id'])  && ilObject::_lookUpType($sco_rec['user_id'])=="usr" ) {
					$user = new ilObjUser($sco_rec['user_id']);
					$temp = array("user_full_name" => $name["lastname"].", ".
									$name["firstname"]." [".ilObjUser::_lookupLogin($sco_rec["user_id"])."]",
								    "user_id" => $sco_rec["user_id"],"last_access" => $sco_rec['last_access'],
									"user_id" => $sco_rec["user_id"],"last_access" => $sco_rec['last_access'],
									"version"=> $this->getModuleVersionForUser($sco_rec["user_id"]),
									"attempts" => $this->getAttemptsForUser($sco_rec["user_id"]),
									"username" =>  $user->getLastname().", ".$user->getFirstname()
								);
				if ($a_search != "" && (strpos(strtolower($user->getLastname()), strtolower($a_search)) !== false || strpos(strtolower($user->getFirstname()), strtolower($a_search)) !== false ) ) {
					$items[] = $temp;
				} else if ($a_search == "") {
					$items[] = $temp;
				}	
			}	
		}

		return $items;
	}

	/**
	* get all tracked items of current user
	*/
	function deleteTrackingDataOfUsers($a_users)
	{
		global $ilDB;
		
		foreach($a_users as $user)
		{
			$q = "DELETE FROM cmi_node WHERE user_id = ".$ilDB->quote($user).
				" AND cp_node_id IN (SELECT cp_node_id FROM cp_node WHERE slm_id = ".
				$ilDB->quote($this->getId()).")";
			$ilDB->query($q);
		}
	}
	
	
	function getTrackedItems()
	{
		global $ilUser, $ilDB, $ilUser;

		$query = "SELECT DISTINCT cmi_node.cp_node_id AS id".
			" FROM cp_node, cmi_node WHERE slm_id = ".
			$ilDB->quote($this->getId()).
			" AND cp_node.cp_node_id = cmi_node.cp_node_id ".
			" ORDER BY cp_node.cp_node_id ";
			
		$sco_set = $ilDB->query($query);

		$items = array();
		while($sco_rec = $sco_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$item['id']=$sco_rec["id"];
			$item['title']=self::_lookupItemTitle($sco_rec["id"]);
			$items[count($items)] =$item;
		
		}
		return $items;
	}
	
	
	function getTrackingDataAgg($a_sco_id)
	{
		global $ilDB;
      
	    $scos = array();
		$data = array();
		//get all SCO's of this object		
		$query = "SELECT cp_node_id FROM cp_node WHERE".
				" nodeName='item' AND cp_node.slm_id = ".$ilDB->quote($this->getId());		
								
		$val_set = $ilDB->query($query);
		while ($val_rec = $val_set->fetchRow(DB_FETCHMODE_ASSOC)) {
			array_push($scos,$val_rec['cp_node_id']);
		}
		foreach ($scos as $sco) {
			$query = "SELECT *,UNIX_TIMESTAMP(TIMESTAMP) AS last_access FROM cmi_node WHERE".
		   	" cp_node_id = ".$ilDB->quote($sco).
		   	" AND user_id =".$ilDB->quote($_GET["user_id"]);
		   	$data_set = $ilDB->query($query);
	   		while($data_rec = $data_set->fetchRow(DB_FETCHMODE_ASSOC))
	   		{
	   			if ($data_rec["success_status"]!="") {
	   				$status = $data_rec["success_status"];
	   			} else {
	   				if ($data_rec["completion_status"]=="") {
	   					$status="unknown";
	   				} else {
	   					$status = $data_rec["completion_status"];
	   				}	
	   			}	
	   			$time = ilFormat::_secondsToString(self::_ISODurationToCentisec($data_rec["session_time"])/100);
	   			$score = $data_rec["scaled"];
	   			$title = self::_lookupItemTitle($data_rec["cp_node_id"]);
	   			$last_access=ilFormat::formatDate(date("Y-m-d H:i:s", $data_rec["last_access"]));
				 $data[] = array("user_id" => $user_rec["user_id"],
				   	"score" => $score, "time" => $time, "status" => $status,"last_access"=>$last_access,"title"=>$title);
	   		}
      	}
	  

		return $data;
	}
	
	/**
	* get number of atttempts for a certain user and package
	*/
	function getAttemptsForUser($a_user_id){
		global $ilDB;
		
		$query = "SELECT * FROM cmi_custom WHERE".
			" user_id = ".$ilDB->quote($a_user_id).
			" AND sco_id = 0".
			" AND lvalue='package_attempts'".
			" AND obj_id = ".$ilDB->quote($this->getId());

		$val_set = $ilDB->query($query);
		$val_rec = $val_set->fetchRow(DB_FETCHMODE_ASSOC);
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
		
		$query = "SELECT * FROM cmi_custom WHERE".
			" user_id = ".$ilDB->quote($a_user_id).
			" AND sco_id = 0".
			" AND lvalue='module_version'".
			" AND obj_id = ".$ilDB->quote($this->getId());

		$val_set = $ilDB->query($query);
		$val_rec = $val_set->fetchRow(DB_FETCHMODE_ASSOC);
		$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
		if ($val_rec["rvalue"] == null) {
			$val_rec["rvalue"]="";
		}
		return $val_rec["rvalue"];
	}
	
	
	
	function exportSelected($a_exportall=0, $a_user)
	{
		global $ilDB, $ilUser;
	 	$scos = array();
		 //get all SCO's of this object		
		$query = "SELECT cp_node.cp_node_id FROM cp_node,cp_resource,cp_item WHERE".
		 		" cp_item.cp_node_id=cp_node.cp_node_id AND cp_item.resourceId = cp_resource.id AND scormType='sco' AND nodeName='item' AND cp_node.slm_id = ".$ilDB->quote($this->getId());
        
		$val_set = $ilDB->query($query);
		while ($val_rec = $val_set->fetchRow(DB_FETCHMODE_ASSOC)) {
			array_push($scos,$val_rec['cp_node_id']);
		}	
		$csv = null;
		//a module is completed when all SCO's are completed
		$user_array = array();
		
		if ($a_exportall == 1) {
			$query3 = "SELECT DISTINCT user_id,UNIX_TIMESTAMP(MAX(TIMESTAMP)) AS last_access FROM cmi_node, cp_node WHERE".
				"	 cmi_node.cp_node_id = cp_node.cp_node_id ".
					" AND cp_node.slm_id = ".$ilDB->quote($this->getId())."GROUP BY user_id";
			$val_set3 = $ilDB->query($query3);
			while ($val_rec3 = $val_set3->fetchRow(DB_FETCHMODE_ASSOC)) {
			 	array_push($user_array,$val_rec3['user_id']);
			}
			
		} else {
			$user_array = $a_user;
		}
		
		
		foreach ($user_array as $user)
		{
			$scos_c = $scos;
			//copy SCO_array
			//check if all SCO's are completed
			for ($i=0;$i<count($scos);$i++){
				$query = "SELECT * FROM cmi_node WHERE (user_id=".$ilDB->quote($user).
						 " AND cp_node_id=".$ilDB->quote($scos[$i]).
						 " AND completion_status='completed' AND success_status='passed')";
				$val_set = $ilDB->query($query);
				if ($val_set->numRows()>0) {
					//delete from array
					$key = array_search($scos[$i], $scos_c); 
					unset ($scos_c[$key]);
				}
			}
			//check for completion
			if (count($scos_c) == 0) {
				$completion = 1;
			} else {
				$completion = 0;
			}
			//write export entry
			if (ilObject::_exists($user)  && ilObject::_lookUpType($user)=="usr" ) {
				$e_user = new ilObjUser($user);
				$login = $e_user->getLogin();
				$firstname = $e_user->getFirstname();
				$lastname = $e_user->getLastname();
				$email = $e_user->getEmail();
				$department = $e_user->getDepartment();
			
				$query2 = "SELECT DISTINCT user_id,MAX(DATE_FORMAT(TIMESTAMP,\"%d.%m.%y\")) AS date FROM cmi_node, cp_node WHERE".
					" cmi_node.cp_node_id = cp_node.cp_node_id ".
					" AND cp_node.slm_id = ".$ilDB->quote($this->getId())." GROUP BY user_id";
				$val_set2 = $ilDB->query($query2);
				$val_rec2 = $val_set2->fetchRow(DB_FETCHMODE_ASSOC);
				if ($val_set2->numRows()>0) {
					$date = $val_rec2['date'];
				} else {
					$date = "";
				}	
				$csv = $csv. "$department;$login;$lastname;$firstname;$email;$date;$completion\n";	
			}
		}
		$header = "Department;Login;Lastname;Firstname;Email;Date;Status\n";
		$this->sendExportFile($header,$csv);
	}
	
	
	function importSuccess($a_file) {
		global $ilDB, $ilUser;
		$scos = array();
		//get all SCO's of this object		
		$query = "SELECT cp_node.cp_node_id FROM cp_node,cp_resource,cp_item WHERE".
				" cp_item.cp_node_id=cp_node.cp_node_id AND cp_item.resourceId = cp_resource.id AND scormType='sco' AND nodeName='item' AND cp_node.slm_id = ".$ilDB->quote($this->getId());
		
		$val_set = $ilDB->query($query);
		while ($val_rec = $val_set->fetchRow(DB_FETCHMODE_ASSOC)) {
			array_push($scos,$val_rec['cp_node_id']);
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
						foreach ($scos as $sco) {
							$sco_id = $ilDB->quote($sco);
							$date = $data['Date'];
							$query = "REPLACE INTO cmi_node (cp_node_id,user_id,completion_status,success_status,TIMESTAMP)".
									  "values ($sco_id,$user_id,'completed','passed',str_to_date(\"$date\", \"%d.%m.%Y\"))";
						    $val_set = $ilDB->query($query);
						}
					}
			  	} else {
					//echo "Warning! User $csv_rows[0] does not exist in ILIAS. Data for this user was skipped.\n";
				}
		}
		return 0;
	}
	
	/**
	* convert ISO 8601 Timeperiods to centiseconds
	* ta
	*
	* @access static
	*/
	function _ISODurationToCentisec($str) {
	    $aV = array(0, 0, 0, 0, 0, 0);
	    $bErr = false;
	    $bTFound = false;
	    if (strpos($str,"P") != 0) {
	        $bErr = true;
	    }
	    if (!$bErr) {
	        $aT =  array("Y", "M", "D", "H", "M", "S");
	        $p = 0;
	 		$i = 0;
	        $str = substr($str,1);
	        for ($i = 0; $i < count($aT); $i++) {
	            if (strpos($str,"T")===0) {
	                $str = substr($str,1);
	                $i = max($i, 3);
	                $bTFound = true;
	            }
	            $p = strpos($str,$aT[$i]);
				
	            if ($p > -1) {
	                if ($i == 1 && strpos($str,"T") > -1 && strpos($str,"T") < $p) {
	                    continue;
	                }
	                if ($aT[$i] == "S") {
	                    $aV[$i] = substr($str,0, $p);
					
	                } else {
	                    $aV[$i] = intval(substr($str,0, $p));
	                }
	                if (!is_numeric($aV[$i])) {
	                    $bErr = true;
	                    break;
	                } else if ($i > 2 && !$bTFound) {
	                    $bErr = true;
	                    break;
	                }
	                $str = substr($str,$p + 1);
			
	            }
	        }
	        if (!$bErr && strlen($str) != 0) {
	            $bErr = true;
				
	        }
	    }
	
	    if ($bErr) {
	        return;
	    }
	    return $aV[0] * 3155760000 + $aV[1] * 262980000 + $aV[2] * 8640000 + $aV[3] * 360000 + $aV[4] * 6000 + round($aV[5] * 100);
	}
	
	
	/**
	* get all tracking items of scorm object
	*
	* currently a for learning progress only
	*
	* @access static
	*/
	
	function _getTrackingItems($a_obj_id)
	{
		global $ilDB;
		
		$q = "SELECT cp_item.* ".
			" FROM cp_node, cp_item WHERE slm_id = ".
			$ilDB->quote($a_obj_id).
			" AND cp_node.cp_node_id = cp_item.cp_node_id ".
			" ORDER BY cp_node.cp_node_id ";

		$item_set = $ilDB->query($q);
			
		$items = array();
		while ($item_rec = $item_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$s2 = $ilDB->query("SELECT cp_resource.* ".
				" FROM cp_node, cp_resource WHERE slm_id = ".
				$ilDB->quote($a_obj_id).
				" AND cp_node.cp_node_id = cp_resource.cp_node_id ".
				" AND cp_resource.id = ".$ilDB->quote($item_rec["resourceId"]));
			if ($res = $s2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if ($res["scormType"] == "sco")
				{
					$items[] = array("id" => $item_rec["cp_node_id"],
						"title" => $item_rec["title"]);
				}
			}
		}

		return $items;
	}

	static function _getStatus($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM cmi_gobjective ".
			" WHERE scope_id = ".$ilDB->quote($a_obj_id).
			" AND objective_id = ".$ilDB->quote("-course_overall_status-").
			" AND user_id = ".$ilDB->quote($a_user_id);

		$status_set = $ilDB->query($q);

		if ($status_rec = $status_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $status_rec["status"];
		}

		return false;
	}

	static function _getSatisfied($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM cmi_gobjective ".
			" WHERE scope_id = ".$ilDB->quote($a_obj_id).
			" AND objective_id = ".$ilDB->quote("-course_overall_status-").
			" AND user_id = ".$ilDB->quote($a_user_id);

		$status_set = $ilDB->query($q);

		if ($status_rec = $status_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $status_rec["satisfied"];
		}

		return false;
	}

	static function _getMeasure($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM cmi_gobjective ".
			" WHERE scope_id = ".$ilDB->quote($a_obj_id).
			" AND objective_id = ".$ilDB->quote("-course_overall_status-").
			" AND user_id = ".$ilDB->quote($a_user_id);

		$status_set = $ilDB->query($q);

		if ($status_rec = $status_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $status_rec["measure"];
		}

		return false;
	}
	
	static function _lookupItemTitle($a_node_id)
	{
		global $ilDB;
		
		$r = $ilDB->query("SELECT * FROM cp_item ".
			" WHERE cp_node_id = ".$ilDB->quote($a_node_id));
		if ($i = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $i["title"];
		}
		return "";
	}


} // END class.ilObjSCORM2004LearningModule
?>
