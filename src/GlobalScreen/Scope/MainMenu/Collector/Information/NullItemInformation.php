<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class NullItemInformation
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullItemInformation implements ItemInformation
{
    public function isItemActive(isItem $item) : bool
    {
        return false;
    }
    
    public function customPosition(isItem $item) : isItem
    {
        return $item;
    }
    
    public function customTranslationForUser(hasTitle $item) : hasTitle
    {
        return $item;
    }
    
    public function getParent(isChild $item) : IdentificationInterface
    {
        return new NullIdentification();
    }
    
    public function customSymbol(hasSymbol $item) : hasSymbol
    {
        return $item;
    }
    
}
