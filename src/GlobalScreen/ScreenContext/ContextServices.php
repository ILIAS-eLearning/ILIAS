<?php namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

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
 * Class ContextServices
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextServices
{
    private ContextRepository $context_repository;
    
    private ContextCollection $collection;
    
    /**
     * ContextServices constructor.
     */
    public function __construct()
    {
        $this->context_repository = new ContextRepository();
        $this->collection = new CalledContexts($this->context_repository);
    }
    
    /**
     * @return CalledContexts
     */
    public function stack() : CalledContexts
    {
        return $this->collection;
    }
    
    /**
     * @return ScreenContext
     */
    public function current() : ScreenContext
    {
        return $this->collection->current();
    }
    
    /**
     * @return CalledContexts
     */
    public function claim() : CalledContexts
    {
        return $this->collection;
    }
    
    public function collection() : ContextCollection
    {
        return new ContextCollection($this->context_repository);
    }
    
    /**
     * @return ContextRepository
     */
    public function availableContexts() : ContextRepository
    {
        return $this->context_repository;
    }
}
