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

	public function simpleUserImport($file_path){
		$this->stats = array("updated" => 0, "deleted" => 0, "edited" => 0);
		$a = file_get_contents($file_path, "r");
		$xml = new SimpleXMLElement($a);
		foreach($xml->Assignment as $a){
			$this->simpleUserImportElement($a);
		}
	}

	public function simpleUserImportElement(SimpleXMLElement $a){
		global $rbacadmin;
		$attributes = $a->attributes();
		$action = $attributes->action;
		$user_id_type = $a->User->attributes()->id_type;
		$user_id = (string) $a->User;
		$org_unit_id_type = $a->OrgUnit->attributes()->id_type;
		$org_unit_id = (string) $a->OrgUnit;
		$role = (string) $a->Role;

		if(!$user_id = $this->buildUserId($user_id, $user_id_type)){
			$this->addError("user_not_found", $user_id);
			return;
		}

		if(!$org_unit_id = $this->buildRef($org_unit_id, $org_unit_id_type)){
			$this->addError("org_unit_not_found", $org_unit_id);
			return;
		}
		$org_unit = new ilObjOrgUnit($org_unit_id);

		if($role == "employee"){
			$role_id = $org_unit->getEmployeeRole();
		}elseif($role == "superior")
			$role_id = $org_unit->getSuperiorRole();
		else{
			$this->addError("not_a_valid_role", $user_id);
			return;
		}


		if($action == "add"){
			$rbacadmin->assignUser($role_id, $user_id);
			$this->stats["created"]++;
		}elseif($action == "remove"){
			$rbacadmin->deassignUser($role_id, $user_id);
			$this->stats["removed"]++;
		}else{
			$this->addError("not_a_valid_role", $user_id);
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
			if(!ilObjOrgUnit::_exists($id, true))
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

	private function buildUserId($id, $type){
		global $ilDB;
		if($type == "ilias_login"){
			$user_id = ilObjUser::_lookupId($id);
			return $user_id?$user_id:false;
		}elseif($type == "external_id"){
			$user_id = ilObjUser::_lookupObjIdByImportId($id);
			return $user_id?$user_id:false;
		}elseif($type == "email"){
			$q = "SELECT usr_id FROM usr_data WHERE email = ".$ilDB->quote($id, "text");
			$set = $ilDB->query($q);
			$user_id = $ilDB->fetchAssoc($set);
			return $user_id?$user_id:false;
		}elseif($type == "user_id"){
			return $id;
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