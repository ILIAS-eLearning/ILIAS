<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasIcon;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMLinkListItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMLinkListItemRenderer extends BaseTypeRenderer {

	use ilMMSlateSessionStateCode;


	/**
	 * @param LinkList $item
	 *
	 * @return Component
	 */
	public function getComponentForItem(isItem $item): Component {
		/**
		 * @var $item LinkList
		 */
		$symbol = $this->getIcon($item);
		$slate = $this->ui_factory->mainControls()->slate()->combined($item->getTitle(), $symbol);

		$slate = $this->addOnloadCode($slate, $item);

		foreach ($item->getLinks() as $link) {
			$symbol = $this->getIcon($link);

			$button = $this->ui_factory->button()->bulky($symbol, $link->getTitle(), $link->getAction());
			$slate = $slate->withAdditionalEntry($button);
		}

		return $slate;
	}


	/**
	 * @param isItem $item
	 *
	 * @return \ILIAS\UI\Component\Glyph\Glyph|\ILIAS\UI\Component\Icon\Icon
	 */
	private function getIcon(isItem $item) {
		if ($item instanceof hasIcon && $item->hasIcon()) {
			$symbol = $item->getIcon();
		} else {
			$symbol = $this->getStandardIcon();
		}

		return $symbol;
	}
}
