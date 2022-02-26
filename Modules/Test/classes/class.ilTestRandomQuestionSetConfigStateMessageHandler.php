<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetConfigStateMessageHandler
{
    /**
     * @var ILIAS\DI\Container
     */
    protected $DIC;
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTestRandomQuestionSetConfigGUI
     */
    protected $targetGUI;

    const CONTEXT_GENERAL_CONFIG = 'generalConfigContext';
    const CONTEXT_POOL_SELECTION = 'poolSelectionContext';

    /**
     * @var string
     */
    protected $context;

    /**
     * @var bool
     */
    protected $participantDataExists;

    /**
     * @var ilTestRandomQuestionSetNonAvailablePool[]
     */
    protected $lostPools;

    /**
     * @var ilTestRandomQuestionSetConfig
     */
    protected $questionSetConfig;

    /**
     * @var bool
     */
    protected $validationFailed;
    
    /**
     * @var array
     */
    protected $validationReports;
    
    /**
     * @param ilLanguage $lng
     */
    public function __construct(ilLanguage $lng, ilCtrl $ctrl)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->DIC = $DIC;
        
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->validationFailed = false;
        $this->validationReports = array();
    }

    /**
     * @return ilTestRandomQuestionSetNonAvailablePool[]
     */
    public function getLostPools()
    {
        return $this->lostPools;
    }

    /**
     * @param ilTestRandomQuestionSetNonAvailablePool[] $lostPools
     */
    public function setLostPools($lostPools)
    {
        $this->lostPools = $lostPools;
    }

    /**
     * @return boolean
     */
    public function doesParticipantDataExists()
    {
        return $this->participantDataExists;
    }

    /**
     * @param boolean $participantDataExists
     */
    public function setParticipantDataExists($participantDataExists)
    {
        $this->participantDataExists = $participantDataExists;
    }

    /**
     * @return ilTestRandomQuestionSetConfigGUI
     */
    public function getTargetGUI()
    {
        return $this->targetGUI;
    }

    /**
     * @param ilTestRandomQuestionSetConfigGUI $targetGUI
     */
    public function setTargetGUI($targetGUI)
    {
        $this->targetGUI = $targetGUI;
    }
    
    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return ilTestRandomQuestionSetConfig
     */
    public function getQuestionSetConfig()
    {
        return $this->questionSetConfig;
    }

    /**
     * @param ilTestRandomQuestionSetConfig $questionSetConfig
     */
    public function setQuestionSetConfig($questionSetConfig)
    {
        $this->questionSetConfig = $questionSetConfig;
    }

    /**
     * @return bool
     */
    public function isValidationFailed()
    {
        return $this->validationFailed;
    }

    /**
     * @param bool $validationFailed
     */
    public function setValidationFailed($validationFailed)
    {
        $this->validationFailed = $validationFailed;
    }
    
    /**
     * @return array
     */
    public function getValidationReportHtml()
    {
        return implode('<br />', $this->validationReports);
    }
    
    /**
     * @return array
     */
    public function hasValidationReports()
    {
        return count($this->validationReports);
    }
    
    /**
     * @param string $validationReport
     */
    public function addValidationReport($validationReport)
    {
        $this->validationReports[] = $validationReport;
    }

    public function handle()
    {
        if ($this->isNoAvailableQuestionPoolsHintRequired()) {
            $this->addValidationReport($this->lng->txt('tst_msg_rand_quest_set_no_pools_available'));
        } elseif ($this->getLostPools()) {
            $this->populateMessage($this->buildLostPoolsReportMessage());
        } elseif (!$this->questionSetConfig->isQuestionAmountConfigComplete()) {
            $this->addValidationReport($this->lng->txt('tst_msg_rand_quest_set_incomplete_quest_amount_cfg'));

            if ($this->isQuestionAmountConfigPerTestHintRequired()) {
                $this->addValidationReport(
                    sprintf(
                        $this->lng->txt('tst_msg_rand_quest_set_change_quest_amount_here'),
                        $this->buildGeneralConfigSubTabLink()
                    )
                );
            } elseif ($this->isQuestionAmountConfigPerPoolHintRequired()) {
                $this->addValidationReport(
                    sprintf(
                        $this->lng->txt('tst_msg_rand_quest_set_change_quest_amount_here'),
                        $this->buildQuestionSelectionSubTabLink()
                    )
                );
            }
        } elseif (!$this->questionSetConfig->hasSourcePoolDefinitions()) {
            $this->addValidationReport($this->lng->txt('tst_msg_rand_quest_set_no_src_pool_defs'));
        }
        // fau: delayCopyRandomQuestions - show info message if date of last synchronisation is empty
        elseif ($this->questionSetConfig->getLastQuestionSyncTimestamp() == 0) {
            $message = $this->DIC->language()->txt('tst_msg_rand_quest_set_not_sync');
            $button = $this->buildQuestionStageRebuildButton();
            $msgBox = $this->DIC->ui()->factory()->messageBox()->info($message)->withButtons(array($button));
            $this->populateMessage($this->DIC->ui()->renderer()->render($msgBox));
        }
        // fau.

        elseif (!$this->questionSetConfig->isQuestionSetBuildable()) {
            $this->setValidationFailed(true);
            $this->addValidationReport($this->lng->txt('tst_msg_rand_quest_set_pass_not_buildable'));

            //fau: fixRandomTestBuildable - show the messages if set is not buildable
            $this->addValidationReport(implode('<br />', $this->questionSetConfig->getBuildableMessages()));
        //fau.
        } elseif ($this->questionSetConfig->getLastQuestionSyncTimestamp()) {
            $message = $this->lng->txt('tst_msg_rand_quest_set_pass_buildable');
            
            $syncDate = new ilDateTime(
                $this->questionSetConfig->getLastQuestionSyncTimestamp(),
                IL_CAL_UNIX
            );
            
            $message .= sprintf(
                $this->lng->txt('tst_msg_rand_quest_set_stage_pool_last_sync'),
                ilDatePresentation::formatDate($syncDate)
            );
            
            if (!$this->doesParticipantDataExists() && !$this->getLostPools()) {
                $msgBox = $this->DIC->ui()->factory()->messageBox()->info($message)->withButtons(
                    array($this->buildQuestionStageRebuildButton())
                );
            } else {
                $msgBox = $this->DIC->ui()->factory()->messageBox()->info($message);
            }
            
            $this->populateMessage($this->DIC->ui()->renderer()->render($msgBox));
        }
    }
    
    private function buildLostQuestionPoolsString()
    {
        $titles = array();
        
        foreach ($this->getLostPools() as $lostPool) {
            $titles[] = $lostPool->getTitle();
        }
        
        return implode(', ', $titles);
    }
    
    private function getAfterRebuildQuestionStageCommand()
    {
        switch ($this->getContext()) {
            case self::CONTEXT_POOL_SELECTION:
                
                return ilTestRandomQuestionSetConfigGUI::CMD_SHOW_SRC_POOL_DEF_LIST;
                
            case self::CONTEXT_GENERAL_CONFIG:
            default:

                return ilTestRandomQuestionSetConfigGUI::CMD_SHOW_GENERAL_CONFIG_FORM;
        }
    }
    
    private function buildQuestionStageRebuildButton() : \ILIAS\UI\Component\Button\Standard
    {
        $this->ctrl->setParameter(
            $this->getTargetGUI(),
            ilTestRandomQuestionSetConfigGUI::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD,
            $this->getAfterRebuildQuestionStageCommand()
        );

        $href = $this->ctrl->getLinkTarget(
            $this->getTargetGUI(),
            ilTestRandomQuestionSetConfigGUI::CMD_BUILD_QUESTION_STAGE
        );
        $label = $this->lng->txt('tst_btn_rebuild_random_question_stage');

        return $this->DIC->ui()->factory()->button()->standard($label, $href)->withLoadingAnimationOnClick(true);
    }

    private function buildGeneralConfigSubTabLink()
    {
        $href = $this->ctrl->getLinkTarget(
            $this->getTargetGUI(),
            ilTestRandomQuestionSetConfigGUI::CMD_SHOW_GENERAL_CONFIG_FORM
        );
        
        $label = $this->lng->txt('tst_rnd_quest_cfg_tab_general');

        return "<a href=\"{$href}\">{$label}</a>";
    }

    private function buildQuestionSelectionSubTabLink()
    {
        $href = $this->ctrl->getLinkTarget(
            $this->getTargetGUI(),
            ilTestRandomQuestionSetConfigGUI::CMD_SHOW_SRC_POOL_DEF_LIST
        );
        
        $label = $this->lng->txt('tst_rnd_quest_cfg_tab_pool');

        return "<a href=\"{$href}\">{$label}</a>";
    }

    /**
     * @param $currentRequestCmd
     * @return bool
     */
    private function isNoAvailableQuestionPoolsHintRequired()
    {
        if ($this->getContext() != self::CONTEXT_POOL_SELECTION) {
            return false;
        }

        if ($this->questionSetConfig->doesSelectableQuestionPoolsExist()) {
            return false;
        }

        return true;
    }

    /**
     * @param $currentRequestCmd
     * @return bool
     */
    private function isQuestionAmountConfigPerPoolHintRequired()
    {
        if ($this->getContext() != self::CONTEXT_GENERAL_CONFIG) {
            return false;
        }

        if (!$this->questionSetConfig->isQuestionAmountConfigurationModePerPool()) {
            return false;
        }

        return true;
    }

    /**
     * @param $currentRequestCmd
     * @return bool
     */
    private function isQuestionAmountConfigPerTestHintRequired()
    {
        if ($this->getContext() != self::CONTEXT_POOL_SELECTION) {
            return false;
        }

        if (!$this->questionSetConfig->isQuestionAmountConfigurationModePerTest()) {
            return false;
        }

        return true;
    }
    
    /**
     * @return string
     */
    protected function buildLostPoolsReportMessage()
    {
        $report = sprintf(
            $this->lng->txt('tst_msg_rand_quest_set_lost_pools'),
            $this->buildLostQuestionPoolsString()
        );
        
        if ($this->getContext() == self::CONTEXT_GENERAL_CONFIG) {
            $action = $this->ctrl->getLinkTarget(
                $this->getTargetGUI(),
                ilTestRandomQuestionSetConfigGUI::CMD_SHOW_SRC_POOL_DEF_LIST
            );
            
            $link = $this->DIC->ui()->factory()->link()->standard(
                $this->lng->txt('tst_msg_rand_quest_set_lost_pools_link'),
                $action
            );
            
            $msgBox = $this->DIC->ui()->factory()->messageBox()->info($report)->withLinks(array($link));
        } else {
            $msgBox = $this->DIC->ui()->factory()->messageBox()->info($report);
        }
        
        return $this->DIC->ui()->renderer()->render($msgBox);
    }
    
    /**
     * @param $message
     */
    protected function populateMessage($message)
    {
        $this->DIC->ui()->mainTemplate()->setCurrentBlock('mess');
        $this->DIC->ui()->mainTemplate()->setVariable('MESSAGE', $message);
        $this->DIC->ui()->mainTemplate()->parseCurrentBlock();
    }
}
