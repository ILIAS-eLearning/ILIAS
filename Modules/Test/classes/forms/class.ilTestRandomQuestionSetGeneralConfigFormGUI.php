<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * GUI class for random question set general config form
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetGeneralConfigFormGUI extends ilPropertyFormGUI
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
	}
	
	public function build()
	{
		$this->setFormAction( $this->ctrl->getFormAction($this->questionSetConfigGUI) );
		
		$this->setTitle( $this->lng->txt('tst_rnd_quest_set_cfg_general_form') );
		$this->setId('tstRndQuestSetCfgGeneralForm');
		
		$this->addCommandButton(
				ilTestRandomQuestionSetConfigGUI::CMD_SAVE_GENERAL_CONFIG_FORM, $this->lng->txt('save')
		);

		// Require Pools with Homogeneous Scored Questions
		
		$requirePoolsQuestionsHomoScored = new ilCheckboxInputGUI(
				$this->lng->txt('tst_inp_all_quest_points_equal_per_pool'), 'quest_points_equal_per_pool'
		);
		
		$requirePoolsQuestionsHomoScored->setInfo(
				$this->lng->txt('tst_inp_all_quest_points_equal_per_pool_desc')
		);
		
		$requirePoolsQuestionsHomoScored->setChecked(
				$this->questionSetConfig->arePoolsWithHomogeneousScoredQuestionsRequired()
		);
		
		$this->addItem($requirePoolsQuestionsHomoScored);
		
		// question amount config mode (per test / per pool)
		
		$questionAmountConfigMode = new ilRadioGroupInputGUI(
				$this->lng->txt('tst_inp_quest_amount_cfg_mode'), 'quest_amount_cfg_mode'
		);
		
		$questionAmountConfigMode->setValue(
				$this->questionSetConfig->getQuestionAmountConfigurationMode()
		);
		
		$questionAmountConfigModePerTest = new ilRadioOption(
				$this->lng->txt('tst_inp_quest_amount_cfg_mode_test'),
				ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST
		);
		
		$questionAmountConfigMode->addOption($questionAmountConfigModePerTest);
		
		$questionAmountConfigModePerPool = new ilRadioOption(
				$this->lng->txt('tst_inp_quest_amount_cfg_mode_pool'),
				ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL
		);
		
		$questionAmountConfigMode->addOption($questionAmountConfigModePerPool);
		
		$this->addItem($questionAmountConfigMode);
		
			// question amount per test
			
			$questionAmountPerTest = new ilNumberInputGUI(
					$this->lng->txt('tst_inp_quest_amount_per_test'), 'quest_amount_per_test'
			);
			
			$questionAmountPerTest->setRequired(true);
			$questionAmountPerTest->setMinValue(0);
			$questionAmountPerTest->allowDecimals(false);
			$questionAmountPerTest->setMinvalueShouldBeGreater(true);
			
			$questionAmountPerTest->setValue(
					$this->questionSetConfig->getQuestionAmountPerTest()
			);
			
		$questionAmountConfigModePerTest->addSubItem($questionAmountPerTest);
	}
}
