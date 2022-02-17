<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Identification\NullPluginIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\MainMenu\Provider\CustomMainBarProvider;

/**
 * Class ilMMItemRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemRepository
{

    /**
     * @var ilDBInterface
     */
    private $db;
    /**
     * @var ilGlobalCache
     */
    private $cache;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $services;
    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector
     */
    private $main_collector;

    /**
     * ilMMItemRepository constructor.
     * @throws Throwable
     */
    public function __construct()
    {
        global $DIC;
        $this->cache          = ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
        $this->db             = $DIC->database();
        $this->main_collector = $DIC->globalScreen()->collector()->mainmenu();
        $this->main_collector->collectOnce();
        $this->services = $DIC->globalScreen();

        foreach ($this->main_collector->getRawItems() as $top_item) {
            ilMMItemStorage::register($top_item);
        }
    }

    public function clearCache() : void
    {
        $this->cache->flush();
    }

    /**
     * @param IdentificationInterface $identification
     * @return isItem
     */
    public function getSingleItem(IdentificationInterface $identification) : isItem
    {
        return $this->main_collector->getSingleItemFromRaw($identification);
    }

    public function getSingleItemFromFilter(IdentificationInterface $identification) : isItem
    {
        return $this->main_collector->getSingleItemFromFilter($identification);
    }

    public function resolveIdentificationFromString(string $identification_string) : IdentificationInterface
    {
        return $this->services->identification()->fromSerializedIdentification($identification_string);
    }

    /**
     * @return ilMMItemRepository
     */
    public function repository() : ilMMItemRepository
    {
        return $this;
    }

    /**
     * @return array
     * @throws arException
     */
    public function getTopItems() : array
    {
        return ilMMItemStorage::where(" parent_identification = '' OR parent_identification IS NULL ")->orderBy('position')->getArray();
    }

    /**
     * @return array
     */
    public function getSubItemsForTable() : array
    {
        $r      = $this->db->query(
            "SELECT sub_items.*, top_items.position AS parent_position
FROM il_mm_items AS sub_items
LEFT JOIN il_mm_items AS top_items ON top_items.identification = sub_items.parent_identification
WHERE sub_items.parent_identification != '' ORDER BY top_items.position, parent_identification, sub_items.position ASC"
        );
        $return = [];
        while ($data = $this->db->fetchAssoc($r)) {
            $return[] = $data;
        }

        return $return;
    }

    public function flushLostItems()
    {
        foreach ($this->getTopItems() as $item) {
            $item_facade = $this->getItemFacade($this->services->identification()->fromSerializedIdentification($item['identification']));
            if (Lost::class === $item_facade->getType()) {
                $item_facade->delete();
            }
        }

        foreach ($this->getSubItemsForTable() as $item) {
            $item_facade = $this->getItemFacade($this->services->identification()->fromSerializedIdentification($item['identification']));
            if (Lost::class === $item_facade->getType()) {
                $item_facade->delete();
            }
        }
    }

    public function hasLostItems() : bool
    {
        foreach ($this->getTopItems() as $item) {
            $item_facade = $this->getItemFacade($this->services->identification()->fromSerializedIdentification($item['identification']));
            if (Lost::class === $item_facade->getType()) {
                return true;
            }
        }

        foreach ($this->getSubItemsForTable() as $item) {
            $item_facade = $this->getItemFacade($this->services->identification()->fromSerializedIdentification($item['identification']));
            if (Lost::class === $item_facade->getType()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param IdentificationInterface|null $identification
     * @return ilMMItemFacadeInterface
     * @throws Throwable
     */
    public function getItemFacade(IdentificationInterface $identification = null) : ilMMItemFacadeInterface
    {
        if ($identification === null || $identification instanceof NullIdentification || $identification instanceof NullPluginIdentification) {
            return new ilMMNullItemFacade($identification ? $identification : new NullIdentification(), $this->main_collector);
        }
        if ($identification->getClassName() === CustomMainBarProvider::class) {
            return new ilMMCustomItemFacade($identification, $this->main_collector);
        }

        return new ilMMItemFacade($identification, $this->main_collector);
    }

    /**
     * @param string $identification
     * @return ilMMItemFacadeInterface
     * @throws Throwable
     */
    public function getItemFacadeForIdentificationString(string $identification) : ilMMItemFacadeInterface
    {
        $id = $this->services->identification()->fromSerializedIdentification($identification);

        return $this->getItemFacade($id);
    }

    public function getPossibleParentsForFormAndTable() : array
    {
        static $parents;
        if ($parents === null) {
            $parents = [];
            foreach ($this->getTopItems() as $top_item_identification => $data) {
                $identification = $this->services->identification()->fromSerializedIdentification($top_item_identification);
                $item           = $this->getSingleItem($identification);
                if ($item instanceof TopParentItem) {
                    $parents[$top_item_identification] = $this->getItemFacade($identification)
                                                              ->getDefaultTitle();
                }
            }
        }

        return $parents;
    }

    /**
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation[]
     */
    public function getPossibleSubItemTypesWithInformation() : array
    {
        $types = [];
        foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
            if ($information->isCreationPrevented()) {
                continue;
            }
            if ($information->isChild()) {
                if ($information->getType() === TopLinkItem::class) { // since these two types are identical (more or less), we truncate one
                    continue;
                }
                $types[$information->getType()] = $information;
            }
        }

        return $types;
    }

    /**
     * @return \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation[]
     */
    public function getPossibleTopItemTypesWithInformation() : array
    {
        $types = [];
        foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
            if ($information->isTop()) {
                $types[$information->getType()] = $information;
            }
        }

        return $types;
    }

    /**
     * @param string $type
     * @return TypeHandler
     * @deprecated
     */
    public function getTypeHandlerForType(string $type) : TypeHandler
    {
        $item = $this->services->mainBar()->custom($type, new NullIdentification());

        return $this->main_collector->getTypeHandlerForItem($item);
    }

    /**
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function updateItem(ilMMItemFacadeInterface $item_facade) : void
    {
        if ($item_facade->isEditable()) {
            $item_facade->update();
            $this->clearCache();
        }
    }

    /**
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function createItem(ilMMItemFacadeInterface $item_facade) : void
    {
        $item_facade->create();
        $this->clearCache();
    }

    /**
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function deleteItem(ilMMItemFacadeInterface $item_facade) : void
    {
        if ($item_facade->isDeletable()) {
            $item_facade->delete();
            $this->clearCache();
        }
    }
}
