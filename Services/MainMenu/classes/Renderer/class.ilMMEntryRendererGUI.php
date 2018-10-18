<?php

use ILIAS\GlobalScreen\MainMenu\hasAsyncContent;
use ILIAS\GlobalScreen\MainMenu\Item\Separator;
use ILIAS\GlobalScreen\MainMenu\Item\Link;
use ILIAS\GlobalScreen\MainMenu\Item\LinkList;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\hasTitle;

/**
 * Class ilMMEntryRendererGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMEntryRendererGUI {

	/**
	 * @return string
	 * @throws ilTemplateException
	 */
	public function getHTML(): string {
		global $DIC;
		$storage = $DIC->globalScreen()->storage();

		$cacke_key = 'rendered_menu_' . $DIC->user()->getId();
		if ($storage->cache()->exists($cacke_key)) {
			$cached_menu = $storage->cache()->get($cacke_key);
			if (is_string($cached_menu)) {
				return $cached_menu;
			}
		}

		$top_items = (new ilMMItemRepository($storage))->getStackedTopItemsForPresentation();
		$tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');
		/**
		 * @var $top_item \ILIAS\GlobalScreen\MainMenu\isItem|\ILIAS\GlobalScreen\MainMenu\isTopItem
		 */
		foreach ($top_items as $top_item) {
			$tpl->setCurrentBlock('mmentry');
			$tpl->setVariable("TITLE", $top_item->getTitle());
			$tpl->setVariable("ID", $top_item->getProviderIdentification()->getInternalIdentifier());

			$gl = new ilGroupedListGUI();
			$gl->setAsDropDown(true);
			/**
			 * @var $child Link
			 */
			if ($top_item instanceof \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem) {
				foreach ($top_item->getChildren() as $child) {
					$i = $child->getProviderIdentification()->getInternalIdentifier();
					switch (true) {
						case ($child instanceof hasAsyncContent):
							$identifier = $child->getProviderIdentification()->getInternalIdentifier();
							$atpl = new ilTemplate("tpl.self_loading_item.html", false, false, 'Services/MainMenu');
							$atpl->setVariable("ASYNC_URL", $child->getAsyncContentURL());
							$gl->addEntry(
								$atpl->get(), "#", "_top", "", "", $identifier, ilHelp::getMainMenuTooltip($identifier), "left center", "right center", false
							);
							break;
						case ($child instanceof LinkList):
							if (count($child->getLinks()) > 0) {
								$gl->addGroupHeader($child->getTitle());
								foreach ($child->getLinks() as $link) {
									$this->addEntry($gl, $link, $i);
								}
							}
							break;
						case ($child instanceof Separator):
							$gl->addSeparator();
							break;
						case ($child instanceof hasAction && $child instanceof hasTitle):
							$this->addEntry($gl, $child, $i);
							break;
					}
				}
				$tpl->setVariable("CONTENT", $gl->getHTML());
			} elseif ($top_item instanceof \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem) {
				// $tpl->setVariable("CONTENT", "LINK");
			}

			$tpl->parseCurrentBlock();
		}

		$html = $tpl->get();

		$storage->cache()->set($cacke_key, $html, 10);

		return $html;
	}


	/**
	 * @param ilGroupedListGUI $gl
	 * @param Link             $child
	 * @param string           $identifier
	 */
	protected function addEntry(ilGroupedListGUI $gl, hasTitle $child, string $identifier) {
		$gl->addEntry(
			$child->getTitle(), ($child instanceof hasAction) ? $child->getAction() : "#", "_top", "", "", $identifier, ilHelp::getMainMenuTooltip($identifier), "left center", "right center", false
		);
	}
}
