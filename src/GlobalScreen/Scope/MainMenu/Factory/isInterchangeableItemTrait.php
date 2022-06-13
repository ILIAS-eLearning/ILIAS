<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

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
