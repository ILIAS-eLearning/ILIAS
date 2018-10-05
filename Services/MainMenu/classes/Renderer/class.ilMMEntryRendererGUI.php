<?php

use ILIAS\GlobalScreen\MainMenu\Entry\Divider;
use ILIAS\GlobalScreen\MainMenu\Entry\Link;
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
		$provider = new ilMainMenuProvider($DIC);
		$provider->inject(new \ILIAS\GlobalScreen\Services());

		$tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');
		/**
		 * @var $slate \ILIAS\GlobalScreen\MainMenu\Slate\Slate
		 * @var $entry \ILIAS\GlobalScreen\MainMenu\ChildEntryInterface
		 */
		$slates = [];
		foreach ($provider->getStaticSlates() as $slate) {
			$id = $slate->getProviderIdentification()->serialize();
			$slates[$id] = $slate;
		}

		$time = microtime(true);

		foreach ($provider->getStaticEntries() as $entry) {
			if ($entry->hasParent()) {
				$parent_id = $entry->getParent()->serialize();
				$slates[$parent_id]->appendChild($entry);
			}
		}

		$time = microtime(true) - $time;

		foreach ($slates as $slate) {
			if (!$slate->isVisible()) {
				continue;
			}
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
					case ($child instanceof Divider):
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
