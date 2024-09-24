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

namespace ILIAS\Test\Questions\Presentation;

use ILIAS\Test\Questions\Properties\Repository as TestQuestionsRepository;
use ILIAS\Test\Presentation\TabsManager;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\OrderingRow;
use ILIAS\UI\Component\Table\Action\Action as TableAction;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\URI;
use ILIAS\Language\Language;
use Psr\Http\Message\ServerRequestInterface;

class QuestionsTableActions
{
    private const ACTION_SAVE_ORDER = 'save_order';
    private const ACTION_DELETE = 'delete';
    private const ACTION_DELETE_CONFIRMED = 'deletion_confirmed';
    private const ACTION_COPY = 'copy';
    private const ACTION_ADD_TO_POOL = 'add_qpl';
    private const ACTION_PREVIEW = 'preview';
    private const ACTION_ADJUST = 'correction';
    private const ACTION_STATISTICS = 'statistics';
    private const ACTION_EDIT_QUESTION = 'edit_question';
    private const ACTION_EDIT_PAGE = 'edit_page';
    private const ACTION_FEEDBACK = 'feedback';
    private const ACTION_HINTS = 'hints';
    private const ACTION_PRINT_QUESTIONS = 'print_questions';
    private const ACTION_PRINT_ANSWERS = 'print_answers';

    private const RESULTS_VIEW_TYPE_SHOW = 'show';
    private const RESULTS_VIEW_TYPE_HIDE = 'hide';

    private string $table_id;

    /**
     * @param array $data <string, mixed>
     */
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private \ilGlobalTemplateInterface $tpl,
        private readonly ServerRequestInterface $request,
        private readonly QuestionsTableQuery $table_query,
        private readonly Language $lng,
        private readonly \ilCtrl $ctrl,
        private readonly TestQuestionsRepository $questionrepository,
        private readonly Printer $question_printer,
        private readonly \ilObjTest $test_obj,
        private readonly bool $is_adjusting_questions_with_results_allowed,
        private readonly bool $is_in_test_with_results,
        private readonly bool $is_in_test_with_random_question_set
    ) {
        $this->table_id = (string) $test_obj->getId();
    }

    public function setDisabledActions(
        OrderingRow $row
    ): OrderingRow {
        $disable_default_actions = $this->is_in_test_with_random_question_set
            || $this->is_in_test_with_results;

        return $row->withDisabledAction(
            self::ACTION_DELETE,
            $this->is_in_test_with_random_question_set && !$this->is_in_test_with_results
        )->withDisabledAction(self::ACTION_COPY, $disable_default_actions)
            ->withDisabledAction(self::ACTION_ADD_TO_POOL, $this->is_in_test_with_random_question_set)
            ->withDisabledAction(self::ACTION_EDIT_QUESTION, $disable_default_actions)
            ->withDisabledAction(self::ACTION_EDIT_PAGE, $disable_default_actions)
            ->withDisabledAction(
                self::ACTION_ADJUST,
                $this->is_adjusting_questions_with_results_allowed && !$this->is_in_test_with_results
            )->withDisabledAction(self::ACTION_FEEDBACK, $disable_default_actions)
            ->withDisabledAction(self::ACTION_PRINT_ANSWERS, !$this->is_in_test_with_results);
    }

    public function getOrderActionUrl(): URI
    {
        return $this->table_query->getActionURL(self::ACTION_SAVE_ORDER);
    }

    public function getActions(): array
    {
        $ag = fn(string $type, string $label, $action): TableAction => $this->
            ui_factory->table()->action()->$type(
                $this->lng->txt($label),
                ...$this->table_query->getRowBoundURLBuilder($action)
            );

        return [
            self::ACTION_PREVIEW => $ag('single', 'preview', self::ACTION_PREVIEW),
            self::ACTION_ADJUST => $ag('single', 'tst_corrections_qst_form', self::ACTION_ADJUST),
            self::ACTION_STATISTICS => $ag('single', 'statistics', self::ACTION_STATISTICS),
            self::ACTION_EDIT_QUESTION => $ag('single', 'tst_question_hints_tab', self::ACTION_EDIT_QUESTION),
            self::ACTION_EDIT_PAGE => $ag('single', 'edit_page', self::ACTION_EDIT_PAGE),
            self::ACTION_FEEDBACK => $ag('single', 'tst_feedback', self::ACTION_FEEDBACK),
            self::ACTION_HINTS => $ag('single', 'tst_question_hints_tab', self::ACTION_HINTS),
            self::ACTION_PRINT_ANSWERS => $ag('single', 'print_answers', self::ACTION_PRINT_ANSWERS),
            self::ACTION_DELETE => $ag('standard', 'delete', self::ACTION_DELETE)
                ->withAsync(),
            self::ACTION_COPY => $ag('standard', 'copy', self::ACTION_COPY),
            self::ACTION_ADD_TO_POOL => $ag('standard', 'copy_and_link_to_questionpool', self::ACTION_ADD_TO_POOL),
            self::ACTION_PRINT_QUESTIONS => $ag('multi', 'print', self::ACTION_PRINT_QUESTIONS)
        ];
    }

    public function getQuestionTargetLinkBuilder(): \Closure
    {
        return function (int $question_id): string {
            [$url_builder, $row_id_token] = $this->table_query->getRowBoundURLBuilder(self::ACTION_PREVIEW);
            return $url_builder->withParameter($row_id_token, (string) $question_id)
                ->buildURI()
                ->__toString();
        };
    }

    /**
     * @return bool Show Questions After Return
     */
    public function handleCommand(
        string $cmd,
        array $row_ids,
        \Closure $protect_by_write_protection,
        \Closure $copy_and_link_to_questionpool,
        \Closure $get_table
    ): bool {
        switch ($cmd) {
            case self::ACTION_SAVE_ORDER:
                $protect_by_write_protection();
                $data = $get_table()->getTableComponent()->getData();
                $this->test_obj->setQuestionOrder(array_flip($data), []);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                return true;

            case self::ACTION_PREVIEW:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionPreviewGUI::class,
                    \ilAssQuestionPreviewGUI::CMD_SHOW
                );
                return false;

            case self::ACTION_ADJUST:
                $this->ctrl->setParameterByClass(\ilTestCorrectionsGUI::class, 'qid', (int) current($row_ids));
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilTestCorrectionsGUI::class,
                    'showQuestion'
                );
                return false;

            case self::ACTION_STATISTICS:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionPreviewGUI::class,
                    \ilAssQuestionPreviewGUI::CMD_STATISTICS
                );
                return false;

            case self::ACTION_EDIT_QUESTION:
                $question_id = current($row_ids);
                $qtype = $this->test_obj->getQuestionType($question_id);
                $target_class = $qtype . 'GUI';
                $this->redirectWithQuestionParameters(
                    $question_id,
                    $target_class,
                    'editQuestion'
                );
                return false;

            case self::ACTION_EDIT_PAGE:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionPageGUI::class,
                    'edit'
                );
                return false;

            case self::ACTION_FEEDBACK:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionFeedbackEditingGUI::class,
                    \ilAssQuestionFeedbackEditingGUI::CMD_SHOW
                );
                return false;

            case self::ACTION_HINTS:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionHintsGUI::class,
                    \ilAssQuestionHintsGUI::CMD_SHOW_LIST
                );
                return false;

            case self::ACTION_DELETE:
                echo $this->ui_renderer->renderAsync(
                    $this->getDeleteConfirmation(array_filter($row_ids))
                );
                exit();

            case self::ACTION_DELETE_CONFIRMED:
                $row_ids = $this->request->getParsedBody()['interruptive_items'] ?? [];
                if (array_filter($row_ids) === []) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_selection'));
                    return true;
                }
                $protect_by_write_protection();
                if ($this->is_in_test_with_results) {
                    $this->test_obj->removeQuestionsWithResults($row_ids);
                } else {
                    $this->test_obj->removeQuestions($row_ids);
                    $this->test_obj->saveCompleteStatus($this->test_question_set_config_factory->getQuestionSetConfig());
                }
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_questions_removed'), true);
                return true;

            case self::ACTION_DELETE_WITH_RESULTS_CONFIRMED:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionPageGUI::class,
                    'edit'
                );
                return false;

            case self::ACTION_COPY:
                if (array_filter($row_ids) === []) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_selection'));
                    return true;
                }
                $protect_by_write_protection();
                $this->test_obj->copyQuestions($row_ids);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('copy_questions_success'), true);
                break;

            case self::ACTION_ADD_TO_POOL:
                if (array_filter($row_ids) === []) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_no_question_selected_for_moving_to_qpl'));
                    return true;
                }
                $protect_by_write_protection();
                if (!$this->checkQuestionParametersForCopyToPool($row_ids)) {
                    return true;
                }
                $copy_and_link_to_questionpool();
                return true;

            case self::ACTION_PRINT_QUESTIONS:
                if (array_filter($row_ids) === []) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_selection'));
                    return true;
                }
                $protect_by_write_protection();
                $this->question_printer->printSelectedQuestions(
                    [
                        $this->lng->txt('show_best_solution') => $this->table_query
                            ->getPrintViewTypeURL(self::ACTION_PRINT_QUESTIONS, self::RESULTS_VIEW_TYPE_SHOW)->__toString(),
                        $this->lng->txt('hide_best_solution') => $this->table_query
                            ->getPrintViewTypeURL(self::ACTION_PRINT_QUESTIONS, self::RESULTS_VIEW_TYPE_HIDE)->__toString()
                    ],
                    $this->table_query->getPrintViewType() ?? self::RESULTS_VIEW_TYPE_SHOW === self::RESULTS_VIEW_TYPE_HIDE
                        ? [$this->lng->txt('hide_best_solution') => self::RESULTS_VIEW_TYPE_HIDE]
                        : [$this->lng->txt('show_best_solution') => self::RESULTS_VIEW_TYPE_SHOW],
                    $row_ids
                );
                return false;

            case self::ACTION_PRINT_ANSWERS:
                $protect_by_write_protection();
                $this->question_printer->printAnswers(current($row_ids));
                return false;

            default:
                throw new \InvalidArgumentException("No such table_cmd: '$cmd'.");
        }
    }

    private function getDeleteConfirmation(array $row_ids): Interruptive
    {
        $items = [];
        foreach ($row_ids as $id) {
            $qdata = $this->test_obj->getQuestionDataset($id);
            $type = $this->questionrepository->getQuestionPropertiesForQuestionId($id)
                ->getGeneralQuestionProperties()->getTypeName($this->lng);
            $icon = $this->ui_renderer->render(
                $this->ui_factory->symbol()->icon()->standard('ques', $type, 'small')
            );
            $items[] = $this->ui_factory->modal()->interruptiveItem()->keyvalue(
                (string) $id,
                $icon . ' ' . $qdata->title,
                $type
            );
        }

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('remove'),
            $this->lng->txt(
                $this->is_in_test_with_results
                    ? 'tst_remove_questions_and_results'
                    : 'tst_remove_questions'
            ),
            $this->table_query->getActionURL(self::ACTION_DELETE_CONFIRMED)->__toString()
        )
        ->withAffectedItems($items);
    }

    private function redirectWithQuestionParameters(
        int $question_id,
        string $target_class,
        string $cmd
    ): void {

        $this->ctrl->setParameterByClass(
            $target_class,
            'q_id',
            $question_id
        );

        $this->ctrl->setParameterByClass(
            $target_class,
            'calling_test',
            (string) $this->test_obj->getRefId()
        );

        $this->ctrl->redirectByClass($target_class, $cmd);
    }

    /**
     * @param array<int> $question_ids
     */
    private function checkQuestionParametersForCopyToPool(array $question_ids): bool
    {
        $question_properties = $this->questionrepository
            ->getQuestionPropertiesForQuestionIds($question_ids);
        foreach ($question_ids as $q_id) {
            if (!$this->questionrepository->originalQuestionExists($q_id)) {
                continue;
            }

            $type = \ilObject::_lookupType(
                \assQuestion::lookupParentObjId(
                    $question_properties[$q_id]->getGeneralQuestionProperties()->getOriginalId()
                )
            );

            if ($type !== 'tst') {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_link_only_unassigned'), true);
                return false;
            }
        }
        return true;
    }
}
