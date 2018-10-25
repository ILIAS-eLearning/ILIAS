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
		foreach ($top_items as $top_item) {
			$tpl->setVariable("ID", "mm_" . $top_item->getProviderIdentification()->getInternalIdentifier());

			/**
			 * @var $child Link
			 */
			$tpl->setCurrentBlock('mmentry');
			$tpl->setVariable("TITLE", $top_item->getTitle());
			if ($top_item instanceof \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem) {
				$this->handleTopParentItem($tpl, $top_item);
			} elseif ($top_item instanceof \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem) {
				$this->handleTopLinkItem($tpl, $top_item);
			}

			$tpl->parseCurrentBlock();
		}

		$html = $tpl->get();

		$storage->cache()->set($cacke_key, $html, 10);

		return $html;
	}


	/**
	 * @param ilGroupedListGUI $gl
	 * @param hasTitle         $child
	 * @param string           $identifier
	 */
	protected function addEntry(ilGroupedListGUI $gl, hasTitle $child, string $identifier) {
		$target = $child instanceof hasAction ? ($child->isLinkWithExternalAction() ? "_blank" : "_top") : "_top";
		$href = ($child instanceof hasAction) ? $child->getAction() : "#";
		$tooltip = ilHelp::getMainMenuTooltip($identifier);
		$a_id = "mm_" . $identifier;
		$gl->addEntry(
			$child->getTitle(), $href, $target, "", "", $a_id, $tooltip, "left center", "right center", false
		);
	}


	/**
	 * @param $child
	 * @param $gl
	 *
	 * @throws ilTemplateException
	 */
	private function handleAsyncContent($child, $gl) {
		$identifier = $child->getProviderIdentification()->getInternalIdentifier();
		$atpl = new ilTemplate("tpl.self_loading_item.html", false, false, 'Services/MainMenu');
		$atpl->setVariable("ASYNC_URL", $child->getAsyncContentURL());
		$gl->addEntry(
			$atpl->get(), "#", "_top", "", "", $identifier, ilHelp::getMainMenuTooltip($identifier), "left center", "right center", false
		);
	}


	/**
	 * @param $child
	 * @param $gl
	 * @param $i
	 */
	private function handleLinkList($child, $gl, $i) {
		if (count($child->getLinks()) > 0) {
			$gl->addGroupHeader($child->getTitle());
			foreach ($child->getLinks() as $link) {
				$this->addEntry($gl, $link, $i);
			}
		}
	}


	/**
	 * @param $tpl
	 * @param $top_item
	 *
	 * @throws ilTemplateException
	 */
	private function handleTopParentItem($tpl, $top_item) {
		$tpl->setVariable("ACTION", "#");
		$tpl->setVariable("CARET", "caret");
		$tpl->setVariable("DROPDOWN_HANDLER", "class=\"dropdown-toggle\" data-toggle=\"dropdown\"");
		// $tpl->setCurrentBlock('dropdown');
		// $tpl->parseCurrentBlock();
		$gl = new ilGroupedListGUI();
		$gl->setAsDropDown(true);
		foreach ($top_item->getChildren() as $child) {
			$i = $child->getProviderIdentification()->getInternalIdentifier();
			switch (true) {
				case ($child instanceof hasAsyncContent):
					$this->handleAsyncContent($child, $gl);
					break;
				case ($child instanceof LinkList):
					$this->handleLinkList($child, $gl, $i);
					break;
				case ($child instanceof Separator):
					$this->handleSeparator($child, $gl);
					break;
				case ($child instanceof hasAction && $child instanceof hasTitle):
					$this->addEntry($gl, $child, $i);
					break;
			}
		}
		$tpl->setVariable("CONTENT", $gl->getHTML());
	}


	/**
	 * @param ilTemplate $tpl
	 * @param hasAction  $top_item
	 */
	private function handleTopLinkItem(ilTemplate $tpl, hasAction $top_item) {
		$tpl->setVariable("ACTION", $top_item->getAction());
		$tpl->setVariable("TARGET", $top_item->isLinkWithExternalAction() ? "_blank" : "_top");
	}


	/**
	 * @param $child
	 * @param $gl
	 */
	private function handleSeparator($child, $gl) {
		if ($child->isTitleVisible()) {
			$gl->addGroupHeader($child->getTitle());
		} else {
			$gl->addSeparator();
		}
	}
}
