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

use ILIAS\Container\Content\ViewManager;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\Notes\Note;

/**
 * BlockGUI class for polls.
 *
 * @author Jörg Lützenkirchen
 * @ilCtrl_IsCalledBy ilPollBlockGUI: ilColumnGUI
 */
class ilPollBlockGUI extends ilBlockGUI
{
    public static string $block_type = "poll";
    protected \ILIAS\Notes\Service $notes;
    protected ilPollBlock $poll_block;
    public static bool $js_init = false;
    protected ViewManager $container_view_manager;
    /**
     * @var bool
     */
    protected bool $new_rendering = true;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->access = $DIC->access();

        parent::__construct();

        $this->lng->loadLanguageModule("poll");
        $this->setRowTemplate("tpl.block.html", "Modules/Poll");

        $this->container_view_manager = $DIC
            ->container()
            ->internal()
            ->domain()
            ->content()
            ->view();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->notes = $DIC->notes();
    }

    public function getBlockType(): string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
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
        // todo: Refactoring needed
        $a_set = $a_set[0];

        // handle messages

        $mess = $this->poll_block->getMessage($this->user->getId());
        if ($mess) {
            $this->tpl->setVariable("TXT_QUESTION", $mess);
            return;
        }


        // nested form problem
        if (!$this->container_view_manager->isAdminView()) {
            // vote

            if ($this->poll_block->mayVote($this->user->getId())) {
                $this->tpl->setCurrentBlock("mode_info_bl");
                if ($this->poll_block->getPoll()->getNonAnonymous()) {
                    $mode_info = $this->lng->txt("poll_non_anonymous_warning");
                } else {
                    $mode_info = $this->lng->txt("poll_anonymous_warning");
                }
                $this->tpl->setVariable("MODE_INFO", $mode_info);
                $this->tpl->parseCurrentBlock();

                $is_multi_answer = ($this->poll_block->getPoll()->getMaxNumberOfAnswers() > 1);

                $session_last_poll_vote = ilSession::get('last_poll_vote');
                if (isset($session_last_poll_vote[$this->poll_block->getPoll()->getId()])) {
                    $last_vote = $session_last_poll_vote[$this->poll_block->getPoll()->getId()];
                    unset($session_last_poll_vote[$this->poll_block->getPoll()->getId()]);
                    ilSession::set('last_poll_vote', $session_last_poll_vote);

                    if ($is_multi_answer) {
                        $error = sprintf(
                            $this->lng->txt("poll_vote_error_multi"),
                            $this->poll_block->getPoll()->getMaxNumberOfAnswers()
                        );
                    } else {
                        $error = $this->lng->txt("poll_vote_error_single");
                    }

                    $this->tpl->setCurrentBlock("error_bl");
                    $this->tpl->setVariable("FORM_ERROR", $error);
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("answer");
                foreach ($a_set->getAnswers() as $item) {
                    $id = (int) ($item['id'] ?? 0);
                    $answer = (string) ($item['answer'] ?? 0);
                    if (!$is_multi_answer) {
                        $this->tpl->setVariable("ANSWER_INPUT", "radio");
                        $this->tpl->setVariable("ANSWER_NAME", "aw");
                    } else {
                        $this->tpl->setVariable("ANSWER_INPUT", "checkbox");
                        $this->tpl->setVariable("ANSWER_NAME", "aw[]");

                        if (!empty($last_vote) && is_array($last_vote) && in_array($id, $last_vote)) {
                            $this->tpl->setVariable("ANSWER_STATUS", 'checked="checked"');
                        }
                    }
                    $this->tpl->setVariable("VALUE_ANSWER", $id);
                    $this->tpl->setVariable("TXT_ANSWER_VOTE", nl2br($answer));
                    $this->tpl->parseCurrentBlock();
                }

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

                $url .= "#poll" . $a_set->getID();

                $this->tpl->setVariable("URL_FORM", $url);
                $this->tpl->setVariable("CMD_FORM", "vote");
                $this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("poll_vote"));

                if ($this->poll_block->getPoll()->getVotingPeriod()) {
                    $this->tpl->setVariable(
                        "TXT_VOTING_END_PERIOD",
                        sprintf(
                            $this->lng->txt("poll_voting_period_info"),
                            ilDatePresentation::formatDate(new ilDateTime($this->poll_block->getPoll()->getVotingPeriodEnd(), IL_CAL_UNIX))
                        )
                    );
                }
            }


            // result
            if ($this->poll_block->maySeeResults($this->user->getId())) {
                if (!$this->poll_block->mayNotResultsYet()) {
                    $answers = [];
                    foreach ($a_set->getAnswers() as $item) {
                        $id = (int) ($item['id'] ?? 0);
                        $answers[$id] = (string) ($item['answer'] ?? 0);
                    }

                    $perc = $this->poll_block->getPoll()->getVotePercentages();
                    $total = (int) ($perc['total'] ?? 0);
                    $perc = (array) ($perc['perc'] ?? []);

                    $this->tpl->setVariable("TOTAL_ANSWERS", sprintf($this->lng->txt("poll_population"), $total));

                    if ($total) {
                        // sort results by votes / original position
                        if ($this->poll_block->getPoll()->getSortResultByVotes()) {
                            $order = array_keys(ilArrayUtil::sortArray($perc, "abs", "desc", true, true));

                            foreach (array_keys($answers) as $answer_id) {
                                if (!in_array($answer_id, $order)) {
                                    $order[] = $answer_id;
                                }
                            }
                        } else {
                            $order = array_keys($answers);
                        }

                        // pie chart
                        if ($this->poll_block->showResultsAs() === ilObjPoll::SHOW_RESULTS_AS_PIECHART) {
                            $chart = ilChart::getInstanceByType(ilChart::TYPE_PIE, "poll_results_pie_" . $this->getRefId());
                            $chart->setSize("400", "200");
                            $chart->setAutoResize(true);

                            $chart_data = $chart->getDataInstance();

                            foreach ($order as $answer_id) {
                                $chart_data->addPiePoint(
                                    (int) round((float) ($perc[$answer_id]["perc"] ?? 0)),
                                    nl2br((string) ($answers[$answer_id] ?? ''))
                                );
                            }

                            // disable legend, use inner labels - currently not preferred
                            // $chart_data->setLabelRadius(0.8);

                            $chart->addData($chart_data);

                            $pie_legend_id = "poll_legend_" . $this->getRefId();
                            $legend = new ilChartLegend();
                            $legend->setContainer($pie_legend_id);
                            $chart->setLegend($legend);

                            $this->tpl->setVariable("PIE_LEGEND_ID", $pie_legend_id);
                            $this->tpl->setVariable("PIE_CHART", $chart->getHTML());
                        } // bar chart
                        else {
                            $this->tpl->setCurrentBlock("answer_result");
                            foreach ($order as $answer_id) {
                                $pbar = ilProgressBar::getInstance();
                                $pbar->setCurrent(round((float) ($perc[$answer_id]["perc"] ?? 0)));
                                $this->tpl->setVariable("PERC_ANSWER_RESULT", $pbar->render());
                                $this->tpl->setVariable("TXT_ANSWER_RESULT", nl2br((string) ($answers[$answer_id] ?? '')));
                                $this->tpl->parseCurrentBlock();
                            }
                        }
                    }
                } else {
                    $rel = ilDatePresentation::useRelativeDates();
                    ilDatePresentation::setUseRelativeDates(false);
                    $end = $this->poll_block->getPoll()->getVotingPeriodEnd();
                    $end = ilDatePresentation::formatDate(new ilDateTime($end, IL_CAL_UNIX));
                    ilDatePresentation::setUseRelativeDates($rel);

                    // #14607
                    $info = "";
                    if ($this->poll_block->getPoll()->hasUserVoted($this->user->getId())) {
                        $info .= $this->lng->txt("poll_block_message_already_voted") . " ";
                    }

                    $this->tpl->setVariable("TOTAL_ANSWERS", $info .
                        sprintf($this->lng->txt("poll_block_results_available_on"), $end));
                }
            } elseif ($this->poll_block->getPoll()->hasUserVoted($this->user->getId())) {
                $this->tpl->setVariable("TOTAL_ANSWERS", $this->lng->txt("poll_block_message_already_voted"));
            }
        }

        if (!$this->poll_block->mayVote($this->user->getId()) && !$this->poll_block->getPoll()->hasUserVoted($this->user->getId())) {
            if ($this->poll_block->getPoll()->getVotingPeriod()) {
                $this->tpl->setVariable(
                    "TXT_VOTING_PERIOD",
                    sprintf(
                        $this->lng->txt("poll_voting_period_full_info"),
                        ilDatePresentation::formatDate(new ilDateTime($this->poll_block->getPoll()->getVotingPeriodBegin(), IL_CAL_UNIX)),
                        ilDatePresentation::formatDate(new ilDateTime($this->poll_block->getPoll()->getVotingPeriodEnd(), IL_CAL_UNIX))
                    )
                );
            }
        } else {
            $this->tpl->setVariable("TXT_QUESTION", nl2br(trim($a_set->getQuestion())));

            $img = $a_set->getImageFullPath();
            if ($img) {
                $this->tpl->setVariable("URL_IMAGE", ilWACSignedPath::signFile($img));
            }
        }


        $this->tpl->setVariable("ANCHOR_ID", $a_set->getID());
        //$this->tpl->setVariable("TXT_QUESTION", nl2br(trim($a_poll->getQuestion())));

        $desc = trim($a_set->getDescription());
        if ($desc) {
            $this->tpl->setVariable("TXT_DESC", nl2br($desc));
        }


        if ($this->poll_block->showComments()) {
            $this->tpl->setCurrentBlock("comment_link");
            $this->tpl->setVariable("LANG_COMMENTS", $this->lng->txt('poll_comments'));
            $this->tpl->setVariable("COMMENT_JSCALL", $this->commentJSCall());
            $this->tpl->setVariable("COMMENTS_COUNT_ID", $this->getRefId());

            $comments_count = $this->getNumberOfComments($this->getRefId());

            if ($comments_count > 0) {
                $this->tpl->setVariable("COMMENTS_COUNT", "(" . $comments_count . ")");
            }

            if (!self::$js_init) {
                $redraw_url = $this->ctrl->getLinkTarget(
                    $this,
                    "getNumberOfCommentsForRedraw",
                    "",
                    true
                );
                $this->tpl->setVariable("COMMENTS_REDRAW_URL", $redraw_url);

                $this->main_tpl->addJavaScript("Modules/Poll/js/ilPoll.js");
                self::$js_init = true;
            }
        }
    }

    public function getHTML(): string
    {
        $this->poll_block->setRefId($this->getRefId());
        $may_write = $this->access->checkAccess("write", "", $this->getRefId());
        $has_content = $this->poll_block->hasAnyContent($this->user->getId(), $this->getRefId());

        #22078 and 22079 it always contains something.
        /*if(!$may_write && !$has_content)
        {
            return "";
        }*/

        $poll_obj = $this->poll_block->getPoll();
        $this->setTitle($poll_obj->getTitle());
        $this->setData(array(array($poll_obj)));

        $this->ctrl->setParameterByClass(
            $this->getRepositoryObjectGUIName(),
            "ref_id",
            $this->getRefId()
        );

        if (!$this->poll_block->getMessage($this->user->getId())) {
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
                $this->lng->txt("edit_content")
            );
            $this->addBlockCommand(
                $this->ctrl->getLinkTargetByClass(
                    array("ilrepositorygui", $this->getRepositoryObjectGUIName()),
                    "edit"
                ),
                $this->lng->txt("settings")
            );
        }

        $this->ctrl->clearParametersByClass($this->getRepositoryObjectGUIName());

        return parent::getHTML();
    }

    /**
     * Builds JavaScript Call to open CommentLayer via html link
     */
    private function commentJSCall(): string
    {
        $refId = $this->getRefId();
        $objectId = ilObject2::_lookupObjectId($refId);

        $ajaxHash = ilCommonActionDispatcherGUI::buildAjaxHash(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $refId,
            "poll",
            $objectId
        );


        return ilNoteGUI::getListCommentsJSCall($ajaxHash, "ilPoll.redrawComments(" . $refId . ");");
    }

    public function getNumberOfCommentsForRedraw(): void
    {
        global $DIC;

        $poll_id = 0;
        if ($this->http->wrapper()->query()->has('poll_id')) {
            $poll_id = $this->http->wrapper()->query()->retrieve(
                'poll_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $number = $this->getNumberOfComments($poll_id);

        if ($number > 0) {
            echo "(" . $number . ")";
        } else {
            echo "";
        }

        exit();
    }

    public function getNumberOfComments(int $ref_id): int
    {
        $obj_id = ilObject2::_lookupObjectId($ref_id);
        $context = $this->notes->data()->context($obj_id, 0, "poll");
        return $this->notes->domain()->getNrOfCommentsForContext($context);
    }

    public function fillDataSection(): void
    {
        $this->setDataSection($this->getLegacyContent());
    }

    //
    // New rendering
    //

    /**
     * @inheritdoc
     */
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
