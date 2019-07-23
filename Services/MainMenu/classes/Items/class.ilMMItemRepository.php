<?php

use ILIAS\GlobalScreen\Collector\CoreStorageFacade;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Identification\NullPluginIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\MainMenu\Provider\CustomMainBarProvider;

/**
 * Class ilMMItemRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemRepository
{

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $services;
    /**
     * @var bool
     */
    private $synced = false;
    /**
     * @var StorageFacade
     */
    private $storage;
    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector
     */
    private $main_collector;


    /**
     * ilMMItemRepository constructor.
     *
     * @throws Throwable
     */
    public function __construct()
    {
        global $DIC;
        $this->storage = new CoreStorageFacade();
        $this->main_collector = $DIC->globalScreen()->collector()->mainmenu();
        $this->services = $DIC->globalScreen();

        foreach ($this->main_collector->getAllItems() as $top_item) {
            ilMMItemStorage::register($top_item);
            if ($top_item instanceof isParent) {
                foreach ($top_item->getChildren() as $child) {
                    ilMMItemStorage::register($child);
                }
            }
        }
    }


    /**
     * @param string $class_name
     *
     * @return isItem
     */
    public function getEmptyItemForTypeString(string $class_name) : isItem
    {
        return $this->services->mainBar()->custom($class_name, new  NullIdentification());
    }


    public function clearCache()
    {
        $this->storage->cache()->flush();
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return isItem
     * @throws Throwable
     */
    public function getSingleItem(IdentificationInterface $identification) : isItem
    {
        return $this->main_collector->getSingleItem($identification);
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
        $r = $this->storage->db()->query(
            "SELECT sub_items.*, top_items.position AS parent_position 
FROM il_mm_items AS sub_items 
LEFT JOIN il_mm_items AS top_items ON top_items.identification = sub_items.parent_identification
WHERE sub_items.parent_identification != '' ORDER BY top_items.position, parent_identification, sub_items.position ASC"
        );
        $return = [];
        while ($data = $this->storage->db()->fetchAssoc($r)) {
            $return[] = $data;
        }

        return $return;
    }


    /**
     * @param IdentificationInterface|null $identification
     *
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
     *
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
        if (is_null($parents)) {
            $parents = [];
            foreach ($this->getTopItems() as $top_item_identification => $data) {
                $identification = $this->services->identification()->fromSerializedIdentification($top_item_identification);
                $item = $this->getSingleItem($identification);
                if ($item instanceof TopParentItem) {
                    $parents[$top_item_identification] = $this->getItemFacade($identification)
                        ->getDefaultTitle();
                }
            }
        }

        return $parents;
    }


    /**
     * @return array
     * @see getPossibleSubItemTypesWithInformation
     *
     * @deprecated
     */
    public function getPossibleSubItemTypesForForm() : array
    {
        $types = [];
        foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
            if ($information->isCreationPrevented()) {
                continue;
            }
            if ($information->isChild()) {
                $types[$information->getType()] = $information->getTypeNameForPresentation();
            }
        }

        return $types;
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
                $types[$information->getType()] = $information;
            }
        }

        return $types;
    }


    /**
     * @return array
     * @see getPossibleTopItemTypesWithInformation
     * @deprecated
     */
    public function getPossibleTopItemTypesForForm() : array
    {
        $types = [];
        foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
            if ($information->isTop()) {
                $types[$information->getType()] = $information->getTypeNameForPresentation();
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
     *
     * @return TypeHandler
     * @deprecated
     *
     */
    public function getTypeHandlerForType(string $type) : TypeHandler
    {
        $item = $this->services->mainBar()->custom($type, new NullIdentification());

        return $this->main_collector->getHandlerForItem($item);
    }


    /**
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function updateItem(ilMMItemFacadeInterface $item_facade)
    {
        if ($item_facade->isEditable()) {
            $item_facade->update();
            $this->storage->cache()->flush();
        }
    }


    /**
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function createItem(ilMMItemFacadeInterface $item_facade)
    {
        $item_facade->create();
        $this->storage->cache()->flush();
    }


    /**
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function deleteItem(ilMMItemFacadeInterface $item_facade)
    {
        if ($item_facade->isDeletable()) {
            $item_facade->delete();
            $this->storage->cache()->flush();
        }
    }
}
