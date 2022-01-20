<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class BaseTypeHandler
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
