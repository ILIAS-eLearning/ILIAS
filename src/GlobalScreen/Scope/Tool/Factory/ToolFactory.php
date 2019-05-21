<?php namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class ToolFactory
 *
 * This factory provides you all available types for MainMenu GlobalScreen Tools.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ToolFactory {

	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return Tool
	 */
	public function tool(IdentificationInterface $identification): Tool {
		return new Tool($identification);
	}
}
