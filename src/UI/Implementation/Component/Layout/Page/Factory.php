<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Layout\Page;
use \ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements Page\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard(
		MainControls\Metabar $metabar,
		MainControls\Mainbar $mainbar,
		$content,
		$locator = null,
		Image $logo = null
	): Page\Standard {
		return new Standard($metabar, $mainbar, $content, $locator, $logo);
	}
}
