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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControl as ViewControlContainer;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterContainer;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\Test\Presentation\TabsManager;
use ILIAS\UI\Component\Navigation\Sequence\SegmentRetrieval;
use ILIAS\UI\Component\Navigation\Sequence\SegmentBuilder;
use ILIAS\UI\Component\Navigation\Sequence\Segment;
use ILIAS\UI\Component\Legacy\Content as LegacyContent;
use ILIAS\UI\Component\Prompt\Prompt;
use ILIAS\UI\Component\Button\Standard as StdButton;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;
use ILIAS\UI\Component\Panel\Report as ReportPanel;
use ILIAS\UI\Component\Panel\Sub as SubPanel;

class ConsecutiveScoringGUI implements SegmentRetrieval
{
    public const CMD_VIEW = 'view';
    public const DEFAULT_COMMAND = 'view';

    private const ACT_FORM_STATE = 'fs';
    private const ACT_STORE_STATE = 'ss';
    private const ACT_STORE = 'store';
    private const ACT_SCORING_COMPLETE = 'ucomplete';
    private const ACT_SCORING_INCOMPLETE = 'uincomplete';

    private const F_USERS = 'fusers';
    private const F_QUESTIONS = 'fquestions';
    private const F_ANSWERED = 'fanswerd';
    private const F_FINAL = 'ffinal';
    private const F_USER_FINAL = 'fusrfinal';
    private const F_ONLY = 'only';
    private const F_HIDE = 'hide';
    private const F_SCOREDBY = 'fscrby';

    private ?array $filter_values = null;
    private Prompt $prompt;

    public function __construct(
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly \ilGlobalTemplateInterface $tpl,
        protected readonly \ilTabsGUI $tabs,
        protected readonly \ilLanguage $lng,
        protected readonly \ilObjTest $object,
        protected readonly \ilTestAccess $test_access,
        protected readonly UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly ServerRequestInterface $request,
        protected readonly ConsecutiveScoring $scoring,
        protected readonly ConsecutiveScoringURLs $url_builder,
        protected readonly \ilUIFilterService $filter_service,
    ) {
        $this->prompt = $this->ui_factory->prompt()->standard($this->url_builder->buildURI());
    }
    public function executeCommand(): void
    {
        if (!$this->test_access->checkScoreParticipantsAccess()
            && !$this->test_access->checkScoreParticipantsAccessAnon()
        ) {
            \ilObjTestGUI::accessViolationRedirect();
        }

        if (!$this->object->getGlobalSettings()->isManualScoringEnabled()) {
            // allow only if at least one question type is marked for manual scoring
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("manscoring_not_allowed"), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }

        $this->tabs->activateTab(TabsManager::TAB_ID_MANUAL_SCORING);

        $act = $this->url_builder->getAction() ?? self::DEFAULT_COMMAND;
        list($qid, $uid, $pid) = $this->url_builder->getIdParameters();

        switch ($act) {
            case self::ACT_FORM_STATE:
                $answer_section =
                    $this->ui_renderer->render([
                        $this->getUserRepresentation($uid, $pid),
                        $this->getUserAnswer($qid, $uid, $pid, false, false),
                    ]);
                $response = $this->ui_factory->prompt()->state()->show(
                    $this->getScoringForm(self::ACT_STORE_STATE, $qid, $uid, $pid)
                )->withTitle( //TODO: should not be on state, but on form
                    $answer_section
                );
                echo($this->ui_renderer->renderAsync($response));
                exit();

            case self::ACT_STORE_STATE:
                $form = $this->getScoringForm(self::ACT_STORE_STATE, $qid, $uid, $pid)
                    ->withRequest($this->request);
                $formdata = $form->getData();
                if ($formdata !== null) {
                    $this->store($formdata);
                    $msg = sprintf(
                        $this->lng->txt('tst_saved_manscoring_successfully'),
                        $pid + 1,
                        $this->scoring->getUserFullName($uid, (string) $pid)
                    );
                    $this->tpl->setOnScreenMessage('success', $msg, true);

                    $anchor = sprintf('anchor_%s_%s', $formdata['qid'], $formdata['usr_active_id']);
                    $url = $this->url_builder
                        ->withAction(self::CMD_VIEW)
                        ->withFragment($anchor)
                        ->buildURI();

                    $response = $this->ui_factory->prompt()->state()->redirect($url);
                } else {
                    $response = $this->ui_factory->prompt()->state()->show($form);
                }
                echo($this->ui_renderer->renderAsync($response));
                exit();

            case self::ACT_STORE:
                $form = $this->getScoringForm(self::ACT_STORE, $qid, $uid, $pid)
                    ->withRequest($this->request);
                $formdata = $form->getData();
                if ($formdata === null) {
                    $this->tpl->setContent($this->view());
                } else {
                    $this->store($formdata);
                    $msg = sprintf(
                        $this->lng->txt('tst_saved_manscoring_successfully'),
                        $pid + 1,
                        $this->scoring->getUserFullName($uid, (string) $pid)
                    );
                    $this->tpl->setOnScreenMessage('success', $msg, true);
                    $this->url_builder->withAction(self::CMD_VIEW)->redirect();
                }
                break;

            case self::ACT_SCORING_COMPLETE:
                $this->scoring->completeScoring($uid, true);
                $msg = $this->lng->txt('manscoring_finalized');
                $this->tpl->setOnScreenMessage('success', $msg, true);
                $this->url_builder->withAction(self::CMD_VIEW)->redirect();
                break;

            case self::ACT_SCORING_INCOMPLETE:
                $this->scoring->completeScoring($uid, false);
                $msg = $this->lng->txt('manscoring_finalized_removed');
                $this->tpl->setOnScreenMessage('success', $msg, true);
                $this->url_builder->withAction(self::CMD_VIEW)->redirect();
                break;

            case self::DEFAULT_COMMAND:
                $this->tpl->setContent($this->view());
                break;

            default:
                throw new \Exception('no such command/action: ' . $act);
        }
    }

    protected function view(): string
    {
        $filter = $this->getFilter();
        $this->filter_values = $this->filter_service->getData($filter);

        $sequence = $this->ui_factory->navigation()->sequence(
            $this,
            $this->lng->txt(TabsManager::TAB_ID_MANUAL_SCORING)
        )
        ->withId('cs_' . (string) $this->object->getRefId())
        ->withViewControls($this->getViewControls())
        ->withRequest($this->request);

        return $this->ui_renderer->render([
            $filter,
            $sequence
        ]);
    }

    /**
     * @param ConsecutiveScoringMode $viewcontrol_values
     */
    public function getAllPositions(
        ServerRequestInterface $request,
        mixed $viewcontrol_values,
        mixed $filter_values
    ): array {
        $filter_values = $this->filter_values;
        $positions = [];

        $usr_active_ids = $filter_values[self::F_USERS] ?? array_keys($this->scoring->getTestParticipants());
        $usr_active_ids = array_map('intval', $usr_active_ids);

        $question_ids = $filter_values[self::F_QUESTIONS] ??
            array_map(
                static fn(GeneralQuestionProperties $q) => $q->getQuestionId(),
                $this->scoring->getManuallyScorableQuestionsInTest()
            );
        $question_ids = array_map('intval', $question_ids);


        if ($filter_values[self::F_USER_FINAL] ?? '' !== '') {
            $usr_active_ids = array_filter(
                $usr_active_ids,
                function ($uid) use ($filter_values) {
                    $complete = $this->scoring->isScoringComplete($uid);
                    $filter = $filter_values[self::F_USER_FINAL];
                    return
                        ($complete && $filter === self::F_ONLY) ||
                        (!$complete && $filter === self::F_HIDE);
                }
            );
        }

        $viewcontrol_values = array_shift($viewcontrol_values);

        if ($viewcontrol_values->isUserCentric()) {
            $position_ids = $this->filterForUsers(
                $usr_active_ids,
                $question_ids,
                $filter_values[self::F_ANSWERED] ?? null,
                $filter_values[self::F_FINAL] ?? null,
                $filter_values[self::F_SCOREDBY] ?? [],
            );

            foreach ($position_ids as $uid => $qids) {
                if ($viewcontrol_values->isSingle()) {
                    foreach ($qids as $qid) {
                        $positions[] = [[$uid], [$qid]];
                    }
                } else {
                    $positions[] = [[$uid], $qids];
                }
            }

        } else {
            $position_ids = $this->filterForQuestions(
                $usr_active_ids,
                $question_ids,
                $filter_values[self::F_ANSWERED] ?? null,
                $filter_values[self::F_FINAL] ?? null,
                $filter_values[self::F_SCOREDBY] ?? [],
            );

            foreach ($position_ids as $qid => $uids) {
                if ($viewcontrol_values->isSingle()) {
                    foreach ($uids as $uid) {
                        $positions[] = [[$uid], [$qid]];
                    }
                } else {
                    $positions[] = [$uids, [$qid]];
                }
            }
        }

        if ($positions === []) {
            return [null];
        }
        return $positions;
    }


    /**
     * @return uid => qids
     */
    protected function filterForUsers(
        array $usr_active_ids,
        array $question_ids,
        ?string $filter_answered,
        ?string $filter_finalized,
        array $filter_scoredby,
    ): array {
        $answered = array_reduce(
            $usr_active_ids,
            function ($r, $uid) use ($question_ids) {
                $r[$uid] = $question_ids;
                return $r;
            },
            []
        );
        $finalized = $answered;

        if ($filter_answered !== null) {
            $answered = $this->scoring->getAnsweredQuestionIds(...$usr_active_ids);
        }
        if ($filter_finalized !== null) {
            $finalized = $this->scoring->getFinalizedFeedbackIds($usr_active_ids, $question_ids);
        }

        $scored_by = [];
        if ($filter_scoredby !== []) {
            $scored_by = $this->scoring->getQidsFinalizedBy(
                $usr_active_ids,
                $question_ids,
                $filter_scoredby
            );
        }

        $ret = [];
        foreach ($usr_active_ids as $uid) {
            $ret[$uid] = $question_ids;

            if ($filter_answered === self::F_ONLY) {
                $ret[$uid] = array_intersect($ret[$uid], $answered[$uid]);
            }
            if ($filter_answered === self::F_HIDE) {
                $ret[$uid] = array_diff($ret[$uid], $answered[$uid]);
            }

            if ($filter_finalized === self::F_ONLY) {
                $ret[$uid] = array_diff($ret[$uid], $finalized[$uid]);
            }
            if ($filter_finalized === self::F_HIDE) {
                $ret[$uid] = array_intersect($ret[$uid], $finalized[$uid]);
            }
            if (array_key_exists($uid, $scored_by)) {
                $ret[$uid] = array_intersect($ret[$uid], $scored_by[$uid]);
            }
        }
        return array_filter($ret);
    }

    /**
     * @return qid => uids
     */
    protected function filterForQuestions(
        array $usr_active_ids,
        array $question_ids,
        ?string $filter_answered,
        ?string $filter_finalized,
        array $filter_scoredby,
    ): array {
        $answered = array_reduce(
            $question_ids,
            function ($r, $qid) use ($usr_active_ids) {
                $r[$qid] = $usr_active_ids;
                return $r;
            },
            []
        );
        $finalized = $answered;

        if ($filter_answered !== null) {
            $answered = $this->pivot(
                $this->scoring->getAnsweredQuestionIds(...$usr_active_ids)
            );
        }
        if ($filter_finalized !== null) {
            $finalized = $this->pivot(
                $this->scoring->getFinalizedFeedbackIds($usr_active_ids, $question_ids)
            );
        }

        $scored_by = [];
        if ($filter_scoredby !== []) {
            $scored_by = $this->pivot(
                $this->scoring->getQidsFinalizedBy(
                    $usr_active_ids,
                    $question_ids,
                    $filter_scoredby
                )
            );
        }

        $ret = [];
        foreach ($question_ids as $qid) {
            $ret[$qid] = $usr_active_ids;

            if ($filter_answered === self::F_ONLY) {
                $ret[$qid] = array_diff($ret[$qid], $answered[$qid] ?? []);
            }
            if ($filter_answered === self::F_HIDE) {
                $ret[$qid] = array_intersect($ret[$qid], $answered[$qid] ?? []);
            }
            if ($filter_finalized === self::F_ONLY) {
                $ret[$qid] = array_diff($ret[$qid], $finalized[$qid] ?? []);
            }
            if ($filter_finalized === self::F_HIDE) {
                $ret[$qid] = array_intersect($ret[$qid], $finalized[$qid] ?? []);
            }
            if (array_key_exists($qid, $scored_by)) {
                $ret[$qid] = array_intersect($ret[$qid], $scored_by[$qid]);
            }
        }
        return array_filter($ret);
    }

    private function pivot(array $data): array
    {
        $pivoted = [];
        foreach ($data as $key => $values) {
            foreach ($values as $value) {
                if (!array_key_exists($value, $pivoted)) {
                    $pivoted[$value] = [];
                }
                $pivoted[$value][] = $key;
            }
        }
        return $pivoted;
    }

    public function getSegment(
        ServerRequestInterface $request,
        mixed $position_data,
        mixed $viewcontrol_values,
        mixed $filter_values
    ): Segment {

        if ($position_data === null) {
            return $this->ui_factory->legacy()->segment(
                '',
                $this->lng->txt('ui_table_no_records')
            );
        }
        list($usr_active_ids, $question_ids) = $position_data;

        $pass_id = $this->scoring->getPassUsedForEvaluation(current($usr_active_ids));

        $viewcontrol_values = array_shift($viewcontrol_values);

        $title = $viewcontrol_values->isUserCentric() ?
            $this->getUserRepresentation(current($usr_active_ids), $pass_id) :
            $this->getQuestionRepresentation(current($question_ids), true);

        $usr_active_id = current($usr_active_ids);
        $qid = current($question_ids);

        if ($viewcontrol_values->isSingle()) {

            $form = $this->getScoringForm(self::ACT_STORE, $qid, $usr_active_id, $pass_id);

            if ($request->getMethod() === 'POST') {
                $form = $form->withRequest($request);
            }

            $representation = $viewcontrol_values->isUserCentric() ?
                $this->getQuestionRepresentation(current($question_ids), true) :
                $this->getUserRepresentation(current($usr_active_ids), $pass_id);

            $user_answer = $this->getUserAnswer($qid, $usr_active_id, $pass_id);

            $feedback_properties = $this->ui_factory->listing()->property();
            $feedback = $this->scoring->getSingleManualFeedback($qid, $usr_active_id, $pass_id);
            $feedback_properties = $this->getWithFinalizedProperties($feedback, $feedback_properties);
            $panel = $this->ui_factory->panel()->standard("", [$form, $feedback_properties]);

            $layout = $this->ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
                $user_answer,
                $panel,
            );

            $out = [
                $representation,
                $layout,
            ];

        } else {
            $out = $viewcontrol_values->isUserCentric() ?
                $this->collectForUser($usr_active_id, $question_ids) :
                $this->collectForQuestion($qid, $usr_active_ids);
            $out[] = $this->prompt;
        }

        $segment = $this->ui_factory->legacy()->segment(
            $this->ui_renderer->render($title),
            $this->ui_renderer->render($out)
        );
        if ($viewcontrol_values->isUserCentric()) {
            $segment = $segment->withSegmentActions(
                ...$this->getSegmentActionsForUser($usr_active_id)
            );
        }
        return $segment;
    }

    protected function getViewControls(): ViewControlContainer
    {
        $vcs = [
            $this->ui_factory->input()->viewControl()->mode(
                [
                    ConsecutiveScoringMode::MODE_USER => $this->lng->txt('mode_user'),
                    ConsecutiveScoringMode::MODE_QUESTION => $this->lng->txt('mode_question'),
                ]
            ),
            $this->ui_factory->input()->viewControl()->mode(
                [
                    ConsecutiveScoringMode::MODE_ALL => $this->lng->txt('mode_allatonce'),
                    ConsecutiveScoringMode::MODE_ONE => $this->lng->txt('mode_onebyone'),
                ]
            )
        ];

        return $this->ui_factory->input()->container()->viewControl()->standard($vcs)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($v) => [new ConsecutiveScoringMode(...$v)]
                )
            );
    }

    protected function getFilter(): FilterContainer
    {
        $user_options = [];
        foreach ($this->scoring->getTestParticipants() as $usr_active_id => $u) {
            $user_options[$usr_active_id] = $this->scoring->getUserFullName($usr_active_id, 'x');
        }

        $question_options = [];
        foreach ($this->scoring->getManuallyScorableQuestionsInTest() as $q) {
            $question_options[$q->getQuestionId()] = sprintf(
                '%s (%s)',
                $q->getTitle(),
                $q->getTypeName($this->lng)
            );
        }

        $answered_options = [
            self::F_ONLY => $this->lng->txt('tst_man_scoring_answered_only'),
            self::F_HIDE => $this->lng->txt('tst_man_scoring_answered_hide'),
        ];

        $finalized_options = [
            self::F_ONLY => $this->lng->txt('tst_man_scoring_finalized_hide'),
            self::F_HIDE => $this->lng->txt('tst_man_scoring_finalized_only'),
        ];

        $final_options = [
            self::F_ONLY => $this->lng->txt('evaluated_users'),
            self::F_HIDE => $this->lng->txt('not_evaluated_users')
        ];

        $scored_by_options = [];
        foreach ($this->scoring->getAllFinalizingUsrIds() as $scorer_id) {
            $ud = current(\ilObjUser::_getUserData([$scorer_id]));
            $scored_by_options[$scorer_id] = $ud['firstname'] . " " . $ud['lastname'];
        }

        $filter = [
            self::F_USERS => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('tst_man_scoring_userselection'),
                $user_options
            ),
            self::F_QUESTIONS => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('tst_man_scoring_questionselection'),
                $question_options
            ),
            self::F_ANSWERED => $this->ui_factory->input()->field()->select(
                $this->lng->txt('tst_man_scoring_only_answered'),
                $answered_options
            )->withValue(null),
            self::F_FINAL => $this->ui_factory->input()->field()->select(
                $this->lng->txt('tst_man_scoring_finalized'),
                $finalized_options
            )->withValue(null),
            self::F_USER_FINAL => $this->ui_factory->input()->field()->select(
                $this->lng->txt('finalized_evaluation'),
                $final_options
            )->withValue(null),
            self::F_SCOREDBY => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('scored_by'),
                $scored_by_options
            )->withValue(null),

        ];
        return $this->filter_service->standard(
            'csfilter_' . (string) $this->object->getRefId(),
            $this->ctrl->getLinkTarget($this, self::CMD_VIEW),
            $filter,
            array_map(fn() => true, $filter),
            true,
            true
        );
    }

    protected function getUserRepresentation(int $usr_active_id, $pass_id): \ILIAS\UI\Component\Entity\Entity
    {
        $pid = $this->scoring->getPassUsedForEvaluation($usr_active_id);
        $usr_fullname = $this->scoring->getUserFullName($usr_active_id, (string) $pid);

        $usr_avatar = (new \ilUserAvatarResolver((int) $this->scoring->getUserId($usr_active_id, (string) $pid)))->getAvatar();

        $scored_participant_entity =
            $this->ui_factory->entity()->standard(
                $usr_fullname,
                $usr_avatar
            )->withDetails(
                $this->ui_factory->listing()->property()->withProperty(
                    $this->lng->txt("scored_pass"),
                    (string) ($pid + 1)
                )->withProperty(
                    $this->lng->txt("usr_manscoring_complete"),
                    $this->scoring->isScoringComplete($usr_active_id) ?
                        $this->lng->txt('yes') : $this->lng->txt('no')
                )->withProperty(
                    $this->lng->txt("exam_id"),
                    $this->object->lookupExamId($usr_active_id, $pass_id)
                )
            );
        return $scored_participant_entity;
    }

    protected function getQuestionRepresentation(int $qid, bool $show_title = false): \ILIAS\UI\Component\Legacy\Content
    {
        $tpl = new \ilTemplate('tpl.il_as_tst_manual_scoring_consecutive_question.html', true, true, 'components/ILIAS/Test');

        $question = $this->scoring->getQuestionObject($qid);

        $question_text = $question->getQuestion();

        if ($show_title) {
            $tpl->setCurrentBlock("expandable_title");
            $question_title = $question->getTitle();
            $tpl->setVariable('TITLE', $question_title);
        } else {
            $tpl->setCurrentBlock("question_only");
            $tpl->setVariable('EXPAND_COLLAPSE', $this->lng->txt("expand") . "/" . $this->lng->txt("collapse"));
        }
        $tpl->setVariable('QUESTION', $question_text);
        $tpl->parseCurrentBlock();

        $legacy_container = $this->ui_factory->legacy()->content($tpl->get());

        return $legacy_container;
    }

    protected function getUserAnswer(
        int $qid,
        int $usr_active_id,
        int $pass_id,
        bool $show_feedback_html = false,
        bool $show_grade_btn = false,
        bool $show_properties = false
    ): LegacyContent {
        $question_gui = $this->scoring->getUserQuestionGUI($qid, $usr_active_id, $pass_id);
        $question_solution = $question_gui->getSolutionOutput(
            $usr_active_id,
            $pass_id,
            $graphical_output = true,
            $result_output = true,
            $show_question_only = true,
            $show_feedback = false,
            $show_correct_solution = false,
            $show_manual_scoring = true,
            $show_question_text = false,
            $show_inline_feedback = false
        );
        $tpl = new \ilTemplate('tpl.il_as_tst_manual_scoring_consecutive_answer.html', true, true, 'components/ILIAS/Test');

        $usr_question = $question_gui->getObject();
        $feedback = $this->scoring->getSingleManualFeedback($qid, $usr_active_id, $pass_id);

        if ($show_properties) {
            $info =
                $this->ui_factory->listing()->property()
                    ->withProperty(
                        $this->lng->txt('tst_highscore_score'),
                        (string) $usr_question->getReachedPoints($usr_active_id, $pass_id) . " " . $this->lng->txt('tst_manscoring_input_of_max') . " " . (string) $usr_question->getMaximumPoints()
                    )
                    ->withProperty(
                        $this->lng->txt('finalized_evaluation'),
                        (bool) ($feedback['finalized_evaluation'] ?? false) ?
                            $this->lng->txt('yes') : $this->lng->txt('no')
                    );
            $info = $this->getWithFinalizedProperties($feedback, $info);
            $tpl->setVariable('PROPERTIES', $this->ui_renderer->render($info));
        }

        $tpl->setVariable('ANSWER', $question_solution);
        if (array_key_exists('feedback', $feedback) && $show_feedback_html) {
            $tpl->setVariable(
                'FEEDBACK',
                $this->refinery->string()->markdown()->toHTML()->transform($feedback['feedback'])
            );
        } elseif ($show_feedback_html) {
            $tpl->setVariable('FEEDBACK', $this->lng->txt('tst_manscoring_no_feedback'));
        }
        if ($show_grade_btn) {
            $tpl->setVariable(
                'GRADEBTN',
                $this->ui_renderer->render(
                    $this->getSingleFormButton($qid, $usr_active_id, $pass_id)
                )
            );
        }

        return $this->ui_factory->legacy()->content($tpl->get());
    }

    protected function getScoringForm(string $action, int $qid, int $usr_active_id, int $pass_id): Form
    {
        $action = $this->url_builder
            ->withAction($action)
            ->withIdParameters($qid, $usr_active_id, $pass_id)
            ->buildURI()->__toString();

        $question = $this->scoring->getQuestionObject($qid);
        $max_points = $question->getMaximumPoints();
        $score = $question->getReachedPoints($usr_active_id, $pass_id);
        $feedback = $this->scoring->getSingleManualFeedback($qid, $usr_active_id, $pass_id);
        $feedback_final = (bool) ($feedback['finalized_evaluation'] ?? false);
        $feedback_txt = $feedback['feedback'] ?? '';

        $inputs = [];
        $inputs[] = $this->ui_factory->input()->field()->numeric(
            $this->lng->txt('tst_change_points_for_question')
        )
        ->withByline($this->lng->txt('tst_manscoring_input_of_max') . " " . $max_points)
        ->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                fn($v) => (float) $v <= $max_points,
                fn() => sprintf(
                    $this->lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'),
                    $max_points
                )
            )
        )
        ->withAdditionalTransformation($this->refinery->kindlyTo()->float())
        ->withValue($score);

        $inputs[] = $this->ui_factory->input()->field()->markdown(
            new \ilUIMarkdownPreviewGUI(),
            $this->lng->txt('set_manual_feedback')
        )
        ->withAdditionalTransformation($this->refinery->to()->string())
        ->withValue($feedback_txt);

        $inputs[] = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('finalized_evaluation')
        )
        ->withAdditionalTransformation($this->refinery->kindlyTo()->bool())
        ->withValue($feedback_final);

        $to_int = $this->refinery->kindlyTo()->int();
        $inputs[] = $this->ui_factory->input()->field()->group(
            [
                $this->ui_factory->input()->field()->hidden()
                ->withAdditionalTransformation($to_int)
                ->withValue($qid),
                $this->ui_factory->input()->field()->hidden()
                ->withAdditionalTransformation($to_int)
                ->withValue($usr_active_id),
                $this->ui_factory->input()->field()->hidden()
                ->withAdditionalTransformation($to_int)
                ->withValue($pass_id)
            ]
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                fn($values) => [
                        'qid' => $values[0],
                        'usr_active_id' => $values[1],
                        'pass_id' => $values[2],
                    ]
            )
        );

        return $this->ui_factory->input()->container()->form()->standard($action, $inputs)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($values) => [
                        'qid' => $values[3]['qid'],
                        'usr_active_id' => $values[3]['usr_active_id'],
                        'attempt' => $values[3]['pass_id'],
                        'score' => $values[0],
                        'final' => $values[2] ?? false,
                        'feedback' => $values[1],
                        'max_points' => $max_points,
                    ]
                )
            );
    }

    protected function collectForUser(int $usr_active_id, array $question_ids): array
    {
        $pass_id = $this->scoring->getPassUsedForEvaluation($usr_active_id);
        $entries = [];
        foreach ($question_ids as $qid) {
            $content = [
                $this->getQuestionRepresentation($qid, true),
                $this->getUserAnswer($qid, $usr_active_id, $pass_id, true, true, true)
            ];
            $panel = $this->ui_factory->panel()->standard("", $content);
            $entries[] = $this->ui_factory->legacy()->content(sprintf('<a id="anchor_%s_%s"></a>', $qid, $usr_active_id));
            $entries[] = $panel;
        }
        return $entries;
    }

    protected function collectForQuestion(int $qid, array $usr_active_ids): array
    {
        $entries = [];
        foreach ($usr_active_ids as $usr_active_id) {
            $pass_id = $this->scoring->getPassUsedForEvaluation($usr_active_id);
            $content = [
                $this->getUserRepresentation($usr_active_id, $pass_id),
                $this->getUserAnswer($qid, $usr_active_id, $pass_id, true, true, true)
            ];
            $panel = $this->ui_factory->panel()->standard("", $content);
            $entries[] = $this->ui_factory->legacy()->content(sprintf('<a id="anchor_%s_%s"></a>', $qid, $usr_active_id));
            $entries[] = $panel;
        }
        return $entries;
    }


    protected function getSingleFormButton(int $qid, int $usr_active_id, int $pass_id): StdButton
    {
        $url = $this->url_builder
            ->withAction(self::ACT_FORM_STATE)
            ->withIdParameters($qid, $usr_active_id, $pass_id)
            ->buildURI();

        return $this->ui_factory->button()->standard(
            $this->lng->txt('grade'),
            $this->prompt->getShowSignal($url)
        );
    }

    protected function getSegmentActionsForUser(int $usr_active_id): array
    {
        $done_label = 'set_manscoring_done';
        $done_action = self::ACT_SCORING_COMPLETE;
        if ($this->scoring->isScoringComplete($usr_active_id)) {
            $done_label = 'set_manscoring_open';
            $done_action = self::ACT_SCORING_INCOMPLETE;
        }
        $btn_done = $this->ui_factory->button()->standard(
            $this->lng->txt($done_label),
            $this->url_builder
                ->withAction($done_action)
                ->withUserId($usr_active_id)
                ->buildURI()->__toString()
        );
        return [$btn_done];
    }

    protected function store(array $data)
    {
        $this->scoring->store(
            $data['qid'],
            $data['usr_active_id'],
            $data['attempt'],
            $data['score'],
            $data['final'],
            $data['feedback'],
            $data['max_points']
        );
    }

    /**
     * @param array $feedback
     * @param \ILIAS\UI\Component\Listing\Property $info
     * @return \ILIAS\UI\Component\Listing\Property
     */
    public function getWithFinalizedProperties(array $feedback, \ILIAS\UI\Component\Listing\Property $info): \ILIAS\UI\Component\Listing\Property
    {

        if (array_key_exists('finalized_by_usr_id', $feedback) && $feedback['finalized_by_usr_id'] !== 0) {
            $feedback_usr_data = $this->object->getUserData([$feedback['finalized_by_usr_id']])[$feedback['finalized_by_usr_id']];
            $feedback_usr_name = $feedback_usr_data['firstname'] . " " . $feedback_usr_data['lastname'];
            $info = $info->withProperty(
                $this->lng->txt('finalized_by'),
                $feedback_usr_name
            )->withProperty(
                $this->lng->txt('finalized_on'),
                (string) $feedback['finalized_time'],
                false
            );
        }
        return $info;
    }
}
