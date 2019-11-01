<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector;

use ArrayObject;
use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;

/**
 * Class ItemMap
 *
 * @package ILIAS\GlobalScreen\Scope\MainMenu\Collector
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class ItemMap
{

    /**
     * @var ArrayObject|isItem[]
     */
    private $map;


    /**
     * ItemMap constructor.
     */
    public function __construct()
    {
        $this->map = new ArrayObject();
    }


    /**
     * @param isItem $item
     */
    public function add(isItem $item) : void
    {
        $this->map->offsetSet($item->getProviderIdentification()->serialize(), $item);
    }


    /**
     * @param isItem ...$items
     */
    public function addMultiple(isItem ...$items) : void
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return isItem
     */
    public function get(IdentificationInterface $identification) : isItem
    {
        return $this->map->offsetGet($identification->serialize());
    }


    /**
     * @param IdentificationInterface $identification
     */
    public function remove(IdentificationInterface $identification) : void
    {
        $this->map->offsetUnset($identification->serialize());
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return bool
     */
    public function exists(IdentificationInterface $identification) : bool
    {
        return $this->map->offsetExists($identification->serialize());
    }


    /**
     * @return bool
     */
    public function has() : bool
    {
        return $this->map->count() > 0;
    }


    /**
     * @param Closure $c
     */
    public function walk(Closure $c) : void
    {
        array_walk($this->map, $c);
    }


    /**
     * @param Closure $c
     */
    public function filter(Closure $c) : void
    {
        $this->map = new ArrayObject(array_filter($this->map->getArrayCopy(), $c));
    }


    /**
     * @return \Generator|isItem[]
     */
    public function getAll() : \Generator
    {
        yield from $this->map;
    }
}
