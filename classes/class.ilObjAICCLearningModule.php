<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
require_once "classes/class.ilMetaData.php";

/**
* Class ilObjAICCLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObject
* @package ilias-core
*/
class ilObjAICCLearningModule extends ilObject
{
	var $meta_data;
	var $cifModule;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjAICCLearningModule($a_id = 0, $a_call_by_reference = true)
	{
		$this->cifModule=new AICC_CourseInterchangeFiles();
		
		$this->type = "alm";
		parent::ilObject($a_id,$a_call_by_reference);
		if ($a_id == 0)
		{
			$new_meta =& new ilMetaData();
			$this->assignMetaData($new_meta);
		}

	}
	
	function getCiFileModule() {
		return $this->cifModule;
	}

	/**
	* create file based lm
	*/
	function create()
	{
		global $ilDB;

		parent::create();
		$this->createDataDirectory();
		$this->meta_data->setId($this->getId());
//echo "<br>title:".$this->getId();
		$this->meta_data->setType($this->getType());
//echo "<br>title:".$this->getType();
		$this->meta_data->setTitle($this->getTitle());
//echo "<br>title:".$this->getTitle();
		$this->meta_data->setDescription($this->getDescription());
		$this->meta_data->setObject($this);
		$this->meta_data->create();

		$q = "INSERT INTO aicc_lm (id, online, api_adapter) VALUES ".
			" (".$ilDB->quote($this->getID()).",".$ilDB->quote("n").",".
			$ilDB->quote("API").")";
		$ilDB->query($q);
	}

	/**
	* read object
	*/
	function read()
	{
		parent::read();
		$this->meta_data =& new ilMetaData($this->getType(), $this->getId());

		$q = "SELECT * FROM aicc_lm WHERE id = '".$this->getId()."'";
		$lm_set = $this->ilias->db->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setOnline(ilUtil::yn2tf($lm_rec["online"]));
		$this->setAPIAdapterName($lm_rec["api_adapter"]);
		$this->setAPIFunctionsPrefix($lm_rec["api_func_prefix"]);

	}

	/**
	* get title of content object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return $this->meta_data->getTitle();
	}

	/**
	* set title of content object
	*
	* @param	string	$a_title		title
	*/
	function setTitle($a_title)
	{
		$this->meta_data->setTitle($a_title);
	}

	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
		return $this->meta_data->getDescription();
	}

	/**
	* set description of content object
	*
	* @param	string	$a_description		description
	*/
	function setDescription($a_description)
	{
		$this->meta_data->setDescription($a_description);
	}

	/**
	* assign a meta data object to content object
	*
	* @param	object		$a_meta_data	meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object of content object
	*
	* @return	object		meta data object
	*/
	function &getMetaData()
	{
		return $this->meta_data;
	}


	/**
	* creates data directory for package files
	* ("./data/lm_data/lm_<id>")
	*/
	function createDataDirectory()
	{
		$lm_data_dir = ilUtil::getWebspaceDir()."/lm_data";
		ilUtil::makeDir($lm_data_dir);
		ilUtil::makeDir($this->getDataDirectory());
	}

	/**
	* get data directory of lm
	*/
	function getDataDirectory($mode = "filesystem")
	{
		$lm_data_dir = ilUtil::getWebspaceDir($mode)."/lm_data";
		$lm_dir = $lm_data_dir."/lm_".$this->getId();

		return $lm_dir;
	}

	/**
	* update meta data only
	*/
	function updateMetaData()
	{
		$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->meta_data->setTitle($meta[0]["value"]);
			$meta = $this->meta_data->getElement("Description", "General");
			$this->meta_data->setDescription($meta[0]["value"]);
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		parent::update();

	}


	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		$this->updateMetaData();

		$q = "UPDATE aicc_lm SET ".
			" online = '".ilUtil::tf2yn($this->getOnline())."',".
			" api_adapter = '".$this->getAPIAdapterName()."',".
			" api_func_prefix = '".$this->getAPIFunctionsPrefix()."'".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* get api adapter name
	*/
	function getAPIAdapterName()
	{
		return $this->api_adapter;
	}

	/**
	* set api adapter name
	*/
	function setAPIAdapterName($a_api)
	{
		$this->api_adapter = $a_api;
	}

	/**
	* get api functions prefix
	*/
	function getAPIFunctionsPrefix()
	{
		return $this->api_func_prefix;
	}

	/**
	* set api functions prefix
	*/
	function setAPIFunctionsPrefix($a_prefix)
	{
		$this->api_func_prefix = $a_prefix;
	}

	/**
	* get online
	*/
	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	/**
	* set online
	*/
	function getOnline()
	{
		return $this->online;
	}


	/**
	* copy all properties and subobjects of a SCROM LearningModule.
	*
	* @access	public
	* @return	integer	new ref id
	*/
	function clone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::clone($a_parent_ref);
		
		// put here slm specific stuff

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete SCORM learning module and all related data	
	*
	* this method has been tested on may 9th 2004
	* meta data, scorm lm data, scorm tree, scorm objects (organization(s),
	* manifest, resources and items), tracking data and data directory
	* have been deleted correctly as desired
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete meta data of scorm content object
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();

		// delete data directory
		ilUtil::delDir($this->getDataDirectory());

		// delete scorm learning module record
		$q = "DELETE FROM aicc_lm WHERE id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
/*
		// remove all scorm objects and scorm tree
		include_once("content/classes/SCORM/class.ilSCORMTree.php");
		include_once("content/classes/SCORM/class.ilSCORMObject.php");
		$sc_tree = new ilSCORMTree($this->getId());
		$items = $sc_tree->getSubTree($sc_tree->getNodeData($sc_tree->readRootId()));
		foreach($items as $item)
		{
			$sc_object =& ilSCORMObject::_getInstance($item["obj_id"]);
			$sc_object->delete();
		}
		$sc_tree->removeTree($sc_tree->getTreeId());
*/

		// always call parent delete function at the end!!
		return true;
	}


	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional paramters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;

		switch ($a_event)
		{
			case "link":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "SCORMLearningModule ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "cut":

				//echo "SCORMLearningModule ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "SCORMLearningModule ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":
				
				//echo "SCORMLearningModule ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "new":
				
				//echo "SCORMLearningModule ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}

		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}

	/**
	* get all tracking items of scorm object
	*/
	function getTrackingItems()
	{
/*		
		include_once("content/classes/SCORM/class.ilSCORMTree.php");
		$tree = new ilSCORMTree($this->getId());
		$root_id = $tree->readRootId();

		$items = array();
		$childs = $tree->getSubTree($tree->getNodeData($root_id));
		foreach($childs as $child)
		{
			if($child["type"] == "sit")
			{
				include_once("content/classes/SCORM/class.ilSCORMItem.php");
				$sc_item =& new ilSCORMItem($child["obj_id"]);
				if ($sc_item->getIdentifierRef() != "")
				{
					$items[count($items)] =& $sc_item;
				}
			}
		}
*/
		return $items;
	}


} // END class.ilObjAICCLearningModule

class AICC_CourseInterchangeFiles {
	
	var $coursefiles;
	var $data;
	var $errorText;
	var $requiredFiles=array("crs", "au", "cst", "des");
	var $optionalFiles=array("cmp", "ort", "pre");
	
	function AICC_CourseInterchangeFiles() {
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
		if (count($missingFiles)>0)
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
							$data2[$header[$col]]=array($value, $data[$col]);
						else
							$data2[$header[$col]][]=$data[$col];
					} else
						$data2[$header[$col]]=$data[$col];
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
	
}
?>
