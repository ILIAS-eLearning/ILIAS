<?php namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class ContextServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextServices
{

    /**
     * @var ContextRepository
     */
    private $context_repository;
    /**
     * @var ContextCollection
     */
    private $collection;


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


    /**
     * @return ContextCollection
     */
    public function collection()
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
