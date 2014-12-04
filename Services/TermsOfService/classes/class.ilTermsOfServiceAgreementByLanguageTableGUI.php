<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableGUI.php';
require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';
require_once 'Services/UIComponent/Modal/classes/class.ilModalGUI.php';

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
		$this->addColumn($this->lng->txt('tos_agreement_document'), 'agreement_document');
		$this->optionalColumns        = (array)$this->getSelectableColumns();
		$this->visibleOptionalColumns = (array)$this->getSelectedColumns();
		foreach($this->visibleOptionalColumns as $column)
		{
			$this->addColumn($this->optionalColumns[$column]['txt'], $column);
		}

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'applyAgreementByLanguageFilter'));

		$this->setRowTemplate('tpl.tos_agreement_by_lng_table_row.html', 'Services/TermsOfService');

		$this->setShowRowsSelector(true);

		require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		require_once 'Services/YUI/classes/class.ilYuiUtil.php';
		iljQueryUtil::initjQuery();
		ilYuiUtil::initPanel();
		ilYuiUtil::initOverlay();

		$this->initFilter();
		$this->setFilterCommand('applyAgreementByLanguageFilter');
		$this->setResetCommand('resetAgreementByLanguageFilter');
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns()
	{
		$cols = array('agreement_document_modification_ts' => array('txt' => $this->lng->txt('tos_last_modified'), 'default' => true));

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
		if(is_string($row['agreement_document']) && strlen($row['agreement_document']))
		{
			$unique_id = md5($row['language']);

			$this->ctrl->setParameter($this->getParentObject(), 'agreement_document', rawurlencode($row['agreement_document']));
			$row['content_link'] = $this->ctrl->getLinkTarget($this->getParentObject(), 'getAgreementTextByFilenameAsynch', '', true, false);
			$this->ctrl->setParameter($this->getParentObject(), 'agreement_document', '');
			$row['img_down'] = ilGlyphGUI::get(ilGlyphGUI::SEARCH);
			$row['id']       = $unique_id;

			$modal = ilModalGUI::getInstance();
			$modal->setHeading($this->lng->txt('tos_agreement_document'));
			$modal->setId('tos_' . $unique_id);
			$modal->setBody('');
			$row['modal'] = $modal->getHTML();
		}
		else
		{
			$row['missing_agreement_css_class'] = 'warning';
		}
	}

	/**
	 * @return array
	 */
	protected function getStaticData()
	{
		return array('modal', 'id', 'language', 'agreement', 'missing_agreement_css_class', 'agreement_document', 'content_link', 'img_down', 'language_key');
	}

	/**
	 * @param mixed $column
	 * @param array $row
	 * @return mixed
	 */
	protected function formatCellValue($column, array $row)
	{
		if($column == 'agreement_document')
		{
			if(!is_string($row[$column]) || !strlen($row[$column]))
			{
				return $this->lng->txt('tos_agreement_document_missing');
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
		else if($column == 'agreement_document_modification_ts')
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
		if('agreement_document_modification_ts' == $column)
		{
			return true;
		}

		return false;
	}
}
