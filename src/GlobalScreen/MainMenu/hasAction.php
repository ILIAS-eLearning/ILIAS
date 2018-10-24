<?php namespace ILIAS\GlobalScreen\MainMenu;

/**
 * Interface hasAction
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasAction {

	/**
	 * @param string $action
	 *
	 * @return hasAction
	 */
	public function withAction(string $action): hasAction;


	/**
	 * @return string
	 */
	public function getAction(): string;
}
