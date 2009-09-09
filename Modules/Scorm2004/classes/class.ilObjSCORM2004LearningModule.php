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
		if (!function_exists('json_encode') ||  !function_exists('json_decode') || ($ilDB->getDBType() == 'mysql' && !$ilDB->isMysql4_1OrHigher())) {
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
		if ($_POST["editable"] == "y")
			return $newPack->il_importLM($this,$this->getDataDirectory());
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
		$this->fixReload();
	  	$doc->load($this->imsmanifestFile);
	  	$elements = $doc->getElementsByTagName("schemaversion");
		$schema=$elements->item(0)->nodeValue;
		if (strtolower(trim($schema))=="cam 1.3" || strtolower(trim($schema))=="2004 3rd edition") {
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
			SELECT MAX(c_timestamp) last_access 
			FROM cmi_node, cp_node 
			WHERE cmi_node.cp_node_id = cp_node.cp_node_id 
			AND cp_node.slm_id = %s
			AND user_id = %s
			ORDER BY c_timestamp DESC',
		array('integer', 'integer'),
		array($a_obj_id, $a_usr_id));
		if ($ilDB->numRows($result))
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["last_access"];
		}		
		
		return "";
	}

	/**
	* get all tracked items of current user
	*/
	function getTrackedUsers($a_search)
	{
		global $ilUser, $ilDB, $ilUser;

		$sco_set = $ilDB->queryF('
			SELECT DISTINCT user_id,MAX(c_timestamp) last_access 
			FROM cmi_node, cp_node 
			WHERE cmi_node.cp_node_id = cp_node.cp_node_id 
			AND cp_node.slm_id = %s
			GROUP BY user_id',
			array('integer'),
			array($this->getId()));
		
		$items = array();
		$temp = array();
		
		while($sco_rec = $ilDB->fetchAssoc($sco_set))
		{
			$name = ilObjUser::_lookupName($sco_rec["user_id"]);
			if ($sco_rec['last_access'] != 0) {
				$sco_rec['last_access'] = ilDatePresentation::formatDate(new ilDateTime($sco_rec['last_access'],IL_CAL_DATETIME));
				
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
			$res = $ilDB->manipulateF(
				'DELETE FROM cmi_node WHERE user_id = %s'.
				'AND cp_node_id IN (SELECT cp_node_id FROM cp_node WHERE slm_id = %s)', 
				array('integer', 'integer'),
				array($user, $this->getId()));
		}
	}
	
	
	function getTrackedItems()
	{
		global $ilUser, $ilDB, $ilUser;

		$sco_set = $ilDB->queryF('
		SELECT DISTINCT cmi_node.cp_node_id id
		FROM cp_node, cmi_node 
		WHERE slm_id = %s
		AND cp_node.cp_node_id = cmi_node.cp_node_id 
		ORDER BY cp_node.cp_node_id ',
		array('integer'),
		array($this->getId()));
		
		$items = array();

		while($sco_rec = $ilDB->fetchAssoc($sco_set))
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
	
		$val_set = $ilDB->queryF(
			'SELECT cp_node_id FROM cp_node 
			WHERE nodename = %s
			AND cp_node.slm_id = %s',
			array('text', 'integer'),
			array('item',$this->getId())
		);
		while($val_rec = $ilDB->fetchAssoc($val_set))
		{
			array_push($scos,$val_rec['cp_node_id']);
		}
		
		foreach ($scos as $sco) 
		{
			$data_set = $ilDB->queryF('
				SELECT c_timestamp last_access, session_time, success_status, completion_status,
					   c_raw, cp_node_id
				FROM cmi_node 
				WHERE cp_node_id = %s
				AND user_id = %s',
				array('integer','integer'),
				array($sco,$_GET["user_id"])
			);	
			
			while($data_rec = $ilDB->fetchAssoc($data_set))
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
	   			$score = $data_rec["c_raw"];
	   			$title = self::_lookupItemTitle($data_rec["cp_node_id"]);
	   			$last_access=ilDatePresentation::formatDate(new ilDateTime($data_rec['last_access'],IL_CAL_UNIX));
				 $data[] = array("user_id" => $data_rec["user_id"],
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

		$val_set = $ilDB->queryF('
		SELECT * FROM cmi_custom 
		WHERE user_id = %s
				AND sco_id = %s
				AND lvalue = %s
				AND obj_id = %s',
		array('integer','integer', 'text','integer'),
		array($a_user_id, 0,'package_attempts',$this->getId()));
		
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
		SELECT * FROM cmi_custom 
		WHERE user_id = %s
				AND sco_id = %s
				AND lvalue = %s
				AND obj_id = %s',
		array('integer','integer', 'text','integer'),
		array($a_user_id, 0,'module_version',$this->getId()));
		
		$val_rec = $ilDB->fetchAssoc($val_set);				
		
		$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
		if ($val_rec["rvalue"] == null) {
			$val_rec["rvalue"]="";
		}
		return $val_rec["rvalue"];
	}
	
	
	
	function exportSelected($a_exportall = 0, $a_user = array())
	{
		global $ilDB, $ilUser;
	 	
		$scos = array();
		
		//get all SCO's of this object
		$query = 'SELECT cp_node.cp_node_id '
			   . 'FROM cp_node, cp_resource, cp_item '
		 	   . 'WHERE cp_item.cp_node_id = cp_node.cp_node_id ' 
		 	   . 'AND cp_item.resourceid = cp_resource.id AND scormtype = %s ' 
		 	   . 'AND nodename = %s	AND cp_node.slm_id = %s';		
	 	$res = $ilDB->queryF(
			$query,
		 	array('text', 'text', 'integer'),
		 	array('sco', 'item', $this->getId())
		);		
		while($row = $ilDB->fetchAssoc($res))
		{
			$scos[] = $row['cp_node_id'];
		}
		
		$csv = null;
		
		//a module is completed when all SCO's are completed
		$user_array = array();
		
		if($a_exportall == 1) 
		{
			$query = 'SELECT user_id '
				   . 'FROM cmi_node, cp_node '
				   . 'WHERE cmi_node.cp_node_id = cp_node.cp_node_id AND cp_node.slm_id = %s '
				   . 'GROUP BY user_id';
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
				$query = 'SELECT * FROM cmi_node ' 
					   . 'WHERE user_id = %s AND cp_node_id = %s '
					   . 'AND completion_status = %s AND success_status = %s';
				$res = $ilDB->queryF(
					$query,
					array('integer', 'integer', 'text', 'text'),
					array($user, $scos[$i], 'completed', 'passed')
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
			
			//write export entry
			if(ilObject::_exists($user)  && ilObject::_lookUpType($user) == 'usr')
			{
				$e_user = new ilObjUser($user);
				$login = $e_user->getLogin();
				$firstname = $e_user->getFirstname();
				$lastname = $e_user->getLastname();
				$email = $e_user->getEmail();
				$department = $e_user->getDepartment();
			
				$query = 'SELECT user_id, MAX(c_timestamp) exp_date '
					   . 'FROM cmi_node, cp_node ' 
					   . 'WHERE cmi_node.cp_node_id = cp_node.cp_node_id ' 
					   . 'AND cp_node.slm_id = %s '
					   . 'GROUP BY user_id';
				$res = $ilDB->queryF(
					$query,
					array('integer'),
					array($this->getId())
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
	
	
	function importSuccess($a_file) {
		global $ilDB, $ilUser;
		$scos = array();
		//get all SCO's of this object		
		$val_set = $ilDB->queryF('
			SELECT cp_node.cp_node_id FROM cp_node,cp_resource,cp_item 
			WHERE cp_item.cp_node_id = cp_node.cp_node_id 
			AND cp_item.resourceid = cp_resource.id 
			AND scormtype = %s 
			AND nodename = %s 
			AND cp_node.slm_id = %s',
			array('text','text', 'integer'),
			array('sco','item', $this->getId())
		); 
		while ($val_rec = $ilDB->fetchAssoc($val_set))
		{
			array_push($scos,$val_rec['cp_node_id']);
		}
		
		$fhandle = fopen($a_file, "r");

		$obj_id = $this->getID();

		$fields = fgetcsv($fhandle, 4096, ';');

		while(($csv_rows = fgetcsv($fhandle, 4096, ";")) !== FALSE) {
			$data = array_combine($fields, $csv_rows);
			  //check the format
			  $statuscheck = 0;
			  if (count($csv_rows) == 6) {$statuscheck = 1;}
			
			  if ($this->get_user_id($data["Login"])>0) {
					
				$user_id = $this->get_user_id($data["Login"]);
				$import = $data["Status"];
				if ($import == "") {$import = 1;}
					//iterate over all SCO's
					if ($import == 1) {
						foreach ($scos as $sco) 
						{
							$sco_id = $sco;
							$date = $data['Date'];

							$res = $ilDB->queryF('
							SELECT * FROM cmi_node
							WHERE 	cp_node_id = %s
							AND 	user_id  = %s
							AND 	completion_status = %s
							AND		success_status = %s
							AND		c_timestamp = %s',
							array('integer','integer','text','text','timestamp'),
							array($sco_id,$user_id,'completed','passed',$data['Date']));
						
							if(!$ilDB->numRows($res))
							{
								$nextId = $ilDB->nextId('cmi_node');
								$val_set = $ilDB->manipulateF('
								INSERT INTO cmi_node
								(cp_node_id,user_id,completion_status,success_status,c_timestamp)
								VALUES(%s,%s,%s,%s,%s)',
								array('integer','integer','text','text','timestamp'),
								array($nextId,$user_id,'completed','passed',$data['Date']));
							}
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
	
	function getCourseCompletionForUser($a_user) 
	{
		global $ilDB, $ilUser;
		
	 	$scos = array();
		 //get all SCO's of this object		

		$val_set = $ilDB->queryF('
		SELECT 	cp_node.cp_node_id FROM cp_node,cp_resource,cp_item 
		WHERE  	cp_item.cp_node_id = cp_node.cp_node_id 
		AND 	cp_item.resourceid = cp_resource.id 
		AND scormtype = %s
		AND nodename = %s
		AND cp_node.slm_id = %s ',
		array('text','text','integer'),
		array('sco','item',$this->getId()));
		
		while ($val_rec = $ilDB->fetchAssoc($val_set))
		{
			array_push($scos,$val_rec['cp_node_id']);
		}
		
		
		$scos_c = $scos;
		//copy SCO_array
		//check if all SCO's are completed
		for ($i=0;$i<count($scos);$i++)
		{

			$val_set = $ilDB->queryF('
				SELECT * FROM cmi_node 
				WHERE (user_id= %s
				AND cp_node_id= %s
				AND (completion_status=%s OR success_status=%s))',
				array('integer','integer','text', 'text'), 
				array($a_user,$scos[$i],'completed','passed')
			);
			
			if ($ilDB->numRows($val_set) > 0) {
				//delete from array
				$key = array_search($scos[$i], $scos_c); 
				unset ($scos_c[$key]);
			}		
			
		}
		//check for completion
		if (count($scos_c) == 0) {
			$completion = true;
		} else {
			$completion = false;
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
		global $ilDB, $ilUser;
	 	$scos = array();
		 //get all SCO's of the object

	 	$val_set = $ilDB->queryF('
	 	SELECT cp_node.cp_node_id FROM cp_node,cp_resource,cp_item 
	 	WHERE cp_item.cp_node_id = cp_node.cp_node_id 
	 	AND cp_item.resourceid = cp_resource.id 
	 	AND scormtype = %s
	 	AND nodename =  %s 
	 	AND cp_node.slm_id =  %s',
	 	array('text','text','integer'), array('sco' ,'item',$a_id));
		while ($val_rec = $ilDB->fetchAssoc($val_set)) 
		{
			array_push($scos,$val_rec['cp_node_id']);
		} 	
	 	
		$scos_c = $scos;
		//copy SCO_array
		//check if all SCO's are completed
		for ($i=0;$i<count($scos);$i++)
		{

			$val_set = $ilDB->queryF('
				SELECT * FROM cmi_node 
				WHERE (user_id= %s
				AND cp_node_id= %s
				AND (completion_status = %s OR success_status = %s))',
			array('integer','integer','text','text'),
			array($a_user,$scos[$i],'completed','passed'));
			
			if ($ilDB->numRows($val_set) > 0) 
			{
				//delete from array
				$key = array_search($scos[$i], $scos_c); 
				unset ($scos_c[$key]);
			}
			
		}
		//check for completion
		if (count($scos_c) == 0) {
			$completion = true;
		} else {
			$completion = false;
		}
		return $completion;
	}
	
	/**
	* Get the Unique Scaled Score of a course
	* Conditions: Only one SCO may set cmi.score.scaled
	* @param int $a_id Object id
	* @param int $a_user User id
	* @return float scaled score, -1 if not unique
	*/
	public static function _getUniqueScaledScoreForUser($a_id, $a_user) 
	{
		global $ilDB, $ilUser;		
		$scos = array();
		
		$val_set = $ilDB->queryF("SELECT cp_node.cp_node_id FROM cp_node,cp_resource,cp_item WHERE".
			" cp_item.cp_node_id=cp_node.cp_node_id AND cp_item.resourceId = cp_resource.id AND scormType='sco' AND nodeName='item' AND cp_node.slm_id = %s GROUP BY cp_node.cp_node_id",
			array('integer'),
			array($a_id)
		);
		while ($val_rec = $ilDB->fetchAssoc($val_set)) 
		{
			array_push($scos,$val_rec['cp_node_id']);
		}
		$set = 0;   //numbers of SCO that set cmi.score.scaled
		$scaled = null;
		for ($i=0;$i<count($scos);$i++)
		{
			$val_set = $ilDB->queryF("SELECT scaled FROM cmi_node WHERE (user_id = %s AND cp_node_id = %s)",
				array('integer', 'integer'),
				array($a_user, $scos[$i])
			);
			if ($val_set->numRows()>0) 
			{
				$val_rec = $ilDB->fetchAssoc($val_set);
				if ($val_rec['scaled']!=NULL) {
					$set++;
					$scaled = $val_rec['scaled'];
				}
			}
		}	
		$retVal = ($set == 1) ? $scaled : null ;
		return $retVal;
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
		

		$item_set = $ilDB->queryF('
			SELECT cp_item.*  FROM cp_node, cp_item WHERE slm_id = %s
			AND cp_node.cp_node_id = cp_item.cp_node_id 
			ORDER BY cp_node.cp_node_id ',
			array('integer'),
			array($a_obj_id)
		);
			
		$items = array();
		while ($item_rec = $ilDB->fetchAssoc($item_set))
		{	

			$s2 = $ilDB->queryF('
				SELECT cp_resource.* FROM cp_node, cp_resource 
				WHERE slm_id = %s
				AND cp_node.cp_node_id = cp_resource.cp_node_id 
				AND cp_resource.id = %s ',
				array('integer','text'),
				array($a_obj_id,$item_rec["resourceid"])
			);
				
				
			if ($res = $ilDB->fetchAssoc($s2))	
	
			{
				if ($res["scormtype"] == "sco")
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
		
		$status_set = $ilDB->queryF('
			SELECT * FROM cmi_gobjective 
			WHERE scope_id = %s
			AND objective_id = %s
			AND user_id = %s',
			array('integer','text','integer'),
			array($a_obj_id,'course_overall_status',$a_user_id)
		);

		if ($status_rec = $ilDB->fetchAssoc($status_set))
		{
			return $status_rec["status"];
		}

		return false;
	}

	static function _getSatisfied($a_obj_id, $a_user_id)
	{
		global $ilDB;
		

		$status_set = $ilDB->queryF('
			SELECT * FROM cmi_gobjective 
			WHERE scope_id = %s
			AND objective_id = %s
			AND user_id = %s',
			array('integer','text','integer'),
			array($a_obj_id,'course_overall_status',$a_user_id)
		);

		if ($status_rec = $ilDB->fetchAssoc($status_set))		
		{
			return $status_rec["satisfied"];
		}

		return false;
	}

	static function _getMeasure($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$status_set = $ilDB->queryF('
			SELECT * FROM cmi_gobjective 
			WHERE scope_id = %s
			AND objective_id = %s
			AND user_id = %s',
			array('integer','text','integer'),
			array($a_obj_id,'course_overall_status',$a_user_id)
		);

		if ($status_rec = $ilDB->fetchAssoc($status_set))		
		{
			return $status_rec["measure"];
		}

		return false;
	}
	
	static function _lookupItemTitle($a_node_id)
	{
		global $ilDB;
		
		$r = $ilDB->queryF('
			SELECT * FROM cp_item
			WHERE cp_node_id = %s',
			array('integer'),
			array($a_node_id)
		);
		
		if ($i = $ilDB->fetchAssoc($r))
		{
			return $i["title"];
		}
		return "";
	}
	
	/**
	 * Create Scorm 2004 Tree used by Editor
	 */
	function createScorm2004Tree()
	{
		$this->slm_tree =& new ilTree($this->getId());
		$this->slm_tree->setTreeTablePK("slm_id");
		$this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$this->slm_tree->addTree($this->getId(), 1);
		
		//add seqinfo for rootNode
		include_once ("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
		$seq_info = new ilSCORM2004Sequencing($this->getId(),true);
		$seq_info->insert();
	}

	function getTree()
	{
		$this->slm_tree = new ilTree($this->getId());
		$this->slm_tree->setTreeTablePK("slm_id");
		$this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		return $this->slm_tree;
	}
	
	function getSequencingSettings(){
		
		global $ilTabs;
		$ilTabs->setTabActive("sequencing");
		
		include_once ("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
		$control_settings = new ilSCORM2004Sequencing($this->getId(),true);
		
		return $control_settings;
	}

	function updateSequencingSettings(){
		include_once ("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
		
		$control_settings = new ilSCORM2004Sequencing($this->getId(),true);
		$control_settings->setChoice(ilUtil::yn2tf($_POST["choice"]));
		$control_settings->setFlow(ilUtil::yn2tf($_POST["flow"]));
		$control_settings->setForwardOnly(ilUtil::yn2tf($_POST["forwardonly"]));
		$control_settings->insert();
		
		return true;
	}

	/**
	* Execute Drag Drop Action
	*
	* @param	string	$source_id		Source element ID
	* @param	string	$target_id		Target element ID
	* @param	string	$first_child	Insert as first child of target
	* @param	string	$movecopy		Position ("move" | "copy")
	*/
	function executeDragDrop($source_id, $target_id, $first_child, $as_subitem = false, $movecopy = "move")
	{
		$this->slm_tree = new ilTree($this->getId());
		$this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$this->slm_tree->setTreeTablePK("slm_id");
		
		require_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
		
		$source_obj = ilSCORM2004NodeFactory::getInstance($this, $source_id, true);
		//$source_obj->setLMId($this->getId());

		if (!$first_child)
		{
			$target_obj = ilSCORM2004NodeFactory::getInstance($this, $target_id, true);
			//$target_obj->setLMId($this->getId());
			$target_parent = $this->slm_tree->getParentId($target_id);
		}
//echo "-".$source_obj->getType()."-";
		// handle pages
		if ($source_obj->getType() == "page")
		{
			if ($this->slm_tree->isInTree($source_obj->getId()))
			{
				$node_data = $this->slm_tree->getNodeData($source_obj->getId());

				// cut on move
				if ($movecopy == "move")
				{
					$parent_id = $this->slm_tree->getParentId($source_obj->getId());
					$this->slm_tree->deleteTree($node_data);

					// write history entry
/*					require_once("classes/class.ilHistory.php");
					ilHistory::_createEntry($source_obj->getId(), "cut",
						array(ilLMObject::_lookupTitle($parent_id), $parent_id),
						$this->getType().":pg");
					ilHistory::_createEntry($parent_id, "cut_page",
						array(ilLMObject::_lookupTitle($source_obj->getId()), $source_obj->getId()),
						$this->getType().":st");
*/
				}
/*				else			// this is not implemented here
				{
					// copy page
					$new_page =& $source_obj->copy();
					$source_id = $new_page->getId();
					$source_obj =& $new_page;
				}
*/

				// paste page
				if(!$this->slm_tree->isInTree($source_obj->getId()))
				{
					if ($first_child)			// as first child
					{
						$target_pos = IL_FIRST_NODE;
						$parent = $target_id;
					}
					else if ($as_subitem)		// as last child
					{
						$parent = $target_id;
						$target_pos = IL_FIRST_NODE;
						$pg_childs = $this->slm_tree->getChildsByType($parent, "page");
						if (count($pg_childs) != 0)
						{
							$target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
						}
					}
					else						// at position
					{
						$target_pos = $target_id;
						$parent = $target_parent;
					}

					// insert page into tree
					$this->slm_tree->insertNode($source_obj->getId(),
						$parent, $target_pos);

					// write history entry
/*					if ($movecopy == "move")
					{
						// write history comments
						include_once("classes/class.ilHistory.php");
						ilHistory::_createEntry($source_obj->getId(), "paste",
							array(ilLMObject::_lookupTitle($parent), $parent),
							$this->getType().":pg");
						ilHistory::_createEntry($parent, "paste_page",
							array(ilLMObject::_lookupTitle($source_obj->getId()), $source_obj->getId()),
							$this->getType().":st");
					}
*/

				}
			}
		}

		// handle scos
		if ($source_obj->getType() == "sco")
		{
//echo "2";
			$source_node = $this->slm_tree->getNodeData($source_id);
			$subnodes = $this->slm_tree->getSubtree($source_node);

			// check, if target is within subtree
			foreach ($subnodes as $subnode)
			{
				if($subnode["obj_id"] == $target_id)
				{
					return;
				}
			}

			$target_pos = $target_id;

			if ($first_child)		// as first sco
			{
				$target_pos = IL_FIRST_NODE;
				$target_parent = $target_id;
				
				$pg_childs = $this->slm_tree->getChildsByType($target_parent, "page");
				if (count($pg_childs) != 0)
				{
					$target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
				}
			}
			else if ($as_subitem)		// as last sco
			{
				$target_parent = $target_id;
				$target_pos = IL_FIRST_NODE;
				$childs = $this->slm_tree->getChilds($target_parent);
				if (count($childs) != 0)
				{
					$target_pos = $childs[count($childs) - 1]["obj_id"];
				}
			}

			// delete source tree
			if ($movecopy == "move")
			{
				$this->slm_tree->deleteTree($source_node);
			}
/*			else
			{
				// copy chapter (incl. subcontents)
				$new_chapter =& $source_obj->copy($this->slm_tree, $target_parent, $target_pos);
			}
*/

			if (!$this->slm_tree->isInTree($source_id))
			{
				$this->slm_tree->insertNode($source_id, $target_parent, $target_pos);

				// insert moved tree
				if ($movecopy == "move")
				{
					foreach ($subnodes as $node)
					{
						if($node["obj_id"] != $source_id)
						{
							$this->slm_tree->insertNode($node["obj_id"], $node["parent"]);
						}
					}
				}
			}

			// check the tree
//			$this->checkTree();
		}

		// handle chapters
		if ($source_obj->getType() == "chap")
		{
//echo "2";
			$source_node = $this->slm_tree->getNodeData($source_id);
			$subnodes = $this->slm_tree->getSubtree($source_node);

			// check, if target is within subtree
			foreach ($subnodes as $subnode)
			{
				if($subnode["obj_id"] == $target_id)
				{
					return;
				}
			}

			$target_pos = $target_id;

			if ($first_child)		// as first chapter
			{
				$target_pos = IL_FIRST_NODE;
				$target_parent = $target_id;
				
				$sco_childs = $this->slm_tree->getChildsByType($target_parent, "sco");
				if (count($sco_childs) != 0)
				{
					$target_pos = $sco_childs[count($sco_childs) - 1]["obj_id"];
				}
			}
			else if ($as_subitem)		// as last chapter
			{
				$target_parent = $target_id;
				$target_pos = IL_FIRST_NODE;
				$childs = $this->slm_tree->getChilds($target_parent);
				if (count($childs) != 0)
				{
					$target_pos = $childs[count($childs) - 1]["obj_id"];
				}
			}

			// delete source tree
			if ($movecopy == "move")
			{
				$this->slm_tree->deleteTree($source_node);
			}
/*			else
			{
				// copy chapter (incl. subcontents)
				$new_chapter =& $source_obj->copy($this->slm_tree, $target_parent, $target_pos);
			}
*/

			if (!$this->slm_tree->isInTree($source_id))
			{
				$this->slm_tree->insertNode($source_id, $target_parent, $target_pos);

				// insert moved tree
				if ($movecopy == "move")
				{
					foreach ($subnodes as $node)
					{
						if($node["obj_id"] != $source_id)
						{
							$this->slm_tree->insertNode($node["obj_id"], $node["parent"]);
						}
					}
				}
			}

			// check the tree
//			$this->checkTree();
		}

//		$this->checkTree();
	}
	
	function getExportFiles()
	{
		$file = array();

		require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Export.php");

		$export = new ilSCORM2004Export($this);
		foreach ($export->getSupportedExportTypes() as $type)
		{
			$dir = $export->getExportDirectoryForType($type);
			// quit if import dir not available
			if (!@is_dir($dir) or !is_writeable($dir))
			{
				continue;
			}
			// open directory
			$cdir = dir($dir);

			// get files and save the in the array
			while ($entry = $cdir->read())
			{
				if ($entry != "." and
				$entry != ".." and
				(
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(".$this->getType()."_)*[0-9]+\.zip\$", $entry) or
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(".$this->getType()."_)*[0-9]+\.pdf\$", $entry) or
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(".$this->getType()."_)*[0-9]+\.iso\$", $entry) 
				))
				{
					$file[$entry.$type] = array("type" => $type, "file" => $entry,
						"size" => filesize($dir."/".$entry));
				}
			}

			// close import directory
			$cdir->close();
		}

		// sort files
		ksort ($file);
		reset ($file);
		return $file;
	}

	function exportScorm($a_inst, $a_target_dir, $ver, &$expLog)
	{
		
		$a_xml_writer = new ilXmlWriter;

		$this->exportXMLMetaData($a_xml_writer);
		$metadata_xml = $a_xml_writer->xmlDumpMem(false);
		$a_xml_writer->_XmlWriter;
		
		$xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/metadata.xsl");
		$args = array( '/_xml' => $metadata_xml , '/_xsl' => $xsl );
		$xh = xslt_create();
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args,NULL);
		xslt_free($xh);
		file_put_contents($a_target_dir.'/indexMD.xml',$output);
		//die(htmlentities($metadata_xml).'<br/>'. htmlentities($output));		
		
		$a_xml_writer = new ilXmlWriter;
		// set dtd definition
		$a_xml_writer->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

		// set generated comment
		$a_xml_writer->xmlSetGenCmt("Export of ILIAS Content Module ".	$this->getId()." of installation ".$a_inst.".");

		// set xml header
		$a_xml_writer->xmlHeader();

		global $ilBench;

		$a_xml_writer->xmlStartTag("ContentObject", array("Type"=>"SCORM2004LearningModule"));

		// MetaData
		$this->exportXMLMetaData($a_xml_writer);

		$this->exportXMLStructureObjects($a_xml_writer, $a_inst, &$expLog);
		
		// SCO Objects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Sco Objects");
		$ilBench->start("ContentObjectExport", "exportScoObjects");
		$this->exportXMLScoObjects($a_inst, $a_target_dir, $ver, &$expLog);
		$ilBench->stop("ContentObjectExport", "exportScoObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Sco Objects");
	
		$a_xml_writer->xmlEndTag("ContentObject");
		$a_xml_writer->xmlDumpFile($a_target_dir.'/index.xml', false);
		
		include_once("class.ilContObjectManifestBuilder.php");
		$manifestBuilder = new ilContObjectManifestBuilder($this);
		$manifestBuilder->buildManifest($ver);
		$manifestBuilder->dump($a_target_dir);
			
		$xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/module.xsl");
		$args = array( '/_xml' => file_get_contents($a_target_dir."/imsmanifest.xml"), '/_xsl' => $xsl );
		$xh = xslt_create();
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args,NULL);
		xslt_free($xh);
		fputs(fopen($a_target_dir.'/index.html','w+'),$output);
		
		switch ($ver)
		{
			case "2004":
				ilUtil::rCopy('./Modules/Scorm2004/templates/xsd/adlcp_130_export_2004',$a_target_dir,false);
				break;
			case "12":
				ilUtil::rCopy('./Modules/Scorm2004/templates/xsd/adlcp_120_export_12',$a_target_dir,false);
				break;	
		}
		
		
		$a_xml_writer->_XmlWriter;
	}

	 
	function exportHTML4PDF($a_inst, $a_target_dir, &$expLog)
	{
		global $ilBench;
		$tree = new ilTree($this->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		foreach($tree->getSubTree($tree->getNodeData($tree->getRootId()),true,'sco') as $sco)
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
			$sco_folder = $a_target_dir."/".$sco['obj_id'];
			ilUtil::makeDir($sco_folder);
			$node = new ilSCORM2004Sco($this,$sco['obj_id']);
			$node->exportHTML4PDF($a_inst, $sco_folder, &$expLog);
		}
	}
	
	function exportHTML($a_inst, $a_target_dir, &$expLog)
	{
		$a_xml_writer = new ilXmlWriter;
		// set dtd definition
		$a_xml_writer->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

		// set generated comment
		$a_xml_writer->xmlSetGenCmt("Export of ILIAS Content Module ".	$this->getId()." of installation ".$a_inst.".");

		// set xml header
		$a_xml_writer->xmlHeader();

		global $ilBench;

		$a_xml_writer->xmlStartTag("ContentObject", array("Type"=>"SCORM2004LearningModule"));

		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Sco Objects");
		$ilBench->start("ContentObjectExport", "exportScoObjects");
		$this->exportHTMLScoObjects($a_inst, $a_target_dir, &$expLog);
		$ilBench->stop("ContentObjectExport", "exportScoObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Sco Objects");
	
		$a_xml_writer->xmlEndTag("ContentObject");
		
		include_once("class.ilContObjectManifestBuilder.php");
		$manifestBuilder = new ilContObjectManifestBuilder($this);
		$manifestBuilder->buildManifest('12');
			
		$xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/module.xsl");
		$xml = simplexml_load_string($manifestBuilder->writer->xmlDumpMem());
		$args = array( '/_xml' => $xml->organizations->organization->asXml(), '/_xsl' => $xsl );
		$xh = xslt_create();
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args,NULL);
		xslt_free($xh);
		fputs(fopen($a_target_dir.'/index.html','w+'),$output);
		$a_xml_writer->_XmlWriter;
	}

	/**
	 * export content objects meta data to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	function exportXMLMetaData(&$a_xml_writer)
	{
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

	/**
	 * export structure objects to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	function exportXMLStructureObjects(&$a_xml_writer, $a_inst, &$expLog)
	{
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$tree = new ilTree($this->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		$a_xml_writer->xmlStartTag("StructureObject");
		foreach($tree->getFilteredSubTree($tree->getRootId(),Array('page')) as $obj)
		{
			if($obj['type']=='') continue;
			$md2xml = new ilMD2XML($obj['obj_id'], 0, $obj['type']);
			$md2xml->setExportMode(true);
			$md2xml->startExport();
			$a_xml_writer->appendXML($md2xml->getXML());
		}
		$a_xml_writer->xmlEndTag("StructureObject");
	}


	/**
	 * export page objects to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	function exportXMLScoObjects($a_inst, $a_target_dir, $ver, &$expLog)
	{
		global $ilBench;
		$tree = new ilTree($this->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		foreach($tree->getSubTree($tree->getNodeData($tree->getRootId()),true,'sco') as $sco)
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
			$sco_folder = $a_target_dir."/".$sco['obj_id'];
			ilUtil::makeDir($sco_folder);
			$node = new ilSCORM2004Sco($this,$sco['obj_id']);
			$node->exportScorm($a_inst, $sco_folder, $ver, &$expLog);
		}
	}

	/* export page objects to xml (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	function exportHTMLScoObjects($a_inst, $a_target_dir, &$expLog)
	{
		global $ilBench;
		$tree = new ilTree($this->getId());
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		foreach($tree->getSubTree($tree->getNodeData($tree->getRootId()),true,'sco') as $sco)
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
			$sco_folder = $a_target_dir."/".$sco['obj_id'];
			ilUtil::makeDir($sco_folder);
			$node = new ilSCORM2004Sco($this,$sco['obj_id']);
			$node->exportHTML($a_inst, $sco_folder, &$expLog);
		}
	}
	/* get public export file
	 *
	 * @param	string		$a_type		type ("xml" / "html")
	 *
	 * @return	string		$a_file		file name
	 */
	function getPublicExportFile($a_type)
	{
		return $this->public_export_file[$a_type];
	}

	/**
	 * export files of file itmes
	 *
	 */
	function exportFileItems($a_target_dir, &$expLog)
	{
		include_once("./Modules/File/classes/class.ilObjFile.php");

		foreach ($this->file_ids as $file_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
			$file_obj = new ilObjFile($file_id, false);
			$file_obj->export($a_target_dir);
			unset($file_obj);
		}
	}

	function setPublicExportFile($a_type, $a_file)
	{
		$this->public_export_file[$a_type] = $a_file;
	}

} // END class.ilObjSCORM2004LearningModule
?>
