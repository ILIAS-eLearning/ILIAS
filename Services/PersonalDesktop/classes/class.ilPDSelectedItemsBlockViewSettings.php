<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/interfaces/interface.ilPDConstants.php';

/**
 * Class ilPDSelectedItemsBlockViewSettings
 */
class ilPDSelectedItemsBlockViewSettings implements ilPDConstants
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
	 * @param $view
	 */
	public function __construct($view = self::VIEW_SELECTED_ITEMS)
	{
		global $ilSetting;

		$this->settings = $ilSetting;

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
	public function isSelectedItemViewActive()
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
	 * @return boolean
	 */
	protected function allViewsEnabled()
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
		return (int)$this->settings->get('personal_items_default_view', $this->getSelectedItemsView());
	}

	/**
	 * @param $view int
	 */
	public function storeDefaultView($view)
	{
		assert('in_array($view, self::$availableViews');
		$this->settings->set('personal_items_default_view', $view);
	}

	/**
	 * 
	 */
	public function parse()
	{
		$this->validViews = self::$availableViews;

		foreach(array_filter([
			$this->getMembershipsView()   => !$this->enabledMemberships(),
			$this->getSelectedItemsView() => !$this->enabledSelectedItems()
		]) as $viewId => $status)
		{
			$key = array_search($viewId, $this->validViews);
			if($key !== false)
			{
				unset($this->validViews[$key]);
			}
		}

		if(count($this->validViews) == 1)
		{
			$this->storeDefaultView($this->getSelectedItemsView());
			$this->validViews[] = $this->getSelectedItemsView();
		}

		if(!$this->isValidView($this->getCurrentView()))
		{
			$this->currentView = $this->getDefaultView();
		}
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