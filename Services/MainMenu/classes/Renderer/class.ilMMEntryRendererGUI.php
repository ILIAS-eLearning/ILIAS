<?php

use ILIAS\GlobalScreen\MainMenu\hasAsyncContent;
use ILIAS\GlobalScreen\MainMenu\isParent;
use ILIAS\GlobalScreen\MainMenu\Item\Separator;
use ILIAS\GlobalScreen\MainMenu\Item\Link;
use ILIAS\GlobalScreen\MainMenu\Item\LinkList;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;

/**
 * Class ilMMEntryRendererGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMEntryRendererGUI {

	/**
	 * @return string
	 * @throws Throwable
	 * @throws ilTemplateException
	 */
	public function getHTML(): string {
		global $DIC;
		$storage = $DIC->globalScreen()->storage();

		$cacke_key = 'rendered_menu_' . $DIC->user()->getId();
		if ($storage->cache()->exists($cacke_key)) {
			$cached_menu = $storage->cache()->get($cacke_key);
			if (is_string($cached_menu)) {
				// return $cached_menu;
			}
		}

		$top_items = (new ilMMItemRepository($storage))->getStackedTopItemsForPresentation();
		$tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');
		/**
		 * @var $top_item \ILIAS\GlobalScreen\MainMenu\isItem|\ILIAS\GlobalScreen\MainMenu\isTopItem
		 */
		$components = [];

		foreach ($top_items as $top_item) {
			$components[] = $top_item->getTypeInformation()->getRenderer()->getComponentForItem($top_item);
		}

		$tpl->setVariable("ENTRIES", $DIC->ui()->renderer()->render($components));

		$html = $tpl->get();

		$storage->cache()->set($cacke_key, $html, 10);

		return $html;
	}
}
