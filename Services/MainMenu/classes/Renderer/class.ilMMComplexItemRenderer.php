<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasIcon;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Complex;
use ILIAS\UI\Component\Component;

/**
 * Class ilMMComplexItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMComplexItemRenderer extends BaseTypeRenderer {

	/**
	 * @param isItem $item
	 *
	 * @return Component
	 */
	public function getComponentForItem(isItem $item): Component {
		if ($item instanceof hasIcon) {
			$symbol = $this->ui_factory->icon()->custom($item->getIcon(), '-');
		} else {
			$symbol = $this->ui_factory->glyph()->expand();
		}
		/**
		 * @var $item Complex
		 */

		if ($item->getAsyncContentURL()) {
			$atpl = new ilTemplate("tpl.self_loading_item.html", false, false, 'Services/MainMenu');
			$atpl->setVariable("ASYNC_URL", $item->getAsyncContentURL());
			$content = $this->ui_factory->legacy($atpl->get());
		} else {
			$content = $item->getContent();
		}
		$slate = $this->ui_factory->mainControls()->slate()->legacy($item->getProviderIdentification()->serialize(), $symbol, $content); // TODO

		return $slate;
	}
}
