<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\MainControls\Metabar;
use ILIAS\UI\Component\MainControls\Mainbar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes the Page.
 */
interface Standard extends Page, JavaScriptBindable {

	/**
	 * @param Metabar $meta_bar
	 *
	 * @return Standard
	 */
	public function withMetabar(Metabar $meta_bar): Standard;


	/**
	 * @param Mainbar $main_bar
	 *
	 * @return Standard
	 */
	public function withMainbar(MainBar $main_bar): Standard;


	/**
	 * @param Image $logo
	 *
	 * @return Standard
	 */
	public function withLogo(Image $logo): Standard;


	/**
	 * @return bool
	 */
	public function hasMetabar(): bool;


	/**
	 * @return bool
	 */
	public function hasMainbar(): bool;


	/**
	 * @return bool
	 */
	public function hasLogo(): bool;


	/**
	 * @return Metabar
	 */
	public function getMetabar(): Metabar;


	/**
	 * @return Mainbar
	 */
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
