<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;

/**
 * Class ilMMTypeHandlerSeparator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTypeHandlerSeparator implements \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler
{

    /**
     * @inheritDoc
     */
    public function matchesForType() : string
    {
        return Separator::class;
    }


    /**
     * @inheritDoc
     */
    public function enrichItem(\ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem $item) : \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem
    {
        if ($item instanceof Separator && $item->getTitle() !== "") {
            $item = $item->withVisibleTitle(true);
        }

        return $item;
    }


    /**
     * @inheritDoc
     */
    public function getAdditionalFieldsForSubForm(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification) : array
    {
        return array();
    }


    /**
     * @inheritDoc
     */
    public function saveFormFields(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, array $data) : bool
    {
        return true;
    }
}
