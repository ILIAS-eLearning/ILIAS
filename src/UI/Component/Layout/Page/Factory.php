<?php

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;

/**
 * This is what a factory for pages looks like.
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Standard Page is the regular view upon ILIAS.
	 *
	 *   composition: >
	 *     The main parts of a Page are the Metabar, the Mainbar providing
	 *     main navigation, the logo, breadcrumbs and, of course, the pages's
	 *     content. "Content" in this case is the part of the page that is
	 *     not Mainbar, Metabar, Footer or Locator, but e.g. the Repository-Listing,
	 *     an object's view or edit form, etc.
	 *     The locator (in form of breadcrumbs) and the logo are optional.
	 *
	 * featurewiki:
	 *   - Desktop: https://docu.ilias.de/goto_docu_wiki_wpage_4563_1357.html
	 *   - Mobile: https://docu.ilias.de/goto_docu_wiki_wpage_5095_1357.html
	 *
	 * rules:
	 *   usage:
	 *     1: The Standard Page MUST be rendered with content, i.e. the actual view on the context.
	 *     2: The Standard Page MUST be rendered with a Metabar.
	 *     3: The Standard Page MUST be rendered with a Mainbar.
	 *     4: The Standard Page SHOULD be rendered with Breadcrumbs.
	 *     5: The Standard Page SHOULD be rendered with a Logo.
	 *
	 * ----
	 *
	 * @param  \ILIAS\UI\Component\MainControls\Metabar $metabar
	 * @param  \ILIAS\UI\Component\MainControls\Mainbar $mainbar
	 * @param  \ILIAS\UI\Component\Component[] $content
	 * @param  \ILIAS\UI\Component\Breadcrumbs\Breadcrumbs $locator
	 * @return \ILIAS\UI\Component\Layout\Page\Standard
	 */
	public function standard(
		MainControls\Metabar $metabar,
		MainControls\Mainbar $mainbar,
		array $content,
		Breadcrumbs $locator = null,
		Image $logo = null
	): Standard;

}
