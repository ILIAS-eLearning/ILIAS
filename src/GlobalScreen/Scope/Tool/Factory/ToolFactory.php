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
	 * Returns you a Tool which can contain special features in s context
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return Tool
	 *
	 * @see CalledContexts
	 */
	public function tool(IdentificationInterface $identification): Tool {
		return new Tool($identification);
	}
}
