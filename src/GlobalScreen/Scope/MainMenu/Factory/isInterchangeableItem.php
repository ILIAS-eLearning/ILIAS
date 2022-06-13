<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

/**
 * Interface isInterchangeableItem
 * @package ILIAS\GlobalScreen\Scope\MainMenu\Factory
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
interface isInterchangeableItem extends isItem
{
    public function hasChanged() : bool;
}
