<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Component\MainControls;

class Factory implements Page\Factory {

	/**
	 * @inheritdoc
	 */
	public function standard(
		array $content,
		MainControls\MetaBar $metabar = null,
		MainControls\MainBar $mainbar = null,
		Breadcrumbs $locator = null,
		Image $logo = null,
		MainControls\Footer $footer = null,
		string $title = ''
	): Page\Standard {
		return new Standard($content, $metabar, $mainbar, $locator, $logo, $footer, $title);
	}
}
