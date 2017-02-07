<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PersonalDesktop/MainBlock/classes/class.ilPDSelectedItemsBlockGroup.php';

/**
 * Class ilPDSelectedItemsBlockViewGUI
 */
abstract class ilPDSelectedItemsBlockViewGUI
{
	/**
	 * @var ilPDSelectedItemsBlockSelectedItemsBlockViewSettings
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
	 * ilPDSelectedItemsBlockViewGUI constructor.
	 * @param ilPDSelectedItemsBlockSelectedItemsBlockViewSettings $viewSettings
	 * @param ilPDSelectedItemsBlockProvider                       $provider
	 */
	final private function __construct(ilPDSelectedItemsBlockSelectedItemsBlockViewSettings $viewSettings, ilPDSelectedItemsBlockProvider $provider)
	{
		global $DIC;

		$this->lng  = $DIC->language();
		$this->tree = $DIC->repositoryTree();

		$this->viewSettings = $viewSettings;
		$this->provider     = $provider;
	}

	/**
	 * @return string
	 */
	abstract public function getScreenId();

	/**
	 * @return string
	 */
	abstract public function getTitleLanguageVariable();

	/**
	 * @return boolean
	 */
	abstract public function supportsSelectAll();

	/**
	 * @return string
	 */
	abstract function getIntroductionHtml();

	/**
	 * @return ilPDSelectedItemsBlockGroup[]
	 */
	abstract function getGroups();

	/**
	 * @return ilPDSelectedItemsBlockGroup[]
	 */
	public function getItemsGroups()
	{
		$items_groups = $this->getGroups();

		$this->preloadItemGroups($items_groups);

		return $items_groups;
	}

	/**
	 * @param ilPDSelectedItemsBlockSelectedItemsBlockViewSettings $viewSettings
	 * @return self
	 */
	public static function bySettings(ilPDSelectedItemsBlockSelectedItemsBlockViewSettings $viewSettings)
	{
		if($viewSettings->isMembershipsViewActive())
		{
			require_once 'Services/PersonalDesktop/MainBlock/classes/class.ilPDSelectedItemsBlockMembershipsViewGUI.php';
			require_once 'Services/PersonalDesktop/MainBlock/classes/class.ilPDSelectedItemsBlockMembershipsProvider.php';
			return new ilPDSelectedItemsBlockMembershipsViewGUI(
				$viewSettings, new ilPDSelectedItemsBlockMembershipsProvider($viewSettings->getActor())
			);
		}

		require_once 'Services/PersonalDesktop/MainBlock/classes/class.ilPDSelectedItemsBlockSelectedItemsViewGUI.php';
		require_once 'Services/PersonalDesktop/MainBlock/classes/class.ilPDSelectedItemsBlockSelectedItemsProvider.php';
		return new ilPDSelectedItemsBlockSelectedItemsViewGUI(
			$viewSettings, new ilPDSelectedItemsBlockSelectedItemsProvider($viewSettings->getActor())
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
		$nd    = $this->tree->getNodeData($this->tree->getRootId());
		$title = $nd['title'];

		if($title == 'ILIAS')
		{
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

		foreach($item_groups as $item_group)
		{
			foreach($item_group->getItems() as $item)
			{
				$listPreloader->addItem($item['obj_id'], $item['type'], $item['ref_id']);
			}
		}

		$listPreloader->preload();
	}

	/**
	 * @return ilPDSelectedItemsBlockGroup[]
	 */
	protected function groupItemsByType()
	{
		global $DIC;

		$object_types_by_container = $DIC['objDefinition']->getGroupedRepositoryObjectTypes(array('cat', 'crs', 'grp', 'fold'));

		$grouped_items = array();

		foreach($object_types_by_container as $container_object_type => $container_data)
		{
			$group = new ilPDSelectedItemsBlockGroup();
			$group->setLabel($this->lng->txt('objs_'. $container_object_type)); // @todo: Determine icon
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
	 * @return ilPDSelectedItemsBlockGroup[]
	 */
	protected function groupItemsByLocation()
	{
		$grouped_items = array();

		foreach($this->provider->getItems() as $key => $item)
		{
			if(!array_key_exists('grp_' . $item['parent_ref'], $grouped_items))
			{
				$group = new ilPDSelectedItemsBlockGroup();
				$group->setLabel($item['parent_ref']); // @todo: Determine parent title
				$grouped_items['grp_' . $item['parent_ref']] = $group;
			}

			$grouped_items['grp_' . $item['parent_ref']]->pushItem($item);
		}

		return $grouped_items;
	}
}