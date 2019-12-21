<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map;

use ArrayIterator;
use ArrayObject;
use Closure;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

/**
 * Class Map
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class Map implements Filterable, Walkable
{

    /**
     * @var ArrayObject
     */
    protected $map;
    /**
     * @var ArrayIterator
     */
    protected $filter;


    /**
     * Tree constructor.
     */
    public function __construct()
    {
        $this->map = new ArrayObject();
        $this->filter = new ArrayIterator($this->map);
    }


    /**
     * @param isItem $item
     */
    public function add(isItem $item) : void
    {
        $this->map[$item->getProviderIdentification()->serialize()] = $item;
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
        $item = $this->map->offsetGet($identification->serialize());
        if ($item === null) {
            return $this->getLostItem($identification);
        }

        return $item;
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
     * @return \Generator|isItem[]
     */
    public function getAll() : \Generator
    {
        yield from $this->map;
    }


    /**
     * @return \Generator|isItem[]
     */
    public function getFiltered() : \Generator
    {
        yield from $this->filter;
    }


    public function getArrayCopy() : array
    {
        return $this->map->getArrayCopy();
    }


    /**
     * @inheritDoc
     */
    public function walk(Closure $c) : void
    {
        array_walk($this->map, $c);
    }


    /**
     * @inheritDoc
     */
    public function filter(Closure $c) : void
    {
        $this->filter = new \CallbackFilterIterator($this->filter, $c);
    }


    public function sort()
    {
        $this->map->uasort(function (isItem $item_one, isItem $item_two) : bool {
            /**
             * @var $parent isParent
             */
            if ($item_one instanceof isChild) {
                $parent = $this->get($item_one->getParent());
                $position_item_one = ($parent->getPosition() * 1000) + $item_one->getPosition();
            } else {
                $position_item_one = $item_one->getPosition();
            }

            if ($item_two instanceof isChild) {
                $parent = $this->get($item_two->getParent());
                $position_item_two = ($parent->getPosition() * 1000) + $item_two->getPosition();
            } else {
                $position_item_two = $item_two->getPosition();
            }

            return $position_item_one > $position_item_two;
        });
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return Lost
     */
    private function getLostItem(IdentificationInterface $identification) : Lost
    {
        global $DIC;

        return $DIC->globalScreen()->mainBar()->custom(Lost::class, new NullIdentification($identification))
            ->withAlwaysAvailable(true)
            ->withNonAvailableReason($DIC->ui()->factory()->legacy("{$DIC->language()->txt('mme_lost_item_reason')}"))
            ->withVisibilityCallable(
                function () use ($DIC) {
                    return (bool) ($DIC->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
                }
            )->withTitle($DIC->language()->txt("mme_lost_item_title"));
    }
}
