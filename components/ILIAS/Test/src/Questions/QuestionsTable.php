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

namespace ILIAS\Test\Questions;

use ILIAS\Test\Utilities\TitleColumnsBuilder;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\Language\Language;

use Psr\Http\Message\ServerRequestInterface;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
* (editing) table for questions in test
*/
class QuestionsTable
{
    public const ACTION_SAVE_ORDER = 'save_order';
    public const ACTION_DELETE = 'delete';
    public const ACTION_DELETE_CONFIRMED = 'deletion_confirmed';
    public const ACTION_COPY = 'copy';
    public const ACTION_ADD_TO_POOL = 'add_qpl';
    public const ACTION_PREVIEW = 'preview';
    public const ACTION_CORRECTION = 'correction';
    public const ACTION_STATISTICS = 'statistics';
    public const ACTION_EDIT_QUESTION = 'edit_question';
    public const ACTION_EDIT_PAGE = 'edit_page';
    public const ACTION_FEEDBACK = 'feedback';
    public const ACTION_HINTS = 'hints';

    public const CONTEXT_DEFAULT = 'default';
    public const CONTEXT_CORRECTIONS = 'corrections';

    protected string $table_id;
    protected string $context = self::CONTEXT_DEFAULT;
    protected bool $question_editing = true;

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
        protected GeneralQuestionPropertiesRepository $questionrepository,
        protected TitleColumnsBuilder $title_builder
    ) {
        $this->table_id = (string) $test_obj->getId();
    }

    public function withContextCorrections(): self
    {
        $clone = clone $this;
        $clone->context = self::CONTEXT_CORRECTIONS;
        return $clone;
    }

    public function withQuestionEditing(bool $question_editing = true): self
    {
        $clone = clone $this;
        $clone->question_editing = $question_editing;
        return $clone;
    }

    protected function getOrderData(): ?array
    {
        return $this->getTableComponent([])->getData();
    }

    public function getTableComponent(array $data): Table\Ordering
    {
        $target = $this->commands->getActionURL(self::ACTION_SAVE_ORDER);
        $table = $this->ui_factory->table()->ordering(
            $this->lng->txt('list_of_questions'),
            $this->getColumns(),
            $this->getBinding($data),
            $target
        )
        ->withId($this->table_id)
        ->withActions($this->getActions())
        ->withRequest($this->request);

        return $table;
    }

    protected function getBinding(array $data): Table\OrderingBinding
    {
        $title_link_action = $this->context === self::CONTEXT_DEFAULT
            ? self::ACTION_PREVIEW : self::ACTION_CORRECTION;

        return new QuestionsTableBinding(
            $data,
            $this->lng,
            $this->getTitleLinkBuilder($title_link_action),
            $this->title_builder,
            $this->context,
            $this->question_editing,
        );
    }

    protected function getTitleLinkBuilder(string $title_link_action): \Closure
    {
        list($url_builder, $row_id_token) = $this->commands->getRowBoundURLBuilder($title_link_action);
        return fn(string $title, int $question_id): Link\Standard =>
            $this->ui_factory->link()->standard(
                $title,
                $url_builder
                    ->withParameter($row_id_token, (string) $question_id)
                    ->buildURI()
                    ->__toString()
            );
    }

    /**
     * @return Column[]
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
        ];

        if ($this->context !== self::CONTEXT_DEFAULT) {
            unset($columns['complete']);
        }
        return $columns;
    }

    protected function getActions(): array
    {
        $std_actions = [
            self::ACTION_DELETE => 'delete',
        ];
        if ($this->context === self::CONTEXT_DEFAULT) {
            $std_actions[self::ACTION_COPY] = 'copy';
            $std_actions[self::ACTION_ADD_TO_POOL] = 'copy_and_link_to_questionpool';
        }

        $single_actions = [
            self::ACTION_PREVIEW => 'preview',
            self::ACTION_CORRECTION => 'tst_corrections_qst_form',
            self::ACTION_STATISTICS => 'statistics',
            self::ACTION_EDIT_QUESTION => 'edit_question',
            self::ACTION_EDIT_PAGE => 'edit_page',
            self::ACTION_FEEDBACK => 'tst_feedback',
            self::ACTION_HINTS => 'tst_question_hints_tab',
        ];

        if (! $this->question_editing) {
            $std_actions = [];
        }

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
        switch($cmd) {
            case QuestionsTable::ACTION_SAVE_ORDER:
                $data = $this->getOrderData();
                $protect_by_write_protection();
                $this->test_obj->setQuestionOrderAndObligations(array_flip($data), []);
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
        foreach ($question_ids as $q_id) {
            if (!$this->questionrepository->originalQuestionExists($q_id)) {
                continue;
            }

            $type = ilObject::_lookupType(
                assQuestion::lookupParentObjId(
                    $this->questionrepository->getForQuestionId($q_id)->getOriginalId()
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
