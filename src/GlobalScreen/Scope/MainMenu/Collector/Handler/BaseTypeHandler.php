<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class BaseTypeHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class BaseTypeHandler implements TypeHandler
{

    /**
     * @inheritDoc
     */
    public function matchesForType() : string
    {
        return "";
    }


    /**
     * @inheritDoc
     */
    public function enrichItem(isItem $item) : isItem
    {
        return $item;
    }


    /**
     * @inheritDoc
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification) : array
    {
        return array();
    }


    /**
     * @inheritDoc
     */
    public function saveFormFields(IdentificationInterface $identification, array $data) : bool
    {
        return true;
    }
}
