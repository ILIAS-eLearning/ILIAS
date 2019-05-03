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
		global $DIC;

		$top_items = (new ilMMItemRepository())->getStackedTopItemsForPresentation();
		$tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');
		/**
		 * @var $top_item \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem|\ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem
		 */
		$components = [];

		foreach ($top_items as $top_item) {
			$components[] = $top_item->getTypeInformation()->getRenderer()->getComponentForItem($top_item);
		}

		$context_stack = "Contexts: " . implode(", ", $DIC->navigationContext()->stack()->getStackAsArray());

		$tpl->setVariable("ENTRIES", $DIC->ui()->renderer()->render($components) . $context_stack);

		$html = $tpl->get();

		return $html;
	}
}
