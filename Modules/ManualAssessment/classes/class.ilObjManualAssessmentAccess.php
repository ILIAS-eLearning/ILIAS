<?php

require_once('./Services/Object/classes/class.ilObjectAccess.php');

class ilObjManualAssessmentAccess extends ilObjectAccess {


	/**
	 * @inheritdoc
	 */
	static function _getCommands() {
		$commands = array(
			array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "edit", "default" => false)
		);
		
		return $commands;
	}
}