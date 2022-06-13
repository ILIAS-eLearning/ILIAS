<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

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
     * @param isItem $item
     *
     * @return isItem
     */
    public function customPosition(isItem $item) : isItem;


    /**
     * @param hasTitle $item
     *
     * @return hasTitle
     */
    public function customTranslationForUser(hasTitle $item) : hasTitle;


    /**
     * @param isItem $item
     *
     * @return IdentificationInterface
     */
    public function getParent(isItem $item) : IdentificationInterface;


    /**
     * @param hasSymbol $item
     *
     * @return hasSymbol
     */
    public function customSymbol(hasSymbol $item) : hasSymbol;
}
