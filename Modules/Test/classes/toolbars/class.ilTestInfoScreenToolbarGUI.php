<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestInfoScreenToolbarGUI extends ilToolbarGUI
{
    private static array $TARGET_CLASS_PATH_BASE = ['ilRepositoryGUI', 'ilObjTestGUI'];

    protected ?ilTestQuestionSetConfig $testQuestionSetConfig = null;
    protected ?ilTestSession $testSession = null;

    /**
     * @var ilTestSequence
     */
    protected $testSequence;

    private string $sessionLockString = '';
    private array $infoMessages = [];
    private array $failureMessages = [];

    public function __construct(
        private ilObjTest $test_obj,
        private ilTestPlayerAbstractGUI $test_player_gui,
        private ilTestQuestionSetConfig $test_question_set_config,
        private ilTestSession $test_session,
        private ilDBInterface $db,
        private ilAccessHandler $access,
        private ilCtrl $ctrl,
        protected ilLanguage $lng,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer,
        private ilGlobalTemplateInterface $main_tpl,
        private ilToolbarGUI $global_toolbar
    ) {
    }

    private function getTestQuestionSetConfig(): ?ilTestQuestionSetConfig
    {
        return $this->test_question_set_config;
    }

    private function getTestOBJ(): ilObjTest
    {
        return $this->test_obj;
    }

    private function getTestPlayerGUI(): ?ilTestPlayerAbstractGUI
    {
        return $this->test_player_gui;
    }

    private function getTestSession(): ?ilTestSession
    {
        return $this->test_session;
    }

    public function getSessionLockString(): ?string
    {
        return $this->sessionLockString;
    }

    public function setSessionLockString($sessionLockString): void
    {
        $this->sessionLockString = $sessionLockString;
    }

    public function getInfoMessages(): array
    {
        return $this->infoMessages;
    }

    /**
     * @ param string $infoMessage Could be. Doesn't have to.
     */
    public function addInfoMessage($infoMessage): void
    {
        $this->infoMessages[] = $infoMessage;
    }

    public function getFailureMessages(): array
    {
        return $this->failureMessages;
    }

    public function addFailureMessage(string $failureMessage): void
    {
        $this->failureMessages[] = $failureMessage;
    }

    public function setFormAction(
        string $a_val,
        bool $a_multipart = false,
        string $a_target = ""
    ): void {
        if ($this->global_toolbar instanceof parent) {
            $this->global_toolbar->setFormAction($a_val, $a_multipart, $a_target);
        } else {
            parent::setFormAction($a_val, $a_multipart, $a_target);
        }
    }

    public function setCloseFormTag(bool $a_val): void
    {
        if ($this->global_toolbar instanceof parent) {
            $this->global_toolbar->setCloseFormTag($a_val);
        } else {
            parent::setCloseFormTag($a_val);
        }
    }

    public function addInputItem(
        ilToolbarItem $a_item,
        bool $a_output_label = false
    ): void {
        if ($this->global_toolbar instanceof parent) {
            $this->global_toolbar->addInputItem($a_item, $a_output_label);
        } else {
            parent::addInputItem($a_item, $a_output_label);
        }
    }

    public function addFormInput($formInput): void
    {
        if ($this->global_toolbar instanceof parent) {
            $this->global_toolbar->addFormInput($formInput);
        }
    }

    public function clearItems(): void
    {
        if ($this->global_toolbar instanceof parent) {
            $this->global_toolbar->setItems([]);
        } else {
            $this->setItems([]);
        }
    }

    private function getClassName($target)
    {
        if (is_object($target)) {
            $target = get_class($target);
        }

        return $target;
    }

    private function getClassNameArray($target): array
    {
        if (is_array($target)) {
            return $target;
        }

        return [$this->getClassName($target)];
    }

    private function getClassPath($target): array
    {
        return array_merge(self::$TARGET_CLASS_PATH_BASE, $this->getClassNameArray($target));
    }

    private function setParameter($target, $parameter, $value): void
    {
        $this->ctrl->setParameterByClass($this->getClassName($target), $parameter, $value);
    }

    private function buildLinkTarget($target, $cmd = null): string
    {
        return $this->ctrl->getLinkTargetByClass($this->getClassPath($target), $cmd);
    }

    private function buildFormAction($target): string
    {
        return $this->ctrl->getFormActionByClass($this->getClassPath($target));
    }

    private function ensureInitialisedSessionLockString(): void
    {
        if ($this->getSessionLockString() === '') {
            $this->setSessionLockString($this->buildSessionLockString());
        }
    }

    private function buildSessionLockString(): string
    {
        return md5($_COOKIE[session_name()] . time());
    }

    private function areSkillLevelThresholdsMissing(): bool
    {
        if (!$this->getTestOBJ()->isSkillServiceEnabled()) {
            return false;
        }

        $questionContainerId = $this->getTestOBJ()->getId();

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

    private function getSkillLevelThresholdsMissingInfo(): string
    {
        $message = $this->lng->txt('tst_skl_level_thresholds_missing');

        $link_target = $this->buildLinkTarget(
            ['ilTestSkillAdministrationGUI', 'ilTestSkillLevelThresholdsGUI'],
            ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
        );

        $link = $this->ui_factory->link()->standard(
            $this->lng->txt('tst_skl_level_thresholds_link'),
            $link_target
        );

        $msg_box = $this->ui_factory->messageBox()->failure($message)->withLinks([$link]);

        return $this->ui_renderer->render($msg_box);
    }

    private function hasFixedQuestionSetSkillAssignsLowerThanBarrier(): bool
    {
        if (!$this->test_obj->isFixedTest()) {
            return false;
        }

        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->test_obj->getId());
        $assignmentList->loadFromDb();

        return $assignmentList->hasSkillsAssignedLowerThanBarrier();
    }

    private function getSkillAssignBarrierInfo(): string
    {
        return sprintf(
            $this->lng->txt('tst_skill_triggerings_num_req_answers_not_reached_warn'),
            ilObjAssessmentFolder::getSkillTriggerAnswerNumberBarrier()
        );
    }

    public function build(): void
    {
        $this->ensureInitialisedSessionLockString();

        $this->setParameter($this->getTestPlayerGUI(), 'lock', $this->getSessionLockString());
        $this->setParameter($this->getTestPlayerGUI(), 'sequence', $this->getTestSession()->getLastSequence());
        $this->setParameter('ilObjTestGUI', 'ref_id', $this->getTestOBJ()->getRefId());

        $this->setFormAction($this->buildFormAction($this->getTestPlayerGUI()));

        if (!$this->getTestOBJ()->getOfflineStatus() && $this->getTestOBJ()->isComplete($this->getTestQuestionSetConfig())) {
            if ($this->access->checkAccess("read", "", $this->getTestOBJ()->getRefId())) {
                $executable = $this->getTestOBJ()->isExecutable(
                    $this->getTestSession(),
                    $this->getTestSession()->getUserId(),
                    true
                );

                if ($executable['executable'] && $this->getTestOBJ()->areObligationsEnabled() && $this->getTestOBJ()->hasObligations()) {
                    $this->addInfoMessage($this->lng->txt('tst_test_contains_obligatory_questions'));
                }
            }
        }
        if ($this->getTestOBJ()->getOfflineStatus() && !$this->getTestQuestionSetConfig()->areDepenciesBroken()) {
            $message = $this->lng->txt("test_is_offline");

            $links = [];

            if ($this->access->checkAccess("write", "", $this->getTestOBJ()->getRefId())) {
                $links[] = $this->ui_factory->link()->standard(
                    $this->lng->txt('test_edit_settings'),
                    $this->buildLinkTarget('ilobjtestsettingsmaingui')
                );
            }

            $msgBox = $this->ui_factory->messageBox()->info($message)->withLinks($links);

            $this->populateMessage($this->ui_renderer->render($msgBox));
        }

        if ($this->access->checkAccess("write", "", $this->getTestOBJ()->getRefId())) {
            $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->test_obj->getId());
            $sltImportFails = new ilTestSkillLevelThresholdImportFails($this->test_obj->getId());

            if ($qsaImportFails->failedImportsRegistered() || $sltImportFails->failedImportsRegistered()) {
                $importFailsMsg = [];

                if ($qsaImportFails->failedImportsRegistered()) {
                    $importFailsMsg[] = $qsaImportFails->getFailedImportsMessage($this->lng);
                }

                if ($sltImportFails->failedImportsRegistered()) {
                    $importFailsMsg[] = $sltImportFails->getFailedImportsMessage($this->lng);
                }

                $message = implode('<br />', $importFailsMsg);

                $button = $this->ui_factory->button()->standard(
                    $this->lng->txt('ass_skl_import_fails_remove_btn'),
                    $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'removeImportFails')
                );

                $msgBox = $this->ui_factory->messageBox()->failure($message)->withButtons([$button]);

                $this->populateMessage($this->ui_renderer->render($msgBox));
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

    protected function populateMessage($message): void
    {
        $this->main_tpl->setCurrentBlock('mess');
        $this->main_tpl->setVariable('MESSAGE', $message);
        $this->main_tpl->parseCurrentBlock();
    }

    public function sendMessages(): void
    {
        $info_messages = $this->getInfoMessages();
        if ($info_messages !== []) {
            $this->main_tpl->setOnScreenMessage('info', array_pop($info_messages));
        }

        $failure_messages = $this->getFailureMessages();
        if ($failure_messages !== []) {
            $this->main_tpl->setOnScreenMessage('failure', array_pop($failure_messages));
        }
    }
}
