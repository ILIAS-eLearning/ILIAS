<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* 
*
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/


require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/Tree/classes/class.ilTree.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

class gevImportOrgStructure {
	
	public function __construct() {
		global $ilDB;
		$this->db = &$ilDB;
		$this->connectShadowDB();

	}



	private function connectShadowDB(){
		global $ilClientIniFile;
		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$this->shadowDB = $mysql;
	}

	
	
	private function createSingleOrgUnit($rec){
		//global $tree;

		$orgu = new ilObjOrgUnit();
		$orgu->setTitle($rec['title']);
		$orgu->create();
		$orgu->createReference();
		
		$id = $orgu->getId();
		$refId = $orgu->getRefId();

print '<hr>';
print '<b>' .$rec['title'] .'</b><br>';
print_r($rec);

		$orgutils = gevOrgUnitUtils::getInstance($id);

		//ilTree.insertNode, find the parent id
		if($rec['parent'] == 'root'){
			$parent = $orgu->getRootOrgRefId();

		}else{
			//get title from shadowDB
			$parent_id = $rec['parent'];
			$sql = "SELECT title FROM interimOrgUnits WHERE id='$parent_id'";

			$result = mysql_query($sql, $this->shadowDB);
			$record = mysql_fetch_assoc($result);

			//get refId from object_reference via object_data.title
			$sql = "SELECT oref.ref_id FROM object_data od"
					." INNER JOIN object_reference oref ON od.obj_id = oref.obj_id"
					." WHERE od.type='orgu' AND od.title=" .$this->db->quote($record['title'], 'text');

			$result = $this->db->query($sql);
			$record = $this->db->fetchAssoc($result);
			$parent = $record['ref_id'];
		}

print '<br>put in tree: ' . $id .'('. $refId.') under ' .$parent;
		
		$orgu->putInTree($parent);


		$orgutils->setType(gevSettings::ORG_TYPE_DEFAULT);
		//$orgutils->orgu_instance = null;

		if($rec['zip']){
			$orgutils->setZipcode($rec['zip']);
print '<pre>';
print_r($orgutils);
die();

		}
		$orgutils->setCity($rec['city']);
		$orgutils->setContactPhone($rec['fon']);
		$orgutils->setContactFax($rec['fax']);
		$orgutils->setFinancialAccount($rec['finaccount']);
		//$rec['street']
		//$orgutils->setStreet();
		//$orgutils->setHouseNumber($rec['title']);


	}



	public function createOrgUnits(){
		//start with root
		$sql = "SELECT * from interimOrgUnits WHERE parent = 'root'";
		$result = mysql_query($sql, $this->shadowDB);
		while($record = mysql_fetch_assoc($result)) {
			$this->createSingleOrgUnit($record);
		}

		//order by parent (notice: "evg" before "O-" or "W-")
		$sql = "SELECT * from interimOrgUnits WHERE parent != 'root' ORDER BY parent ASC";
		$result = mysql_query($sql, $this->shadowDB);
		while($record = mysql_fetch_assoc($result)) {
			$this->createSingleOrgUnit($record);
		}

	}



}
