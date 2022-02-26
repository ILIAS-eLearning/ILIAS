<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface isInterchangeableItem
 * @package ILIAS\GlobalScreen\Scope\MainMenu\Factory
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
interface isInterchangeableItem extends isItem, isChild
{
    public function hasChanged() : bool;
}
