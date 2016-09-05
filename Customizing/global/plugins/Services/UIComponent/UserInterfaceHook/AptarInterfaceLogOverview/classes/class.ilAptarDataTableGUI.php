<?php
require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAptarDataTableGUI
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilAptarDataTableGUI extends ilTable2GUI
{
	/** @var $tpl ilTemplate */
	protected $tpl;

	/** @var $lng ilLanguage */
	protected $lng;

	/** @var $tabs ilTabsGUI */
	protected $tabs;

	/** @var $ctrl ilCtrl */
	protected $ctrl;

	protected $a_parent_obj;

	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 * @var $tpl ilTemplate
		 */
		global $ilTabs, $ilCtrl, $lng, $tpl;

		$this->ctrl	= $ilCtrl;
		$this->tpl	= $tpl;
		$this->lng	= $lng;
		$this->tabs	= $ilTabs;
		$this->a_parent_obj = $a_parent_obj;

		$this->setId('logfiles');
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		
		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('name');

		$this->setTitle($this->a_parent_obj->getPluginObject()->txt('datatable'));
		$this->setRowTemplate('tpl.export_table_row.html', 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AptarInterfaceLogOverview');
		$this->initColumns();

		$this->setSelectAllCheckbox('log_id');
		$this->addMultiCommand('confirmDeleteLogfile', $this->lng->txt('delete'));

		$this->setShowRowsSelector(true);
	}

	/**
	 *
	 */
	protected function initColumns()
	{
		$this->addColumn('', '', '2%');
		$this->addColumn($this->a_parent_obj->getPluginObject()->txt('file'), 'file_path', '23%');;
		$this->addColumn($this->a_parent_obj->getPluginObject()->txt('errors'), 'errors', '5%');
		$this->addColumn($this->a_parent_obj->getPluginObject()->txt('warnings'), 'warnings', '5%');
		$this->addColumn($this->a_parent_obj->getPluginObject()->txt('data_sets'), 'data_sets', '5%');
		$this->addColumn($this->a_parent_obj->getPluginObject()->txt('duration'), 'duration', '10%');
		$this->addColumn($this->a_parent_obj->getPluginObject()->txt('file_size'), 'file_size', '10%');
	}

	/**
	 * @param array $row
	 */
	protected function fillRow(array $row)
	{

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt('actions'));
		$current_selection_list->setId('act_' . $row['log_id']);

		$this->tpl->setVariable('LOG_ID', 		ilUtil::formCheckbox(0, 'log_id[]', $row['log_id']));
		$this->tpl->setVariable('FILE_LINK',	$this->ctrl->getLinkTargetByClass('ilAptarInterfaceLogOverviewConfigGUI', "downloadLogFile&download_id=" . $row['log_id']));
		$this->tpl->setVariable('FILE_NAME',	basename($row['file_path']));
		$this->tpl->setVariable('ERRORS',		$row['errors']);
		$this->tpl->setVariable('WARNINGS',		$row['warnings']);
		$this->tpl->setVariable('DATA_SETS',	$row['data_sets']);
		$this->tpl->setVariable('DURATION',		ilFormat::_secondsToString($row['duration']));
		$this->tpl->setVariable('FILE_SIZE',	$row['file_size']);
		$this->ctrl->setParameter($this->a_parent_obj, 'log_id', $row['log_id']);
	}

}