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


	private function extract_house_nr($streetnr){

		//special cases:
		//Mannheim, Q5
		$i = 0 ;
		if(strtoupper(substr(trim($streetnr), 0, 2)) == 'Q5') {
		    $i = 2;
		}
		if(strtoupper(substr(trim($streetnr), 0, 3)) == 'Q 5') {
		    $i = 3;
		}

		//find first number in string
	    $len = strlen($streetnr);
	    $pos = False;
	    for($i; $i < $len; $i++) {
	        if(is_numeric($streetnr[$i])) {
	        	$pos = $i;
	        	break;
	        }
	    }
	    $street = trim(substr($streetnr, 0, $pos));
	    $nr = trim(substr($streetnr, $pos));
		return array(
			'street' => trim($street), 
			'nr' =>trim($nr)
		);
	}
	
	private function notEmptyString($v){
		if(trim($v) === ''){
			$v = ' ';
		}
		return $v;
	}
	
	private function updateIlIdForOrgUnit($orgu_id, $ilid){
		$sql = "UPDATE interimOrgUnits SET ilid='$ilid' WHERE id='$orgu_id'";
		//print '<br>' .$sql;
		//flush();
		mysql_query($sql, $this->shadowDB);
	}

	private function createSingleOrgUnit($rec){
		//global $tree;

		$orgu = new ilObjOrgUnit();
		$orgu->setTitle($rec['title']);
		$orgu->create();
		$orgu->createReference();
		$orgu->setImportId($rec["id"]);
		$orgu->update();
		
		$id = $orgu->getId();
		$refId = $orgu->getRefId();

		//print '<i>' .$rec['title'] .'</i><br>';

		$orgutils = gevOrgUnitUtils::getInstance($id);

		//ilTree.insertNode, find the parent id
		if($rec['parent'] == 'root'){
			$parent = $orgu->getRootOrgRefId();
		}else{
			//get title from shadowDB
			$parent_id = $rec['parent'];
			$sql = "SELECT ilid FROM interimOrgUnits WHERE id='$parent_id'";

			$result = mysql_query($sql, $this->shadowDB);
			$record = mysql_fetch_assoc($result);

			if (!$record) {
				die("ERROR: ".mysql_error());
			}

			//get refId from object_reference via object_data.title
			$sql = "SELECT oref.ref_id FROM object_data od"
					." INNER JOIN object_reference oref ON od.obj_id = oref.obj_id AND oref.deleted IS NULL"
					." WHERE od.type='orgu' AND od.obj_id=" .$this->db->quote($record['ilid'], 'integer');
			$result = $this->db->query($sql);
			$record = $this->db->fetchAssoc($result);
			$parent = $record['ref_id'];
		}
		
		$orgu->putInTree($parent);
		$orgu->initDefaultRoles();

		$orgutils->setType(gevSettings::ORG_TYPE_DEFAULT);
		$orgutils->setZipcode($this->notEmptyString($rec['zip']));
		$orgutils->setCity($this->notEmptyString($rec['city']));
		$orgutils->setContactPhone($this->notEmptyString($rec['fon']));
		$orgutils->setContactFax($this->notEmptyString($rec['fax']));
		$orgutils->setFinancialAccount($this->notEmptyString($rec['finaccount']));


		$streetnr = $this->extract_house_nr($this->notEmptyString($rec['street']));
		$orgutils->setStreet($this->notEmptyString($streetnr['street']));
		$orgutils->setHouseNumber($this->notEmptyString($streetnr['nr']));

		$this->updateIlIdForOrgUnit($rec['id'], $id);
	}



	public function createOrgUnits(){
		$parents = array("root");
		
		while(count($parents) > 0) {
			$new_parents = array();
			foreach ($parents as $parent) {
				//start with root
				$sql = "SELECT * from interimOrgUnits WHERE parent = '".$parent."'";
				$result = mysql_query($sql, $this->shadowDB);
				
				if ($result === false) {
					die("ERROR: ".mysql_error());
				}
				
				while($record = mysql_fetch_assoc($result)) {
					$this->createSingleOrgUnit($record);
					$new_parents[] = $record["id"];
				}
			}
			$parents = $new_parents;
		}
	}
}
