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
	 * @param \ilBTIO $input
	 * @param $user_input
	 * @return ilBTIO
	 */
	public function interaction(ilBTIO $input, $user_input);
}