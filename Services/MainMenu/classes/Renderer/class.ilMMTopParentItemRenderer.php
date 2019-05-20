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

		$slate = $f->mainControls()->slate()->combined($item->getTitle(), $this->getStandardIcon($item));
		if ($item instanceof isParent) {
			foreach ($item->getChildren() as $child) {

				switch (true) {
					case ($child instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Complex):
						// throw new ilException("Rendering not yet implemented: ".get_class($child));
						break;
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

		$slate = $this->addOnloadCode($slate, $item);

		return $slate;
	}
}
