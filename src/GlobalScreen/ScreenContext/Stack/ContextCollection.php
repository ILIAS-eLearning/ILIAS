<?php namespace ILIAS\GlobalScreen\ScreenContext\Stack;

use ILIAS\GlobalScreen\ScreenContext\ContextRepository;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;

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
 * Class ContextCollection
 * @package ILIAS\GlobalScreen\Scope\Tool\ScreenContext\Stack
 */
class ContextCollection
{
    protected ContextRepository $repo;
    /**
     * @var ScreenContext[]
     */
    protected array $stack = [];
    
    /**
     * ContextCollection constructor.
     * @param ContextRepository $context_repository
     */
    public function __construct(ContextRepository $context_repository)
    {
        $this->repo = $context_repository;
    }
    
    /**
     * @param ScreenContext $context
     */
    public function push(ScreenContext $context)
    {
        $this->stack[] = $context;
    }
    
    /**
     * @return ScreenContext
     */
    public function getLast() : ?ScreenContext
    {
        $last = end($this->stack);
        if ($last) {
            return $last;
        }
        return null;
    }
    
    /**
     * @return ScreenContext[]
     */
    public function getStack() : array
    {
        return $this->stack;
    }
    
    /**
     * @return array
     */
    public function getStackAsArray() : array
    {
        $return = [];
        foreach ($this->stack as $item) {
            $return[] = $item->getUniqueContextIdentifier();
        }
        
        return $return;
    }
    
    /**
     * @param ContextCollection $other_collection
     * @return bool
     */
    public function hasMatch(ContextCollection $other_collection) : bool
    {
        $mapper = function (ScreenContext $c) : string {
            return $c->getUniqueContextIdentifier();
        };
        $mine   = array_map($mapper, $this->getStack());
        $theirs = array_map($mapper, $other_collection->getStack());
        
        return (count(array_intersect($mine, $theirs)) > 0);
    }
    
    public function main() : self
    {
        $context = $this->repo->main();
        $this->push($context);
        
        return $this;
    }
    
    public function desktop() : self
    {
        $this->push($this->repo->desktop());
        
        return $this;
    }
    
    public function repository() : self
    {
        $this->push($this->repo->repository());
        
        return $this;
    }
    
    public function administration() : self
    {
        $this->push($this->repo->administration());
        
        return $this;
    }
    
    public function internal() : self
    {
        $this->push($this->repo->internal());
        
        return $this;
    }
    
    public function external() : self
    {
        $this->push($this->repo->external());
        
        return $this;
    }
    
    public function lti() : self
    {
        $this->push($this->repo->lti());
        return $this;
    }
}
