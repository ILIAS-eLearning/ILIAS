<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMLinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMLinkItemRenderer extends BaseTypeRenderer {

	/**
	 * @param Link $item
	 *
	 * @return Component
	 */
	public function getComponentForItem(isItem $item): Component {
		return $this->ui_factory->button()->bulky($this->getStandardIcon($item), $item->getTitle(), $item->getAction());
	}
}
