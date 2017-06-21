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
	 *       Horizontal Divider is used to mark a thematic change in sequence of
	 *       elements that are stacked from top to bottom.
	 *   composition: >
	 *     Horiztonal dividers consists of a horizontal ruler which may comprise a label.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Horizontal Dividers MUST only be used in container components that render
	 *          a sequence of items from top to bottom.
	 *   ordering:
	 *       1: >
	 *          Horizontal Dividers MUST always have a succeeding element
	 *          in a sequence of elments, which MUST NOT be another Horizontal Divider.
	 *       2: >
	 *          Horizontal Dividers without label MUST always have a preceding
	 *          element in a sequence of elments, which MUST NOT be another
	 *          Horizontal Divider.
	 * ---
	 * @return  \ILIAS\UI\Component\Divider\Horizontal
	 */
	public function horizontal();
}

