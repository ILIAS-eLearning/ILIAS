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

namespace ILIAS\Questions\Presentation\Definitions;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Layout\Factory;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class EnvironmentImplementation implements Environment
{
    private const array QUERY_PARAMETER_NAME_SPACE = ['q'];
    private const string TOKEN_STRING_ACTION = 'a';
    private const string TOKEN_STRING_STEP = 's';
    private const string TOKEN_STRING_QUESTION_ID = 'q';
    private const string TOKEN_STRING_QUESTION_IDS = 'qs';
    private const string TOKEN_TYPE_HASH = 't';
    private const string TOKEN_TABLE_ROW_ID = 'r';
    private const string TOKEN_CARRY_ID = 'c';

    private const string INTERRUPTIVE_ITEMS_KEY = 'interruptive_items';

    private const string TAB_ID_ANSWER_FORM = 'answer_form';

    private ?Properties $properties = null;

    private bool $default_step = false;

    private URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $step_token;
    private readonly URLBuilderToken $question_id_token;
    private readonly URLBuilderToken $question_ids_token;
    private readonly URLBuilderToken $type_hash_token;
    private readonly URLBuilderToken $table_row_token;
    private readonly URLBuilderToken $carry_token;

    private ?array $table_row_ids = null;

    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly HTTPServices $http,
        private readonly Refinery $refinery,
        private readonly Language $lng,
        private readonly \ilTabsGUI $tabs_gui,
        private readonly UuidFactory $uuid_factory,
        private readonly Factory $presentation_factory,
        private readonly Editability $editability,
        URI $base_uri,
        private readonly int $obj_id
    ) {
        $this->acquireURLBuilderAndParameters($base_uri);
    }

    #[\Override]
    public function setEditAnswerFormBackTarget(): void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('cancel'),
            $this->withDefaultStep()->getUrlBuilder()->buildURI()->__toString()
        );
    }

    #[\Override]
    public function addEditAnswerFormSubTab(
        string $step,
        string $language_variable
    ): void {
        $this->tabs_gui->addSubTab(
            $step,
            $this->lng->txt($language_variable),
            $this->getUrlBuilderWithStepParameter($step)
                ->withParameter(
                    $this->action_token,
                    Edit::CMD_OTHER_ANSWER_FORM
                )->buildURI()
                ->__toString()
        );
    }

    #[\Override]
    public function activateEditAnswerFormSubTab(
        string $step
    ): void {
        $this->tabs_gui->activateSubTab($step);
    }

    #[\Override]
    public function getPresentationFactory(): Factory
    {
        return $this->presentation_factory;
    }

    #[\Override]
    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }

    #[\Override]
    public function getUrlBuilderWithStepParameter(
        string $step
    ): URLBuilder {
        return $this->getUrlBuilder()->withParameter($this->step_token, $step);
    }

    #[\Override]
    public function withDefaultStep(): self
    {
        $clone = clone $this;
        $clone->default_step = true;
        return $clone;
    }

    #[\Override]
    public function getStep(): string
    {
        return $this->default_step
            ? ''
            : $this->retrieveStringValueForToken($this->step_token, self::TOKEN_STRING_STEP);
    }

    #[\Override]
    public function getEditability(): Editability
    {
        return $this->editability;
    }

    #[\Override]
    public function getAnswerFormProperties(): ?Properties
    {
        return $this->properties;
    }

    #[\Override]
    public function withAnswerFormProperties(
        Properties $properties
    ): self {
        $clone = clone $this;
        $clone->properties = $properties;
        return $clone;
    }

    #[\Override]
    public function getTableRowIdToken(): URLBuilderToken
    {
        return $this->table_row_token;
    }

    /**
     * @return list<string>
     */
    #[\Override]
    public function getTableRowIds(): array
    {
        if ($this->table_row_ids !== null) {
            return $this->table_row_ids;
        }

        return $this->table_row_ids = $this->http->wrapper()->query()->retrieve(
            $this->table_row_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->custom()->transformation(
                        fn($v): string => $v !== ''
                            ? $this->refinery->kindlyTo()->string()->transform($v)
                            : throw new \UnexpectedValueException()
                    )
                ),
                $this->refinery->always([])
            ])
        );
    }

    #[\Override]
    public function withPreservedTableRowIdsParameter(): self
    {
        $clone = clone $this;
        $clone->table_row_ids = $clone->getTableRowIds();
        $clone->url_builder = $this->url_builder
            ->withParameter($this->table_row_token, $clone->table_row_ids);
        return $clone;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getQuestionIdsToken(): URLBuilderToken
    {
        return $this->question_ids_token;
    }

    public function getAction(): string
    {
        return $this->retrieveStringValueForToken($this->action_token);
    }

    public function withActionParameter(
        string $action
    ): self {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->action_token, $action);
        return $clone;
    }

    public function withQuestionIdParameter(
        Uuid $question_id
    ): self {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->question_id_token, $question_id->toString());
        return $clone;
    }

    public function withAnswerFormTypeHashParameter(
        string $type_hash
    ): self {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->type_hash_token, $type_hash);
        return $clone;
    }

    public function withCarryParameter(
        string $carry
    ): self {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->carry_token, $carry);
        return $clone;
    }

    public function getQuestionId(): ?Uuid
    {
        return $this->http->wrapper()->query()->retrieve(
            $this->question_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    $this->buildRetrieveQuestionIdClosure()
                ),
                $this->refinery->always(null)
            ])
        );
    }

    /**
     * This function will either return the QuestionIds from the corresponding
     * $_GET parameter OR from an InterruptiveItems $_POST value.
     * @return array<\ILIAS\Data\UUID\Uuid>|string|null
     */
    public function getQuestionIds(): array|string|null
    {
        return $this->http->wrapper()->query()->retrieve(
            $this->question_ids_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    fn($v): string => $v === ['ALL_OBJECTS']
                        ? 'ALL_OBJECTS'
                        : throw new \UnexpectedValueException()
                ),
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->custom()->transformation(
                        $this->buildRetrieveQuestionIdClosure()
                    )
                ),
                $this->refinery->always(null)
            ])
        ) ?? $this->http->wrapper()->post()->retrieve(
            self::INTERRUPTIVE_ITEMS_KEY,
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->custom()->transformation(
                    $this->buildRetrieveQuestionIdClosure()
                )
            ),
            $this->refinery->always(null)
        );
    }

    public function getTypeClassHash(): string
    {
        return $this->retrieveStringValueForToken($this->type_hash_token);
    }

    public function getCarry(
        Transformation $to_form_transformation
    ): Input|array|string|null {
        return $this->http->wrapper()->query()->retrieve(
            $this->carry_token->getName(),
            $to_form_transformation
        );
    }

    public function setEditAnswerFormTabs(
        string $cmd_feedback,
        string $cmd_content_for_repetition
    ): void {
        $this->tabs_gui->addTab(
            self::TAB_ID_ANSWER_FORM,
            $this->lng->txt('answer_form'),
            $this->withDefaultStep()->getUrlBuilder()->buildURI()->__toString()
        );

        $this->tabs_gui->addTab(
            $cmd_feedback,
            $this->lng->txt('feedback'),
            $this->withActionParameter($cmd_feedback)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );

        $this->tabs_gui->addTab(
            $cmd_content_for_repetition,
            $this->lng->txt('suggested_solution'),
            $this->withActionParameter($cmd_content_for_repetition)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );

        $this->tabs_gui->addSubTab(
            self::TAB_ID_ANSWER_FORM,
            $this->lng->txt('overview'),
            $this->withDefaultStep()->getUrlBuilder()->buildURI()->__toString()
        );

        $this->tabs_gui->activateTab(self::TAB_ID_ANSWER_FORM);
        $this->tabs_gui->activateSubTab(self::TAB_ID_ANSWER_FORM);
    }

    public function setParametersForQuestionCmds(): void
    {
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            $this->question_id_token->getName(),
            $this->getQuestionId()->toString()
        );
    }

    private function acquireURLBuilderAndParameters(
        URI $base_uri
    ): void {
        [
            $this->url_builder,
            $this->action_token,
            $this->step_token,
            $this->question_id_token,
            $this->question_ids_token,
            $this->type_hash_token,
            $this->table_row_token,
            $this->carry_token
        ] = (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_ACTION,
                self::TOKEN_STRING_STEP,
                self::TOKEN_STRING_QUESTION_ID,
                self::TOKEN_STRING_QUESTION_IDS,
                self::TOKEN_TYPE_HASH,
                self::TOKEN_TABLE_ROW_ID,
                self::TOKEN_CARRY_ID
            );
    }

    private function retrieveStringValueForToken(
        URLBuilderToken $token
    ): string {
        return $this->http->wrapper()->query()->retrieve(
            $token->getName(),
            $this->buildStringTrafo()
        );
    }

    private function buildStringTrafo(): Transformation
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->string(),
            $this->refinery->always('')
        ]);
    }

    private function buildRetrieveQuestionIdClosure(): \Closure
    {
        return fn($v): Uuid => is_string($v)
            ? $this->uuid_factory->fromString($v)
            : throw new \UnexpectedValueException();
    }
}
