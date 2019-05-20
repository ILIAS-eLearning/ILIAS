<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMTopLinkItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopLinkItemRenderer extends BaseTypeRenderer {

	/**
	 * @inheritDoc
	 */
	const BLANK = "_blank";
	const TOP = "_top";


	/**
	 * @param isItem $item
	 *
	 * @return Component
	 * @throws ilTemplateException
	 */
	public function getComponentForItem(isItem $item): Component {
		return $this->ui_factory->button()->bulky($this->getStandardIcon($item), $item->getTitle(), $item->getAction());
	}
}
