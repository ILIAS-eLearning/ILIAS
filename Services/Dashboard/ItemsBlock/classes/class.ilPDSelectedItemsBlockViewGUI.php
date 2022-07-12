<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

abstract class ilPDSelectedItemsBlockViewGUI
{
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    protected ilPDSelectedItemsBlockProvider $provider;
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilObjectDataCache $object_cache;
    protected ilRbacSystem $accessHandler;
    protected bool $isInManageMode = false;

    private function __construct(ilPDSelectedItemsBlockViewSettings $viewSettings, ilPDSelectedItemsBlockProvider $provider)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->object_cache = $DIC['ilObjDataCache'];
        $this->accessHandler = $DIC->rbac()->system();

        $this->viewSettings = $viewSettings;
        $this->provider = $provider;
    }

    abstract public function getScreenId() : string;

    abstract public function getTitle() : string;

    abstract public function supportsSelectAll() : bool;

    abstract public function getIntroductionHtml() : string;

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    abstract public function getGroups() : array;

    public function mayRemoveItem(int $refId) : bool
    {
        return true;
    }

    public function setIsInManageMode(bool $isInManageMode) : void
    {
        $this->isInManageMode = $isInManageMode;
    }

    public function isInManageMode() : bool
    {
        return $this->isInManageMode;
    }

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    public function getItemGroups() : array
    {
        $items_groups = $this->getGroups();
        $this->preloadItemGroups($items_groups);

        return $items_groups;
    }

    public static function bySettings(ilPDSelectedItemsBlockViewSettings $viewSettings) : ilPDSelectedItemsBlockViewGUI
    {
        if ($viewSettings->isMembershipsViewActive()) {
            return new ilPDSelectedItemsBlockMembershipsViewGUI(
                $viewSettings,
                new ilPDSelectedItemsBlockMembershipsProvider($viewSettings->getActor())
            );
        }

        return new ilPDSelectedItemsBlockSelectedItemsViewGUI(
            $viewSettings,
            new ilPDSelectedItemsBlockSelectedItemsProvider($viewSettings->getActor())
        );
    }

    protected function isRootNode(int $refId) : bool
    {
        return $this->tree->getRootId() == $refId;
    }

    protected function getRepositoryTitle() : string
    {
        $nd = $this->tree->getNodeData($this->tree->getRootId());
        $title = $nd['title'];

        if ($title == 'ILIAS') {
            $title = $this->lng->txt('repository');
        }

        return $title;
    }

    /**
     * @param ilPDSelectedItemsBlockGroup[] $item_groups
     */
    protected function preloadItemGroups(array $item_groups)
    {
        $listPreloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP);

        $obj_ids = [];
        foreach ($item_groups as $item_group) {
            foreach ($item_group->getItems() as $item) {
                $obj_ids[] = (int) $item['obj_id'];
                $listPreloader->addItem($item['obj_id'], $item['type'], $item['ref_id']);
            }
        }

        $listPreloader->preload();
        ilAdvancedMDValues::preloadByObjIds($obj_ids);
    }

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    protected function groupItemsByType() : array
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];

        $object_types_by_container = $DIC['objDefinition']->getGroupedRepositoryObjectTypes(array('cat', 'crs', 'grp', 'fold'));

        $grouped_items = array();

        foreach ($object_types_by_container as $container_object_type => $container_data) {
            $group = new ilPDSelectedItemsBlockGroup();
            // Icons are currently not determined for section header objects
            if (!$objDefinition->isPlugin($container_object_type)) {
                $title = $this->lng->txt('objs_' . $container_object_type);
            } else {
                $pl = ilObjectPlugin::getPluginObjectByType($container_object_type);
                $title = $pl->txt("objs_" . $container_object_type);
            }

            $group->setLabel($title);
            $group->setItems($this->provider->getItems($container_data['objs']));

            $grouped_items[] = $group;
        }

        return $grouped_items;
    }

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    protected function groupItemsByStartDate() : array
    {
        $items = $this->provider->getItems();

        if (0 == count($items)) {
            return array();
        }

        $groups = array(
            'upcoming' => array(),
            'ongoing' => array(),
            'ended' => array(),
            'not_dated' => array()
        );
        foreach ($items as $key => $item) {
            if ($item['start'] && $item['start']->get(IL_CAL_UNIX) > 0 && $item['start'] instanceof ilDateTime) {
                if ($item['start']->get(IL_CAL_UNIX) > time()) {
                    $groups['upcoming'][] = $item;
                } elseif ($item['end']->get(IL_CAL_UNIX) > time()) {
                    $groups['ongoing'][] = $item;
                } else {
                    $groups['ended'][] = $item;
                }
            } else {
                $groups['not_dated'][] = $item;
            }
        }

        uasort($groups['upcoming'], function ($left, $right) {
            if ($left['start']->get(IL_CAL_UNIX) < $right['start']->get(IL_CAL_UNIX)) {
                return -1;
            } elseif ($left['start']->get(IL_CAL_UNIX) > $right['start']->get(IL_CAL_UNIX)) {
                return 1;
            }

            return strcmp($left['title'], $right['title']);
        });

        uasort($groups['ongoing'], function ($left, $right) {
            if ($left['start']->get(IL_CAL_UNIX) < $right['start']->get(IL_CAL_UNIX)) {
                return 1;
            } elseif ($left['start']->get(IL_CAL_UNIX) > $right['start']->get(IL_CAL_UNIX)) {
                return -1;
            }

            return strcmp($left['title'], $right['title']);
        });

        uasort($groups['ended'], function ($left, $right) {
            if ($left['start']->get(IL_CAL_UNIX) < $right['start']->get(IL_CAL_UNIX)) {
                return 1;
            } elseif ($left['start']->get(IL_CAL_UNIX) > $right['start']->get(IL_CAL_UNIX)) {
                return -1;
            }

            return strcmp($left['title'], $right['title']);
        });

        uasort($groups['not_dated'], function ($left, $right) {
            return strcmp($left['title'], $right['title']);
        });

        $upcoming = new ilPDSelectedItemsBlockGroup();
        $upcoming->setLabel($this->lng->txt('pd_upcoming'));
        $upcoming->setItems($groups['upcoming']);

        $ongoing = new ilPDSelectedItemsBlockGroup();
        $ongoing->setLabel($this->lng->txt('pd_ongoing'));
        $ongoing->setItems($groups['ongoing']);

        $ended = new ilPDSelectedItemsBlockGroup();
        $ended->setLabel($this->lng->txt('pd_ended'));
        $ended->setItems($groups['ended']);

        $not_dated = new ilPDSelectedItemsBlockGroup();
        $not_dated->setLabel($this->lng->txt('pd_not_date'));
        $not_dated->setItems($groups['not_dated']);

        return array_filter([
            $upcoming,
            $ongoing,
            $ended,
            $not_dated
        ], function (ilPDSelectedItemsBlockGroup $group) {
            return count($group->getItems()) > 0;
        });
    }

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    protected function groupItemsByLocation() : array
    {
        $grouped_items = array();

        $items = $this->provider->getItems();

        $parent_ref_ids = array_values(array_unique(array_map(function ($item) {
            return $item['parent_ref'];
        }, $items)));
        $this->object_cache->preloadReferenceCache($parent_ref_ids);

        foreach ($items as $key => $item) {
            if (!array_key_exists('grp_' . $item['parent_ref'], $grouped_items)) {
                $group = new ilPDSelectedItemsBlockGroup();
                /* The parent objects of items grouped by location do not need an image (per current concept), so
                   we do not determine images to reduced the runtime/memory */
                if ($this->isRootNode($item['parent_ref'])) {
                    $group->setLabel($this->getRepositoryTitle());
                } else {
                    $group->setLabel($this->object_cache->lookupTitle($this->object_cache->lookupObjId((int) $item['parent_ref'])));
                }
                $grouped_items['grp_' . $item['parent_ref']] = $group;
            }

            $grouped_items['grp_' . $item['parent_ref']]->pushItem($item);
        }

        return $grouped_items;
    }

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    protected function sortItemsByAlphabetInOneGroup() : array
    {
        $items = array_values($this->provider->getItems());

        usort($items, static function (array $first, array $second) : int {
            return strnatcmp(strtolower($first['title']), strtolower($second['title']));
        });

        $group = new ilPDSelectedItemsBlockGroup();
        array_map([$group, 'pushItem'], $items);

        return [$group];
    }
}
