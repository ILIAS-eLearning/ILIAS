<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
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
		$slate = $this->ui_factory->mainControls()->slate()->combined($item->getTitle(), $this->getStandardIcon($item));

		$slate = $this->addOnloadCode($slate, $item);

		foreach ($item->getLinks() as $link) {
			$button = $this->ui_factory->button()->bulky($this->getStandardIcon($link), $link->getTitle(), $link->getAction());
			$slate = $slate->withAdditionalEntry($button);
		}

		return $slate;
	}
}
