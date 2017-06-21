<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Cockpit\Slate;

/**
 * Cockpit Slate
 * @package ILIAS\UI\Component\Cockpit
 */
interface Slate extends \ILIAS\UI\Component\Component {

	/**
	 * Returns the content of the slate.
	 *
	 * @return \Component
	 */
	public function content();
}