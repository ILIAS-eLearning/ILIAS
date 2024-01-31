<?php

declare(strict_types=1);

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
 ********************************************************************
 */

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * BlockGUI class for polls.
 *
 * @author Jörg Lützenkirchen
 * @ilCtrl_IsCalledBy ilPollBlockGUI: ilColumnGUI
 */
class ilPollBlockGUI extends ilBlockGUI
{
    public static string $block_type = "poll";
    protected ilPollBlock $poll_block;
    public static bool $js_init = false;
    protected bool $new_rendering = true;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilPollStateInfo $state;
    protected ilPollCommentsHandler $comments;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        parent::__construct();

        $this->lng->loadLanguageModule("poll");
        $this->setRowTemplate("tpl.block.html", "components/ILIAS/Poll");

        $this->comments = new ilPollCommentsHandler(
            $DIC->notes(),
            $DIC->http(),
            $DIC->refinery(),
            $this->getCommentsRedrawURL()
        );
        $this->state = new ilPollStateInfo();
    }

    public function getBlockType(): string
    {
        return self::$block_type;
    }

    protected function isRepositoryObject(): bool
    {
        return true;
    }

    protected function getRepositoryObjectGUIName(): string
    {
        return "ilobjpollgui";
    }

    public function setBlock(ilPollBlock $a_block): void
    {
        $this->setBlockId((string) $a_block->getId());
        $this->poll_block = $a_block;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function fillRow(array $a_set): void
    {
        if ($this->poll_block->getPoll()->getShowComments()) {
            $this->initJS();
        }

        $this->initContentRenderer()->render(
            $this->tpl,
            $this->getRefId(),
            $this->user->getId(),
            $this->poll_block->getPoll(),
            $this->getAdminCommands()
        );
    }

    public function getHTML(): string
    {
        $this->poll_block->setRefId($this->getRefId());
        $may_write = $this->access->checkAccess("write", "", $this->getRefId());

        $poll_obj = $this->poll_block->getPoll();
        $this->setTitle($poll_obj->getTitle());
        $this->setData([[$poll_obj]]);

        $this->ctrl->setParameterByClass(
            $this->getRepositoryObjectGUIName(),
            "ref_id",
            $this->getRefId()
        );

        if (
            !$this->state->isOfflineOrUnavailable($this->poll_block->getPoll()) &&
            !$this->user->isAnonymous()
        ) {
            // notification
            if (ilNotification::hasNotification(ilNotification::TYPE_POLL, $this->user->getId(), $this->poll_block->getPoll()->getId())) {
                $this->addBlockCommand(
                    $this->ctrl->getLinkTargetByClass(
                        array("ilrepositorygui", $this->getRepositoryObjectGUIName()),
                        "unsubscribe"
                    ),
                    $this->lng->txt("poll_notification_unsubscribe")
                );
            } else {
                $this->addBlockCommand(
                    $this->ctrl->getLinkTargetByClass(
                        array("ilrepositorygui", $this->getRepositoryObjectGUIName()),
                        "subscribe"
                    ),
                    $this->lng->txt("poll_notification_subscribe")
                );
            }
        }

        if ($may_write) {
            // edit
            $this->addBlockCommand(
                $this->ctrl->getLinkTargetByClass(
                    array("ilrepositorygui", $this->getRepositoryObjectGUIName()),
                    "render"
                ),
                $this->lng->txt("poll_edit_question")
            );
            $this->addBlockCommand(
                $this->ctrl->getLinkTargetByClass(
                    array("ilrepositorygui", $this->getRepositoryObjectGUIName()),
                    "edit"
                ),
                $this->lng->txt("settings")
            );
            $this->addBlockCommand(
                $this->ctrl->getLinkTargetByClass(
                    array("ilrepositorygui", $this->getRepositoryObjectGUIName()),
                    "showParticipants"
                ),
                $this->lng->txt("poll_result")
            );
        }

        $this->ctrl->clearParametersByClass($this->getRepositoryObjectGUIName());

        return parent::getHTML();
    }

    protected function initContentRenderer(): ilPollContentRenderer
    {
        $answers = new ilPollAnswersHandler(
            $this->poll_block->getPoll(),
            $this->getVoteURL(),
            'vote'
        );
        $results = new ilPollResultsHandler(
            $this->poll_block->getPoll(),
            $answers
        );
        return new ilPollContentRenderer(
            $this->lng,
            $this->ui_factory,
            $this->ui_renderer,
            $this->state,
            $this->comments,
            $answers,
            new ilPollAnswersRenderer($this->lng),
            $results,
            new ilPollResultsRenderer($this->getRefId())
        );
    }

    protected function initJS(): void
    {
        if (!self::$js_init) {
            $this->main_tpl->addJavaScript("assets/js/ilPoll.js");
            self::$js_init = true;
        }
    }

    protected function getCommentsRedrawURL(): string
    {
        return $this->ctrl->getLinkTarget(
            $this,
            "getNumberOfCommentsForRedraw",
            "",
            true
        );
    }

    protected function getVoteURL(): string
    {
        $this->ctrl->setParameterByClass(
            $this->getRepositoryObjectGUIName(),
            "ref_id",
            $this->getRefId()
        );
        $url = $this->ctrl->getLinkTargetByClass(
            array("ilrepositorygui", $this->getRepositoryObjectGUIName()),
            "vote"
        );
        $this->ctrl->clearParametersByClass($this->getRepositoryObjectGUIName());

        return $url .= "#poll" . $this->poll_block->getPoll()->getID();
    }

    public function getNumberOfCommentsForRedraw(): void
    {
        $this->comments->getNumberOfCommentsForRedraw();
    }

    public function fillDataSection(): void
    {
        $this->setDataSection($this->getLegacyContent());
    }

    //
    // New rendering
    //

    protected function getLegacyContent(): string
    {
        $this->tpl = new ilTemplate(
            $this->getRowTemplateName(),
            true,
            true,
            $this->getRowTemplateDir()
        );
        $this->fillRow(current($this->getData()));
        return $this->tpl->get();
    }
}
