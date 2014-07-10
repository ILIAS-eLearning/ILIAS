<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI extends ilToolbarGUI
{
	/**
	 * global $ilCtrl object
	 * 
	 * @var ilCtrl
	 */
	public $ctrl = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilLanguage
	 */
	public $lng = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilTestRandomQuestionSetConfigGUI
	 */
	public $questionSetConfigGUI = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilTestRandomQuestionSetConfig
	 */
	public $questionSetConfig = null;
	
	public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI, ilTestRandomQuestionSetConfig $questionSetConfig)
	{
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->questionSetConfigGUI = $questionSetConfigGUI;
		$this->questionSetConfig = $questionSetConfig;

		parent::__construct();
	}
	
	public function build()
	{
		$this->setFormAction( $this->ctrl->getFormAction($this->questionSetConfigGUI) );

		if( $this->questionSetConfig->doesSelectableQuestionPoolsExist() )
		{
			$this->populateNewQuestionSelectionRuleInputs();
		}
	}
	
	private function buildSourcePoolSelectOptionsArray($availablePools)
	{
		$sourcePoolSelectOptionArray = array();
		
		foreach($availablePools as $poolId => $poolData)
		{
			$sourcePoolSelectOptionArray[$poolId] = $poolData['title'];
		}
		
		return $sourcePoolSelectOptionArray;
	}

	private function populateNewQuestionSelectionRuleInputs()
	{
		$availablePools = $this->questionSetConfig->getSelectableQuestionPools();

		require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
		$poolSelection = new ilSelectInputGUI(null, 'quest_pool_id');
		$poolSelection->setOptions($this->buildSourcePoolSelectOptionsArray($availablePools));

		$this->addInputItem($poolSelection, true);

		$this->addFormButton(
			$this->lng->txt('tst_rnd_quest_set_tb_add_pool_btn'),
			ilTestRandomQuestionSetConfigGUI::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM
		);
	}
}
