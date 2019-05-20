<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAsyncContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;

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
		$html = "";

		// Plugin-Slot
		$uip = new ilUIHookProcessor(
			"Services/MainMenu",
			"main_menu_list_entries",
			array("main_menu_gui" => $this)
		);

		if (!$uip->replaced()) {
			$html = $this->render();
		}

		$html = $uip->getHTML($html);

		return $html;
	}


	/**
	 * @return string
	 * @throws Throwable
	 * @throws ilTemplateException
	 */
	protected function render(): string {
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
		 * @var $top_item \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem|\ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem
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
