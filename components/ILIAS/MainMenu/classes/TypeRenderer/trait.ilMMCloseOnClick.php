<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\UI\Component\Link\Link;

/**
 * Trait ilMMCloseOnClick
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilMMCloseOnClick
{
    protected function addDisengageDecorator(isItem $item): isItem
    {
        if (($item instanceof isParent || $item instanceof isInterchangeableItem) && $item->getParent()->serialize() === '') {
            // always close MainBar when a link has been clicked
            return $item->addComponentDecorator(static function (
                ILIAS\UI\Component\Component $c
            ): ILIAS\UI\Component\Component {
                if (!$c instanceof Link) {
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
