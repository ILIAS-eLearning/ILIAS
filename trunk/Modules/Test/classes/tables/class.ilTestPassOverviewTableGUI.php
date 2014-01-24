<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilTestPassOverviewTableGUI
 */
class ilTestPassOverviewTableGUI extends ilTable2GUI
{
	/**
	 * 
	 */
	const CONTEXT_SHORT = 1;

	/**
	 * 
	 */
	const CONTEXT_LONG  = 2;

	/**
	 * @var bool
	 */
	protected $pdf_view = false;

	/**
	 * @param        $parent
	 * @param string $cmd
	 * @param int    $context
	 */
	public function __construct($parent, $cmd, $context = self::CONTEXT_SHORT, $pdf_view = false)
	{
		$this->pdf_view = $pdf_view;
		
		$this->setId('tst_pass_overview_' . $context . '_' . $parent->object->getId());
		$this->setDefaultOrderField('pass');
		$this->setDefaultOrderDirection('ASC');

		parent::__construct($parent, $cmd, $context);
		
		// Don't set any limit because of print/pdf views. Furthermore, this view is part of different summary views, and no cmd ist passed to he calling method.
		$this->setLimit(PHP_INT_MAX);
		if($this->pdf_view)
		{
			$this->disable('linkbar');
			$this->disable('numinfo');
			$this->disable('numinfo_header');
			$this->disable('hits');
		}
		$this->disable('sort');

		$this->initColumns();
		$this->setRowTemplate('tpl.il_as_tst_pass_overview_row.html', 'Modules/Test');
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public function numericOrdering($field)
	{
		switch($field)
		{
			case 'pass':
			case 'date':
			case 'percentage':
				return true;
		}

		return false;
	}

	/**
	 * @param array $row
	 */
	public function fillRow(array $row)
	{
		$old_value = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);
		$row['date'] = ilDatePresentation::formatDate(new ilDateTime($row['date'], IL_CAL_UNIX));
		ilDatePresentation::setUseRelativeDates($old_value);

		if(array_key_exists('percentage', $row))
		{
			$row['percentage'] = sprintf('%.2f', $row['percentage']) . '%';
		}

		if($this->pdf_view && array_key_exists('pass_details', $row))
		{
			unset($row['pass_details']);
		}

		parent::fillRow($row);
	}

	/**
	 *
	 */
	protected function initColumns()
	{
		if(self::CONTEXT_LONG == $this->getContext())
		{
			$this->addColumn($this->lng->txt('scored_pass'), '', '150');
		}
		$this->addColumn($this->lng->txt('pass'), '', '1%');
		$this->addColumn($this->lng->txt('date'));
		if(self::CONTEXT_LONG == $this->getContext())
		{
			$this->addColumn($this->lng->txt('tst_answered_questions'));
			if($this->getParentObject()->object->isOfferingQuestionHintsEnabled())
			{
				$this->addColumn($this->lng->txt('tst_question_hints_requested_hint_count_header'));
			}
			$this->addColumn($this->lng->txt('tst_reached_points'));
			$this->addColumn($this->lng->txt('tst_percent_solved'));
		}
		// pass details menu
		if(!$this->pdf_view)
		{
			$this->addColumn('', '', '1%');
		}
	}
}