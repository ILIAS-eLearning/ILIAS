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
	protected $accessResultsCommandsEnabled = false;
	protected $manageResultsCommandsEnabled = false;
	protected $manageInviteesCommandsEnabled = false;
	
	protected $rowKeyDataField;
	
	protected $anonymity;
	
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		$this->setId('tst_participants_' . $a_parent_obj->object->getRefId());
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		
		$this->setStyle('table', 'fullwidth');
		
		$this->setFormName('participantsForm');
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		
		$this->setRowTemplate("tpl.il_as_tst_participants_row.html", "Modules/Test");
		
		$this->setTitle($this->lng->txt('tst_participating_users'));
		
		$this->setSelectAllCheckbox('chbUser');
		
		$this->enable('header');
		$this->enable('sort');
		$this->enable('select_all');
		
		$this->setShowRowsSelector(true);
	}
	
	/**
	 * @return bool
	 */
	public function isAccessResultsCommandsEnabled()
	{
		return $this->accessResultsCommandsEnabled;
	}
	
	/**
	 * @param bool $accessResultsCommandsEnabled
	 */
	public function setAccessResultsCommandsEnabled($accessResultsCommandsEnabled)
	{
		$this->accessResultsCommandsEnabled = $accessResultsCommandsEnabled;
	}
	
	/**
	 * @return bool
	 */
	public function isManageResultsCommandsEnabled()
	{
		return $this->manageResultsCommandsEnabled;
	}
	
	/**
	 * @param bool $manageResultsCommandsEnabled
	 */
	public function setManageResultsCommandsEnabled($manageResultsCommandsEnabled)
	{
		$this->manageResultsCommandsEnabled = $manageResultsCommandsEnabled;
	}
	
	/**
	 * @return bool
	 */
	public function isManageInviteesCommandsEnabled()
	{
		return $this->manageInviteesCommandsEnabled;
	}
	
	/**
	 * @param bool $manageInviteesCommandsEnabled
	 */
	public function setManageInviteesCommandsEnabled($manageInviteesCommandsEnabled)
	{
		$this->manageInviteesCommandsEnabled = $manageInviteesCommandsEnabled;
	}
	
	/**
	 * @return string
	 */
	public function getRowKeyDataField()
	{
		return $this->rowKeyDataField;
	}
	
	/**
	 * @param string $rowKeyDataField
	 */
	public function setRowKeyDataField($rowKeyDataField)
	{
		$this->rowKeyDataField = $rowKeyDataField;
	}
	
	/**
	 * @return mixed
	 */
	public function getAnonymity()
	{
		return $this->anonymity;
	}
	
	/**
	 * @param mixed $anonymity
	 */
	public function setAnonymity($anonymity)
	{
		$this->anonymity = $anonymity;
	}
	
	/**
	 * @param array $data
	 */
	public function fillRow($data)
	{
		if( $this->isManageInviteesCommandsEnabled() )
		{
			$this->tpl->setCurrentBlock('client_ip_column');
			$this->tpl->setVariable("CLIENT_IP", $data['clientip']);
			$this->tpl->setVariable("ROW_KEY", $this->fetchRowKey($data));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("ROW_KEY", $this->fetchRowKey($data));
		$this->tpl->setVariable("LOGIN", $data['login']);
		$this->tpl->setVariable("FULLNAME", $data['name']);

		$this->tpl->setVariable("STARTED", ($data['started']) ? $this->buildOkIcon() : '');
		$this->tpl->setVariable("TRIES", $this->fetchTriesValue($data));
		$unfinished_passes = $data['unfinished'] == 1 ? $this->lng->txt('yes') : $this->lng->txt('no');
		$this->tpl->setVariable("UNFINISHED_PASSES", $unfinished_passes );
		
		$this->tpl->setVariable("FINISHED", ($data['finished']) ? $this->buildOkIcon() : '');
		$this->tpl->setVariable("ACCESS", ilDatePresentation::formatDate(new ilDateTime($data['access'],IL_CAL_DATETIME)));

		if( $data['active_id'] > 0 )
		{
			$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
			$resultsHref = $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview');
			$finishHref = $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'finishTestPassForSingleUser');

			if($data['unfinished'] == 1)
			{
				$adv = new ilAdvancedSelectionListGUI();
				$this->tpl->setCurrentBlock('action_results_list');
				$adv->addItem($this->lng->txt('tst_show_results'), $data['result'], $data['result']);
				$adv->addItem($this->lng->txt('finish_test'), $data['finish_link'], $data['finish_link']);
				$this->tpl->setVariable('RESULTS_LIST', $adv->getHTML());
			}
			else
			{
				$this->tpl->setCurrentBlock('action_results');
				$this->tpl->setVariable("RESULTS", $data['result']);
				$this->tpl->setVariable("RESULTS_TEXT", ilUtil::prepareFormOutput($this->lng->txt('tst_show_results')));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if( $this->isActionsColumnRequired() )
		{
			$this->tpl->setCurrentBlock('actions_column');
			$this->tpl->parseCurrentBlock();
		}
	}
	
	public function initColumns()
	{
		$this->addColumn('','','1%');
		$this->addColumn($this->lng->txt("name"),'name', '');
		$this->addColumn($this->lng->txt("login"),'login', '');
		
		if( $this->isManageInviteesCommandsEnabled() )
		{
			$this->addColumn($this->lng->txt("clientip"),'clientip', '');
		}
			
		$this->addColumn($this->lng->txt("tst_started"),'started', '');
		$this->addColumn($this->lng->txt("tst_nr_of_tries_of_user"),'tries', '');

		$this->addColumn($this->lng->txt("unfinished_passes"),'unfinished_passes', '');
		$this->addColumn($this->lng->txt("tst_finished"),'finished', '');

		$this->addColumn($this->lng->txt("last_access"),'access', '');
		
		if( $this->isActionsColumnRequired() )
		{
			$this->addColumn('','', '');
		}		
	}
	
	public function initCommands()
	{
		if( $this->isManageInviteesCommandsEnabled() )
		{
			$this->addMultiCommand('saveClientIP', $this->lng->txt('save'));
			$this->addMultiCommand('removeParticipant', $this->lng->txt('remove_as_participant'));
		}
		
		if( $this->isAccessResultsCommandsEnabled() && !$this->getAnonymity() )
		{
			$this->addMultiCommand('showPassOverview', $this->lng->txt('show_pass_overview'));
			$this->addMultiCommand('showUserAnswers', $this->lng->txt('show_user_answers'));
			$this->addMultiCommand('showDetailedResults', $this->lng->txt('show_detailed_results'));
		}
		
		if( $this->isAccessResultsCommandsEnabled() )
		{
			$this->addMultiCommand('deleteSingleUserResults', $this->lng->txt('delete_user_data'));
		}
	}
	
	public function initFilter()
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
	 * @param string $field
	 * @return bool
	 */
	public function numericOrdering($field)
	{
		return in_array($field, array(
			'access', 'tries'
		));
	}
	
	/**
	 * @return string
	 */
	protected function buildOkIcon()
	{
		return "<img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("icon_ok.svg") . "\" alt=\"" . $this->lng->txt("ok") . "\" />";
	}
	
	/**
	 * @return bool
	 */
	protected function isActionsColumnRequired()
	{
		if( $this->isAccessResultsCommandsEnabled() )
		{
			return true;
		}
		
		if( $this->isManageResultsCommandsEnabled() )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * @param array $data
	 * @return string
	 */
	protected function fetchRowKey($data)
	{
		return $data[$this->getRowKeyDataField()];
	}
	
	/**
	 * @param array $data
	 * @return string
	 */
	protected function fetchTriesValue($data)
	{
		if( $data['tries'] < 1 )
		{
			return '';
		}
		
		if( $data['tries'] > 1 )
		{
			return sprintf($this->lng->txt("passes_finished"), $data['tries']);
		}
		
		return sprintf($this->lng->txt("pass_finished"), $data['tries']);
	}
}