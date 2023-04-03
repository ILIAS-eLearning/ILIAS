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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\GlobalScreen\Identification\NullIdentification;

/**
 * Interface isInterchangeableItem
 * @package ILIAS\GlobalScreen\Scope\MainMenu\Factory
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
trait isInterchangeableItemTrait
{
    public function hasChanged() : bool
    {
        $serialized_parent = $this->getParent()->serialize();
        if ($this instanceof isTopItem) {
            return $serialized_parent !== '';
        } elseif ($this instanceof isChild) {
            return $serialized_parent === '';
        }
        return false;
    }
}
