<?php namespace ILIAS\GlobalScreen\ScreenContext\Stack;

use ILIAS\GlobalScreen\ScreenContext\ContextRepository;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;

/**
 * Class ContextCollection
 *
 * @package ILIAS\GlobalScreen\Scope\Tool\ScreenContext\Stack
 */
class ContextCollection
{

    const C_MAIN = 'main';
    const C_DESKTOP = 'desktop';
    const C_REPO = 'repo';
    const C_ADMINISTRATION = 'administration';
    const C_MAIL = 'mail';
    /**
     * @var ContextRepository
     */
    protected $repo;
    /**
     * @var ScreenContext[]
     */
    protected $stack = [];


    /**
     * ContextCollection constructor.
     *
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
        array_push($this->stack, $context);
    }


    /**
     * @return ScreenContext
     */
    public function getLast() : ScreenContext
    {
        return end($this->stack);
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
     *
     * @return bool
     */
    public function hasMatch(ContextCollection $other_collection) : bool
    {
        $mapper = function (ScreenContext $c) {
            return $c->getUniqueContextIdentifier();
        };
        $mine = array_map($mapper, $this->getStack());
        $theirs = array_map($mapper, $other_collection->getStack());

        return (count(array_intersect($mine, $theirs)) > 0);
    }

    //
    //
    //
    /**
     * @return ContextCollection
     */
    public function main() : ContextCollection
    {
        $context = $this->repo->main();
        $this->push($context);

        return $this;
    }


    /**
     * @return ContextCollection
     */
    public function desktop() : ContextCollection
    {
        $this->push($this->repo->desktop());

        return $this;
    }


    /**
     * @return ContextCollection
     */
    public function repository() : ContextCollection
    {
        $this->push($this->repo->repository());

        return $this;
    }


    /**
     * @return ContextCollection
     */
    public function administration() : ContextCollection
    {
        $this->push($this->repo->administration());

        return $this;
    }


    /**
     * @return ContextCollection
     */
    public function internal() : ContextCollection
    {
        $this->push($this->repo->internal());

        return $this;
    }


    /**
     * @return ContextCollection
     */
    public function external() : ContextCollection
    {
        $this->push($this->repo->external());

        return $this;
    }
}