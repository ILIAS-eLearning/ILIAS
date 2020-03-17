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
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class Map implements Filterable, Walkable
{

    /**
     * @var ArrayObject
     */
    protected $raw;
    /**
     * @var Closure[]
     */
    protected $filters = [];

    /**
     * Tree constructor.
     */
    public function __construct()
    {
        $this->raw = new ArrayObject();
    }

    /**
     * @param isItem $item
     */
    public function add(isItem $item) : void
    {
        $serialize = $item->getProviderIdentification()->serialize();
        if (0 < strlen($serialize)) {
            $this->raw[$serialize] = $item;
        }
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
     * @param bool                    $filtered
     * @return isItem
     */
    public function getSingleItemFromFilter(IdentificationInterface $identification) : isItem
    {
        $item = $this->raw->offsetGet($identification->serialize());

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
        $this->raw->offsetUnset($identification->serialize());
    }

    /**
     * @param IdentificationInterface $identification
     * @return bool
     */
    public function existsInFilter(IdentificationInterface $identification) : bool
    {
        return $this->raw->offsetExists($identification->serialize());
    }

    /**
     * @return bool
     */
    public function has() : bool
    {
        return $this->raw->count() > 0;
    }

    /**
     * @return \Generator|isItem[]
     */
    public function getAllFromFilter() : \Generator
    {
        static $filtered;
        if (!isset($filtered)) {
            $filtered = new ArrayIterator($this->raw);
            foreach ($this->filters as $filter) {
                $filtered = new \CallbackFilterIterator($filtered, $filter);
            }
        }

        yield from $filtered;
    }

    /**
     * @inheritDoc
     */
    public function walk(Closure $c) : void
    {
        array_walk($this->raw, $c);
    }

    /**
     * @inheritDoc
     */
    public function filter(Closure $c) : void
    {
        $this->filters[] = $c;
    }

    public function sort()
    {
        $sorter = function (isItem $item_one, isItem $item_two) : bool {
            /**
             * @var $parent isParent
             */
            if ($item_one instanceof isChild) {
                $parent = $this->getSingleItemFromFilter($item_one->getParent());
                $position_item_one = ($parent->getPosition() * 1000) + $item_one->getPosition();
            } else {
                $position_item_one = $item_one->getPosition();
            }

            if ($item_two instanceof isChild) {
                $parent = $this->getSingleItemFromFilter($item_two->getParent());
                $position_item_two = ($parent->getPosition() * 1000) + $item_two->getPosition();
            } else {
                $position_item_two = $item_two->getPosition();
            }

            return $position_item_one > $position_item_two;
        };

        $this->raw->uasort($sorter);
        $this->walk(static function (isItem &$item) use ($sorter) : isItem {
            if ($item instanceof isParent) {
                $children = $item->getChildren();
                uasort($children, $sorter);
                $item = $item->withChildren($children);
            }
            return $item;
        });
    }

    /**
     * @param IdentificationInterface $identification
     * @return Lost
     */
    private function getLostItem(IdentificationInterface $identification) : Lost
    {
        global $DIC;

        $lost_item = $DIC->globalScreen()->mainBar()->custom(Lost::class, new NullIdentification($identification))
                         ->withAlwaysAvailable(true)
                         ->withNonAvailableReason($DIC->ui()->factory()->legacy("{$DIC->language()->txt('mme_lost_item_reason')}"))
                         ->withVisibilityCallable(
                             function () use ($DIC) {
                                 return (bool) ($DIC->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
                             }
                         )->withTitle($DIC->language()->txt("mme_lost_item_title"));

        $this->add($lost_item);

        return $lost_item;
    }
}
