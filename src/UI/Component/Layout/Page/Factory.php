<?php

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Image\Image;

/**
 * This is what a factory for pages looks like.
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *    The Standard Page is the regular view upon ILIAS.
	 *
	 *   composition: >
	 *      The main parts of a Page are the Metabar, the Mainbar providing
	 *      main navigation, the logo, breadcrumbs and, of course, the pages's
	 *      content.
	 *      The locator (in form of breadcrumbs) and the logo are optional.
	 *
	 * featurewiki:
	 *       - Desktop: https://docu.ilias.de/goto_docu_wiki_wpage_4563_1357.html
	 *       - Mobile: https://docu.ilias.de/goto_docu_wiki_wpage_5095_1357.html
	 *
	 * rules:
	 *   usage:
	 *     1: The page MUST be rendered with content.
	 *     2: The page MUST be rendered with a Metabar.
	 *     3: The page MUST be rendered with a Mainbar.
	 *     4: The page SHOULD be rendered with Breadcrumbs.
	 *     5: The page SHOULD be rendered with a Logo.
	 *
	 * ----
	 *
	 * @return  \ILIAS\UI\Component\Layout\Page\Standard
	 */
	public function standard(
		MainControls\Metabar $metabar,
		MainControls\Mainbar $mainbar,
		$content,
		$locator = null,
		Image $logo = null
	): Standard;

}
