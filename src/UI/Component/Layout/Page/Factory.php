<?php

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\MainControls;

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
	 *      The main parts of a Page are the Metabar hosting the logo and tools,
	 *      the Mainbar providing main navigation, breadcrumbs and, of course,
	 *      the pages's content.
	 *      The locator (in form of breadcrumbs) is optional.
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
	 *     3: The page SHOULD be rendered with Breadcrumbs.
	 *
	 * ----
	 *
	 * @return  \ILIAS\UI\Component\Layout\Page\Standard
	 */
	public function standard(
		MainControls\Metabar $metabar,
		MainControls\Mainbar $mainbar,
		$content,
		$locator = null
	): Standard;

}
