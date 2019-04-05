<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Layout\Page;
use \ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements Page\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard(
		MainControls\MetaBar $metabar,
		MainControls\MainBar $mainbar,
		array $content,
		Breadcrumbs $locator = null,
		Image $logo = null
	): Page\Standard {
		return new Standard($metabar, $mainbar, $content, $locator, $logo);
	}
}
