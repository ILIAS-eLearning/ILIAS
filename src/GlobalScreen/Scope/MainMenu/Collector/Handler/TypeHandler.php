<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * Class TypeHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface TypeHandler
{

    /**
     * @return string Classname of matching Type this TypeHandler can handle
     */
    public function matchesForType() : string;


    /**
     * @param isItem $item
     *
     * @return isItem
     */
    public function enrichItem(isItem $item) : isItem;


    /**
     * @param IdentificationInterface $identification
     *
     * @return Input[]
     */
    public function getAdditionalFieldsForSubForm(IdentificationInterface $identification) : array;


    /**
     * @param IdentificationInterface $identification
     * @param array                   $data
     *
     * @return bool
     */
    public function saveFormFields(IdentificationInterface $identification, array $data) : bool;
}
