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
	abstract public function getItemsGroups();

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
	 * @param array $items
	 */
	public function preloadItems(array $items)
	{
		require_once 'Services/Object/classes/class.ilObjectListGUIPreloader.php';
		$listPreloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP);
		foreach($items as $item)
		{
			$listPreloader->addItem($item['obj_id'], $item['type'], $item['ref_id']);
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