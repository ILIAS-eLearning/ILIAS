<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("./Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("./Services/GEV/Utils/classes/class.gevSettings.php");
require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");

/**
 * Class gevOrgUnitImport
 *
 * Helper class to import Generali OrgUnit structure.
 *
 * @author: Fabian Kochem <fabian.kochem@concepts-and-training.de>
 *
 */
class gevOrgUnitImport {

	protected $root_id;
	private $mysql;
	private $index = array();
	static $instance = null;

	protected function __construct($mysql, $root_id) {
		$this->root_id = $root_id;
		$this->mysql = $mysql;

		$this->obj_cache = new gevOrgUnitCache($root_id);
		$this->obj_cache->index();

		$this->readFromShadowDB();
		$this->importIndex();
	}

	public function getInstance($mysql, $root_id=null) {
		if (self::$instance !== null) {
			return self::$instance;
		}

		if ($root_id === null) {
			$root_id = ilObjOrgUnit::getRootOrgRefId();
		}

		self::$instance = new self($mysql, $root_id);
		return self::$instance;
	}

	public function readFromShadowDB() {
		$sql = "SELECT * FROM ivimport_cleanedorgunit ORDER BY org_einheit";
		$result = mysql_query($sql, $this->mysql);
		while ($row = mysql_fetch_assoc($result)) {
			$this->index[$row['org_einheit']] = $row;
		}
	}

	public function importIndex() {
		$objects = array();
		foreach($this->index as $id => $row) {
			$obj = $this->importRow($row);
			$this->putOrgUnitInTree($obj, $row);
			$obj->initDefaultRoles();
		}
	}

	public function importRow($row) {
		$import_id = (string)$row['org_einheit'];
		if ($this->obj_cache->isImportIdInCache($import_id)) {
			die('import ID exists, update not implemented: '.$import_id);
		} else {
			$obj = $this->createOrgUnit($import_id, $row);

		}
		$this->obj_cache->addToCache($obj->getRefId(), $import_id);
		return $obj;
	}

	public function createOrgUnit($import_id, $row) {
		$obj = new ilObjOrgUnit();
		$this->setAttributes($obj, $import_id, $row);
		$obj->create();
		$obj->createReference();
		return $obj;
	}

	private function setAttributes(&$obj, $import_id, $row) {
		$title = $row['name'];
		echo $title."\n";
		$obj->setImportId($import_id);

		$obj->setTitle($title);

		$utils = gevOrgUnitUtils::getInstance($obj->getId());

		$obj->update();
		$utils->setType(gevSettings::ORG_TYPE_DEFAULT);

		$utils->setStreet($row['strasse']);
		$utils->setZipcode($row['plz']);
		$utils->setCity($row['ort']);
		$utils->setContactPhone($row['telefon']);
		$utils->setContactFax($row['fax']);
		$obj->update();
	}

	public function putOrgUnitInTree(&$obj, $row) {
		$parent_import_id = $row['parent_org_einheit'];
		if ($this->obj_cache->isImportIdInCache($parent_import_id)) {
			$parent_id = $this->obj_cache->getRefIdByImportId($parent_import_id);
		} else {
			$parent_id = $this->root_id;
		}
		$obj->putInTree($parent_id);
	}
}

?>
