<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\hasAsyncContent;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\Item\LinkList;
use ILIAS\GlobalScreen\MainMenu\Item\Separator;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMTopParentItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopParentItemRenderer extends BaseTypeRenderer {

	/**
	 * @inheritDoc
	 */
	public function getComponentForItem(isItem $item): Component {
		global $DIC;
		/**
		 * @var $item TopParentItem
		 */
		$tpl = new ilTemplate("tpl.mm_top_parent_item.html", false, false, 'Services/MainMenu');
		$tpl->setVariable("TITLE", $item->getTitle());

		$gl = new ilGroupedListGUI();
		$gl->setAsDropDown(true);
		foreach ($item->getChildren() as $child) {
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
				case($child instanceof isItem):
				default:
					$com = $child->getTypeInformation()->getRenderer()->getComponentForItem($child);
					$identifier = $child->getProviderIdentification()->getInternalIdentifier();
					$target = $child instanceof hasAction ? ($child->isLinkWithExternalAction() ? "_blank" : "_top") : "_top";
					$href = ($child instanceof hasAction) ? $child->getAction() : "#";
					$tooltip = ilHelp::getMainMenuTooltip($identifier);
					$a_id = "mm_" . $identifier;
					$gl->addEntry(
						$DIC->ui()->renderer()->render($com), $href, $target, "", "", $a_id, $tooltip, "left center", "right center", false
					);

					break;
			}
		}
		$tpl->setVariable("CONTENT", $gl->getHTML());

		return $this->ui_factory->legacy($tpl->get());
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
}
