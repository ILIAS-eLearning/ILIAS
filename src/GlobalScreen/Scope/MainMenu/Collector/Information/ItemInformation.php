<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;

/**
 * Class ItemInformation
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ItemInformation
{

    /**
     * @param isItem $item
     *
     * @return bool
     */
    public function isItemActive(isItem $item) : bool;


    /**
     * @param isChild $child
     *
     * @return int
     */
    public function getPositionOfSubItem(isChild $child) : int;


    /**
     * @param isTopItem $top_item
     *
     * @return int
     */
    public function getPositionOfTopItem(isTopItem $top_item) : int;


    /**
     * @param hasTitle $item
     *
     * @return hasTitle
     */
    public function translateItemForUser(hasTitle $item) : hasTitle;


    /**
     * @param isChild $item
     *
     * @return IdentificationInterface
     */
    public function getParent(isChild $item) : IdentificationInterface;
}
