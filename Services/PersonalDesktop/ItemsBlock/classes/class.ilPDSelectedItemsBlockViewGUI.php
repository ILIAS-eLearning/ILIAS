<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockGroup.php';

/**
 * Class ilPDSelectedItemsBlockViewGUI
 */
abstract class ilPDSelectedItemsBlockViewGUI
{
    /**
     * @var ilPDSelectedItemsBlockViewSettings
     */
    protected $viewSettings;

    /**
     * @var ilPDSelectedItemsBlockProvider
     */
    protected $provider;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var $tree ilTree
     */
    protected $tree;
    
    /**
     * @var ilObjectDataCache
     */
    protected $object_cache;

    /**
     * @var ilRbacSystem
     */
    protected $accessHandler;

    /**
     * ilPDSelectedItemsBlockViewGUI constructor.
     * @param ilPDSelectedItemsBlockViewSettings $viewSettings
     * @param ilPDSelectedItemsBlockProvider                       $provider
     */
    final private function __construct(ilPDSelectedItemsBlockViewSettings $viewSettings, ilPDSelectedItemsBlockProvider $provider)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->object_cache = $DIC['ilObjDataCache'];
        $this->accessHandler = $DIC->rbac()->system();

        $this->viewSettings = $viewSettings;
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    abstract public function getScreenId();

    /**
     * @return string
     */
    abstract public function getTitle();

    /**
     * @return int
     */
    public function getMinimumDetailLevelForSection()
    {
        if ($this->viewSettings->isSortedByLocation()) {
            return 1;
        }

        return 3;
    }

    /**
     * @return boolean
     */
    abstract public function supportsSelectAll();

    /**
     * @return string
     */
    abstract public function getIntroductionHtml();

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    abstract public function getGroups();

    /**
     * @param int $refId
     * @return bool
     */
    public function mayRemoveItem($refId)
    {
        return true;
    }

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    public function getItemGroups()
    {
        $items_groups = $this->getGroups();

        $this->preloadItemGroups($items_groups);

        return $items_groups;
    }

    /**
     * @param ilPDSelectedItemsBlockViewSettings $viewSettings
     * @return self
     */
    public static function bySettings(ilPDSelectedItemsBlockViewSettings $viewSettings)
    {
        if ($viewSettings->isMembershipsViewActive()) {
            require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockMembershipsViewGUI.php';
            require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockMembershipsProvider.php';
            return new ilPDSelectedItemsBlockMembershipsViewGUI(
                $viewSettings,
                new ilPDSelectedItemsBlockMembershipsProvider($viewSettings->getActor())
            );
        }

        require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockSelectedItemsViewGUI.php';
        require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockSelectedItemsProvider.php';
        return new ilPDSelectedItemsBlockSelectedItemsViewGUI(
            $viewSettings,
            new ilPDSelectedItemsBlockSelectedItemsProvider($viewSettings->getActor())
        );
    }

    /**
     * @param int $refId
     * @return bool
     */
    protected function isRootNode($refId)
    {
        return $this->tree->getRootId() == $refId;
    }

    /**
     * @return string
     */
    protected function getRepositoryTitle()
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
        require_once 'Services/Object/classes/class.ilObjectListGUIPreloader.php';
        $listPreloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP);

        $obj_ids = [];
        foreach ($item_groups as $item_group) {
            foreach ($item_group->getItems() as $item) {
                $obj_ids[] = $item['obj_id'];
                $listPreloader->addItem($item['obj_id'], $item['type'], $item['ref_id']);
            }
        }

        $listPreloader->preload();
        ilAdvancedMDValues::preloadByObjIds($obj_ids);
    }

    /**
     * @return ilPDSelectedItemsBlockGroup[]
     */
    protected function groupItemsByType()
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
                include_once("./Services/Component/classes/class.ilPlugin.php");
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
    protected function groupItemsByStartDate()
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
            if ($item['start'] && $item['start'] && $item['start'] instanceof ilDate) {
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
    protected function groupItemsByLocation()
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
                    $group->setLabel($this->object_cache->lookupTitle($this->object_cache->lookupObjId($item['parent_ref'])));
                }
                $grouped_items['grp_' . $item['parent_ref']] = $group;
            }

            $grouped_items['grp_' . $item['parent_ref']]->pushItem($item);
        }

        return $grouped_items;
    }
}
