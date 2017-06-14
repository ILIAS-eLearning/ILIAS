<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Divider;

/**
 * Divider Factory
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The standard divider is the default divider to be used in ILIAS.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Standard Divider MUST be used if there is no good reason using
	 *          another instance.
	 *   ordering:
	 *       1: >
	 *          Dividers MUST always have a preceding and a succeeding element
	 *          in a sequence of elments, which MUST NOT be another Divider.
	 * ---
	 * @return  \ILIAS\UI\Component\Divider\Standard
	 */
	public function standard();
}

