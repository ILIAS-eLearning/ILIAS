<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Metabar;
use ILIAS\UI\Component\MainControls\Mainbar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;

/**
 * This describes the Page.
 */
interface Standard extends Component
{
	/**
	 * @return Component[]
	 */
	public function getContent();

	public function getMetabar(): Metabar;

	public function getMainbar(): Mainbar;

	/**
	 * @return Breadcrumbs|null
	 */
	public function getBreadcrumbs();

	/**
	 * @return Image|null
	 */
	public function getLogo();
}
