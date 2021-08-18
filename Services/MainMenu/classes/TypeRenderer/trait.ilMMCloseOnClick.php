<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Trait ilMMCloseOnClick
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilMMCloseOnClick
{
    protected function addDisengageDecorator(isItem $item) : isItem
    {
        if (($item instanceof isParent || $item instanceof isInterchangeableItem) && $item->getParent()->serialize() === '') {
            // always close MainBar when a link has been clicked
            return $item->addComponentDecorator(static function (ILIAS\UI\Component\Component $c
            ) : ILIAS\UI\Component\Component {
                if ($c instanceof JavaScriptBindable) {
                    return $c->withAdditionalOnLoadCode(function ($id) {
                        return "$('#$id').click(function() { 
                        il.UI.maincontrols.mainbar.disengageAll();
                        il.UI.maincontrols.mainbar.clearStates();
                    })";
                    });
                }
                return $c;
            });
        }

        return $item;
    }
}
