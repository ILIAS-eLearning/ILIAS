<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
require_once 'Services/Form/classes/class.ilFormPropertyGUI.php';
require_once 'Services/Form/classes/class.ilHiddenInputGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestInfoScreenToolbarGUI extends ilToolbarGUI
{
    private static array $TARGET_CLASS_PATH_BASE = array('ilRepositoryGUI', 'ilObjTestGUI');

    protected \ILIAS\DI\Container $DIC;
    private ?ilToolbarGUI $globalToolbar = null;

    protected ilDBInterface $db;
    protected ilAccessHandler $access;
    protected ilCtrl $ctrl;
    protected ilPluginAdmin $pluginAdmin;
    private \ilGlobalTemplateInterface $main_tpl;

    protected ?ilObjTest $testOBJ = null;
    protected ?ilTestQuestionSetConfig $testQuestionSetConfig = null;
    protected ?ilTestPlayerAbstractGUI $testPlayerGUI = null;
    protected ?ilTestSession $testSession = null;

    /**
     * @var ilTestSequence|ilTestSequenceDynamicQuestionSet
     */
    protected $testSequence;

    /**
     * @var string
     */
    private $sessionLockString;
    private array $infoMessages = array();
    private array $failureMessages = array();

    public function __construct(ilDBInterface $db, ilAccessHandler $access, ilCtrl $ctrl, ilLanguage $lng, ilPluginAdmin $pluginAdmin)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate(); /* @var ILIAS\DI\Container $DIC */
        $this->DIC = $DIC;
        $this->db = $db;
        $this->access = $access;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->pluginAdmin = $pluginAdmin;
    }

    public function getGlobalToolbar() : ?ilToolbarGUI
    {
        return $this->globalToolbar;
    }

    public function setGlobalToolbar(ilToolbarGUI $globalToolbar) : void
    {
        $this->globalToolbar = $globalToolbar;
    }
    
    public function getTestOBJ() : ?ilObjTest
    {
        return $this->testOBJ;
    }

    public function setTestOBJ(ilObjTest $testOBJ) : void
    {
        $this->testOBJ = $testOBJ;
    }

    public function getTestQuestionSetConfig() : ?ilTestQuestionSetConfig
    {
        return $this->testQuestionSetConfig;
    }

    public function setTestQuestionSetConfig(ilTestQuestionSetConfig $testQuestionSetConfig) : void
    {
        $this->testQuestionSetConfig = $testQuestionSetConfig;
    }

    public function getTestPlayerGUI() : ?ilTestPlayerAbstractGUI
    {
        return $this->testPlayerGUI;
    }

    public function setTestPlayerGUI(ilTestPlayerAbstractGUI $testPlayerGUI) : void
    {
        $this->testPlayerGUI = $testPlayerGUI;
    }

    public function getTestSession() : ?ilTestSession
    {
        return $this->testSession;
    }

    public function setTestSession(ilTestSession $testSession) : void
    {
        $this->testSession = $testSession;
    }

    /**
     * @return ilTestSequence|ilTestSequenceDynamicQuestionSet
     */
    public function getTestSequence()
    {
        return $this->testSequence;
    }

    /**
     * @param ilTestSequence|ilTestSequenceDynamicQuestionSet $testSequence
     */
    public function setTestSequence($testSequence) : void
    {
        $this->testSequence = $testSequence;
    }

    public function getSessionLockString() : ?string
    {
        return $this->sessionLockString;
    }

    public function setSessionLockString($sessionLockString) : void
    {
        $this->sessionLockString = $sessionLockString;
    }

    public function getInfoMessages() : array
    {
        return $this->infoMessages;
    }

    /**
     * @ param string $infoMessage Could be. Doesn't have to.
     */
    public function addInfoMessage($infoMessage) : void
    {
        $this->infoMessages[] = $infoMessage;
    }

    public function getFailureMessages() : array
    {
        return $this->failureMessages;
    }

    public function addFailureMessage(string $failureMessage) : void
    {
        $this->failureMessages[] = $failureMessage;
    }

    public function setFormAction(
        string $a_val,
        bool $a_multipart = false,
        string $a_target = ""
    ) : void {
        if ($this->globalToolbar instanceof parent) {
            $this->globalToolbar->setFormAction($a_val, $a_multipart, $a_target);
        } else {
            parent::setFormAction($a_val, $a_multipart, $a_target);
        }
    }

    public function addButtonInstance(ilButtonBase $a_button) : void
    {
        if ($this->globalToolbar instanceof parent) {
            $this->globalToolbar->addButtonInstance($a_button);
        } else {
            parent::addButtonInstance($a_button);
        }
    }

    public function setCloseFormTag(bool $a_val) : void
    {
        if ($this->globalToolbar instanceof parent) {
            $this->globalToolbar->setCloseFormTag($a_val);
        } else {
            parent::setCloseFormTag($a_val);
        }
    }

    public function addInputItem(
        ilToolbarItem $a_item,
        bool $a_output_label = false
    ) : void {
        if ($this->globalToolbar instanceof parent) {
            $this->globalToolbar->addInputItem($a_item, $a_output_label);
        } else {
            parent::addInputItem($a_item, $a_output_label);
        }
    }
    
    public function addFormInput($formInput) : void
    {
        if ($this->globalToolbar instanceof parent) {
            $this->globalToolbar->addFormInput($formInput);
        }
    }
    
    public function clearItems() : void
    {
        if ($this->globalToolbar instanceof parent) {
            $this->globalToolbar->setItems(array());
        } else {
            $this->setItems(array());
        }
    }
    
    private function getClassName($target)
    {
        if (is_object($target)) {
            $target = get_class($target);
        }
        
        return $target;
    }

    private function getClassNameArray($target) : array
    {
        if (is_array($target)) {
            return $target;
        }
        
        return array($this->getClassName($target));
    }

    private function getClassPath($target) : array
    {
        return array_merge(self::$TARGET_CLASS_PATH_BASE, $this->getClassNameArray($target));
    }
    
    private function setParameter($target, $parameter, $value) : void
    {
        $this->ctrl->setParameterByClass($this->getClassName($target), $parameter, $value);
    }
    
    private function buildLinkTarget($target, $cmd = null) : string
    {
        return $this->ctrl->getLinkTargetByClass($this->getClassPath($target), $cmd);
    }
    
    private function buildFormAction($target) : string
    {
        return $this->ctrl->getFormActionByClass($this->getClassPath($target));
    }

    private function ensureInitialisedSessionLockString() : void
    {
        if (!strlen($this->getSessionLockString())) {
            $this->setSessionLockString($this->buildSessionLockString());
        }
    }

    private function buildSessionLockString() : string
    {
        return md5($_COOKIE[session_name()] . time());
    }
    
    private function areSkillLevelThresholdsMissing() : bool
    {
        if (!$this->getTestOBJ()->isSkillServiceEnabled()) {
            return false;
        }
        
        $questionContainerId = $this->getTestOBJ()->getId();

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        require_once 'Modules/Test/classes/class.ilTestSkillLevelThreshold.php';

        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($questionContainerId);
        $assignmentList->loadFromDb();

        foreach ($assignmentList->getUniqueAssignedSkills() as $data) {
            foreach ($data['skill']->getLevelData() as $level) {
                $treshold = new ilTestSkillLevelThreshold($this->db);
                $treshold->setTestId($this->getTestOBJ()->getTestId());
                $treshold->setSkillBaseId($data['skill_base_id']);
                $treshold->setSkillTrefId($data['skill_tref_id']);
                $treshold->setSkillLevelId($level['id']);

                if (!$treshold->dbRecordExists()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getSkillLevelThresholdsMissingInfo() : string
    {
        $message = $this->lng->txt('tst_skl_level_thresholds_missing');
        
        $linkTarget = $this->buildLinkTarget(
            array('ilTestSkillAdministrationGUI', 'ilTestSkillLevelThresholdsGUI'),
            ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
        );
        
        $link = $this->DIC->ui()->factory()->link()->standard(
            $this->DIC->language()->txt('tst_skl_level_thresholds_link'),
            $linkTarget
        );
        
        $msgBox = $this->DIC->ui()->factory()->messageBox()->failure($message)->withLinks(array($link));
        
        return $this->DIC->ui()->renderer()->render($msgBox);
    }
    
    private function hasFixedQuestionSetSkillAssignsLowerThanBarrier() : bool
    {
        if (!$this->testOBJ->isFixedTest()) {
            return false;
        }
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->testOBJ->getId());
        $assignmentList->loadFromDb();
        
        return $assignmentList->hasSkillsAssignedLowerThanBarrier();
    }
    
    private function getSkillAssignBarrierInfo() : string
    {
        require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        
        return sprintf(
            $this->lng->txt('tst_skill_triggerings_num_req_answers_not_reached_warn'),
            ilObjAssessmentFolder::getSkillTriggerAnswerNumberBarrier()
        );
    }

    public function build() : void
    {
        if (!$this->testOBJ->isDynamicTest()) {
            $this->ensureInitialisedSessionLockString();
            
            $this->setParameter($this->getTestPlayerGUI(), 'lock', $this->getSessionLockString());
            $this->setParameter($this->getTestPlayerGUI(), 'sequence', $this->getTestSession()->getLastSequence());
            $this->setParameter('ilObjTestGUI', 'ref_id', $this->getTestOBJ()->getRefId());
            
            $this->setFormAction($this->buildFormAction($this->getTestPlayerGUI()));
        }
        
        $online_access = false;
        if ($this->getTestOBJ()->getFixedParticipants()) {
            include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
            $online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->getTestOBJ()->getId(), $this->getTestSession()->getUserId());
            if ($online_access_result === true) {
                $online_access = true;
            } else {
                $this->addInfoMessage($online_access_result);
            }
        }

        if (!$this->getTestOBJ()->getOfflineStatus() && $this->getTestOBJ()->isComplete($this->getTestQuestionSetConfig())) {
            if ((!$this->getTestOBJ()->getFixedParticipants() || $online_access) && $this->access->checkAccess("read", "", $this->getTestOBJ()->getRefId())) {
                $executable = $this->getTestOBJ()->isExecutable(
                    $this->getTestSession(),
                    $this->getTestSession()->getUserId(),
                    $allowPassIncrease = true
                );
                
                if ($executable["executable"]) {
                    if ($this->getTestOBJ()->areObligationsEnabled() && $this->getTestOBJ()->hasObligations($this->getTestOBJ()->getTestId())) {
                        $this->addInfoMessage($this->lng->txt('tst_test_contains_obligatory_questions'));
                    }

                    if ($this->getTestSession()->getActiveId() > 0) {
                        // resume test
                        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
                        $testPassesSelector = new ilTestPassesSelector($this->db, $this->getTestOBJ());
                        $testPassesSelector->setActiveId($this->getTestSession()->getActiveId());
                        $testPassesSelector->setLastFinishedPass($this->getTestSession()->getLastFinishedPass());

                        $closedPasses = $testPassesSelector->getClosedPasses();
                        $existingPasses = $testPassesSelector->getExistingPasses();

                        if ($existingPasses > $closedPasses) {
                            $btn = ilSubmitButton::getInstance();
                            $btn->setCaption('tst_resume_test');
                            $btn->setCommand('resumePlayer');
                            $btn->setPrimary(true);
                            $this->addButtonInstance($btn);
                        } else {
                            $btn = ilSubmitButton::getInstance();
                            $btn->setCaption($this->getTestOBJ()->getStartTestLabel($this->getTestSession()->getActiveId()), false);
                            $btn->setCommand('startPlayer');
                            $btn->setPrimary(true);
                            $this->addButtonInstance($btn);
                        }
                    } else {
                        // start new test
                        $btn = ilSubmitButton::getInstance();
                        $btn->setCaption($this->getTestOBJ()->getStartTestLabel($this->getTestSession()->getActiveId()), false);
                        $btn->setCommand('startPlayer');
                        $btn->setPrimary(true);
                        $this->addButtonInstance($btn);
                    }
                } else {
                    $this->addInfoMessage($executable['errormessage']);
                }
            }

            if ($this->DIC->user()->getId() == ANONYMOUS_USER_ID) {
                if ($this->getItems()) {
                    $this->addSeparator();
                }
                
                require_once 'Services/Form/classes/class.ilTextInputGUI.php';
                $anonymous_id = new ilTextInputGUI($this->lng->txt('enter_anonymous_code'), 'anonymous_id');
                $anonymous_id->setSize(8);
                $this->addInputItem($anonymous_id, true);
                $button = ilSubmitButton::getInstance();
                $button->setCaption('submit');
                $button->setCommand('setAnonymousId');
                $this->addButtonInstance($button);
            }
        }
        if ($this->getTestOBJ()->getOfflineStatus() && !$this->getTestQuestionSetConfig()->areDepenciesBroken()) {
            $message = $this->lng->txt("test_is_offline");
            
            $links = array();
            
            if ($this->access->checkAccess("write", "", $this->getTestOBJ()->getRefId())) {
                $links[] = $this->DIC->ui()->factory()->link()->standard(
                    $this->DIC->language()->txt('test_edit_settings'),
                    $this->buildLinkTarget('ilobjtestsettingsgeneralgui')
                );
            }
            
            $msgBox = $this->DIC->ui()->factory()->messageBox()->info($message)->withLinks($links);
            
            $this->populateMessage($this->DIC->ui()->renderer()->render($msgBox));
        }
        
        if ($this->access->checkAccess("write", "", $this->getTestOBJ()->getRefId())) {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportFails.php';
            $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->testOBJ->getId());
            require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdImportFails.php';
            $sltImportFails = new ilTestSkillLevelThresholdImportFails($this->testOBJ->getId());
            
            if ($qsaImportFails->failedImportsRegistered() || $sltImportFails->failedImportsRegistered()) {
                $importFailsMsg = array();
                
                if ($qsaImportFails->failedImportsRegistered()) {
                    $importFailsMsg[] = $qsaImportFails->getFailedImportsMessage($this->lng);
                }
                
                if ($sltImportFails->failedImportsRegistered()) {
                    $importFailsMsg[] = $sltImportFails->getFailedImportsMessage($this->lng);
                }
                
                $message = implode('<br />', $importFailsMsg);
                
                $button = $this->DIC->ui()->factory()->button()->standard(
                    $this->DIC->language()->txt('ass_skl_import_fails_remove_btn'),
                    $this->DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'removeImportFails')
                );
                
                $msgBox = $this->DIC->ui()->factory()->messageBox()->failure($message)->withButtons(array($button));
                
                $this->populateMessage($this->DIC->ui()->renderer()->render($msgBox));
            } elseif ($this->getTestOBJ()->isSkillServiceToBeConsidered()) {
                if ($this->areSkillLevelThresholdsMissing()) {
                    $this->populateMessage($this->getSkillLevelThresholdsMissingInfo());
                }
                
                if ($this->hasFixedQuestionSetSkillAssignsLowerThanBarrier()) {
                    $this->addInfoMessage($this->getSkillAssignBarrierInfo());
                }
            }

            if ($this->getTestQuestionSetConfig()->areDepenciesBroken()) {
                $this->addFailureMessage($this->getTestQuestionSetConfig()->getDepenciesBrokenMessage($this->lng));

                $this->clearItems();
            } elseif ($this->getTestQuestionSetConfig()->areDepenciesInVulnerableState()) {
                $this->addInfoMessage($this->getTestQuestionSetConfig()->getDepenciesInVulnerableStateMessage($this->lng));
            }
        }
    }

    protected function populateMessage($message) : void
    {
        $this->DIC->ui()->mainTemplate()->setCurrentBlock('mess');
        $this->DIC->ui()->mainTemplate()->setVariable('MESSAGE', $message);
        $this->DIC->ui()->mainTemplate()->parseCurrentBlock();
    }
    
    public function sendMessages() : void
    {
        $info_messages = $this->getInfoMessages();
        if ($info_messages !== array()) {
            $this->main_tpl->setOnScreenMessage('info', array_pop($info_messages));
        }

        $failure_messages = $this->getFailureMessages();
        if ($failure_messages !== array()) {
            $this->main_tpl->setOnScreenMessage('failure', array_pop($failure_messages));
        }
    }
}
