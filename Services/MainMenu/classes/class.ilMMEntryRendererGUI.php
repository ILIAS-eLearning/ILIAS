<?php

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
		$provider->inject(new \ILIAS\UX\Services());

		$tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');
		/**
		 * @var $slate \ILIAS\UX\MainMenu\Slate\SlateInterfaceInterface
		 * @var $entry \ILIAS\UX\MainMenu\ChildEntryInterface
		 */
		$slates = [];
		foreach ($provider->getStaticSlates() as $slate) {
			$id = $slate->getProviderIdentification()->serialize();
			$slates[$id] = $slate;
		}

		foreach ($provider->getStaticEntries() as $entry) {
			if ($entry->hasParent()) {
				$parent_id = $entry->getParent()->serialize();
				$slates[$parent_id]->appendChild($entry);
			}
		}

		foreach ($slates as $slate) {
			if (!$slate->isVisible()) {
				continue;
			}
			$tpl->setCurrentBlock('mmentry');
			$tpl->setVariable("TITLE", $slate->getTitle());

			if ($slate instanceof \ILIAS\UX\MainMenu\AsyncContentEntry && $slate->getAsyncContentURL() !== "") {
				$selection = new ilAdvancedSelectionListGUI();
				$selection->setId("dd_adm");
				$selection->setAsynch(true);
				$selection->setAsynchUrl($slate->getAsyncContentURL());


				$gl = new ilGroupedListGUI();
				$gl->setAsDropDown(true);

				$tpl->setVariable("CONTENT", $selection->getHTML());

			} else {
				$gl = new ilGroupedListGUI();
				$gl->setAsDropDown(true);
				/**
				 * @var $child \ILIAS\UX\MainMenu\Entry\LinkInterface
				 */
				foreach ($slate->getChildren() as $child) {
					if (!$child->isVisible()) {
						continue;
					}
					$i = $child->getProviderIdentification()->getInternalIdentifier();
					switch (true) {
						case ($child instanceof \ILIAS\UX\MainMenu\Entry\DividerInterface):
							$gl->addSeparator();
							break;
						case ($child instanceof \ILIAS\UX\MainMenu\Entry\LinkInterface):
							$this->addEntry($gl, $child, $i);
							break;
					}
				}
				$tpl->setVariable("CONTENT", $gl->getHTML());
			}
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * @param ilGroupedListGUI                       $gl
	 * @param \ILIAS\UX\MainMenu\Entry\LinkInterface $child
	 * @param string                                 $identifier
	 */
	protected function addEntry(ilGroupedListGUI $gl, \ILIAS\UX\MainMenu\Entry\LinkInterface $child, string $identifier) {
		$gl->addEntry(
			$child->getTitle(), $child->getAction(), "_top", "", "", $identifier, ilHelp::getMainMenuTooltip($identifier), "left center", "right center", false
		);
	}
}
