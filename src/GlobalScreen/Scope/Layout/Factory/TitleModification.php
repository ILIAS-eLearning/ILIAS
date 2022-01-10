<?php

namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

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
 * Class TitleModification
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class TitleModification extends AbstractLayoutModification implements LayoutModification
{
    
    /**
     * @inheritDoc
     */
    public function isFinal() : bool
    {
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function getClosureFirstArgumentType() : string
    {
        return 'string';
    }
    
    /**
     * @inheritDoc
     */
    public function getClosureReturnType() : string
    {
        return 'string';
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
        return false;
    }
}
