<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilUnitTableGUI
 */
class ilUnitTableGUI extends ilTable2GUI
{
	/**
	 * @var int
	 */
	private $position = 1;

	/**
	 * @param ilUnitConfigurationGUI         $controller
	 * @param string                         $default_cmd
	 * @param assFormulaQuestionUnitCategory $category
	 */
	public function __construct(ilUnitConfigurationGUI $controller, $default_cmd, assFormulaQuestionUnitCategory $category)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 */
		global $ilCtrl, $lng;

		$this->setId('units_' . $controller->getUniqueId());

		parent::__construct($controller, $default_cmd);

		if($this->getParentObject()->isCRUDContext())
		{
			$this->addColumn('', '', '1%', true);
			$this->setSelectAllCheckbox('unit_ids[]');
			$this->addMultiCommand('confirmDeleteUnits', $this->lng->txt('delete'));
			$this->addCommandButton('saveOrder', $this->lng->txt('un_save_order'));
		}

		$this->setTitle(sprintf($this->lng->txt('un_units_of_category_x'), $category->getDisplayString()));
		
		$this->addColumn($this->lng->txt('un_sequence'), '');
		$this->addColumn($this->lng->txt('unit'), '');
		$this->addColumn($this->lng->txt('baseunit'), '');
		$this->addColumn($this->lng->txt('factor'), '');
		$this->addColumn('', '', '1%', true);

		// Show all units because of sorting
		$this->setLimit(PHP_INT_MAX);

		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), 'showUnitsOfCategory'));

		$this->setDefaultOrderDirection('sequence');
		$this->setDefaultOrderDirection('ASC');

		$this->setRowTemplate('tpl.unit_row_html', 'Modules/TestQuestionPool');
	}

	/**
	 * @param array $row
	 */
	public function fillRow(array $row)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		if($this->getParentObject()->isCRUDContext())
		{
			$row['chb'] = ilUtil::formCheckbox(false, 'unit_ids[]', $row['unit_id']);

			$sequence = new ilNumberInputGUI('', 'sequence[' . $row['unit_id'] . ']');
			$sequence->setValue($this->position++ * 10);
			$sequence->setMinValue(0);
			$sequence->setSize(3);
			$row['sequence'] = $sequence->render();

			$action = new ilAdvancedSelectionListGUI();
			$action->setId('asl_content_' . $row['unit_id']);
			$action->setAsynch(false);
			$action->setListTitle($this->lng->txt('actions'));
			$ilCtrl->setParameter($this->getParentObject(), 'unit_id', $row['unit_id']);
			$action->addItem($this->lng->txt('edit'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'showUnitModificationForm'));
			$action->addItem($this->lng->txt('delete'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'confirmDeleteUnit'));
			$ilCtrl->setParameter($this->getParentObject(), 'unit_id', '');
			$row['actions'] = $action->getHtml();
		}
		if($row['unit_id'] == $row['baseunit_id'])
		{
			$row['baseunit'] = '';
		}
		parent::fillRow($row);
	}
}