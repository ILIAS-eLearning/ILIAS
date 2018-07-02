<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

require_once("Services/Component/classes/class.ilPluginAdmin.php");

/**
 * Enhances a course access handler with methods required for the course creation.
 *
 * TODO: Turn this into a proper object with injected deps instead of globals.
 */
trait CourseAccessExtension {
	static public function _checkAccessExtension($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id) {
		if ($a_cmd === "create_course_from_template") {
			return self::_mayUserCreateCourseFromTemplate((int)$a_user_id, (int)$a_ref_id);
		}
	}

	/**
	 * @param	int	$a_ref_id
	 * @param	int	$a_user_id
	 * @return	bool
	 */
	static public function _mayUserCreateCourseFromTemplate($user_id, $ref_id) {
		assert('is_int($ref_id)');
		assert('is_int($user_id)');
		if (!\ilPluginAdmin::isPluginActive("xccr") || !\ilPluginAdmin::isPluginActive("xcps")) {
			return false;
		}
		if (!self::_isTemplateCourse($ref_id)) {
			return false;
		}
		global $DIC;
		$access = $DIC->access();
		if (!$access->checkAccessOfUser($user_id, "copy", "", $ref_id, "crs")) {
			return false;
		}
		if (!self::_userCanInsertCourseInParentCategory($user_id, $ref_id)) {
			return false;
		}
		if (!self::_userCanSeeCopySettingsObject($user_id, $ref_id)) {
			return false;
		}
		return true;
	}

	static public function _isTemplateCourse($ref_id) {
		assert('is_int($ref_id)');
		global $DIC;
		$tree = $DIC->repositoryTree();
		$node = $tree->getNodeData($ref_id);
		return count($tree->getSubTree($node, false, ["xcps"])) > 0;
	}

	static public function _userCanInsertCourseInParentCategory($user_id, $ref_id) {
		assert('is_int($user_id)');
		assert('is_int($ref_id)');
		global $DIC;
		$tree = $DIC->repositoryTree();
		$node = $tree->getNodeData($ref_id);
		$access = $DIC->access();
		return $access->checkAccessOfUser($user_id, "create_crs", "", $node["parent"]);
	}

	static public function _userCanSeeCopySettingsObject($user_id, $ref_id) {
		assert('is_int($ref_id)');
		assert('is_int($user_id)');
		global $DIC;
		$tree = $DIC->repositoryTree();
		$access = $DIC->access();

		$node = $tree->getNodeData($ref_id);
		foreach($tree->getSubTree($node, false, ["xcps"]) as $cp_ref_id) {
			if ($access->checkAccessOfUser($user_id, "visible", "info", $ref_id, "xcps")) {
				return true;
			}
		}
		return false;
	}
}
