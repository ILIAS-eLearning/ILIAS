<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector;

use ILIAS\GlobalScreen\Collector\Collector;
use ILIAS\GlobalScreen\Collector\LogicException;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\BaseTypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Separator;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;

/**
 * Class MainMenuMainCollector
 *
 * This Collector will collect and then provide all available slates from the
 * providers in the whole system, stack them and enrich them will their content
 * based on the configuration in "Administration".
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainMenuMainCollector implements Collector
{

    /**
     * @var bool
     */
    private static $constructed = false;
    /**
     * @var array|isItem[]
     */
    private static $items = [];
    /**
     * @var array|isItem[]
     */
    private static $topitems = [];
    /**
     * @var TypeInformationCollection
     */
    private $type_information_collection;
    /**
     * @var ItemInformation|null
     */
    private $information;
    /**
     * @var array|Provider[]
     */
    protected $providers;
    /**
     * @var bool
     */
    private $loaded = false;
    /**
     * @var ItemMap
     */
    private $item_map;


    /**
     * MainMenuMainCollector constructor.
     *
     * @param array                $providers
     * @param ItemInformation|null $information
     *
     * @throws \Throwable
     */
    public function __construct(array $providers, ItemInformation $information = null)
    {
        $this->information = $information;
        $this->providers = $providers;
        $this->item_map = new ItemMap();
        $this->type_information_collection = new TypeInformationCollection();
    }


    /**
     * @return \Generator|StaticMainMenuProvider[]
     */
    private function getProvidersFromList() : \Generator
    {
        yield from $this->providers;
    }


    /**
     * @return bool
     * @throws \Throwable
     */
    public function collect() : void
    {
        $this->collectStructure();
        $this->filterItemsByVisibilty(false);
        $this->prepareItemsForUIRepresentation();
    }


    public function collectStructure() : void
    {
        foreach ($this->getProvidersFromList() as $provider) {
            $this->type_information_collection->append($provider->provideTypeInformation());
            $this->item_map->addMultiple(...$provider->getStaticTopItems());
            $this->item_map->addMultiple(...$provider->getStaticSubItems());
        }

        $this->item_map->walk(function (isItem &$item) {
            if ($item instanceof isChild && $item->hasParent()) {
                $parent = $this->item_map->get($this->information->getParent($item));
                if ($parent instanceof isParent) {
                    $parent->appendChild($item);
                    $item->overrideParent($parent->getProviderIdentification());
                }
                // TODO if parent is NullIdentification, add to map
            }
        });
    }


    public function filterItemsByVisibilty(bool $skip_async = false) : void
    {
        // apply filter
        $this->item_map->filter(function (isItem $item) {
            // make parent available if one child is always available
            if ($item instanceof isParent) {
                foreach ($item->getChildren() as $child) {
                    if ($child->isAlwaysAvailable()) {
                        return true;
                    }
                }
            }

            $is_visible = $item->isVisible();
            $is_item_active = $this->information->isItemActive($item);
            $is_always_available = $item->isAlwaysAvailable();

            return !(!$is_visible || (!$is_item_active && !$is_always_available));
        });

        $this->item_map->filter(function (isItem $item) {
            return true;
        });

        // apply special filters such as double dividers etc.
        // TODO!!
    }


    public function prepareItemsForUIRepresentation() : void
    {
        $this->item_map->walk(function (isItem &$item) {
            $item_information = $this->getTypeInformationForItem($item);
            $item->setTypeInformation($item_information);

            // Apply the TypeHandler
            $item = $this->getTypeHandlerForItem($item)->enrichItem($item);
            // Translate Item
            if ($item instanceof hasTitle) {
                $item = $this->getItemInformation()->translateItemForUser($item);
            }
        });
    }


    /**
     * @return \Generator|isItem[]
     */
    public function getRawItems() : \Generator
    {
        yield from $this->item_map->getAll();
    }


    /**
     * @inheritDoc
     */
    public function hasItems() : bool
    {
        return $this->item_map->has();
    }


    /**
     * This will return all available topitems, stacked based on the configuration
     * in "Administration" and for the visibility of the currently user.
     * Additionally this will filter sequent Separators to avoid double Separators
     * in the UI.
     *
     * @return \Generator
     * @throws \Throwable
     */
    public function getItemsForUIRepresentation() : \Generator
    {
        foreach ($this->item_map->getAll() as $item) {
            // $this->applyTypeHandler($item);
            if ($item instanceof isTopItem) {
                yield $item;
            }
        }
        // return $this->getStackedTopItems();
    }


    /**
     * @return isTopItem[]
     * @throws \Throwable
     */
    private function getStackedTopItems() : array
    {
        $top_items = [];
        foreach (self::$topitems as $top_item) {
            if (!$this->checkAvailability($top_item)) {
                continue;
            }
            if ($top_item instanceof isTopItem && $this->information) {
                if ($top_item instanceof isParent) {
                    $has_always_available_item = false;
                    $children = [];
                    /**
                     * @var $top_item  isParent
                     * @var $child     isChild
                     */
                    foreach ($top_item->getChildren() as $child) {
                        $child = $this->applyTypeHandler($child);
                        if (!$this->checkAvailability($child)) {
                            continue;
                        }
                        $position_of_sub_item = $this->information->getPositionOfSubItem($child);
                        if (isset($children[$position_of_sub_item])) {
                            $position_of_sub_item = max(array_keys($children)) + 1;
                        }
                        $children[$position_of_sub_item] = $child;
                        if ($child->isAlwaysAvailable() === true) {
                            $has_always_available_item = true;
                        }
                    }
                    ksort($children);
                    $children = $this->handleDoubleDividers($children);
                    // bugfix mantis 25577
                    $children = $this->handleSolitaryDividers($children, $top_item);

                    // https://mantis.ilias.de/view.php?id=24061
                    if (count($children) === 0) {
                        unset($top_item);
                        continue;
                    }

                    $top_item = $top_item->withChildren($children);
                    if ($has_always_available_item === true) {
                        $top_item = $top_item->withAlwaysAvailable(true);
                    }
                }
                $top_item = $this->applyTypeHandler($top_item);
                $position_of_top_item = $this->information->getPositionOfTopItem($top_item);
                if (isset($top_items[$position_of_top_item])) {
                    $position_of_top_item = max(array_keys($top_items)) + 1;
                }
                $top_items[$position_of_top_item] = $top_item;
            }
        }
        ksort($top_items);

        return $top_items;
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return isItem
     * @throws \Throwable
     *
     * @deprecated
     */
    public function getSingleItem(IdentificationInterface $identification) : isItem
    {
        return $this->item_map->get($identification);
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
            ->setTypeInformation($this->type_information_collection->get(Lost::class))
            ->withNonAvailableReason($DIC->ui()->factory()->legacy("{$DIC->language()->txt('mme_lost_item_reason')}"))
            ->withVisibilityCallable(
                function () use ($DIC) {
                    return (bool) ($DIC->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
                }
            )->withTitle($DIC->language()->txt("mme_lost_item_title"));
    }


    /**
     * @param isItem $item
     *
     * @return TypeHandler
     */
    public function getTypeHandlerForItem(isItem $item) : TypeHandler
    {
        $type_information = $this->getTypeInformationForItem($item);
        if ($type_information === null) {
            return new BaseTypeHandler();
        }

        return $type_information->getTypeHandler();
    }


    /**
     * @param isItem $item
     *
     * @return ItemInformation
     */
    public function getItemInformation() : ItemInformation
    {
        return $this->information;
    }


    /**
     * @param isItem $item
     *
     * @return TypeInformation
     */
    public function getTypeInformationForItem(isItem $item) : TypeInformation
    {
        return $this->getTypeInformationCollection()->get(get_class($item));
    }


    /**
     * @param $children
     *
     * @return array
     */
    private function handleDoubleDividers($children) : array
    {
        $separators = 0;
        foreach ($children as $position => $child) {
            if ($child instanceof Separator) {
                $separators++;
            } else {
                $separators = 0;
            }
            if ($separators > 1) {
                unset($children[$position]);
            }
        }

        return $children;
    }


    /**
     * @return TypeInformationCollection
     */
    public function getTypeInformationCollection() : TypeInformationCollection
    {
        return $this->type_information_collection;
    }


    /**
     * bugfix mantis 25577:
     * prevent solitary dividers from being shown
     *
     * @param $children
     *
     * @return array
     */
    private function handleSolitaryDividers($children, $top_item) : array
    {
        foreach ($children as $position => $child) {
            if ($child instanceof Separator) {
                // remove dividers that are the only item of the item-list and remove their top-item as well
                if (count($children) === 1) {
                    unset($children[$position]);
                    unset($top_item);
                    continue;
                }
                // remove dividers that stand alone at the beginning of the item-list
                if ($position == min(array_keys($children))) {
                    unset($children[$position]);
                    continue;
                }
                // remove dividers that stand alone at the end of the item-list
                if ($position == max(array_keys($children))) {
                    unset($children[$position]);
                    continue;
                }
            }
        }

        return $children;
    }
}
