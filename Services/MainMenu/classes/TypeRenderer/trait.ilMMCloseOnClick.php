<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\UI\Component\JavaScriptBindable;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
            return $item->addComponentDecorator(static function (
                ILIAS\UI\Component\Component $c
            ) : ILIAS\UI\Component\Component {
                if ($c instanceof JavaScriptBindable) {
                    return $c->withAdditionalOnLoadCode(fn ($id) => "$('#$id').click(function() { 
                        il.UI.maincontrols.mainbar.disengageAll();
                        il.UI.maincontrols.mainbar.clearStates();
                    })");
                }
                return $c;
            });
        }

        return $item;
    }
}
