<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilObjLearningSequenceLearnerGUI
{
    const CMD_STANDARD = 'learnerView';
    const CMD_EXTRO = 'learnerViewFinished';
    const CMD_UNSUBSCRIBE = 'unsubscribe';
    const CMD_VIEW = 'view';
    const CMD_START = 'start';

    protected int $ls_ref_id;
    protected bool $has_items;
    protected string $first_access;
    protected int $usr_id;
    protected ilAccess $access;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $renderer;
    protected ilLearningSequenceRoles $roles;
    protected ilLearningSequenceSettings $settings;
    protected ilLSCurriculumBuilder $curriculum_builder;
    protected ilLSPlayer $player;
    protected ArrayAccess $get;

    public function __construct(
        int $ls_ref_id,
        bool $has_items,
        string $first_access,
        int $usr_id,
        ilAccess $access,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        ILIAS\UI\Factory $ui_factory,
        ILIAS\UI\Renderer $ui_renderer,
        ilLearningSequenceRoles $roles,
        ilLearningSequenceSettings $settings,
        ilLSCurriculumBuilder $curriculum_builder,
        ilLSPlayer $player,
        ArrayAccess $get
    ) {
        $this->ls_ref_id = $ls_ref_id;
        $this->has_items = $has_items;
        $this->first_access = $first_access;
        $this->usr_id = $usr_id;
        $this->access = $access;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->ui_factory = $ui_factory;
        $this->renderer = $ui_renderer;
        $this->roles = $roles;
        $this->settings = $settings;
        $this->curriculum_builder = $curriculum_builder;
        $this->player = $player;
        $this->get = $get;
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_STANDARD:
            case self::CMD_EXTRO:
                $this->view($cmd);
                break;
            case self::CMD_START:
                $this->addMember($this->usr_id);
                $this->ctrl->redirect($this, self::CMD_VIEW);
                break;
            case self::CMD_UNSUBSCRIBE:
                if ($this->userMayUnparticipate()) {
                    $this->roles->leave($this->usr_id);
                }
                $this->ctrl->redirect($this, self::CMD_STANDARD);
                break;
            case self::CMD_VIEW:
                $this->play();
                break;

            default:
                throw new ilException(
                    "ilObjLearningSequenceLearnerGUI: " .
                    "Command not supported: $cmd"
                );
        }
    }

    protected function view(string $cmd) : void
    {
        $this->initToolbar($cmd);

        $content = $this->getMainContent($cmd);
        $this->tpl->setContent(
            $this->getWrappedHTML($content)
        );

        $curriculum = $this->curriculum_builder->getLearnerCurriculum();
        if (count($curriculum->getSteps()) > 0) {
            $this->tpl->setRightContent(
                $this->getWrappedHTML([$curriculum])
            );
        }
    }

    protected function addMember(int $usr_id) : void
    {
        $admins = $this->roles->getLearningSequenceAdminIds();
        if (!in_array($usr_id, $admins)) {
            $this->roles->join($usr_id);
        }
    }

    protected function userMayUnparticipate() : bool
    {
        return $this->access->checkAccess('unparticipate', '', $this->ls_ref_id);
    }

    protected function userMayJoin() : bool
    {
        return $this->access->checkAccess('participate', '', $this->ls_ref_id);
    }

    protected function initToolbar(string $cmd) : void
    {
        $is_member = $this->roles->isMember($this->usr_id);
        $completed = $this->roles->isCompletedByUser($this->usr_id);
        $has_items = $this->has_items;

        if (!$is_member) {
            if ($has_items) {
                $may_subscribe = $this->userMayJoin();
                if ($may_subscribe) {
                    $this->toolbar->addButton(
                        $this->lng->txt("lso_player_start"),
                        $this->ctrl->getLinkTarget($this, self::CMD_START)
                    );
                }
            }
        } else {
            if (!$completed) {
                if ($has_items) {
                    $label = "lso_player_resume";
                    if ($this->first_access === '-1') {
                        $label = "lso_player_start";
                    }

                    $this->toolbar->addButton(
                        $this->lng->txt($label),
                        $this->ctrl->getLinkTarget($this, self::CMD_VIEW)
                    );
                }
            } else {
                if ($has_items) {
                    $this->toolbar->addButton(
                        $this->lng->txt("lso_player_review"),
                        $this->ctrl->getLinkTarget($this, self::CMD_VIEW)
                    );
                }
                if ($cmd === self::CMD_STANDARD) {
                    $this->toolbar->addButton(
                        $this->lng->txt("lso_player_extro"),
                        $this->ctrl->getLinkTarget($this, self::CMD_EXTRO)
                    );
                }
                if ($cmd === self::CMD_EXTRO) {
                    $this->toolbar->addButton(
                        $this->lng->txt("lso_player_abstract"),
                        $this->ctrl->getLinkTarget($this, self::CMD_STANDARD)
                    );
                }
            }

            $may_unsubscribe = $this->userMayUnparticipate();
            if ($may_unsubscribe) {
                $this->toolbar->addButton(
                    $this->lng->txt("unparticipate"),
                    $this->ctrl->getLinkTarget($this, self::CMD_UNSUBSCRIBE)
                );
            }
        }
    }

    private function getWrappedHTML(array $components) : string
    {
        array_unshift(
            $components,
            $this->ui_factory->legacy('<div class="ilLSOLearnerView">')
        );
        $components[] = $this->ui_factory->legacy('</div>');

        return $this->renderer->render($components);
    }

    /**
     * @return array<mixed>
     */
    private function getMainContent(string $cmd) : array
    {
        $txt = '';
        $img = '';

        if ($cmd === self::CMD_STANDARD) {
            $txt = $this->settings->getAbstract();
            $img = $this->settings->getAbstractImage();
        }

        if ($cmd === self::CMD_EXTRO) {
            $txt = $this->settings->getExtro();
            $img = $this->settings->getExtroImage();
        }

        $contents = [$this->ui_factory->legacy($txt)];
        if ($img !== '') {
            $contents[] = $this->ui_factory->image()->responsive($img, '');
        }

        return $contents;
    }

    protected function play() : void
    {
        $response = $this->player->play($this->get);

        switch ($response) {
            case null:
                //render the page
                $this->tpl->setFileUploadRefId(null);
                $this->tpl->setContent('THIS SHOULD NOT SHOW');
                return;

            case 'EXIT::' . $this->player::LSO_CMD_FINISH:
                $cmd = self::CMD_EXTRO;
                break;

            case 'EXIT::' . $this->player::LSO_CMD_SUSPEND:
            default:
                $cmd = self::CMD_STANDARD;
                break;
        }
        $href = $this->ctrl->getLinkTarget($this, $cmd, '', false, false);
        \ilUtil::redirect($href);
    }
}
