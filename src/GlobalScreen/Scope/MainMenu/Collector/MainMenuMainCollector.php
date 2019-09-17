<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\BaseTypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
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
class MainMenuMainCollector
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
     * MainMenuMainCollector constructor.
     *
     * @param array                $providers
     * @param ItemInformation|null $information
     *
     * @throws \Throwable
     */
    public function __construct(array $providers, ItemInformation $information = null)
    {
        if (self::$constructed === true) {
            throw new \LogicException("only one Instance of MainMenuMainCollector Collector is possible");
        }
        self::$constructed = true;
        $this->information = $information;
        $this->providers = $providers;
        $this->type_information_collection = new TypeInformationCollection();
        $this->load();
    }


    /**
     * This will return all available topitems, stacked based on the configuration
     * in "Administration" and for the visibility of the currently user.
     * Additionally this will filter sequent Separators to avoid double Separators
     * in the UI.
     *
     * @return isTopItem[]
     * @throws \Throwable
     */
    public function getStackedTopItemsForPresentation() : array
    {
        return $this->getStackedTopItems();
    }


    /**
     * @return isTopItem[]
     * @throws \Throwable
     */
    private function getStackedTopItems() : array
    {
        $this->load();
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
     * @param isItem $item
     *
     * @return bool
     */

    private function checkAvailability(isItem $item) : bool
    {
        $is_visible = $item->isVisible();
        $is_item_active = $this->information->isItemActive($item);
        $is_always_available = $item->isAlwaysAvailable();

        return !(!$is_visible || !$is_item_active && !$is_always_available);
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return isItem
     * @throws \Throwable
     */
    public function getSingleItem(IdentificationInterface $identification) : isItem
    {
        $this->load();
        try {
            return self::$items[$identification->serialize()];
        } catch (\Throwable $e) {
            return $this->getLostItem($identification);
        }
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return Lost
     */
    private function getLostItem(IdentificationInterface $identification) : Lost
    {
        global $DIC;

        return $DIC->globalScreen()->mainmenu()->custom(Lost::class, new NullIdentification($identification))
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
     * @return bool
     * @throws \Throwable
     */
    private function load() : bool
    {
        if ($this->loaded === false || $this->loaded === null) {
            /**
             * @var $provider            \ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider
             * @var $top_item            \ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem
             * @var $sub_item            \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild
             */
            try {
                $this->loaded = true;
                $this->loadTypeInformation();
                $this->loadTopItems();
                $this->loadSubItems();
            } catch (\Throwable $e) {
                throw $e;
            }
        }

        return $this->loaded;
    }


    private function loadTopItems()
    {
        foreach ($this->providers as $provider) {
            foreach ($provider->getStaticTopItems() as $top_item) {
                if ($top_item instanceof hasTitle && $this->information) {
                    $top_item = $this->information->translateItemForUser($top_item);
                }
                $this->addItemToMap($top_item);
            }
        }
    }


    private function loadSubItems()
    {
        foreach ($this->providers as $provider) {
            foreach ($provider->getStaticSubItems() as $sub_item) {
                if ($sub_item instanceof hasTitle && $this->information) {
                    $sub_item = $this->information->translateItemForUser($sub_item);
                }
                if ($sub_item instanceof isChild && $sub_item->hasParent()) {
                    $new_parent_identification = $this->information->getParent($sub_item);
                    $parent_item = $this->getSingleItem($new_parent_identification);
                    if ($parent_item->getProviderIdentification() instanceof NullIdentification) {
                        $this->addItemToMap($parent_item);
                        // self::$items[$parent_item->getProviderIdentification()->serialize()] = $parent_item;
                        // self::$topitems[$parent_item->getProviderIdentification()->serialize()] = $parent_item;
                        $sub_item->overrideParent($parent_item->getProviderIdentification());
                    } else {
                        $sub_item->overrideParent($new_parent_identification);
                    }
                    if ($this->itemExistsInMap($sub_item->getParent()) && $this->getItemFromMap($sub_item->getParent()) instanceof isParent) {
                        $this->getItemFromMap($sub_item->getParent())->appendChild($sub_item);
                        // self::$topitems[$sub_item->getParent()->serialize()]->appendChild($sub_item);
                        if ($sub_item->isAlwaysAvailable()) {
                            $parent = $this->getItemFromMap($sub_item->getParent())->withAlwaysAvailable(true);
                            $this->addItemToMap($parent);
                            // self::$topitems[$sub_item->getParent()->serialize()] = self::$topitems[$sub_item->getParent()->serialize()]->withAlwaysAvailable(true);
                        }
                    }
                }
                $this->addItemToMap($sub_item);
                // self::$items[$sub_item->getProviderIdentification()->serialize()] = $sub_item; // register them always since they could be lost
            }
        }
    }


    /**
     * @param isItem $item
     *
     * @return isItem
     */
    private function applyTypeHandler(isItem $item) : isItem
    {
        $item = $this->getHandlerForItem($item)->enrichItem($item);

        return $item;
    }


    /**
     * @param isItem $item
     *
     * @return TypeHandler
     */
    public function getHandlerForItem(isItem $item) : TypeHandler
    {
        /**
         * @var $handler TypeHandler
         */
        $type = get_class($item);
        $type_information = $this->type_information_collection->get($type);
        if (is_null($type_information)) {
            return new BaseTypeHandler();
        }
        $handler = $type_information->getTypeHandler();

        return $handler;
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


    /**
     * @return TypeInformationCollection
     */
    public function getTypeInformationCollection() : TypeInformationCollection
    {
        return $this->type_information_collection;
    }


    private function loadTypeInformation()
    {
        foreach ($this->providers as $provider) {
            if ($provider instanceof StaticMainMenuProvider) {
                $this->type_information_collection->append($provider->provideTypeInformation());
            }
        }
    }


    /**
     * @param isItem $item
     */
    private function addItemToMap(isItem $item)
    {
        if ($item instanceof isItem) {
            $item->setTypeInformation($this->type_information_collection->get(get_class($item)));
        }

        if ($item instanceof isTopItem) {
            self::$topitems[$item->getProviderIdentification()->serialize()] = $item;
        }
        self::$items[$item->getProviderIdentification()->serialize()] = $item;
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return bool
     */
    private function itemExistsInMap(IdentificationInterface $identification) : bool
    {
        return isset(self::$items[$identification->serialize()]);
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return isItem|mixed
     */
    private function getItemFromMap(IdentificationInterface $identification)
    {
        if (!$this->itemExistsInMap($identification)) {
            // Exception?
        }

        return self::$items[$identification->serialize()];
    }
}
