<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes how a panel could be modified during construction of UI.
 * @author Alex Killing <killing@leifos.de>
 */
interface Sticky extends \ILIAS\UI\Component\Component {
	/**
	 * Get current sticky views
	 *
	 * @return \ILIAS\UI\Component\Panel\StickyView[]
	 */
	public function getViews();
}
