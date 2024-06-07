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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\Modal\Interruptive;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\URI;
use ILIAS\HTTP\Services as HTTPService;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper as RequestWrapper;

use ILIAS\Modules\Test\QuestionPoolLinkedTitleBuilder;

/**
* (editing) table for questions in test
*/
class ilTestQuestionsTableGUI
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


    protected ServerRequestInterface $request;
    protected RequestWrapper $request_wrapper;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $row_id_token;

    protected string $context = self::CONTEXT_DEFAULT;
    protected bool $question_editing = true;

    /**
     * @param array $data <string, mixed>
     */
    public function __construct(
        protected UIFactory $ui_factory,
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        HTTPService $http,
        protected ilLanguage $lng,
        protected string $table_id,
        protected \Closure $qpl_link_builder,
    ) {
        $this->request = $http->request();
        $this->request_wrapper = $http->wrapper()->query();

        $url_builder = $this->getUrlBuilder();
        $query_params_namespace = ['qlist', $table_id];
        list($url_builder, $action_token, $row_id_token) = $url_builder->acquireParameters(
            $query_params_namespace,
            "action",
            "ids"
        );
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;
    }

    protected function getUrlBuilder(): URLBuilder
    {
        /**
         * getUri() may return http:// for servers behind a proxy; the request
         * will be blocked due to insecure targets on an otherwise secure connection.
         * getUriFromGlobals() includes the port (getUri does not) - but it's
         * the port from the actual machine, not the proxy.
         */
        $url = $this->request->getUriFromGlobals();
        $port = ':' . (string) $url->getPort();
        $url = str_replace($port, ':', $url->__toString()) ?? $url->__toString();

        return new URLBuilder(
            $this->data_factory->uri(
                $url
            )
        );
        return new URLBuilder(
            $this->data_factory->uri(
                $this->request->getUri()->__toString()
            )
        );
    }

    public function getTableCommand(): ?string
    {
        if(! $this->request_wrapper->has($this->action_token->getName())) {
            return null;
        }
        return $this->request_wrapper->retrieve(
            $this->action_token->getName(),
            $this->refinery->kindlyTo()->string()
        );
    }

    public function getRowIds(): ?array
    {
        if ($this->request_wrapper->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->identity()
        ) === ['ALL_OBJECTS']) {
            return ['ALL_OBJECTS'];
        }
        return $this->request_wrapper->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->always(null)
                ])
            )
        );
    }

    public function getOrderData(): ?array
    {
        return $this->getTable([])->getData();
    }

    public function getTable(array $data): Table\Ordering
    {
        $target = $this->url_builder->withParameter(
            $this->action_token,
            self::ACTION_SAVE_ORDER
        )->buildURI();

        $table = $this->ui_factory->table()->ordering(
            '',
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

        return new class (
            $data,
            $this->lng,
            $this->getTitleLinkBuilder($title_link_action),
            $this->qpl_link_builder,
            $this->context,
            $this->question_editing,
        ) implements Table\OrderingBinding {
            public function __construct(
                protected array $records,
                protected ilLanguage $lng,
                protected \Closure $title_link_builder,
                protected \Closure $qpl_link_builder,
                protected string $context,
                protected bool $editing_enabled,
            ) {
            }

            public function getRows(
                Table\OrderingRowBuilder $row_builder,
                array $visible_column_ids
            ): \Generator {
                foreach ($this->records as $position_index => $record) {
                    $row_id = (string) $record['question_id'];
                    $record['title'] = $this->getTitleLink($record['title'], $row_id);
                    $record['type_tag'] = $this->lng->txt($record['type_tag']);
                    $record['complete'] = (bool) $record['complete'];
                    $record['lifecycle'] = ilAssQuestionLifecycle::getInstance($record['lifecycle'])->getTranslation($this->lng) ?? '';
                    $record['qpl'] = $this->getQuestionPoolLink($record['orig_obj_fi']);

                    $default_and_edit = !($this->context === 'default' && $this->editing_enabled);
                    yield $row_builder->buildOrderingRow($row_id, $record)
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_DELETE, $default_and_edit && $this->context !== ilTestQuestionsTableGUI::CONTEXT_CORRECTIONS)
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_COPY, $default_and_edit)
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_ADD_TO_POOL, $default_and_edit)
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_PREVIEW, !($this->context === ilTestQuestionsTableGUI::CONTEXT_DEFAULT))
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_CORRECTION, !($this->context === ilTestQuestionsTableGUI::CONTEXT_CORRECTIONS))
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_EDIT_QUESTION, $default_and_edit)
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_EDIT_PAGE, $default_and_edit)
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_FEEDBACK, $default_and_edit)
                        ->withDisabledAction(ilTestQuestionsTableGUI::ACTION_HINTS, $default_and_edit);
                }
            }

            private function getTitleLink($title, $question_id): Link\Standard
            {
                $f = $this->title_link_builder;
                return $f($title, $question_id);
            }

            private function getQuestionPoolLink(?int $qpl_id): string
            {
                $f = $this->qpl_link_builder;
                return $f($qpl_id);
            }
        };
    }

    protected function getTitleLinkBuilder(string $title_link_action): \Closure
    {
        return fn(string $title, string $question_id): Link\Standard =>
            $this->ui_factory->link()->standard(
                $title,
                $this->url_builder
                    ->withParameter($this->action_token, $title_link_action)
                    ->withParameter($this->row_id_token, $question_id)
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
            'question_id' => $f->table()->column()->text($this->lng->txt("question_id"))
                ->withIsOptional(true, false),
            'title' => $f->table()->column()->link($this->lng->txt("tst_question_title")),
            'description' => $f->table()->column()->text($this->lng->txt("description"))
                ->withIsOptional(true, false),
            'complete' => $f->table()->column()->boolean(
                $this->lng->txt("question_complete_title"),
                $f->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small'),
                $f->symbol()->icon()->custom('assets/images/standard/icon_alert.svg', '', 'small')
            ),
            'type_tag' => $f->table()->column()->text($this->lng->txt("tst_question_type")),
            'points' => $f->table()->column()->text($this->lng->txt("points")),
            'author' => $f->table()->column()->text($this->lng->txt("author"))
                ->withIsOptional(true, false),
            'lifecycle' => $f->table()->column()->text($this->lng->txt("qst_lifecycle"))
                ->withIsOptional(true, false),
            'qpl' => $f->table()->column()->text($this->lng->txt("qpl")),
        ];

        if($this->context !== self::CONTEXT_DEFAULT) {
            unset($columns['complete']);
        }
        return $columns;
    }

    protected function getActions(): array
    {

        $std_actions = [
            self::ACTION_DELETE => 'delete',
        ];
        if($this->context === self::CONTEXT_DEFAULT) {
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

        if(! $this->question_editing) {
            $std_actions = [];
        }

        $actions = [];
        foreach(array_merge($single_actions, $std_actions) as $action => $txt) {
            $type = array_key_exists($action, $std_actions) ? 'standard' : 'single';
            $actions[$action] = $this->ui_factory->table()->action()->$type(
                $this->lng->txt($txt),
                $this->url_builder->withParameter($this->action_token, $action),
                $this->row_id_token
            );
            if($action === self::ACTION_DELETE) {
                $actions[$action] = $actions[$action]->withAsync();
            }
        }
        return $actions;
    }

    /**
     * @param array<array> $items [id, title, type]
     */
    public function getDeleteConfirmation(array $items): Interruptive
    {
        $affected = [];
        foreach ($items as $entry) {
            list($id, $title, $type) = $entry;
            $affected[] = $this->ui_factory->modal()->interruptiveItem()->keyvalue(
                (string) $id,
                $title,
                $type
            );
        }
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('remove'),
            $this->lng->txt('tst_remove_questions'),
            $this->url_builder->withParameter(
                $this->action_token,
                self::ACTION_DELETE_CONFIRMED
            )->buildURI()->__toString()
        )
        ->withAffectedItems($affected);
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

}
