<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector;

use ILIAS\GlobalScreen\Collector\AbstractBaseCollector;
use ILIAS\GlobalScreen\Collector\ItemCollector;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\BaseTypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map\Map;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
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
class MainMenuMainCollector extends AbstractBaseCollector implements ItemCollector
{

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
     * @var Map
     */
    private $map;


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
        $this->type_information_collection = new TypeInformationCollection();
        $this->map = new Map();
    }


    /**
     * @return \Generator|StaticMainMenuProvider[]
     */
    private function getProvidersFromList() : \Generator
    {
        yield from $this->providers;
    }


    public function collectStructure() : void
    {
        foreach ($this->getProvidersFromList() as $provider) {
            $this->type_information_collection->append($provider->provideTypeInformation());
            $this->map->addMultiple(...$provider->getStaticTopItems());
            $this->map->addMultiple(...$provider->getStaticSubItems());
        }
    }


    public function filterItemsByVisibilty(bool $skip_async = false) : void
    {
        // apply filter
        $this->map->filter(function (isItem $item) {
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

            $b = ($is_visible && $is_item_active) || (!$is_item_active && $is_always_available);

            return $b;
        });

        // apply special filters such as double dividers etc.
        // TODO!!
    }


    public function prepareItemsForUIRepresentation() : void
    {
        $this->map->walk(function (isItem &$item) {
            $item->setTypeInformation($this->getTypeInformationForItem($item));

            // Apply the TypeHandler
            $item = $this->getTypeHandlerForItem($item)->enrichItem($item);
            // Translate Item
            if ($item instanceof hasTitle) {
                $item = $this->getItemInformation()->translateItemForUser($item);
            }
            if ($item instanceof isTopItem) {
                $item = $item->withPosition($this->getItemInformation()->getPositionOfTopItem($item));
            }
            if ($item instanceof isChild) {
                $item = $item->withPosition($this->getItemInformation()->getPositionOfSubItem($item));
            }
        });

        $this->map->sort();

        $this->map->walk(function (isItem &$item) {
            if ($item instanceof isChild && $item->hasParent() && $item->isVisible()) {
                $parent = $this->map->get($this->information->getParent($item));
                if ($parent instanceof isParent) {
                    $parent->appendChild($item);
                    $item->overrideParent($parent->getProviderIdentification());
                }
                // TODO if parent is NullIdentification, add to map
            }
        });


    }


    /**
     * This will return all available topitems, stacked based on the configuration
     * in "Administration" and for the visibility of the currently user.
     * Additionally this will filter sequent Separators to avoid double Separators
     * in the UI.
     *
     * @return \Generator|isTopItem[]
     * @throws \Throwable
     */
    public function getItemsForUIRepresentation() : \Generator
    {
        foreach ($this->map->getFiltered() as $item) {
            if ($item instanceof isTopItem) {
                yield $item;
            }
        }
    }


    /**
     * @return \Generator|isItem[]
     */
    public function getRawItems() : \Generator
    {
        yield from $this->map->getAll();
    }


    /**
     * @inheritDoc
     */
    public function hasItems() : bool
    {
        return $this->map->has();
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
        return $this->map->get($identification);
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
}
