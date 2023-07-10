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

use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Button\Standard as StandardButton;

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetConfigStateMessageHandler
{
    protected UIServices $ui;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilTestRandomQuestionSetConfigGUI $targetGUI;

    public const CONTEXT_GENERAL_CONFIG = 'generalConfigContext';
    public const CONTEXT_POOL_SELECTION = 'poolSelectionContext';

    protected string $context;
    protected bool $participantDataExists;

    /**
     * @var ilTestRandomQuestionSetNonAvailablePool[]
     */
    protected array $lostPools;

    protected ilTestRandomQuestionSetConfig $questionSetConfig;

    protected bool $validationFailed = false;
    protected array $validationReports = [];
    protected string $sync_info_message = '';

    public function __construct(
        ilLanguage $lng,
        UIServices $ui,
        ilCtrl $ctrl
    ) {
        $this->lng = $lng;
        $this->ui = $ui;
        $this->ctrl = $ctrl;
        $this->validationFailed = false;
        $this->validationReports = array();
    }

    /**
     * @return ilTestRandomQuestionSetNonAvailablePool[]
     */
    public function getLostPools(): array
    {
        return $this->lostPools;
    }

    /**
     * @param ilTestRandomQuestionSetNonAvailablePool[] $lostPools
     */
    public function setLostPools(array $lostPools): void
    {
        $this->lostPools = $lostPools;
    }

    /**
     * @return boolean
     */
    public function doesParticipantDataExists(): bool
    {
        return $this->participantDataExists;
    }

    public function setParticipantDataExists(bool $participantDataExists): void
    {
        $this->participantDataExists = $participantDataExists;
    }

    public function getTargetGUI(): ilTestRandomQuestionSetConfigGUI
    {
        return $this->targetGUI;
    }

    public function setTargetGUI(ilTestRandomQuestionSetConfigGUI $targetGUI)
    {
        $this->targetGUI = $targetGUI;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function setContext(string $context)
    {
        $this->context = $context;
    }

    public function getQuestionSetConfig(): ilTestRandomQuestionSetConfig
    {
        return $this->questionSetConfig;
    }

    public function setQuestionSetConfig(ilTestRandomQuestionSetConfig $questionSetConfig)
    {
        $this->questionSetConfig = $questionSetConfig;
    }

    public function isValidationFailed(): bool
    {
        return $this->validationFailed;
    }

    protected function setValidationFailed(bool $validationFailed)
    {
        $this->validationFailed = $validationFailed;
    }

    public function getValidationReportHtml(): string
    {
        return implode('<br />', $this->validationReports);
    }

    public function hasValidationReports(): int
    {
        return count($this->validationReports);
    }

    protected function addValidationReport(string $validationReport)
    {
        $this->validationReports[] = $validationReport;
    }

    public function getSyncInfoMessage(): string
    {
        return $this->sync_info_message;
    }

    protected function setSyncInfoMessage(string $message): void
    {
        $this->sync_info_message = $message;
    }

    public function handle(): void
    {
        if ($this->isNoAvailableQuestionPoolsHintRequired()) {
            $this->addValidationReport($this->lng->txt('tst_msg_rand_quest_set_no_pools_available'));
        } elseif ($this->getLostPools()) {
            $this->setSyncInfoMessage($this->buildLostPoolsReportMessage());
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
        } elseif ($this->questionSetConfig->getLastQuestionSyncTimestamp() === 0 ||
            $this->questionSetConfig->getLastQuestionSyncTimestamp() === null) {
            $this->setSyncInfoMessage($this->buildNotSyncedMessage());
        } elseif (!$this->questionSetConfig->isQuestionSetBuildable()) {
            $this->setValidationFailed(true);
            $this->addValidationReport($this->lng->txt('tst_msg_rand_quest_set_pass_not_buildable'));
            $this->addValidationReport(implode('<br />', $this->questionSetConfig->getBuildableMessages()));
        } elseif ($this->questionSetConfig->getLastQuestionSyncTimestamp()) {
            $this->setSyncInfoMessage($this->buildLastSyncMessage());
        }
    }

    private function buildLostQuestionPoolsString(): string
    {
        $titles = array();

        foreach ($this->getLostPools() as $lostPool) {
            $titles[] = $lostPool->getTitle();
        }

        return implode(', ', $titles);
    }

    private function getAfterRebuildQuestionStageCommand(): string
    {
        switch ($this->getContext()) {
            case self::CONTEXT_POOL_SELECTION:

                return ilTestRandomQuestionSetConfigGUI::CMD_SHOW_SRC_POOL_DEF_LIST;

            case self::CONTEXT_GENERAL_CONFIG:
            default:

                return ilTestRandomQuestionSetConfigGUI::CMD_SHOW_GENERAL_CONFIG_FORM;
        }
    }

    private function buildQuestionStageRebuildButton(): StandardButton
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

        return $this->ui->factory()->button()->standard($label, $href)->withLoadingAnimationOnClick(true);
    }

    private function buildGeneralConfigSubTabLink(): string
    {
        $href = $this->ctrl->getLinkTarget(
            $this->getTargetGUI(),
            ilTestRandomQuestionSetConfigGUI::CMD_SHOW_GENERAL_CONFIG_FORM
        );

        $label = $this->lng->txt('tst_rnd_quest_cfg_tab_general');

        return "<a href=\"{$href}\">{$label}</a>";
    }

    private function buildQuestionSelectionSubTabLink(): string
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
    private function isNoAvailableQuestionPoolsHintRequired(): bool
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
    private function isQuestionAmountConfigPerPoolHintRequired(): bool
    {
        if ($this->getContext() != self::CONTEXT_GENERAL_CONFIG) {
            return false;
        }

        if (!$this->questionSetConfig->isQuestionAmountConfigurationModePerPool()) {
            return false;
        }

        return true;
    }

    private function isQuestionAmountConfigPerTestHintRequired(): bool
    {
        if ($this->getContext() != self::CONTEXT_POOL_SELECTION) {
            return false;
        }

        if (!$this->questionSetConfig->isQuestionAmountConfigurationModePerTest()) {
            return false;
        }

        return true;
    }

    protected function buildLostPoolsReportMessage(): string
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

            $link = $this->ui->factory()->link()->standard(
                $this->lng->txt('tst_msg_rand_quest_set_lost_pools_link'),
                $action
            );

            $msg_box = $this->ui->factory()->messageBox()->info($report)->withLinks(array($link));
        } else {
            $msg_box = $this->ui->factory()->messageBox()->info($report);
        }

        return $this->ui->renderer()->render($msg_box);
    }

    protected function buildLastSyncMessage(): string
    {
        $sync_date = new ilDateTime(
            $this->questionSetConfig->getLastQuestionSyncTimestamp(),
            IL_CAL_UNIX
        );
        $message = sprintf(
            $this->lng->txt('tst_msg_rand_quest_set_stage_pool_last_sync'),
            ilDatePresentation::formatDate($sync_date)
        );
        if ($this->doesParticipantDataExists()) {
            $message .= '<br>' . $this->lng->txt('tst_msg_cannot_modify_random_question_set_conf_due_to_part');

            $msg_box = $this->ui->factory()->messageBox()->info($message);
        } else {
            $href = $this->ctrl->getLinkTargetByClass(ilTestRandomQuestionSetConfigGUI::class, ilTestRandomQuestionSetConfigGUI::CMD_RESET_POOLSYNC);
            $label = $this->lng->txt('tst_btn_reset_pool_sync');

            $buttons = [
                $this->ui->factory()->button()->standard($label, $href)
            ];

            $msg_box = $this->ui->factory()->messageBox()
            ->info($message)
            ->withButtons($buttons);
        }

        return $this->ui->renderer()->render($msg_box);
    }

    protected function buildNotSyncedMessage(): string
    {
        $message = $this->lng->txt('tst_msg_rand_quest_set_not_sync');
        $button = $this->buildQuestionStageRebuildButton();
        $msg_box = $this->ui->factory()->messageBox()->info($message)->withButtons(array($button));

        return $this->ui->renderer()->render($msg_box);
    }
}
