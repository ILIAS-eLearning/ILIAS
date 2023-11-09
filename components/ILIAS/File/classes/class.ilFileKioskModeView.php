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

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class ilFileKioskModeView extends ilKioskModeView
{
    private const CMD_TOGGLE_LEARNING_PROGRESS = 'toggleManualLearningProgress';

    protected ilObjFile $file_obj;
    protected ilObjUser $user;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilGlobalTemplateInterface $main_template;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ilTabsGUI $tabs;
    /** @var MessageBox[] */
    protected array $messages = [];

    protected function getObjectClass(): string
    {
        return ilObjFile::class;
    }

    protected function setObject(ilObject $object): void
    {
        global $DIC;

        /** @var ilObjFile $object */
        $this->file_obj = $object;

        $this->ctrl = $DIC->ctrl();
        $this->main_template = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
    }

    protected function hasPermissionToAccessKioskMode(): bool
    {
        return $this->access->checkAccess('read', '', $this->file_obj->getRefId());
    }

    public function buildInitialState(State $empty_state): State
    {
        return $empty_state;
    }

    public function buildControls(State $state, ControlBuilder $builder): void
    {
        $learning_progress = \ilObjectLP::getInstance($this->file_obj->getId());
        if ($learning_progress->getCurrentMode(
        ) === \ilLPObjSettings::LP_MODE_MANUAL) { //TODO: create and implement handling for second lp mode
            $this->buildLearningProgressToggleControl($builder);
        }
    }

    public function updateGet(State $state, string $command, int $parameter = null): State
    {
        if ($command === self::CMD_TOGGLE_LEARNING_PROGRESS) {
            $this->toggleLearningProgress();
        }
        return $state;
    }

    public function updatePost(State $state, string $command, array $post): State
    {
        return $state;
    }

    public function render(State $state, Factory $factory, URLBuilder $url_builder, array $post = null): Component
    {
        $file_gui = new ilObjFileGUI($this->file_obj->getRefId());
        return $factory->legacy($file_gui->buildInfoScreen(true)->getHTML());
    }

    protected function buildLearningProgressToggleControl(ControlBuilder $builder): ControlBuilder
    {
        $this->lng->loadLanguageModule('file');
        if (ilLPStatus::_hasUserCompleted($this->file_obj->getId(), $this->user->getId())) {
            $learning_progress_toggle_ctrl_label = $this->lng->txt('file_btn_lp_toggle_state_completed');
        } else {
            $learning_progress_toggle_ctrl_label = $this->lng->txt('file_btn_lp_toggle_state_not_completed');
        }

        return $builder->generic(
            $learning_progress_toggle_ctrl_label,
            self::CMD_TOGGLE_LEARNING_PROGRESS,
            1
        );
    }

    protected function toggleLearningProgress(): void
    {
        if (!ilLPStatus::_hasUserCompleted($this->file_obj->getId(), $this->user->getId())) {
            ilLPStatus::writeStatus(
                $this->file_obj->getId(),
                $this->user->getId(),
                ilLPStatus::LP_STATUS_COMPLETED_NUM
            );
        } else {
            ilLPStatus::writeStatus(
                $this->file_obj->getId(),
                $this->user->getId(),
                ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
            );
        }
    }
}
