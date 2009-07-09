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
*
* @ingroup ModulesScormAicc
*/
class ilObjAICCCourseInterchangeFiles 
{
	var $coursefiles;
	var $data;
	var $errorText;
	var $requiredFiles=array("crs", "au", "cst", "des");
	var $optionalFiles=array("cmp", "ort", "pre");
	
	function ilObjAICCCourseInterchangeFiles() {
		$this->errorText=array();
		$this->coursefiles=array();

	}
	
	//searching for aicc files, beginning in $dir
	// sometimes they are in the main dir - sometimes not
	// sometimes uppercase - sometimes not
	function findFiles($dir) {
		$suffixes=array_merge($this->requiredFiles,$this->optionalFiles);
		$files=$this->getAllFiles($dir);
		foreach ($files as $file) {
			foreach($suffixes as $suffix) {
				if (strcasecmp(substr($file, -(strlen($suffix)+1)), ".".$suffix)==0)
					$this->coursefiles[$suffix] = $file;	
			}
		}
		
		//check for required files
		$missingFiles = array_diff ($this->requiredFiles, array_keys($this->coursefiles));
		if (count($missingFiles)==4)
			$this->errorText[]="Missing all required files.<br>You want to check if your learning module is of a different type.";
		else if (count($missingFiles)>0)
			$this->errorText[]="Missing required file(s): ".implode("<bR>", $missingFiles);
	}

	function readFiles() {
		$this->data=array();
		
		foreach ($this->coursefiles as $suffix=>$filename) {
			if ($suffix=="crs")
				$this->data[$suffix]=$this->readCRS($filename); 
			else
				$this->data[$suffix]=$this->readCSVFile($filename); 			
		}
	
		//Prepare Data-Array: all keys to lower
		$this->data=$this->arraykeys_tolower($this->data);
	}
	
	function getDescriptor($system_id) {
		foreach ($this->data["des"] as $row) {
			if (strcasecmp ($row["system_id"],$system_id)==0)
				return $row;
		}
	}
	
	function validate() {
		$this->checkRequiredKeys();
		$this->checkStructure();
	}
	
	function checkRequiredKeys() {
		//AU
		$requiredKeys=array("system_id", "type", "command_line", "file_name",
												"max_score", "mastery_score", "max_time_allowed",
												"time_limit_action", "system_vendor", "core_vendor",
												"web_launch", "au_password");
		$this->checkCourseFile("au", $this->data["au"], $requiredKeys);
		$this->checkColumnCount("au", $this->data["au"]);
													
		//DES
		$requiredKeys=array("system_id", "title", "description", "developer_id");
		$this->checkCourseFile("des", $this->data["des"], $requiredKeys);
		$this->checkColumnCount("des", $this->data["des"]);
	
		//CRS
		$requiredKeys=array("course_creator", "course_id", "course_system", "course_title",
												 "level", "max_fields_cst", "total_aus", "total_blocks", "version");
		$this->checkCourseFile("crs", $this->data["crs"], $requiredKeys, "course");
		$requiredKeys=array("max_normal");
		$this->checkCourseFile("crs", $this->data["crs"], $requiredKeys, "course_behavior");
					
		//CST
		$requiredKeys=array("block", "member");
		$this->checkCourseFile("cst", $this->data["cst"], $requiredKeys,0);	
		$this->checkColumnCount("cst", $this->data["cst"]);
								
		return $errorText;
	}
	
	function checkCourseFile($fileSuffix, $data, $requiredKeys, $group=0) {
	
		if (count($data)>0 && is_array($data[$group])) {
			
			$keys=array_keys($data[$group]);
	
			$missingKeys = array_diff ($requiredKeys, $keys);
			$optionalKeys = array_diff ($keys, $requiredKeys);
			
			if (count($missingKeys)>0)
				$this->errorText[]="missing keys in ".strtoupper($fileSuffix)."-File: ".implode(",", $missingKeys);
			
		} else if (count($data)>0 && !is_array($data[$group])) {
			$this->errorText[]="missing Group in ".strtoupper($fileSuffix)."-File: $group";
		} else {
			$this->errorText[]="empty ".strtoupper($fileSuffix)."-File";
		}

	}
	
	function checkColumnCount($fileSuffix, $data) {
		if (count($data)>0) {
			$colcount=-1;
			for ($colnr=0;$colnr<count($data);$colnr++) {
				if ($colcount==-1)
					$colcount=count($data[$colnr]);
				else if ($colcount!=count($data[$colnr]))
					$this->errorText[]=strtoupper($fileSuffix)."-File: unexpected number of columns in line ".($colnr+2);
			}
		}
	}
	
	function checkStructure() {
		
		//max member fields in cst-file
		$max=$this->data[crs][course][max_fields_cst];
		for ($row=0;$row<count($this->data[cst]);$row++) {
			$value=$this->data[cst][$row][member];
			if ((is_array($value) && count($value)>$max) || (!is_array($value) && $max==1)) {
				$this->errorText[]="CRS-File: max_fields_cst does not match number of fields in the CST-File";
				return;
			}
		}
		
		//additional validation statements
		//
		//
		
	}
	
	
	function readCRS($filename) {
		$data=@parse_ini_file($filename, TRUE);
	
		//crs-file is not a valid iniFile
		//thats why reading the file again to get course_description
		$lines=file($filename);
		for($i=0;$i<count($lines);$i++) {
			if (trim($lines[$i])=="[Course_Description]") {
				for ($i++;$i<count($lines);$i++) {
					if (strlen(trim($lines[$i]))>0) {
						$data["Course_Description"][description]=$lines[$i];
						break;
					}
				}
			}
		}
		
		return $data;
	}
	
	function readCSVFile($filename) {
		$row=1;
		$handle = fopen($filename, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if ($row++==1) {
				$header=$data;
			} else if (count($data)>1) {
				$data2=array();
				for ($col=0; $col<count($data); $col++) {
					if (array_key_exists($header[$col], $data2)) {
						$value=$data2[$header[$col]];
						if (!is_array($value))
							$data2[$header[$col]]=array($value, utf8_encode($data[$col]));
						else
							$data2[$header[$col]][]=utf8_encode($data[$col]);
					} else
						$data2[$header[$col]]=utf8_encode($data[$col]);
				}
				$rows[]=$data2;	
			}
		}
		fclose($handle);
		return $rows;
	}
	
	function getAllFiles($dir, $arr=array()) {
		if (substr($dir, -1)!="/")
			$dir.="/";

		$handle=opendir($dir); 
		while ($file = readdir ($handle)) { 
	 	  if ($file != "." && $file != "..") { 
	   		if (is_dir($dir.$file))
	   			$arr=$this->getAllFiles($dir.$file, $arr);
	    	else
	    		$arr[]=$dir.$file;
	   	} 
		}
		closedir($handle); 	
		return $arr;
	}
	
	function arraykeys_tolower($arr) {
		$arr=array_change_key_case($arr, CASE_LOWER);
		foreach ($arr as $k=>$v) {
			if (is_array($v))
				$arr[$k]=$this->arraykeys_tolower($v);
		}
		return $arr;
	}
	
	function writeToDatabase($alm_id) {
		include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCTree.php");
		include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCCourse.php");
		include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCUnit.php");
		include_once("./Modules/ScormAicc/classes/AICC/class.ilAICCBlock.php");
		
		//write course to database
		$course=new ilAICCCourse();
		$course->setALMId($alm_id);
		$course->setSystemId("root");
		$course->setTitle($this->data["crs"]["course"]["course_title"]);
		$course->setDescription($this->data["crs"]["course_description"]["description"]);
		
		$course->setCourseCreator($this->data["crs"]["course"]["course_creator"]);
		$course->setCourseId($this->data["crs"]["course"]["course_id"]);
		$course->setCourseSystem($this->data["crs"]["course"]["course_system"]);
		$course->setCourseTitle($this->data["crs"]["course"]["course_title"]);
		$course->setLevel($this->data["crs"]["course"]["level"]);
		$course->setMaxFieldsCst($this->data["crs"]["course"]["max_fields_cst"]);
		$course->setMaxFieldsOrt($this->data["crs"]["course"]["max_fields_ort"]);
		$course->setTotalAUs($this->data["crs"]["course"]["total_aus"]);
		$course->setTotalBlocks($this->data["crs"]["course"]["total_blocks"]);
		$course->setTotalComplexObj($this->data["crs"]["course"]["total_complex_obj"]);
		$course->setTotalObjectives($this->data["crs"]["course"]["total_objectives"]);
		$course->setVersion($this->data["crs"]["course"]["version"]);
		$course->setMaxNormal($this->data["crs"]["course_behavior"]["max_normal"]);
		$course->setDescription($this->data["crs"]["course_description"]["description"]);
		$course->create();	
		$identifier["root"]=$course->getId();
		
		//all blocks
		foreach ($this->data["cst"] as $row) {
			$system_id=strtolower($row["block"]);
			if ($system_id!="root") {
				$unit=new ilAICCBlock();
				$description=$this->getDescriptor($system_id);
				$unit->setALMId($alm_id);
				$unit->setType("sbl");
				$unit->setTitle($description["title"]);
				$unit->setDescription($description["description"]);
				$unit->setDeveloperId($description["developer_id"]);
				$unit->setSystemId($description["system_id"]);
				$unit->create();
				$identifier[$system_id]=$unit->getId();
			}
		}
	
		//write assignable units to database
		foreach ($this->data["au"] as $row) {
			$sysid=strtolower($row["system_id"]);
			$unit=new ilAICCUnit();
			
			$unit->setAUType($row["c_type"]);
			$unit->setCommand_line($row["command_line"]);
			$unit->setMaxTimeAllowed($row["max_time_allowed"]);
			$unit->setTimeLimitAction($row["time_limit_action"]);
			$unit->setMaxScore($row["max_score"]);
			$unit->setCoreVendor($row["core_vendor"]);
			$unit->setSystemVendor($row["system_vendor"]);
			$unit->setFilename($row["file_name"]);
			$unit->setMasteryScore($row["mastery_score"]);
			$unit->setWebLaunch($row["web_launch"]);
			$unit->setAUPassword($row["au_password"]);
				
			$description=$this->getDescriptor($sysid);
			$unit->setALMId($alm_id);
			$unit->setType("sau");
			$unit->setTitle($description["title"]);
			$unit->setDescription($description["description"]);
			$unit->setDeveloperId($description["developer_id"]);
			$unit->setSystemId($description["system_id"]);
			$unit->create();
			$identifier[$sysid]=$unit->getId();	
		}
		
		//write tree
		$tree =& new ilAICCTree($alm_id);
		$tree->addTree($alm_id, $identifier["root"]);
		
		//writing members
		foreach ($this->data["cst"] as $row) {
			$members=$row["member"];
			if (!is_array($members))
				$members=array($members);
			$parentid=$identifier[strtolower($row["block"])];

			foreach($members as $member) {
				$memberid=$identifier[strtolower($member)];
				$tree->insertNode($memberid, $parentid);
			}
		}		
	}
	
}

?>