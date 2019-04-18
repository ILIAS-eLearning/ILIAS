<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAsyncContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasIcon;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\UI\Component\Button\Bulky;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Class ilMMTopParentItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopParentItemRenderer extends BaseTypeRenderer {

	use ilMMSlateSessionStateCode;


	/**
	 * @inheritDoc
	 */
	public function getComponentForItem(isItem $item): Component {
		$f = $this->ui_factory;

		if ($item instanceof hasIcon) {
			$symbol = $item->getIcon();
		} else {
			$symbol = $this->getStandardIcon();
		}

		$slate = $f->mainControls()->slate()->combined($item->getTitle(), $symbol);
		if ($item instanceof isParent) {
			foreach ($item->getChildren() as $child) {

				switch (true) {
					//case ($child instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Complex):
					case ($child instanceof Separator):
						// throw new ilException("Rendering not yet implemented: ".get_class($child));
						break;
					default:
						$com = $child->getTypeInformation()->getRenderer()->getComponentForItem($child);
						if ($com instanceof Bulky || $com instanceof Slate) {
							$slate = $slate->withAdditionalEntry($com);
						}
						break;
				}
			}
		}

		// $slate = $this->addOnloadCode($slate, $item);

		return $slate;
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
	 *
	 * @throws ilTemplateException
	 */
	private function handleContent(hasContent $child, $gl) {
		global $DIC;
		$identifier = $child->getProviderIdentification()->getInternalIdentifier();
		$gl->addEntry(
			$DIC->ui()->renderer()->render($child->getContent()), "#", "_top", "", "", $identifier, ilHelp::getMainMenuTooltip($identifier), "left center", "right center", false
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
