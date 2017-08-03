<?php

/**
 * Class ilOrgUnitPositionTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionTableGUI extends ilTable2GUI {

	const POSITION_ID = "position_id";
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var array
	 */
	protected $columns = array(
		'title',
		'description',
		'authorities',
		'actions',
	);


	/**
	 * ilOrgUnitPositionTableGUI constructor.
	 *
	 * @param object $parent_obj
	 * @param string $parent_cmd
	 */
	public function __construct($parent_obj, $parent_cmd) {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$ilTabs = $DIC['ilTabs'];
		$lng = $DIC['lng'];
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->lng = $lng;
		$this->setPrefix('orgu_types_table');
		$this->setId('orgu_types_table');
		parent::__construct($parent_obj, $parent_cmd);
		$this->setRowTemplate('tpl.position_row.html', 'Modules/OrgUnit');
		$this->initColumns();
		$this->addColumn($this->lng->txt('action'));
		$this->buildData();
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
	}


	/**
	 * Pass data to row template
	 *
	 * @param array $set
	 */
	public function fillRow($set) {
		/**
		 * @var $obj ilOrgUnitPosition
		 */
		$obj = ilOrgUnitPosition::find($set["id"]);
		//		echo '<pre>' . print_r($set, 1) . '</pre>';
		$this->tpl->setVariable('TITLE', $obj->getTitle());
		$this->tpl->setVariable('DESCRIPTION', $obj->getDescription());
		$this->tpl->setVariable('AUTHORITIRS', $obj->get);

		$this->ctrl->setParameterByClass(ilOrgUnitPositionGUI::class, self::POSITION_ID, $set['id']);
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setListTitle($this->lng->txt('actions'));
		$selection->setId(self::POSITION_ID . $set['id']);
		$selection->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTargetByClass(ilOrgUnitPositionGUI::class, ilOrgUnitPositionGUI::CMD_EDIT));
		$selection->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTargetByClass(ilOrgUnitPositionGUI::class, ilOrgUnitPositionGUI::CMD_CONFIRM));
		$this->tpl->setVariable('ACTIONS', $selection->getHTML());
	}


	/**
	 * Add columns
	 */
	protected function initColumns() {
		foreach ($this->columns as $column) {
			$this->addColumn($this->lng->txt($column), $column);
		}
	}


	/**
	 * Build and set data for table.
	 */
	protected function buildData() {
		$this->setData(ilOrgUnitPosition::getArray());

		//		$types = ilOrgUnitType::getAllTypes();
		//		$data = array();
		//		/** @var $type ilOrgUnitType */
		//		foreach ($types as $type) {
		//			$row = array();
		//			$row['id'] = $type->getId();
		//			$row['title'] = $type->getTitle($type->getDefaultLang());
		//			$row['default_language'] = $type->getDefaultLang();
		//			$row['description'] = $type->getDescription($type->getDefaultLang());
		//			$row['icon'] = $type->getIcon();
		//			$data[] = $row;
		//		}
		//		$this->setData(array());
	}
}