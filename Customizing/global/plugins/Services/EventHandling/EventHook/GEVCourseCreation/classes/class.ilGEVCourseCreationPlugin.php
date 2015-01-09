<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");

class ilGEVCourseCreationPlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVCourseCreation";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		if ($a_component !== "Services/Object" || $a_event !== "afterClone") {
			return;
		}
		
		require_once("Services/Object/classes/class.ilObject.php");

		if (ilObject::_lookupType($a_parameter["source_ref_id"], true) == "cat") {
			$this->clonedCategory($a_parameter["source_ref_id"], $a_parameter["target_ref_id"]);
		}

		if (ilObject::_lookupType($a_parameter["source_ref_id"], true) !== "crs") {
			return;
		}

		$this->clonedCourse($a_parameter["source_ref_id"], $a_parameter["target_ref_id"]);
	}

	public function clonedCourse($a_source_ref_id, $a_target_ref_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Modules/Course/classes/class.ilObjCourse.php");
		global $ilLog;
				
		try {
			$target = new ilObjCourse($a_target_ref_id);
			$target_obj_id = gevObjectUtils::getObjId($a_target_ref_id);
			$target_utils = gevCourseUtils::getInstance($target_obj_id);
			
			$source = new ilObjCourse($a_source_ref_id);
			$source_obj_id = gevObjectUtils::getObjId($a_source_ref_id);
			$source_utils = gevCourseUtils::getInstance($source_obj_id);
			
			
			// Do this anyway to prevent havoc!
			$target->setOfflineStatus(true);
			$target_utils->setStartDate(null);
			$target_utils->setEndDate(null);
		
			if ($source_utils->isTemplate()) {
				$target->setTitle($source->getTitle());
				$target_utils->setTemplateTitle($source->getTitle());
				$target_utils->setTemplateRefId(intval($a_source_ref_id));
				$target_utils->setIsTemplate(false);
				$source_utils->getMaterialList()->copyTo($target_obj_id);
			}

			$this->setCustomId($target_utils, $source_utils);
			$this->setMailSettings($source_obj_id, $target_obj_id);
			$this->activateCertificateMaybe($source_obj_id, $target_obj_id);

			$target->update();
		}
		catch (Exception $e) {
			$ilLog->write("Error in GEVCourseCreation::clonedCourse: ".print_r($e, true));
			return;
		}
		
		$ilLog->write("Cloned course ".$a_target_ref_id." from course ". $a_source_ref_id);		
	}
		
	public function setCustomId($a_target_utils, $a_source_utils) {
		if ($a_source_utils->isTemplate()) {
			$custom_id_tmplt = $a_source_utils->getCustomId();
		}
		else {
			$custom_id_tmplt = gevCourseUtils::extractCustomId($a_target_utils->getCustomId());
		}

		$custom_id = gevCourseUtils::createNewCustomId($custom_id_tmplt);
		$a_target_utils->setCustomId($custom_id);
	}
	
	public function setMailSettings($a_source_obj_id, $a_target_obj_id) {
		require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");
		$att = new gevCrsMailAttachments($a_source_obj_id);
		$att->copyTo($a_target_obj_id);
		
		require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
		$inv = new gevCrsInvitationMailSettings($a_source_obj_id);
		$inv->copyTo($a_target_obj_id);
		
		require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
		$add = new gevCrsAdditionalMailSettings($a_source_obj_id);
		$add->copyTo($a_target_obj_id);
	}
	
	// Ok, this is ugly. I can't find the correct location to throw afterClone for courses
	// when courses are inside a copied category. So i have to reconstruct the correct
	// source_ and target_ref_ids of courses inside a category.
	public function clonedCategory($a_source_ref_id, $a_target_ref_id) {
		global $ilDB;
		global $ilLog;
		
		/*$query = "SELECT DISTINCT map.source_ref_id, map.target_ref_id "
				."  FROM copy_mappings map"
				."  JOIN object_reference tref ON tref.ref_id = map.target_ref_id AND tref.deleted IS NULL"
				."  JOIN object_data tod ON tod.obj_id = tref.obj_id"
				."  JOIN tree stree ON stree.child = ".$ilDB->quote($a_source_ref_id, "integer")
				."  RIGHT JOIN tree stree2 ON stree2.lft > stree.lft AND stree2.rgt < stree.rgt "
				."  JOIN tree ttree ON ttree.child =  ".$ilDB->quote($a_target_ref_id, "integer")
				."  RIGHT JOIN tree ttree2 ON ttree2.lft > ttree.lft AND ttree2.rgt < ttree.rgt "
				." WHERE tod.type = 'crs'"
				;*/
		$query =  "SELECT cpm.target_ref_id, cpm.source_ref_id"
				 ."  FROM tree t"
				 ."  JOIN tree ttree ON ttree.lft > t.lft"
				 ."   AND ttree.rgt < t.rgt"
				 ."  JOIN object_reference ref ON ref.ref_id = ttree.child"
				 ."  JOIN object_data od ON od.obj_id = ref.obj_id"
				 ."  JOIN copy_mappings cpm ON cpm.target_ref_id = ttree.child"
				 ." WHERE t.child = ".$ilDB->quote($a_target_ref_id, "integer");
		
		$ilLog->write($query);
		
		$res = $ilDB->query($query);
		
		$ret = array();
		while($rec = $ilDB->fetchAssoc($res)) {
			$this->clonedCourse($rec["source_ref_id"], $rec["target_ref_id"]);
		}
		
		global $ilLog;
		$ilLog->write("Cloned category ".$target_ref_id." from category ". $source_ref_id);		
	}
	
	public function activateCertificateMaybe($source_obj_id, $target_obj_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT obj_id FROM il_certificate".
							" WHERE obj_id = ".$ilDB->quote($source_obj_id, "integer")
							);
		if ($ilDB->numRows($res) > 0) {
			$ilDB->manipulate("INSERT INTO il_certificate (obj_id)".
							  " VALUES (".$ilDB->quote($target_obj_id, "integer").")".
							  " ON DUPLICATE KEY UPDATE obj_id = ".$ilDB->quote($target_obj_id, "integer")
							 );
		}
	}
}

?>