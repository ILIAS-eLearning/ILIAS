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
        if ($this instanceof isTopItem && $this instanceof isInterchangeableItem) {
            return !($this->getParent() instanceof NullIdentification) && $this->getParent()->serialize() !== '';
        } elseif ($this instanceof isChild && $this instanceof isInterchangeableItem) {
            return $this->getParent() instanceof NullIdentification && $this->getParent()->serialize() === '';
        }
        return false;
    }
}
