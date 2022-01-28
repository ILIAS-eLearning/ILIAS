<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class AbstractChildItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractChildItem extends AbstractBaseItem implements isItem, isChild
{
    protected ?IdentificationInterface $parent;
    
    /**
     * @inheritDoc
     */
    public function withParent(IdentificationInterface $identification) : isItem
    {
        $clone = clone $this;
        $clone->parent = $identification;
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function hasParent() : bool
    {
        return ($this->parent instanceof IdentificationInterface);
    }
    
    /**
     * @inheritDoc
     */
    public function getParent() : IdentificationInterface
    {
        return $this->parent;
    }
    
    /**
     * @inheritDoc
     */
    public function overrideParent(IdentificationInterface $identification) : isChild
    {
        $this->parent = $identification;
        
        return $this;
    }
}
