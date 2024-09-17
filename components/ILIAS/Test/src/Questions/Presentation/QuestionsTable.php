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

use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\Test\Questions\Properties\Repository as TestQuestionsRepository;
use ILIAS\Test\Questions\Properties\Properties as TestQuestionProperties;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Language\Language;
use Psr\Http\Message\ServerRequestInterface;

class QuestionsTable implements Table\OrderingBinding
{
    private const ACTION_SAVE_ORDER = 'save_order';
    private const ACTION_DELETE = 'delete';
    private const ACTION_DELETE_CONFIRMED = 'deletion_confirmed';
    private const ACTION_COPY = 'copy';
    private const ACTION_ADD_TO_POOL = 'add_qpl';
    private const ACTION_PREVIEW = 'preview';
    private const ACTION_CORRECTION = 'correction';
    private const ACTION_STATISTICS = 'statistics';
    private const ACTION_EDIT_QUESTION = 'edit_question';
    private const ACTION_EDIT_PAGE = 'edit_page';
    private const ACTION_FEEDBACK = 'feedback';
    private const ACTION_HINTS = 'hints';

    protected string $table_id;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $row_id_token;

    /**
     * @param array $data <string, mixed>
     */
    public function __construct(
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected \ilGlobalTemplateInterface $tpl,
        protected ServerRequestInterface $request,
        protected QuestionsTableQuery $commands,
        protected Language $lng,
        protected \ilCtrl $ctrl,
        protected \ilObjTest $test_obj,
        protected TestQuestionsRepository $questionrepository,
        protected TitleColumnsBuilder $title_builder,
        protected array $records,
        protected bool $is_adjusting_questions_with_results_allowed,
        protected bool $is_in_test_with_results,
        protected bool $is_in_test_with_random_question_set
    ) {
        $this->table_id = (string) $test_obj->getId();
        list($this->url_builder, $this->row_id_token) = $this->commands->getRowBoundURLBuilder(self::ACTION_PREVIEW);

        usort(
            $this->records,
            static fn(TestQuestionProperties $a, TestQuestionProperties $b): int =>
                $a->getSequenceInformation()->getPlaceInSequence() <=> $b->getSequenceInformation()->getPlaceInSequence()
        );
    }

    protected function getOrderData(): ?array
    {
        return $this->getTableComponent()->getData();
    }

    public function getTableComponent(): Table\Ordering
    {

        $target = $this->commands->getActionURL(self::ACTION_SAVE_ORDER);
        $table = $this->ui_factory->table()->ordering(
            $this->lng->txt('list_of_questions'),
            $this->getColumns(),
            $this,
            $target
        )
        ->withId($this->table_id)
        ->withActions($this->getActions())
        ->withRequest($this->request);

        return $table;
    }

    public function getRows(
        Table\OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): \Generator {
        $disable_default_actions = $this->is_in_test_with_random_question_set
            || $this->is_in_test_with_results;
        foreach ($this->records as $record) {
            $row = $record->getAsQuestionsTableRow(
                $this->lng,
                $this->ui_factory,
                $this->url_builder,
                $row_builder,
                $this->title_builder,
                $this->row_id_token
            );
            yield $row->withDisabledAction(
                QuestionsTable::ACTION_DELETE,
                $this->is_in_test_with_random_question_set && !$this->is_in_test_with_results
            )->withDisabledAction(self::ACTION_COPY, $disable_default_actions)
            ->withDisabledAction(self::ACTION_ADD_TO_POOL, $this->is_in_test_with_random_question_set)
            ->withDisabledAction(self::ACTION_EDIT_QUESTION, $disable_default_actions)
            ->withDisabledAction(self::ACTION_EDIT_PAGE, $disable_default_actions)
            ->withDisabledAction(
                self::ACTION_CORRECTION,
                $this->is_adjusting_questions_with_results_allowed && !$this->is_in_test_with_results
            )->withDisabledAction(QuestionsTable::ACTION_FEEDBACK, $disable_default_actions)
            ->withDisabledAction(QuestionsTable::ACTION_HINTS, $disable_default_actions);
        }
    }

    /**
     */
    protected function getColumns(): array
    {
        $f = $this->ui_factory;
        $columns = [
            'question_id' => $f->table()->column()->text($this->lng->txt('question_id'))
                ->withIsOptional(true, false),
            'title' => $f->table()->column()->link($this->lng->txt('tst_question_title')),
            'description' => $f->table()->column()->text($this->lng->txt('description'))
                ->withIsOptional(true, false),
            'complete' => $f->table()->column()->boolean(
                $this->lng->txt('question_complete_title'),
                $f->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small'),
                $f->symbol()->icon()->custom('assets/images/standard/icon_alert.svg', '', 'small')
            ),
            'type_tag' => $f->table()->column()->text($this->lng->txt('tst_question_type')),
            'points' => $f->table()->column()->text($this->lng->txt('points')),
            'author' => $f->table()->column()->text($this->lng->txt('author'))
                ->withIsOptional(true, false),
            'lifecycle' => $f->table()->column()->text($this->lng->txt('qst_lifecycle'))
                ->withIsOptional(true, false),
            'qpl' => $f->table()->column()->link($this->lng->txt('qpl')),
            'nr_of_answers' => $f->table()->column()->number($this->lng->txt('number_of_answers'))
                ->withIsOptional(true, false),
            'average_points' => $f->table()->column()->number($this->lng->txt('average_reached_points'))
                ->withIsOptional(true, false),
            'percentage_points_achieved' => $f->table()->column()->number($this->lng->txt('percentage_points_achieved'))
                ->withIsOptional(true, false),
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $std_actions = [
            self::ACTION_DELETE => 'delete',
        ];
        $std_actions[self::ACTION_COPY] = 'copy';
        $std_actions[self::ACTION_ADD_TO_POOL] = 'copy_and_link_to_questionpool';

        $single_actions = [
            self::ACTION_PREVIEW => 'preview',
            self::ACTION_CORRECTION => 'tst_corrections_qst_form',
            self::ACTION_STATISTICS => 'statistics',
            self::ACTION_EDIT_QUESTION => 'edit_question',
            self::ACTION_EDIT_PAGE => 'edit_page',
            self::ACTION_FEEDBACK => 'tst_feedback',
            self::ACTION_HINTS => 'tst_question_hints_tab',
        ];

        $actions = [];
        foreach (array_merge($single_actions, $std_actions) as $action => $txt) {
            $type = array_key_exists($action, $std_actions) ? 'standard' : 'single';
            $actions[$action] = $this->ui_factory->table()->action()->$type(
                $this->lng->txt($txt),
                ...$this->commands->getRowBoundURLBuilder($action)
            );
            if ($action === self::ACTION_DELETE) {
                $actions[$action] = $actions[$action]->withAsync();
            }
        }
        return $actions;
    }

    public function getDeleteConfirmation(array $row_ids): Interruptive
    {
        $items = [];
        foreach ($row_ids as $id) {
            $qdata = $this->test_obj->getQuestionDataset($id);
            $type = $this->questionrepository->getForQuestionId($id)->getTypeName($this->lng);
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
            $this->lng->txt('tst_remove_questions'),
            $this->commands->getActionURL(self::ACTION_DELETE_CONFIRMED)->__toString()
        )
        ->withAffectedItems($items);
    }

    public function handleCommand(
        string $cmd,
        array $row_ids,
        \Closure $protect_by_write_protection,
        \Closure $copy_and_link_to_questionpool
    ) {
        switch ($cmd) {
            case QuestionsTable::ACTION_SAVE_ORDER:
                $data = $this->getOrderData();
                $protect_by_write_protection();
                $this->test_obj->setQuestionOrder(array_flip($data), []);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                break;

            case QuestionsTable::ACTION_PREVIEW:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionPreviewGUI::class,
                    \ilAssQuestionPreviewGUI::CMD_SHOW
                );
                break;

            case QuestionsTable::ACTION_CORRECTION:
                $this->ctrl->setParameterByClass(\ilTestCorrectionsGUI::class, 'qid', (int) current($row_ids));
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilTestCorrectionsGUI::class,
                    'showQuestion'
                );
                break;

            case QuestionsTable::ACTION_STATISTICS:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionPreviewGUI::class,
                    \ilAssQuestionPreviewGUI::CMD_STATISTICS
                );
                break;

            case QuestionsTable::ACTION_EDIT_QUESTION:
                $question_id = current($row_ids);
                $qtype = $this->test_obj->getQuestionType($question_id);
                $target_class = $qtype . 'GUI';
                $this->redirectWithQuestionParameters(
                    $question_id,
                    $target_class,
                    'editQuestion'
                );
                break;

            case QuestionsTable::ACTION_EDIT_PAGE:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionPageGUI::class,
                    'edit'
                );
                break;

            case QuestionsTable::ACTION_FEEDBACK:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionFeedbackEditingGUI::class,
                    \ilAssQuestionFeedbackEditingGUI::CMD_SHOW
                );
                break;

            case QuestionsTable::ACTION_HINTS:
                $this->redirectWithQuestionParameters(
                    current($row_ids),
                    \ilAssQuestionHintsGUI::class,
                    \ilAssQuestionHintsGUI::CMD_SHOW_LIST
                );
                break;

            case QuestionsTable::ACTION_DELETE:
                echo $this->ui_renderer->renderAsync(
                    $this->getDeleteConfirmation(array_filter($row_ids))
                );
                exit();

            case QuestionsTable::ACTION_DELETE_CONFIRMED:
                $row_ids = $this->request->getParsedBody()['interruptive_items'] ?? [];
                if (array_filter($row_ids) === []) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_selection'));
                    break;
                }
                $protect_by_write_protection();
                $this->test_obj->removeQuestions($row_ids);
                $this->test_obj->saveCompleteStatus($this->test_question_set_config_factory->getQuestionSetConfig());
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_questions_removed'), true);
                break;

            case QuestionsTable::ACTION_COPY:
                if (array_filter($row_ids) === []) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_selection'));
                    break;
                }
                $protect_by_write_protection();
                $this->test_obj->copyQuestions($row_ids);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('copy_questions_success'), true);
                break;

            case QuestionsTable::ACTION_ADD_TO_POOL:
                if (array_filter($row_ids) === []) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_no_question_selected_for_moving_to_qpl'));
                    break;
                }
                $protect_by_write_protection();
                if (!$this->checkQuestionParametersForCopyToPool($row_ids)) {
                    break;
                };
                $copy_and_link_to_questionpool();
                break;

            default:
                throw new \InvalidArgumentException("No such table_cmd: {$cmd}.");
        }
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

            $type = ilObject::_lookupType(
                assQuestion::lookupParentObjId(
                    $question_properties[$q_id]->getOriginalId()
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
