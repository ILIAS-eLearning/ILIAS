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

		if ($storage->cache()->exists('rendered_menu')) {
			$cached_menu = $storage->cache()->get('rendered_menu');
			if (is_string($cached_menu)) {
				return $cached_menu;
			}
		}

		$top_items = (new ilMMItemRepository($storage))->getStackedTopItems();
		$tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');

		foreach ($top_items as $top_item) {
			$tpl->setCurrentBlock('mmentry');
			$tpl->setVariable("TITLE", $top_item->getTitle());

			$gl = new ilGroupedListGUI();
			$gl->setAsDropDown(true);
			/**
			 * @var $child Link
			 */
			foreach ($top_item->getChildren() as $child) {
				if (!$child->isVisible()) {
					continue;
				}
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
						$gl->addGroupHeader($child->getTitle());
						foreach ($child->getLinks() as $link) {
							$this->addEntry($gl, $link, $i);
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

			$tpl->parseCurrentBlock();
		}

		$html = $tpl->get();

		$storage->cache()->set('rendered_menu', $html, 60);

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
