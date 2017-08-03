<?php

namespace ILIAS\Modules\OrgUnit\CtrlHelper;

/**
 * Interface BaseCommands
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class BaseCommands {

	const CMD_INDEX = "index";
	const CMD_ADD = "add";
	const CMD_CREATE = "create";
	const CMD_EDIT = "edit";
	const CMD_UPDATE = "update";
	const CMD_CONFIRM = "confirm";
	const CMD_DELETE = "delete";


	abstract protected function index();


	/***
	 * @param $html
	 */
	protected function setContent($html) {
		$this->getTemplate()->setContent($html);
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	protected function getGlobal($name) {
		return $GLOBALS["DIC"][$name];
	}


	/**
	 * @return \ilTemplate
	 */
	protected function getTemplate() {
		return $this->getGlobal("tpl");
	}


	/**
	 * @return \ilToolbarGUI
	 */
	protected function getToolbar() {
		return $this->getGlobal("ilToolbar");
	}
	/**
	 * @return \ilCtrl
	 */
	protected function getCtrl() {
		return $this->getGlobal("ilCtrl");
	}
}
