<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestParticipantsTableGUI extends ilTable2GUI
{
	protected $testQuestionSetDepenciesBroken;
	protected $anonymity;
	
	protected $actionsColumnRequired;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $testQuestionSetDepenciesBroken, $anonymity, $nrOfDatasets)
	{
		$this->setId('tst_participants_' . $a_parent_obj->object->getRefId());
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		
		$this->initFilter();
		
		$this->testQuestionSetDepenciesBroken = $testQuestionSetDepenciesBroken;
		$this->anonymity = $anonymity;
		$this->setFormName('participantsForm');
		$this->setStyle('table', 'fullwidth');

		$this->addColumn('','','1%');
		$this->addColumn($this->lng->txt("login"),'login', '');
		$this->addColumn($this->lng->txt("name"),'name', '');
		/*
		$this->addColumn($this->lng->txt("lastname"),'lastname', '');
		$this->addColumn($this->lng->txt("firstname"),'firstname', '');
		*/
		$this->addColumn($this->lng->txt("tst_started"),'started', '');
		
		// maxpass => passes ;)
		$this->addColumn($this->lng->txt("tst_nr_of_tries_of_user"),'maxpass', '');
		
		$this->addColumn($this->lng->txt("tst_finished"),'finished', '');
		$this->addColumn($this->lng->txt("last_access"),'access', '');
		
		$this->actionsColumnRequired = false;
		if( !$this->testQuestionSetDepenciesBroken )
		{
			$this->actionsColumnRequired = true;
			
			$this->addColumn('','', '');
		}
	
		$this->setTitle($this->lng->txt('tst_participating_users'));
		$this->setRowTemplate("tpl.il_as_tst_participants_row.html", "Modules/Test");

		if( !$this->anonymity && !$this->testQuestionSetDepenciesBroken )
		{
			$this->addMultiCommand('showPassOverview', $this->lng->txt('show_pass_overview'));
			$this->addMultiCommand('showUserAnswers', $this->lng->txt('show_user_answers'));
			$this->addMultiCommand('showDetailedResults', $this->lng->txt('show_detailed_results'));
		}
		$this->addMultiCommand('deleteSingleUserResults', $this->lng->txt('delete_user_data'));

		if ($nrOfDatasets)
		{
			$this->addCommandButton('deleteAllUserResults', $this->lng->txt('delete_all_user_data'));
		}

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		if (!$this->anonymity)
		{
			$this->setDefaultOrderField("login");
		}
		else
		{
			$this->setDefaultOrderField("access");
		}
		$this->setDefaultOrderDirection("asc");
		$this->setSelectAllCheckbox('chbUser');
		
		$this->enable('header');
		$this->enable('sort');
		$this->enable('select_all');
		
		$this->setShowRowsSelector(true);
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
		$finished = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.svg") . "\" alt=\"".$this->lng->txt("ok")."\" />";
		$started  = "<img border=\"0\" align=\"middle\" src=\"".ilUtil::getImagePath("icon_ok.svg") . "\" alt=\"".$this->lng->txt("ok")."\" />" ;
		$passes = ($data['maxpass']) ? (($data['maxpass'] == 1) ? sprintf($this->lng->txt("pass_finished"), $data['maxpass']) : sprintf($this->lng->txt("passes_finished"), $data['maxpass'])) : '';
		$this->tpl->setVariable("USER_ID", $data['usr_id']);
		$this->tpl->setVariable("LOGIN", $data['login']);
		$this->tpl->setVariable("FULLNAME", $data['name']);
		/*
		$this->tpl->setVariable("FIRSTNAME", $data['firstname']);
		$this->tpl->setVariable("LASTNAME", $data['lastname']);
		*/
		$this->tpl->setVariable("STARTED", ($data['started']) ? $started : '');
		$this->tpl->setVariable("PASSES", $passes);
		$this->tpl->setVariable("FINISHED", ($data['finished']) ? $finished : '');
		$this->tpl->setVariable("ACCESS", ilDatePresentation::formatDate(new ilDateTime($data['access'],IL_CAL_DATETIME)));

		if( $data['active_id'] > 0 && !$this->testQuestionSetDepenciesBroken )
		{
			$this->tpl->setCurrentBlock('action_results');
			$this->tpl->setVariable("RESULTS", $data['result']);
			$this->tpl->setVariable("RESULTS_TEXT", ilUtil::prepareFormOutput($this->lng->txt('tst_show_results')));
			$this->tpl->parseCurrentBlock();
		}
		
		if( $this->actionsColumnRequired )
		{
			$this->tpl->setCurrentBlock('actions_column');
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;

		// title/description
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$ti = new ilSelectInputGUI($lng->txt("selection"), "selection");
		$ti->setOptions(
			array(
				'all' => $lng->txt('all_participants'), 
				'withSolutions' => $lng->txt('with_solutions_participants'), 
				'withoutSolutions' => $lng->txt('without_solutions_participants')
			)
		);		
		$this->addFilterItem($ti);
		$ti->readFromSession();        // get currenty value from session (always after addFilterItem())
		$this->filter["title"] = $ti->getValue();
	}

	/**
	 * @return bool
	 */
	public function numericOrdering($field)
	{
		return in_array($field, array(
			'access', 'maxpass'
		));
	}
}