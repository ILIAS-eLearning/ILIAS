<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/MainBlock/classes/class.ilPDSelectedItemsBlockMembershipsViewGUI.php';

/**
 * Class ilPDSelectedItemsBlockMembershipsViewGUI
 */
class ilPDSelectedItemsBlockMembershipsViewGUI extends ilPDSelectedItemsBlockViewGUI
{
	/**
	 * @return ilPDSelectedItemsBlockGroup[]
	 */
	protected function groupItemsByStartDate()
	{
		$items = $this->provider->getItems();

		if(0 == count($items))
		{
			return array();
		}

		$upcoming = new ilPDSelectedItemsBlockGroup();
		$upcoming->setLabel($this->lng->txt('pd_upcoming'));
	
		$ongoing = new ilPDSelectedItemsBlockGroup();
		$ongoing->setLabel($this->lng->txt('pd_ongoing'));
		
		$ended = new ilPDSelectedItemsBlockGroup();
		$ended->setLabel($this->lng->txt('pd_ended'));

		$not_dated = new ilPDSelectedItemsBlockGroup();
		$not_dated->setLabel($this->lng->txt('pd_not_date'));

		foreach($items as $key => $item)
		{
			if($item['start'] && $item['start'] && $item['start'] instanceof ilDate)
			{
				if($item['start']->get(IL_CAL_UNIX) > time())
				{
					$upcoming->pushItem($item);
				}
				else if($item['end']->get(IL_CAL_UNIX) > time())
				{
					$ongoing->pushItem($item);
				}
				else
				{
					$ended->pushItem($item);
				}
			}
			else
			{
				$groups['not_dated']['items'][$key] = $item;
				$not_dated->pushItem($item);
			}
		}

		/*uasort($groups['upcoming']['items'], function($left, $right) {
			if($left['start']->get(IL_CAL_UNIX) < $right['start']->get(IL_CAL_UNIX))
			{
				return -1;
			}
			else if($left['start']->get(IL_CAL_UNIX) > $right['start']->get(IL_CAL_UNIX))
			{
				return 1;
			}

			return strcmp($left['title'], $right['title']);
		});

		uasort($groups['ongoing']['items'], function($left, $right) {
			if($left['start']->get(IL_CAL_UNIX) < $right['start']->get(IL_CAL_UNIX))
			{
				return 1;
			}
			else if($left['start']->get(IL_CAL_UNIX) > $right['start']->get(IL_CAL_UNIX))
			{
				return -1;
			}

			return strcmp($left['title'], $right['title']);
		});

		uasort($groups['ended']['items'], function($left, $right) {
			if($left['start']->get(IL_CAL_UNIX) < $right['start']->get(IL_CAL_UNIX))
			{
				return 1;
			}
			else if($left['start']->get(IL_CAL_UNIX) > $right['start']->get(IL_CAL_UNIX))
			{
				return -1;
			}

			return strcmp($left['title'], $right['title']);
		});

		uasort($groups['not_dated']['items'], function($left, $right) {
			return strcmp($left['title'], $right['title']);
		});*/

		// @todo: Sort, Preload (for all modes)

		return array_filter([
			$upcoming,
			$ongoing,
			$ended,
			$not_dated
		], function(ilPDSelectedItemsBlockGroup $group) {
			return count($group->getItems()) > 0;
		});
	}

	/**
	 * @inheritdoc
	 */
	public function getItemsGroups()
	{
		if($this->viewSettings->isSortedByLocation())
		{
			return $this->groupItemsByLocation();
		}
		else if($this->viewSettings->isSortedByStartDate())
		{
			return $this->groupItemsByStartDate();
		}

		return $this->groupItemsByType();
	}

	/**
	 * @inheritdoc
	 */
	public function getScreenId()
	{
		return 'crs_grp';
	}

	/**
	 * @inheritdoc
	 */
	public function getTitleLanguageVariable()
	{
		return 'pd_my_memberships';
	}

	/**
	 * @inheritdoc
	 */
	public function supportsSelectAll()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getIntroductionHtml()
	{
		$tpl = new ilTemplate('tpl.pd_my_memberships_intro.html', true, true, 'Services/PersonalDesktop');
		$tpl->setVariable('IMG_PD_LARGE', ilObject::_getIcon('', 'big', 'pd'));
		$tpl->setVariable('TXT_WELCOME', $this->lng->txt('pd_my_memberships_intro'));
		$tpl->setVariable('TXT_INTRO_1', $this->lng->txt('pd_my_memberships_intro2'));

		return $tpl->get();
	}
}