<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/ItemsBlock/interfaces/interface.ilPDSelectedItemsBlockConstants.php';

/**
 * Class ilPDSelectedItemsBlockViewSettings
 */
class ilPDSelectedItemsBlockViewSettings implements ilPDSelectedItemsBlockConstants
{
    /**
     * @var array
     */
    protected static $availableViews = array(
        self::VIEW_SELECTED_ITEMS,
        self::VIEW_MY_MEMBERSHIPS,
        self::VIEW_MY_STUDYPROGRAMME
    );

    /**
     * @var array
     */
    protected static $availableSortOptions = array(
        self::SORT_BY_LOCATION,
        self::SORT_BY_TYPE,
        self::SORT_BY_START_DATE
    );

    /**
     * @var array
     */
    protected static $availableSortOptionsByView = array(
        self::VIEW_SELECTED_ITEMS => array(
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE
        ),
        self::VIEW_MY_MEMBERSHIPS => array(
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_START_DATE
        ),
        self::VIEW_MY_STUDYPROGRAMME => array(
        )
    );

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilObjUser
     */
    protected $actor;

    /**
     * @var array
     */
    protected $validViews = array();

    /**
     * @var int
     */
    protected $currentView = self::VIEW_SELECTED_ITEMS;

    /**
     * @var int
     */
    protected $currentSortOption = self::SORT_BY_LOCATION;

    /**
     * ilPDSelectedItemsBlockViewSettings constructor.
     * @param ilObjUser $actor
     * @param int $view
     */
    public function __construct($actor, $view = self::VIEW_SELECTED_ITEMS)
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $this->settings = $ilSetting;

        $this->actor = $actor;
        $this->currentView = $view;
    }

    /**
     * @return int
     */
    public function getMembershipsView()
    {
        return self::VIEW_MY_MEMBERSHIPS;
    }

    /**
     * @return int
     */
    public function getSelectedItemsView()
    {
        return self::VIEW_SELECTED_ITEMS;
    }

    /**
     * @return int
     */
    public function getStudyProgrammeView()
    {
        return self::VIEW_MY_STUDYPROGRAMME;
    }

    /**
     * @return boolean
     */
    public function isMembershipsViewActive()
    {
        return $this->currentView == $this->getMembershipsView();
    }

    /**
     * @return boolean
     */
    public function isSelectedItemsViewActive()
    {
        return $this->currentView == $this->getSelectedItemsView();
    }

    /**
     * @return boolean
     */
    public function isStudyProgrammeViewActive()
    {
        return $this->currentView == $this->getStudyProgrammeView();
    }

    /**
     * @return string
     */
    public function getSortByStartDateMode()
    {
        return self::SORT_BY_START_DATE;
    }

    /**
     * @return string
     */
    public function getSortByLocationMode()
    {
        return self::SORT_BY_LOCATION;
    }

    /**
     * @return string
     */
    public function getSortByTypeMode()
    {
        return self::SORT_BY_TYPE;
    }

    /**
     * @return string
     */
    public function getDefaultSortType()
    {
        return $this->settings->get('my_memberships_def_sort', $this->getSortByLocationMode());
    }

    /**
     * @return boolean
     */
    public function isSortedByType()
    {
        return $this->currentSortOption == $this->getSortByTypeMode();
    }

    /**
     * @return boolean
     */
    public function isSortedByLocation()
    {
        return $this->currentSortOption == $this->getSortByLocationMode();
    }

    /**
     * @return boolean
     */
    public function isSortedByStartDate()
    {
        return $this->currentSortOption == $this->getSortByStartDateMode();
    }

    /**
     * @param string $type
     */
    public function storeDefaultSortType($type)
    {
        assert(in_array($type, self::$availableSortOptions));
        $this->settings->set('my_memberships_def_sort', $type);
    }

    /**
     * @return boolean
     */
    public function enabledMemberships()
    {
        return $this->settings->get('disable_my_memberships', 0) == 0;
    }

    /**
     * @return boolean
     */
    public function enabledSelectedItems()
    {
        return $this->settings->get('disable_my_offers', 0) == 0;
    }

    /**
     * @param $status boolean
     */
    public function enableMemberships($status)
    {
        $this->settings->set('disable_my_memberships', (int) !$status);
    }

    /**
     * @param $status boolean
     */
    public function enableSelectedItems($status)
    {
        $this->settings->set('disable_my_offers', (int) !$status);
    }

    /**
     * @return boolean
     */
    public function allViewsEnabled()
    {
        return $this->enabledMemberships() && $this->enabledSelectedItems();
    }

    /**
     * @return boolean
     */
    protected function allViewsDisabled()
    {
        return !$this->enabledMemberships() && !$this->enabledSelectedItems();
    }

    /**
     * @return int
     */
    public function getDefaultView()
    {
        return (int) $this->settings->get('personal_items_default_view', $this->getSelectedItemsView());
    }

    /**
     * @param $view int
     */
    public function storeDefaultView($view)
    {
        assert(in_array($view, self::$availableViews));
        $this->settings->set('personal_items_default_view', $view);
    }

    /**
     *
     */
    public function parse()
    {
        $this->validViews = self::$availableViews;

        foreach (array_filter([
            $this->getMembershipsView() => !$this->enabledMemberships(),
            $this->getSelectedItemsView() => !$this->enabledSelectedItems()
        ]) as $viewId => $status) {
            $key = array_search($viewId, $this->validViews);
            if ($key !== false) {
                unset($this->validViews[$key]);
            }
        }

        if (count($this->validViews) == 1) {
            $this->storeDefaultView($this->getSelectedItemsView());
            $this->validViews[] = $this->getSelectedItemsView();
        }

        if (!$this->isValidView($this->getCurrentView())) {
            $this->currentView = $this->getDefaultView();
        }

        $this->currentSortOption = $this->actor->getPref('pd_order_items');
        if (!in_array($this->currentSortOption, self::$availableSortOptionsByView[$this->currentView])) {
            if ($this->isSelectedItemsViewActive()) {
                $this->currentSortOption = self::SORT_BY_LOCATION;
            } elseif ($this->isStudyProgrammeViewActive()) {
                $this->currentSortOption = $this->getDefaultSortType();
            }
        }
    }

    /**
     * @return ilObjUser
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * @return int
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    /**
     * @return int
     */
    public function getCurrentSortOption()
    {
        return $this->currentSortOption;
    }

    /**
     * @param string $view
     * @return boolean
     */
    public function isValidView($view)
    {
        return in_array($view, $this->validViews);
    }
}
