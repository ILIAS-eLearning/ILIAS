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
            $serialize = $this->getParent()->serialize();
            return !$this->getParent() instanceof NullIdentification;
        } elseif ($this instanceof isChild) {
            return $this->getParent() instanceof NullIdentification && empty($this->getParent()->serialize());
        }
        return false;
    }
}
