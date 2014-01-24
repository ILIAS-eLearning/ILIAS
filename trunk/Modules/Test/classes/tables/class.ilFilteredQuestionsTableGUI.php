<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 * 
 * @ilCtrl_Calls ilFilteredQuestionsTableGUI: ilFormPropertyDispatchGUI
 */
class ilFilteredQuestionsTableGUI extends ilTable2GUI
{
	protected $show_marker;
	
	protected $taxIds = array();

	/**
	 * Constructor
	 *
	 * @global ilObjUser $ilUser
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $show_marker, $taxIds)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->show_marker = $show_marker;
		$this->taxIds = $taxIds;
		
		$this->setFormName('filteredquestions');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');

		if ($this->show_marker)
		{
			$this->addColumn($this->lng->txt("tst_question_marker"),'marked', '');
		}
		
		$this->addColumn($this->lng->txt("worked_through"),'worked_through', '');
		$this->addColumn('' ,'postponed', '');
	
		$this->setTitle($this->lng->txt('tst_filtered_question_list'));
	
		$this->setRowTemplate("tpl.il_as_tst_filtered_questions_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->enable('header');
		$this->disable('sort');
		$this->disable('select_all');
		
		$this->initFilter();
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';

		foreach($this->taxIds as $taxId)
		{
			$postvar = "tax_$taxId";

			$inp = new ilTaxSelectInputGUI($taxId, $postvar, true);
			$this->addFilterItem($inp);
			$inp->readFromSession();
			$this->filter[$postvar] = $inp->getValue();
		}
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		if (strlen($data['description']))
		{
			$this->tpl->setCurrentBlock('description');
			$this->tpl->setVariable("DESCRIPTION", ilUtil::prepareFormOutput($data['description']));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($data["worked_through"])
		{
			$this->tpl->setVariable("SRC_WORKED_THROUGH", ilUtil::getImagePath("icon_ok.png"));
			$this->tpl->setVariable("ALT_WORKED_THROUGH", $this->lng->txt("worked_through"));
		}
		else
		{
			$this->tpl->setVariable("SRC_WORKED_THROUGH", ilUtil::getImagePath("icon_not_ok.png"));
			$this->tpl->setVariable("ALT_WORKED_THROUGH", $this->lng->txt("not_worked_through"));
		}
		
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data['title']));
		$this->tpl->setVariable("POSTPONED", ($data["postponed"]) ? $this->lng->txt("postponed") : '');
		
		if ($this->show_marker)
		{
			if ($data['marked'])
			{
				$this->tpl->setCurrentBlock('marked_img');
				$this->tpl->setVariable("ALT_MARKED", $this->lng->txt("tst_question_marked"));
				$this->tpl->setVariable("HREF_MARKED", ilUtil::getImagePath("marked.png"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->touchBlock('marker');
			}
		}
	}
}
?>