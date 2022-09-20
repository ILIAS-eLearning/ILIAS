<?php declare(strict_types=1);

/**
 * Class ilObjLearningSequenceLearnerGUI
 */
class ilObjLearningSequenceLearnerGUI
{
    const CMD_STANDARD = 'learnerView';
    const CMD_EXTRO = 'learnerViewFinished';
    const CMD_UNSUBSCRIBE = 'unsubscribe';
    const CMD_VIEW = 'view';
    const CMD_START = 'start';
    const PARAM_LSO_NEXT_ITEM = 'lsoni';
    const LSO_CMD_NEXT = 'lson';
    const LSO_CMD_PREV = 'lsop';

    public function __construct(
        int $ls_ref_id,
        $first_access,
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
        ilLSPlayer $player
    ) {
        $this->ls_object = $ls_object;
        $this->ls_ref_id = $ls_ref_id;
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
    }

    public function executeCommand()
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

            case LSControlBuilder::CMD_CHECK_CURRENT_ITEM_LP:
                $this->getCurrentItemLearningProgress();

                // no break
            default:
                throw new ilException(
                    "ilObjLearningSequenceLearnerGUI: " .
                    "Command not supported: $cmd"
                );
        }
    }

    protected function view(string $cmd)
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

    protected function addMember(int $usr_id)
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

    protected function initToolbar(string $cmd)
    {
        $is_member = $this->roles->isMember($this->usr_id);
        $completed = $this->roles->isCompletedByUser($this->usr_id);

        if (!$is_member) {
            $may_subscribe = $this->userMayJoin();
            if ($may_subscribe) {
                $sub_button = ilLinkButton::getInstance();
                $sub_button->setPrimary(true);
                $sub_button->setCaption("lso_player_start");
                $sub_button->setUrl($this->ctrl->getLinkTarget($this,self::CMD_START));
                $this->toolbar->addButtonInstance($sub_button);
            }
        } else {
            if (!$completed) {
                $res_button = ilLinkButton::getInstance();
                $res_button->setPrimary(true);
                $res_button->setCaption("lso_player_resume");
                if ($this->first_access === -1) {
                    $res_button->setCaption("lso_player_start");
                }
                $res_button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_VIEW));
                $this->toolbar->addButtonInstance($res_button);
            } else {
                $review_button = ilLinkButton::getInstance();
                $review_button->setCaption("lso_player_review");
                $review_button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_VIEW));
                $this->toolbar->addButtonInstance($review_button);

                if ($cmd === self::CMD_STANDARD) {
                    $button = ilLinkButton::getInstance();
                    $button->setCaption("lso_player_extro");
                    $button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_EXTRO));
                    $this->toolbar->addButtonInstance($button);
                }
                if ($cmd === self::CMD_EXTRO) {
                    $button = ilLinkButton::getInstance();
                    $button->setCaption("lso_player_abstract");
                    $button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_STANDARD));
                    $this->toolbar->addButtonInstance($button);
                }
            }

            $may_unsubscribe = $this->userMayUnparticipate();
            if ($may_unsubscribe) {
                $unsub_button = ilLinkButton::getInstance();
                $unsub_button->setCaption("unparticipate");
                $unsub_button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_UNSUBSCRIBE));
                $this->toolbar->addButtonInstance($unsub_button);
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

    private function getMainContent(string $cmd) : array
    {
        if ($cmd === self::CMD_STANDARD) {
            $txt = $this->settings->getAbstract();
            $img = $this->settings->getAbstractImage();
        }

        if ($cmd === self::CMD_EXTRO) {
            $txt = $this->settings->getExtro();
            $img = $this->settings->getExtroImage();
        }

        $contents = [$this->ui_factory->legacy($txt)];
        if (!is_null($img)) {
            $contents[] = $this->ui_factory->image()->responsive($img, '');
        }

        return $contents;
    }

    protected function play()
    {
        $response = $this->player->play($_GET, $_POST);

        switch ($response) {
            case null:
                $this->tpl->enableDragDropFileUpload(null);
                $this->tpl->setContent('THIS SHOULD NOT SHOW');
                return;
            
            case ilLSPlayer::RET_NOITEMS:
                \ilUtil::sendInfo($this->lng->txt('container_no_items'));
                $this->tpl->setContent('');
                return;

            case ilLSPlayer::RET_EXIT . ilLSPlayer::LSO_CMD_FINISH:
                $cmd = self::CMD_EXTRO;
                break;

            case ilLSPlayer::RET_EXIT . ilLSPlayer::LSO_CMD_SUSPEND:
            default:
                $cmd = self::CMD_STANDARD;
                break;
        }
        $href = $this->ctrl->getLinkTarget($this, $cmd, '', false, false);
        \ilUtil::redirect($href);
    }

    protected function getCurrentItemLearningProgress()
    {
        print $this->player->getCurrentItemLearningProgress();
        exit;
    }
}
