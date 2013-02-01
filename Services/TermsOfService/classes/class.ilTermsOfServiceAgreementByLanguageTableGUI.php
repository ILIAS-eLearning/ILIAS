<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableGUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAgreementByLanguageTableGUI extends ilTermsOfServiceTableGUI
{
	/**
	 * @param ilObjectGUI $a_parent_obj
	 * @param string      $a_parent_cmd
	 */
	public function __construct(ilObjectGUI $a_parent_obj, $a_parent_cmd)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$this->ctrl = $ilCtrl;

		// Call this immediately in constructor
		$this->setId('tos_agreement_by_lng');

		$this->setDefaultOrderDirection('ASC');
		$this->setDefaultOrderField('language');
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(false);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt('tos_agreement_by_lng'));

		$this->addColumn($this->lng->txt('language'), 'language');
		$this->addColumn($this->lng->txt('tos_agreement'), 'agreement');
		$this->addColumn($this->lng->txt('tos_agreement_file'), 'agreement_file');
		$this->optionalColumns        = (array)$this->getSelectableColumns();
		$this->visibleOptionalColumns = (array)$this->getSelectedColumns();
		foreach($this->visibleOptionalColumns as $column)
		{
			$this->addColumn($this->optionalColumns[$column]['txt'], $column);
		}

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'applyAgreementByLanguageFilter'));

		$this->setRowTemplate('tpl.tos_agreement_by_lng_table_row.html', 'Services/TermsOfService');

		$this->setShowRowsSelector(true);

		$this->initFilter();
		$this->setFilterCommand('applyAgreementByLanguageFilter');
		$this->setResetCommand('resetAgreementByLanguageFilter');
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns()
	{
		$cols = array('agreement_file_mtime' => array('txt' => $this->lng->txt('tos_last_modified'), 'default' => true));

		return $cols;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function prepareData(array &$data)
	{
		foreach($data['items'] as &$row)
		{
			$row['language'] = $this->lng->txt('meta_l_' . $row['language']);
		}
	}

	/**
	 * @param array $row
	 * @return array
	 */
	protected function prepareRow(array &$row)
	{
		if(is_string($row['agreement_file']) && strlen($row['agreement_file']))
		{
			$action = new ilAdvancedSelectionListGUI();
			$action->setId('asl_content_' . md5($row['language']));
			$action->setAsynch(true);
			$this->ctrl->setParameter($this->getParentObject(), 'agreement_file', rawurlencode($row['agreement_file']));
			$action->setAsynchUrl($this->ctrl->getLinkTarget($this->getParentObject(), 'showAgreementTextAsynch', '', true, false));
			$this->ctrl->setParameter($this->getParentObject(), 'agreement_file', '');
			$row['action_show_agreement_text'] = $action->getHtml();
		}
		else
		{
			$row['missing_agreement_css_class'] = 'warning';
		}
	}

	/**
	 * Return an array of all static (always visible) columns.
	 * For each column key there has to be a variable name VAL_<COLUMN_KEY> in your defined row template.
	 * Example:
	 *     return array('title', 'checkbox');
	 *     There have to be two template variables: VAL_TITLE and VAL_CHECKBOX
	 * @return array
	 */
	protected function getStaticColumns()
	{
		return array('language', 'agreement', 'missing_agreement_css_class', 'agreement_file', 'action_show_agreement_text');
	}

	/**
	 * @param mixed $column
	 * @param array $row
	 * @return mixed
	 */
	protected function formatCellValue($column, array $row)
	{
		if($column == 'agreement_file')
		{
			if(!is_string($row[$column]) || !strlen($row[$column]))
			{
				return $this->lng->txt('tos_agreement_file_missing');
			}
		}
		else if($column == 'agreement')
		{
			if($row[$column])
			{
				return $this->lng->txt('tos_agreement_exists');
			}
			else
			{
				return $this->lng->txt('tos_agreement_missing');
			}
		}
		else if($column == 'agreement_file_mtime')
		{
			return ilDatePresentation::formatDate(new ilDateTime($row[$column], IL_CAL_UNIX));
		}

		return $row[$column];
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	public function numericOrdering($column)
	{
		if('agreement_file_mtime' == $column)
		{
			return true;
		}

		return false;
	}
}
