<?php

namespace ILIAS\TMS\Wizard;

/**
 * Definition of a wizard.
 */
interface Wizard {
	/**
	 * Get a unique id for that wizard.
	 *
	 * Must be unique over all similar wizards, perform by different people,
	 * under different circumstances, ...
	 *
	 * It must be garanteed, that the steps do not change over different
	 * instantiations of the same wizard.
	 *
	 * @return	string
	 */
	public function getId();

	/**
	 * Get the steps to be processed.
	 *
	 * @return	Step[]
	 */
	public function getSteps();

	/**
	 * Clean up wizard after steps are processed.
	 *
	 * @return void
	 */
	public function finish();
}
