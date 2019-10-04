<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\UI\Component\Component;

/**
 * Class TopParentItemRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItemRenderer extends BaseTypeRenderer
{

    use SlateSessionStateCode;
    use isSupportedTrait;


    /**
     * @inheritDoc
     */
    public function getComponentForItem(isItem $item) : Component
    {
        $f = $this->ui_factory;

        $slate = $f->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));
        if ($item instanceof isParent) {
            foreach ($item->getChildren() as $child) {

                switch (true) {
                    case ($child instanceof Separator):
                        // throw new ilException("Rendering not yet implemented: ".get_class($child));
                        break;
                    default:
                        $component = $child->getTypeInformation()->getRenderer()->getComponentForItem($child);
                        if ($this->isComponentSupportedForCombinedSlate($component)) {
                            $slate = $slate->withAdditionalEntry($component);
                        }
                        break;
                }
            }
        }

        $slate = $this->addOnloadCode($slate, $item);

        return $slate;
    }
}
