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
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

/**
 * Class MainMenuMainCollector
 * This Collector will collect and then provide all available slates from the
 * providers in the whole system, stack them and enrich them will their content
 * based on the configuration in "Administration".
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
     * @param array                $providers
     * @param ItemInformation|null $information
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

    public function filterItemsByVisibilty(bool $async_only = false) : void
    {
        // apply filter
        $this->map->filter(function (isItem $item) use ($async_only) : bool {
            if ($async_only && !$item instanceof supportsAsynchronousLoading) {
                return false;
            }
            if (!$item->isAvailable()) {
                return false;
            }

            // make parent available if one child is always available
            if ($item instanceof isParent) {
                foreach ($item->getChildren() as $child) {
                    if ($child->isAlwaysAvailable()) {
                        return true;
                    }
                }
            }

            // Always avaiable must be delivered when visible
            if ($item->isAlwaysAvailable()) {
                return $item->isVisible();
            }
            // All other cases
            return $item->isAvailable() && $item->isVisible() && $this->information->isItemActive($item);
        });
    }

    public function prepareItemsForUIRepresentation() : void
    {
        /*$this->map->walk(static function (\Iterator $i) {
            $item = $i->current();
            if ($item instanceof isParent) {
                $separators = 0;
                $children   = [];
                foreach ($item->getChildren() as $position => $child) {
                    if ($child instanceof Separator) {
                        $separators++;
                    } else {
                        $separators = 0;
                    }
                    if ($separators > 1) {
                        continue;
                    }
                    $children[] = $child;
                }
                $item = $item->withChildren($children);
            }
            return true;
        });*/

        $this->map->walk(function (isItem &$item) : isItem {
            $item->setTypeInformation($this->getTypeInformationForItem($item));

            // Apply the TypeHandler
            $item = $this->getTypeHandlerForItem($item)->enrichItem($item);
            // Translate Item
            if ($item instanceof hasTitle) {
                $item = $this->getItemInformation()->customTranslationForUser($item);
            }
            // Custom Symbol
            if ($item instanceof hasSymbol) {
                $item = $this->getItemInformation()->customSymbol($item);
            }
            // Custom Position
            $item = $this->getItemInformation()->customPosition($item);

            return $item;
        });

        // Override parent from configuration
        $this->map->walk(function (isItem &$item) {
            if ($item instanceof isChild) {
                $parent = $this->map->getSingleItemFromFilter($this->information->getParent($item));
                if ($parent instanceof isParent) {
                    $parent->appendChild($item);
                    if ($parent instanceof Lost && $parent->getProviderIdentification()->serialize() === '') {
                        $item->overrideParent($parent->getProviderIdentification());
                    }
                }
            }

            return $item;
        });
    }

    public function cleanupItemsForUIRepresentation() : void
    {
        // Remove not visible children
        $this->map->walk(function (isItem &$item) : isItem {
            if ($item instanceof isParent) {
                foreach ($item->getChildren() as $child) {
                    if (!$this->map->existsInFilter($child->getProviderIdentification())) {
                        $item->removeChild($child);
                    }
                }
            }
            return $item;
        });

        // filter empty slates
        $this->map->filter(static function (isItem $i) : bool {
            if ($i instanceof isParent) {
                return count($i->getChildren()) > 0;
            }

            return true;
        });
    }

    public function sortItemsForUIRepresentation() : void
    {
        $this->map->sort();
    }

    /**
     * This will return all available isTopItem (and moved isInterchangeableItem),
     * stacked based on the configuration in "Administration" and for the
     * visibility of the currently user.
     * @return \Generator|isTopItem[]|isInterchangeableItem[]
     */
    public function getItemsForUIRepresentation() : \Generator
    {
        foreach ($this->map->getAllFromFilter() as $item) {
            if ($item->isTop()) {
                yield $item;
            }
        }
    }

    /**
     * @return \Generator|isItem[]
     */
    public function getRawItems() : \Generator
    {
        yield from $this->map->getAllFromFilter();
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
     * @return isItem
     * @deprecated
     */
    public function getSingleItemFromFilter(IdentificationInterface $identification) : isItem
    {
        $item = $this->map->getSingleItemFromFilter($identification);
        $this->map->add($item);

        return $item;
    }

    /**
     * @param IdentificationInterface $identification
     * @return isItem
     * @deprecated
     */
    public function getSingleItemFromRaw(IdentificationInterface $identification) : isItem
    {
        $item = $this->map->getSingleItemFromRaw($identification);
        $this->map->add($item);

        return $item;
    }

    /**
     * @param isItem $item
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
     * @return ItemInformation
     */
    public function getItemInformation() : ItemInformation
    {
        return $this->information;
    }

    /**
     * @param isItem $item
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
}
