<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

require_once("Services/TMS/PluginObjectFactory.php");

/**
 * Enhances a course list gui with methods required for display of the action to
 * create the course.
 */
trait CourseListGUIExtension {
	use LinkHelper;
	use \PluginObjectFactory;

	/**
	 * @return	\ilCtrl
	 */
	protected function getCtrl() {
		return $this->ctrl;
	}

	/**
	 * @return \ilLanguage
	 */
	protected function getLng() {
		return $this->lng;
	}

	/**
	 * @inheritdoc
	 */
	protected function getUser() {
		return $this->user;
	}

	/**
	 * @inheritdoc
	 */
	protected function sendInfo($message) {
		\ilUtil::sendInfo($message);
	}

	/**
	 * Overwritten from ilObjectListGUI. Enhances the supplied commands by
	 * a custom command for the course creation.
	 *
	 * @inheritdocs
	 */
	public function getCommands() {
		$commands = parent::getCommands();
		if ($this->getCreateCourseAccessGranted()
			&& $this->noOpenRequests()
		) {
			$commands[] =
				[ "cmd" => $this->getCreateCourseCommand()
				, "link" => $this->getCreateCourseCommandLink(["ilRepositoryGUI"], "frameset", (int)$this->parent_ref_id, (int)$this->ref_id)
				, "frame" => ""
				, "lang_var" => $this->getCreateCourseCommandLngVar()
				, "txt" => null
				, "granted" => $this->getCreateCourseAccessGranted()
				, "access_info" => null
				, "img" => null
				, "default" => null
				];
		}
		return $commands;
	}

	protected function getCreateCourseAccessGranted() {
		return \ilObjCourseAccess::_checkAccess($this->getCreateCourseCommand(), "copy", $this->ref_id, $this->obj_id);
	}

	protected function noOpenRequests() {
		return count($this->getUsersDueRequests($this->getUser(), $this->getCourseCreationPlugin())) == 0;
	}
}
