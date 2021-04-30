<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPDSelectedItemsBlockViewSettings
 */
class ilPDSelectedItemsBlockViewSettings implements ilPDSelectedItemsBlockConstants
{
    /**
     * @var int[]
     */
    protected static $availableViews = [
        self::VIEW_SELECTED_ITEMS,
        self::VIEW_MY_MEMBERSHIPS,
        self::VIEW_MY_STUDYPROGRAMME
    ];

    /**
     * @var string[]
     */
    protected static $availableSortOptions = [
        self::SORT_BY_LOCATION,
        self::SORT_BY_TYPE,
        self::SORT_BY_START_DATE
    ];

    /**
     * @var string[]
     */
    protected static $availablePresentations = [
        self::PRESENTATION_LIST,
        self::PRESENTATION_TILE
    ];

    /**
     * @var array[]
     */
    protected static $availableSortOptionsByView = [
        self::VIEW_SELECTED_ITEMS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_START_DATE
        ],
        self::VIEW_MY_STUDYPROGRAMME => []
    ];

    /**
     * @var array[]
     */
    protected static $availablePresentationsByView = [
        self::VIEW_SELECTED_ITEMS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_STUDYPROGRAMME => []
    ];

    /** @var ilSetting */
    protected $settings;

    /** @var ilObjUser */
    protected $actor;

    /** @var int[] */
    protected $validViews = [];

    /** @var int */
    protected $currentView = self::VIEW_SELECTED_ITEMS;

    /** @var int */
    protected $currentSortOption = self::SORT_BY_LOCATION;

    /** @var string */
    protected $currentPresentationOption = self::PRESENTATION_LIST;

    /**
     * @var \ILIAS\Dashboard\Access\DashboardAccess
     */
    protected $access;

    /**
     * ilPDSelectedItemsBlockViewSettings constructor.
     * @param ilObjUser $actor
     * @param int $view
     */
    public function __construct(ilObjUser $actor, int $view = self::VIEW_SELECTED_ITEMS)
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $this->settings = $ilSetting;

        $this->actor = $actor;
        $this->currentView = $view;
        include_once("./Services/Dashboard/Access/class.DashboardAccess.php");
        $this->access = new \ILIAS\Dashboard\Access\DashboardAccess();
    }

    /**
     * @return int
     */
    public function getMembershipsView() : int
    {
        return self::VIEW_MY_MEMBERSHIPS;
    }

    /**
     * @return int
     */
    public function getSelectedItemsView() : int
    {
        return self::VIEW_SELECTED_ITEMS;
    }

    /**
     * @return int
     */
    public function getStudyProgrammeView() : int
    {
        return self::VIEW_MY_STUDYPROGRAMME;
    }

    /**
     * @return string
     */
    public function getListPresentationMode() : string
    {
        return self::PRESENTATION_LIST;
    }

    /**
     * @return int
     */
    public function getTilePresentationMode() : string
    {
        return self::PRESENTATION_TILE;
    }

    /**
     * @return bool
     */
    public function isMembershipsViewActive() : bool
    {
        return $this->currentView === $this->getMembershipsView();
    }

    /**
     * @return bool
     */
    public function isSelectedItemsViewActive() : bool
    {
        return $this->currentView === $this->getSelectedItemsView();
    }

    /**
     * @return bool
     */
    public function isStudyProgrammeViewActive() : bool
    {
        return $this->currentView === $this->getStudyProgrammeView();
    }

    /**
     * @return string
     */
    public function getSortByStartDateMode() : string
    {
        return self::SORT_BY_START_DATE;
    }

    /**
     * @return string
     */
    public function getSortByLocationMode() : string
    {
        return self::SORT_BY_LOCATION;
    }

    /**
     * @return string
     */
    public function getSortByTypeMode() : string
    {
        return self::SORT_BY_TYPE;
    }

    /**
     * Get available sort options by view
     *
     * @param int $view
     * @return array
     */
    public function getAvailableSortOptionsByView(int $view) : array
    {
        return self::$availableSortOptionsByView[$view];
    }

    /**
     * Get available presentations by view
     *
     * @param int $view
     * @return array
     */
    public function getAvailablePresentationsByView(int $view) : array
    {
        return self::$availablePresentationsByView[$view];
    }

    /**
     * @param int $view
     * @return string
     */
    public function getDefaultSortingByView(int $view) : string
    {
        switch ($view) {
            case $this->getSelectedItemsView():
                return $this->settings->get('selected_items_def_sort', $this->getSortByLocationMode());

            default:
                return $this->settings->get('my_memberships_def_sort', $this->getSortByLocationMode());
        }
    }


    /**
     * @return bool
     */
    public function isSortedByType() : bool
    {
        return $this->currentSortOption === $this->getSortByTypeMode();
    }

    /**
     * @return bool
     */
    public function isSortedByLocation() : bool
    {
        return $this->currentSortOption === $this->getSortByLocationMode();
    }

    /**
     * @return bool
     */
    public function isSortedByStartDate() : bool
    {
        return $this->currentSortOption === $this->getSortByStartDateMode();
    }

    /**
     * @return bool
     */
    public function isTilePresentation() : bool
    {
        return $this->currentPresentationOption === $this->getTilePresentationMode();
    }

    /**
     * @return bool
     */
    public function isListPresentation() : bool
    {
        return $this->currentPresentationOption === $this->getListPresentationMode();
    }

    /**
     * @param int $view
     * @param string $type
     * @param array $active
     */
    public function storeViewSorting(int $view, string $type, array $active)
    {
        if (!in_array($type, $active)) {
            $active[] = $type;
        }

        assert(in_array($type, $this->getAvailableSortOptionsByView($view)));

        switch ($view) {
            case $this->getSelectedItemsView():
                $this->settings->set('selected_items_def_sort', $type);
                break;

            default:
                $this->settings->set('my_memberships_def_sort', $type);
                break;
        }

        $this->settings->set('pd_active_sort_view_' . $view, serialize($active));
    }

    /**
     * Get active sort options by view
     *
     * @param int $view
     * @return array
     */
    public function getActiveSortingsByView(int $view)
    {
        $val = $this->settings->get('pd_active_sort_view_' . $view);
        return ($val == "")
            ? []
            : unserialize($val);
    }

    /**
     * Store default presentation
     *
     * @param int $view
     * @param string $default
     * @param array $active
     */
    public function storeViewPresentation(int $view, string $default, array $active)
    {
        if (!in_array($default, $active)) {
            $active[] = $default;
        }
        $this->settings->set('pd_def_pres_view_' . $view, $default);
        $this->settings->set('pd_active_pres_view_' . $view, serialize($active));
    }

    /**
     * Get default presentation
     *
     * @param int $view
     * @return string
     */
    public function getDefaultPresentationByView(int $view) : string
    {
        return $this->settings->get('pd_def_pres_view_' . $view, "list");
    }

    /**
     * Get active presentations by view
     *
     * @param int $view
     * @return array
     */
    public function getActivePresentationsByView(int $view) : array
    {
        $val = $this->settings->get('pd_active_pres_view_' . $view, '');

        return ('' === $val)
            ? []
            : unserialize($val);
    }

    /**
     * @return bool
     */
    public function enabledMemberships() : bool
    {
        return $this->settings->get('disable_my_memberships', 0) == 0;
    }

    /**
     * @return bool
     */
    public function enabledSelectedItems() : bool
    {
        return $this->settings->get('disable_my_offers', 0) == 0;
    }

    /**
     * @param $status bool
     */
    public function enableMemberships(bool $status)
    {
        $this->settings->set('disable_my_memberships', (int) !$status);
    }

    /**
     * @param $status bool
     */
    public function enableSelectedItems(bool $status)
    {
        $this->settings->set('disable_my_offers', (int) !$status);
    }

    /**
     * @return bool
     */
    public function allViewsEnabled() : bool
    {
        return $this->enabledMemberships() && $this->enabledSelectedItems();
    }

    /**
     * @return bool
     */
    protected function allViewsDisabled() : bool
    {
        return !$this->enabledMemberships() && !$this->enabledSelectedItems();
    }

    /**
     * @return int
     */
    public function getDefaultView() : int
    {
        return (int) $this->settings->get('personal_items_default_view', $this->getSelectedItemsView());
    }

    /**
     * @param $view int
     */
    public function storeDefaultView(int $view)
    {
        $this->settings->set('personal_items_default_view', $view);
    }

    /**
     *
     */
    public function parse()
    {
        $this->validViews = self::$availableViews;

        /*
        foreach (array_filter([
            $this->getMembershipsView() => !$this->enabledMemberships(),
            $this->getSelectedItemsView() => !$this->enabledSelectedItems()
        ]) as $viewId => $status) {
            $key = array_search($viewId, $this->validViews);
            if ($key !== false) {
                unset($this->validViews[$key]);
            }
        }


        if (1 === count($this->validViews)) {
            $this->storeDefaultView($this->getSelectedItemsView());
            $this->validViews[] = $this->getSelectedItemsView();
        }

        if (!$this->isValidView($this->getCurrentView())) {
            $this->currentView = $this->getDefaultView();
        }*/

        $this->currentSortOption = $this->getEffectiveSortingMode();
        $this->currentPresentationOption = $this->getEffectivePresentationMode();
    }

    /**
     * @return string
     */
    public function getEffectivePresentationMode() : string
    {
        $mode = $this->actor->getPref('pd_view_pres_' . $this->currentView);

        if (!in_array($mode, $this->getSelectablePresentationModes())) {
            $mode = $this->getDefaultPresentationByView($this->currentView);
        }

        return $mode;
    }


    /**
     * @return string
     */
    public function getEffectiveSortingMode() : string
    {
        $mode = $this->actor->getPref('pd_order_items_' . $this->currentView);

        if (!in_array($mode, $this->getSelectableSortingModes())) {
            $mode = $this->getDefaultSortingByView($this->currentView);
        }

        return $mode;
    }

    /**
     * @return string[]
     */
    public function getSelectableSortingModes() : array
    {
        return array_intersect(
            $this->getActiveSortingsByView($this->currentView),
            $this->getAvailableSortOptionsByView($this->currentView)
        );
    }

    /**
     * @return string[]
     */
    public function getSelectablePresentationModes() : array
    {
        if (!$this->access->canChangePresentation($this->actor->getId())) {
            return [$this->getDefaultSortingByView($this->currentView)];
        }
        return array_intersect(
            $this->getActivePresentationsByView($this->currentView),
            $this->getAvailablePresentationsByView($this->currentView)
        );
    }

    /**
     * @param string $presentationMode
     */
    public function storeActorPresentationMode(string $presentationMode)
    {
        if (in_array($presentationMode, $this->getSelectablePresentationModes())) {
            $this->actor->writePref(
                'pd_view_pres_' . $this->currentView,
                $presentationMode
            );
        }
    }

    /**
     * @param string $sortingMode
     */
    public function storeActorSortingMode(string $sortingMode)
    {
        if (in_array($sortingMode, $this->getSelectableSortingModes())) {
            $this->actor->writePref(
                'pd_order_items_' . $this->currentView,
                $sortingMode
            );
        }
    }

    /**
     * @return ilObjUser
     */
    public function getActor() : ilObjUser
    {
        return $this->actor;
    }

    /**
     * @return int
     */
    public function getCurrentView() : int
    {
        return $this->currentView;
    }

    /**
     * @return int
     */
    public function getCurrentSortOption() : int
    {
        return $this->currentSortOption;
    }

    /**
     * @param int $view
     * @return bool
     */
    public function isValidView(int $view) : bool
    {
        return in_array($view, $this->validViews);
    }
}
