<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableGUI.php';
require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';
require_once 'Services/UIComponent/Modal/classes/class.ilModalGUI.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceHistoryTableGUI extends ilTermsOfServiceTableGUI
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
		$this->setId('tos_acceptance_history');

		$this->setDefaultOrderDirection('DESC');
		$this->setDefaultOrderField('ts');
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt('tos_acceptance_history'));

		$this->addColumn($this->lng->txt('tos_acceptance_datetime'), 'ts');
		$this->addColumn($this->lng->txt('login'), 'login');
		$this->optionalColumns        = (array)$this->getSelectableColumns();
		$this->visibleOptionalColumns = (array)$this->getSelectedColumns();
		foreach($this->visibleOptionalColumns as $column)
		{
			$this->addColumn($this->optionalColumns[$column]['txt'], $column);
		}
		$this->addColumn($this->lng->txt('language'), 'lng');
		$this->addColumn($this->lng->txt('tos_agreement_document'), 'src');

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'applyAcceptanceHistoryFilter'));

		$this->setRowTemplate('tpl.tos_acceptance_history_table_row.html', 'Services/TermsOfService');

		require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		require_once 'Services/YUI/classes/class.ilYuiUtil.php';
		iljQueryUtil::initjQuery();
		ilYuiUtil::initPanel();
		ilYuiUtil::initOverlay();

		$this->setShowRowsSelector(true);

		$this->initFilter();
		$this->setFilterCommand('applyAcceptanceHistoryFilter');
		$this->setResetCommand('resetAcceptanceHistoryFilter');
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns()
	{
		$cols = array(
			'firstname' => array('txt' => $this->lng->txt('firstname'), 'default' => false),
			'lastname' => array('txt' => $this->lng->txt('lastname'), 'default' => false)
		);

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
			$row['lng'] = $this->lng->txt('meta_l_' . $row['lng']);
		}
	}

	/**
	 * @param array $row
	 * @return array
	 */
	protected function prepareRow(array &$row)
	{
		$unique_id = md5($row['usr_id'].$row['ts']);

		$this->ctrl->setParameter($this->getParentObject(), 'tosv_id', $row['tosv_id']);
		$row['content_link'] = $this->ctrl->getLinkTarget($this->getParentObject(), 'getAcceptedContentAsynch', '', true, false);
		$this->ctrl->setParameter($this->getParentObject(), 'tosv_id', '');
		$row['img_down'] = ilGlyphGUI::get(ilGlyphGUI::SEARCH);
		$row['id']       = $unique_id;

		$modal = ilModalGUI::getInstance();
		$modal->setHeading($this->lng->txt('tos_agreement_document'));
		$modal->setId('tos_' . $unique_id);
		$modal->setBody('');
		$row['modal'] = $modal->getHTML();
	}

	/**
	 * @return array
	 */
	protected function getStaticData()
	{
		return array('modal', 'ts', 'login', 'lng', 'src', 'text', 'id', 'img_down', 'content_link');
	}

	/**
	 * @param mixed $column
	 * @param array $row
	 * @return mixed
	 */
	protected function formatCellValue($column, array $row)
	{
		if($column == 'ts')
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
		if('ts' == $column)
		{
			return true;
		}

		return false;
	}

	/**
	 * 
	 */
	public function initFilter()
	{
		/**
		 * @var $tpl ilTemplate
		 */
		global $tpl;

		include_once 'Services/Form/classes/class.ilTextInputGUI.php';
		$ul = new ilTextInputGUI($this->lng->txt('login').'/'.$this->lng->txt('email').'/'.$this->lng->txt('name'), 'query');
		$ul->setDataSource($this->ctrl->getLinkTarget($this->getParentObject(), 'addUserAutoComplete', '', true));
		$ul->setSize(20);
		$ul->setSubmitFormOnEnter(true);
		$this->addFilterItem($ul);
		$ul->readFromSession();
		$this->filter['query'] = $ul->getValue();

		include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
		$options = array();
		$languages = ilObject::_getObjectsByType('lng');
		foreach($languages as $lng)
		{
			$options[$lng['title']] = $this->lng->txt('meta_l_' . $lng['title']);
		}
		asort($options);
		
		$options = array('' => $this->lng->txt('any_language')) + $options;
		
		$si = new ilSelectInputGUI($this->lng->txt('language'), 'lng');
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter['lng'] = $si->getValue();
		
		include_once 'Services/Form/classes/class.ilDateDurationInputGUI.php';
		$tpl->addJavaScript('./Services/Form/js/date_duration.js');
		$duration = new ilDateDurationInputGUI($this->lng->txt('tos_period'), 'period');
		$duration->setStartText($this->lng->txt('tos_period_from'));
		$duration->setEndText($this->lng->txt('tos_period_until'));
		$duration->setStart(new ilDateTime(strtotime('-1 year', time()), IL_CAL_UNIX));
		$duration->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
		$duration->setShowTime(true);
		$this->addFilterItem($duration, true);
		$duration->readFromSession();
		$this->optional_filter['period'] = $duration->getValue();
	}
}
