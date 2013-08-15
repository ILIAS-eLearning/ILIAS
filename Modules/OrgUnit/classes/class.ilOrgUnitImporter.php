<?php

require_once("./Modules/Category/classes/class.ilCategoryImporter.php");
class ilOrgUnitImporter extends ilCategoryImporter {

	/** @var  array lang_var => language variable, import_id => the reference or import id, depending on the ou_id_type */
	private $errors;

	/** @var  array lang_var => language variable, import_id => the reference or import id, depending on the ou_id_type */
	private $warnings;

	/** @var array keys in {updated, edited, deleted} */
	private $stats;

	public function simpleImport($file_path){
		global $lng;
		$this->stats = array("updated" => 0, "deleted" => 0, "edited" => 0);
		$a = file_get_contents($file_path, "r");
		$xml = new SimpleXMLElement($a);
		foreach($xml->OrgUnit as $o){
			$this->simpleImportElement($o);
		}
	}

	public function simpleImportElement(SimpleXMLElement $o){
		global $tree, $tpl;
		$title = $o->title;
		$description = $o->description;
		$external_id = $o->external_id;
		$create_mode = true;
		$attributes = $o->attributes();
		$action = (string)$attributes->action;
		$ou_id = (string)$attributes->ou_id;
		$ou_id_type = (string)$attributes->ou_id_type;
		$ou_parent_id = (string)$attributes->ou_parent_id;
		$ou_parent_id_type = (string)$attributes->ou_parent_id_type;

		if($ou_id == ilObjOrgUnit::getRootOrgRefId()){
			$this->addWarning("cannot_change_root_node", $ou_id, $action);
			return;
		}

		if($ou_parent_id == "__ILIAS"){
			$ou_parent_id = ilObjOrgUnit::getRootOrgRefId();
			$ou_parent_id_type = "reference_id";
		}

		$ref_id = $this->buildRef($ou_id, $ou_id_type);
		$parent_ref_id = $this->buildRef($ou_parent_id, $ou_parent_id_type);

		if($action == "delete"){
			if(!$parent_ref_id){
				$this->addError("ou_parent_id_not_valid", $ou_id, $action);
				return;
			}
			if(!$ref_id){
				$this->addError("ou_id_not_valid", $ou_id, $action);
				return;
			}
			include_once("./Services/Repository/classes/class.ilRepUtil.php");
			$ru = new ilRepUtil($this);
			try{
				$ru->deleteObjects($parent_ref_id, array($ref_id)) !== false;
				$this->stats["deleted"]++;
			}catch(Excpetion $e){
				$this->addWarning("orgu_already_deleted", $ou_id, $action);
			}
			return;
		}elseif($action == "update"){
			if(!$parent_ref_id){
				$this->addError("ou_parent_id_not_valid", $ou_id, $action);
				return;
			}
			if(!$ref_id){
				$this->addError("ou_id_not_valid", $ou_id, $action);
				return;
			}
			$object = new ilObjOrgUnit($ref_id);
			$object->setTitle($title);
			$object->setDescription($description);
			$object->update();
			$object->setImportId($external_id);
			if($parent_ref_id != $tree->getParentId($ref_id)){
				try{
					$tree->moveTree($ref_id, $parent_ref_id);
				}catch(Exception $e){
					global $ilLog;
					$this->addWarning("not_movable", $ou_id, $action);
					$ilLog->write($e->getMessage()."\\n".$e->getTraceAsString());
				}
			}
			$this->stats["updated"]++;
		}elseif($action == "create"){
			if(!$parent_ref_id){
				$this->addError("ou_parent_id_not_valid", $ou_id, $action);
				return;
			}
			$object = new ilObjOrgUnit();
			$object->setTitle($title);
			$object->setDescription($description);
			$object->setImportId($external_id);
			$object->create();
			$object->createReference();
			$object->putInTree($parent_ref_id);
			$object->setPermissions($ou_parent_id);
			$this->stats["created"]++;
		}else{
			$this->addError("no_valid_action_given", $ou_id, $action);
		}
	}

	private function buildRef($id, $type){
		if($type == "reference_id"){
			if(!ilObject::_exists($id))
				return false;
			return $id;
		}
		elseif($type == "external_id"){
			$obj_id = ilObject::_lookupObjIdByImportId($id);
			$ref_ids = ilObject::_getAllReferences($obj_id);
			if(!count($ref_ids))
				return false;
			return array_shift($ref_ids);
		}else
			return false;
	}

	public function hasErrors(){
		return count($this->errors)!=0;
	}

	public function hasWarnings(){
		return count($this->warnings)!=0;
	}

	private function addWarning($lang_var, $import_id, $action = null){
		$this->warnings[] = array("lang_var" => $lang_var, "import_id" => $import_id, "action" => $action);
	}

	private function addError($lang_var, $import_id, $action = null){
		$this->errors[] = array("lang_var" => $lang_var, "import_id" => $import_id, "action" => $action);
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @return array
	 */
	public function getWarnings()
	{
		return $this->warnings;
	}

	/**
	 * @return array
	 */
	public function getStats()
	{
		return $this->stats;
	}
}