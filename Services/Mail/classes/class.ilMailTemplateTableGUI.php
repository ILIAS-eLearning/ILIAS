<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Mail/classes/class.ilMailTemplateService.php';

/**
 * Class ilMailTemplateTableGUI
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailTemplateTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilMailTemplateContext[]
	 */
	protected $contexts = array();

	/**
	 * @var bool
	 */
	protected $readOnly = false;

	/**
	 * @param        $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param bool   $readOnly
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $readOnly = false)
	{
		global $DIC;

		$this->readOnly = $readOnly;

		$this->ctrl = $DIC->ctrl();

		$this->setId('mail_man_tpl');
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt('mail_templates'));
		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('title');

		if (!$this->readOnly) {
			$this->addColumn('', '', '1%', true);
			$this->setSelectAllCheckbox('tpl_id');
			$this->addMultiCommand('confirmDeleteTemplate', $this->lng->txt('delete'));
		}

		$this->addColumn($this->lng->txt('title'), 'title', '30%');
		$this->addColumn($this->lng->txt('mail_template_context'), 'context', '20%');
		/*$this->addColumn($this->lng->txt('language'), 'lang', '20%');*/
		$this->addColumn($this->lng->txt('action'), '', '10%');

		$this->setRowTemplate('tpl.mail_template_row.html', 'Services/Mail');
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->contexts = ilMailTemplateService::getTemplateContexts();
	}

	/**
	 * @param  string $column
	 * @param array   $row
	 * @return string
	 */
	protected function formatCellValue($column, array $row)
	{
		if($column == 'tpl_id')
		{
			return ilUtil::formCheckbox(false, 'tpl_id[]', $row[$column]);
		}
		else if($column == 'lang')
		{
			return $this->lng->txt('meta_l_' . $row[$column]);
		}
		else if($column == 'context')
		{
			if(isset($this->contexts[$row[$column]]))
			{
				return $this->contexts[$row[$column]]->getTitle();
			}
			else
			{
				return $this->lng->txt('mail_template_orphaned_context');
			}
		}

		return $row[$column];
	}

	/**
	 * @param array $row
	 */
	protected function fillRow($row)
	{
		foreach($row as $column => $value)
		{
			if ($column == 'tpl_id' && $this->readOnly) {
				continue;
			}

			$value = $this->formatCellValue($column, $row);
			$this->tpl->setVariable('VAL_' . strtoupper($column), $value);
		}

		$this->ctrl->setParameter($this->getParentObject(), 'tpl_id', $row['tpl_id']);
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setListTitle($this->lng->txt('actions'));
		$actions->setId('act_' . $row['tpl_id']);
		if(count($this->contexts))
		{
			if (!$this->readOnly) {
				$actions->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->parent_obj, 'showEditTemplateForm'));
			} else {
				$actions->addItem($this->lng->txt('view'), '', $this->ctrl->getLinkTarget($this->parent_obj, 'showEditTemplateForm'));
			}
		}
		if (!$this->readOnly) {
			$actions->addItem($this->lng->txt('delete'), '',
				$this->ctrl->getLinkTarget($this->parent_obj, 'confirmDeleteTemplate'));
		}
		$this->tpl->setVariable('VAL_ACTION', $actions->getHTML());
		$this->ctrl->clearParameters($this->getParentObject());
	}
}
