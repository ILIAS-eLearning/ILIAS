<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\DropDown;

use ILIAS\UI\Component\DropDown as D;

class Factory implements D\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard($items) {
        return new Standard($items);
    }

	/**
	 * @inheritdoc
	 */
	public function item($label, $action) {
        return new DropDownItem($label, $action);
    }
}
