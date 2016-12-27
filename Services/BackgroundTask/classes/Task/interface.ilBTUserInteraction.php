<?php

/**
 * Interface ilBTJob
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
interface ilBTUserInteraction extends ilBTTask {
	/**
	 * @return array returns an array with value => lang_var. What options can the user select?
	 */
	public function getOptions();

	/**
	 * Create the output here.
	 * @param $user_input
	 * @return void
	 */
	public function interaction($user_input);

}