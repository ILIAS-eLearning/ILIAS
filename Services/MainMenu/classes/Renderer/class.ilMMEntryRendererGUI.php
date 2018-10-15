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
	 */
	public function getHTML(): string {
		global $DIC;
		$time = microtime(true);
		$slates = (new ilMainMenuCollector($DIC->database(), ilGlobalCache::getInstance('ux')))->getStackedSlates();
		$tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');

		$time = microtime(true) - $time;

		foreach ($slates as $slate) {
			$tpl->setCurrentBlock('mmentry');
			$tpl->setVariable("TITLE", $slate->getTitle());

			$gl = new ilGroupedListGUI();
			$gl->setAsDropDown(true);
			/**
			 * @var $child Link
			 */
			foreach ($slate->getChildren() as $child) {
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

		return $time . $tpl->get();
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
