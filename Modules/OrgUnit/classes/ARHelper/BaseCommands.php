<?php

namespace ILIAS\Modules\OrgUnit\ARHelper;

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
	const CMD_CANCEL = "cancel";
	const AR_ID = "arid";


	abstract protected function index();


	abstract protected function cancel();


	/***
	 * @param $html
	 */
	protected function setContent($html) {
		$this->tpl()->setContent($html);
	}


	public function executeCommand() {
		$this->dic()->language()->loadLanguageModule("orgu");
		$cmd = $this->dic()->ctrl()->getCmd(self::CMD_INDEX);
		switch ($cmd) {
			default:
				if ($this->checkRequestReferenceId()) {
					$this->{$cmd}();
				}
				break;
		}
	}


	protected function checkRequestReferenceId() {
		/**
		 * @var $ilAccess \ilAccessHandler§
		 */
		$http = $this->dic()->http();
		$ref_id = $http->request()->getQueryParams()["ref_id"];
		if ($ref_id) {
			return $this->dic()->access()->checkAccess("read", "", $ref_id);
		}

		return true;
	}


	/**
	 * @param $variable
	 *
	 * @return mixed
	 */
	public function txt($variable) {
		return $this->dic()->language()->txt($variable);
	}


	/**
	 * @return \ILIAS\DI\Container
	 */
	public function dic() {
		return $GLOBALS["DIC"];
	}


	/**
	 * @return \ilTemplate
	 */
	protected function tpl() {
		return $this->dic()->ui()->mainTemplate();
	}


	/**
	 * @return \ilCtrl
	 */
	protected function ctrl() {
		return $this->dic()->ctrl();
	}
}
