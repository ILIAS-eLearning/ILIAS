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
 *
 * @ilCtrl_Calls ilTestRandomQuestionSetGeneralConfigFormGUI: ilFormPropertyDispatchGUI
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
     * object instance for current test
     *
     * @var ilObjTest
     */
    public $testOBJ = null;
    
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

    /**
     * @var bool
     */
    protected $editModeEnabled = true;

    /**
     * @param ilCtrl $ctrl
     * @param ilLanguage $lng
     * @param ilObjTest $testOBJ
     * @param ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI
     * @param ilTestRandomQuestionSetConfig $questionSetConfig
     */
    public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilObjTest $testOBJ, ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI, ilTestRandomQuestionSetConfig $questionSetConfig)
    {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        // Bugfix for mantis: 0015081
        $this->lng->loadLanguageModule('form');
        $this->testOBJ = $testOBJ;
        $this->questionSetConfigGUI = $questionSetConfigGUI;
        $this->questionSetConfig = $questionSetConfig;
    }

    /**
     * @return boolean
     */
    public function isEditModeEnabled()
    {
        return $this->editModeEnabled;
    }

    /**
     * @param boolean $editModeEnabled
     */
    public function setEditModeEnabled($editModeEnabled)
    {
        $this->editModeEnabled = $editModeEnabled;
    }
    
    public function build()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->questionSetConfigGUI));
        
        $this->setTitle($this->lng->txt('tst_rnd_quest_set_cfg_general_form'));
        $this->setId('tstRndQuestSetCfgGeneralForm');
        
        $this->addCommandButton(
            ilTestRandomQuestionSetConfigGUI::CMD_SAVE_GENERAL_CONFIG_FORM,
            $this->lng->txt('save')
        );

        // Require Pools with Homogeneous Scored Questions
        
        $requirePoolsQuestionsHomoScored = new ilCheckboxInputGUI(
            $this->lng->txt('tst_inp_all_quest_points_equal_per_pool'),
            'quest_points_equal_per_pool'
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
            $this->lng->txt('tst_inp_quest_amount_cfg_mode'),
            'quest_amount_cfg_mode'
        );

        $questionAmountConfigMode->setValue($this->fetchValidQuestionAmountConfigModeWithFallbackModePerTest(
            $this->questionSetConfig
        ));
        
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
        
        $questionAmountConfigMode->setRequired(true);
        
        $this->addItem($questionAmountConfigMode);
        
        // question amount per test
            
        $questionAmountPerTest = new ilNumberInputGUI(
                $this->lng->txt('tst_inp_quest_amount_per_test'),
                'quest_amount_per_test'
            );
            
        $questionAmountPerTest->setRequired(true);
        $questionAmountPerTest->setMinValue(1);
        $questionAmountPerTest->allowDecimals(false);
        $questionAmountPerTest->setMinvalueShouldBeGreater(false);
        $questionAmountPerTest->setSize(4);
            
        $questionAmountPerTest->setValue(
                $this->questionSetConfig->getQuestionAmountPerTest()
            );
            
        $questionAmountConfigModePerTest->addSubItem($questionAmountPerTest);

        if (!$this->isEditModeEnabled()) {
            $requirePoolsQuestionsHomoScored->setDisabled(true);
            $questionAmountConfigMode->setDisabled(true);
            $questionAmountPerTest->setDisabled(true);
        }
    }

    private function fetchValidQuestionAmountConfigModeWithFallbackModePerTest(ilTestRandomQuestionSetConfig $config)
    {
        switch ($config->getQuestionAmountConfigurationMode()) {
            case ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST:
            case ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL:

                return $config->getQuestionAmountConfigurationMode();
        }

        return ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST;
    }
    
    public function save()
    {
        $this->questionSetConfig->setPoolsWithHomogeneousScoredQuestionsRequired(
            $this->getItemByPostVar('quest_points_equal_per_pool')->getChecked()
        );

        switch ($this->getItemByPostVar('quest_amount_cfg_mode')->getValue()) {
            case ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST:
                
                $this->questionSetConfig->setQuestionAmountConfigurationMode(
                    $this->getItemByPostVar('quest_amount_cfg_mode')->getValue()
                );
                
                $this->questionSetConfig->setQuestionAmountPerTest(
                    $this->getItemByPostVar('quest_amount_per_test')->getValue()
                );
                
                break;
                
            case ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL:
                
                $this->questionSetConfig->setQuestionAmountConfigurationMode(
                    $this->getItemByPostVar('quest_amount_cfg_mode')->getValue()
                );
                
                $this->questionSetConfig->setQuestionAmountPerTest(null);
                
                break;
        }
        
        return $this->questionSetConfig->saveToDb($this->testOBJ->getTestId());
    }
}
