<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

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
 * Class NullModification
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullModification extends AbstractLayoutModification implements LayoutModification
{
    
    /**
     * @inheritDoc
     */
    public function getClosureFirstArgumentType() : string
    {
        return '';
    }
    
    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return '';
    }
    
    /**
     * @inheritDoc
     */
    public function getPriority() : int
    {
        return -1;
    }
    
    /**
     * @inheritDoc
     */
    public function firstArgumentAllowsNull() : bool
    {
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function returnTypeAllowsNull() : bool
    {
        return true;
    }
}
