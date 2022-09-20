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

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilLearningModuleKioskModeView
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModuleKioskModeView extends ilKioskModeView
{
    public const CMD_TOGGLE_LEARNING_PROGRESS = 'toggleManualLearningProgress';
    protected ilPageObject $contentPageObject;

    protected ilObjLearningModule $lm;
    protected ilLMPresentationService $lm_pres_service;
    protected ?ilLMPresentationGUI $lm_pres = null;
    protected ilObjUser $user;
    protected Factory $uiFactory;
    protected Renderer $uiRenderer;
    protected ilGlobalTemplateInterface $mainTemplate;
    protected ServerRequestInterface $httpRequest;
    protected ilTabsGUI $tabs;
    protected array $messages = [];
    protected ?int $current_page_id = 0;
    protected array $additional_content = [];

    protected function getObjectClass(): string
    {
        return \ilObjLearningModule::class;
    }

    protected function setObject(\ilObject $object): void
    {
        global $DIC;

        /** @var ilObjLearningModule $object */
        $this->lm = $object;
        $this->ctrl = $DIC->ctrl();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->httpRequest = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
    }

    public function updateGet(
        State $state,
        string $command,
        int $parameter = null
    ): State {
        switch ($command) {
            case "layout":
                if ($parameter > 0) {
                    $this->current_page_id = $parameter;
                    $state = $state->withValueFor("current_page", (string) $this->current_page_id);
                }
                break;
            case self::CMD_TOGGLE_LEARNING_PROGRESS:
                $this->toggleLearningProgress($command);
                break;
        }

        $this->initLMService($this->current_page_id);

        return $state;
    }

    // Init learning module presentation service
    protected function initLMService(?int $current_page): void
    {
        if (is_object($this->lm_pres)) {
            return;
        }
        $this->lm_pres = new ilLMPresentationGUI(
            "",
            false,
            "",
            false,
            ["ref_id" => $this->lm->getRefId(),
             "obj_id" => (int) $current_page],
            true
        );

        $this->lm_pres_service = $this->lm_pres->getService();
    }

    protected function hasPermissionToAccessKioskMode(): bool
    {
        return $this->access->checkAccess('read', '', $this->lm->getRefId());
    }

    public function buildInitialState(State $empty_state): State
    {
        return $empty_state->withValueFor("current_page", "");
    }

    public function buildControls(
        State $state,
        ControlBuilder $builder
    ): ControlBuilder {
        global $DIC;

        $main_tpl = $DIC->ui()->mainTemplate();

        // this may be necessary if updateGet has not been processed

        // THIS currently fails
        $this->initLMService($state->getValueFor("current_page"));
        $nav_stat = $this->lm_pres_service->getNavigationStatus();

        // next
        $succ_id = $nav_stat->getSuccessorPageId();
        if ($succ_id > 0) {
            $builder->next("layout", $succ_id);
        }

        // previous
        $prev_id = $nav_stat->getPredecessorPageId();
        if ($prev_id > 0) {
            $builder->previous("layout", $prev_id);
        }

        $toc = $builder->tableOfContent($this->lm->getTitle(), 'layout', 0);
        $lm_toc_renderer = new ilLMSlateTocRendererGUI($this->lm_pres_service);
        $lm_toc_renderer->renderLSToc($toc);

        // learning progress
        $builder = $this->maybeBuildLearningProgressToggleControl($builder);

        // menu
        $menu = new \ILIAS\LearningModule\Menu\ilLMMenuGUI($this->lm_pres_service);
        foreach ($menu->getEntries() as $entry) {
            if (is_object($entry["signal"])) {
                $builder = $builder->genericWithSignal(
                    $entry["label"],
                    $entry["signal"]
                );
            }
            if (is_object($entry["modal"])) {
                $this->additional_content[] = $entry["modal"];
            }
            if ($entry["on_load"] != "") {
                $main_tpl->addOnLoadCode($entry["on_load"]);
            }
        }

        //$builder = $this->addPrintViewSelectionMenuButton($builder);

        return $builder;
    }


    protected function maybeBuildLearningProgressToggleControl(
        ControlBuilder $builder
    ): ControlBuilder {
        $learningProgress = \ilObjectLP::getInstance($this->lm->getId());
        if ($learningProgress->getCurrentMode() == \ilLPObjSettings::LP_MODE_MANUAL) {
            $isCompleted = \ilLPMarks::_hasCompleted($this->user->getId(), $this->lm->getId());

            $this->lng->loadLanguageModule('lm');
            $learningProgressToggleCtrlLabel = $this->lng->txt('lm_btn_lp_toggle_state_completed');
            if (!$isCompleted) {
                $learningProgressToggleCtrlLabel = $this->lng->txt('lm_btn_lp_toggle_state_not_completed');
            }
            $builder = $builder->generic(
                $learningProgressToggleCtrlLabel,
                self::CMD_TOGGLE_LEARNING_PROGRESS,
                1
            );
        }
        return $builder;
    }

    protected function toggleLearningProgress(
        string $command
    ): void {
        if (self::CMD_TOGGLE_LEARNING_PROGRESS === $command) {
            $learningProgress = \ilObjectLP::getInstance($this->lm->getId());
            if ($learningProgress->getCurrentMode() == \ilLPObjSettings::LP_MODE_MANUAL) {
                $marks = new \ilLPMarks($this->lm->getId(), $this->user->getId());
                $marks->setCompleted(!$marks->getCompleted());
                $marks->update();

                \ilLPStatusWrapper::_updateStatus($this->lm->getId(), $this->user->getId());

                $this->lng->loadLanguageModule('trac');
                $this->messages[] = $this->uiFactory->messageBox()->success(
                    $this->lng->txt('trac_updated_status')
                );
            }
        }
    }

    public function updatePost(
        State $state,
        string $command,
        array $post
    ): State {
        return $state;
    }

    public function render(
        State $state,
        Factory $factory,
        URLBuilder $url_builder,
        array $post = null
    ): Component {
        $this->ctrl->setParameterByClass("illmpresentationgui", 'ref_id', $this->lm->getRefId());
        $content = $this->uiRenderer->render($this->messages);
        // @todo Check non-existence of third parameter (existed in ILIAS 7)
        $content .= $this->ctrl->getHTML($this->lm_pres, ["cmd" => "layout"], ["illmpresentationgui"]);
        $content .= $this->uiRenderer->render($this->additional_content);
        return $factory->legacy($content);
    }
}
