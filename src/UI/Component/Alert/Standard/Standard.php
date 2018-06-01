<?php

/* Copyright (c) 2018 Thomas Famuka <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Alert\Standard;

/**
 * Interface Standard Alert
 */
interface Standard extends \ILIAS\UI\Component\Alert\Alert {
	// Types of glyphs:
	const FAILURE = "failure";
	const SUCCESS = "success";
	const INFO = "info";
	const CONFIRMATION = "confirmation";

	/**
	 * Get the type of the Alert.
	 *
	 * @return	string
	 */
	public function getType();

}