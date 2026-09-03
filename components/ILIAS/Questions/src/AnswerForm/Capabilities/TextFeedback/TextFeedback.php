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

namespace ILIAS\Questions\AnswerForm\Capabilities\TextFeedback;

use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Feedback;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackView;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Capabilities\TypeSpecification;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\Text\Markdown;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

abstract class TextFeedback implements TypeSpecification, Feedback
{
    private const string KEY_PANEL_TITEL = 'panelTitle';
    private const string KEY_SPECIFIC_FEEDBACK_END_POINT = 'specificFeedbackEndpoint';
    private const string KEY_BEST_RESPONSE = 'best';
    private const string KEY_OTHER_RESPONSE = 'other';

    private ?Markdown $feedback_best_response = null;
    private string $feedback_best_response_legacy = '';
    private ?Markdown $feedback_other_response = null;
    private string $feedback_other_response_legacy = '';

    /**
     * @var array<string, SpecificTextFeedback>
     */
    private array $specific_feedbacks = [];

    abstract public function getAdditionalInputs(
        Language $lng,
        UIFactory $ui_factory,
        bool $set_legacy_texts_as_values
    ): ?array;

    abstract public function getSpecificFeedbackTable(
        Environment $environment
    ): ?OverviewTable;

    abstract public function onAnswerFormClone(
        UuidFactory $uuid_factory,
        Properties $old_answer_form_properties,
        Properties $new_answer_form_properties
    ): static;

    abstract public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): static;

    abstract protected function specificFeedbacksDisplayLegacyTexts(): bool;

    /**
     * @return list<Component>
     */
    abstract protected function getSpecificFeedbackParticipantOutput(
        UIFactory $ui_factory,
        Refinery $refinery,
        Properties $properties,
        ?Response $response
    ): array;

    /**
     * The endpoint must point to a javascript namespace containing the functions
     * `retrieveSpecificFeedback(data, bestResponse, response)` where `data` is
     * the array produced by `getSpecificFeedbackForClientSideCode()`, `bestResponse`
     * it the array produced by `Reponse::toClientSideRepresentation()` and `response`
     * is an object containing all the inputs and their values as set in the
     * interface.
     */
    abstract protected function getSpecificFeedbackClientSideEndPoint(): string;

    abstract protected function getSpecificFeedbackForClientSideCode(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        Properties $properties
    ): array;

    #[\Override]
    final public static function getCapabilityIdentifier(): string
    {
        return Capability::getIdentifier();
    }

    public function withGenericFeedbackFromDatabase(
        TextFactory $text_factory,
        ?array $database_data
    ): static {
        if ($database_data === null) {
            return $this;
        }
        $clone = clone $this;
        if ($database_data[0]['feedback_best_response'] !== '') {
            $clone->feedback_best_response = $text_factory->markdown(
                $database_data[0]['feedback_best_response']
            );
        }
        $clone->feedback_best_response_legacy = $database_data[0]['feedback_best_response_legacy'];

        if ($database_data[0]['feedback_other_response'] !== '') {
            $clone->feedback_other_response = $text_factory->markdown(
                $database_data[0]['feedback_other_response']
            );
        }
        $clone->feedback_other_response_legacy = $database_data[0]['feedback_other_response_legacy'];
        return $clone;
    }

    public function withSpecificFeedbackFromDatabase(
        UuidFactory $uuid_factory,
        TextFactory $text_factory,
        ?array $database_data
    ): static {
        if ($database_data === null
            || $database_data === []) {
            return $this;
        }

        $clone = clone $this;
        foreach ($database_data as $feedback_data) {
            $feedback = new SpecificTextFeedback(
                $uuid_factory->fromString($feedback_data['id']),
                $uuid_factory->fromString($feedback_data['answer_form_id']),
                $uuid_factory->fromString($feedback_data['parent_id']),
                $feedback_data['condition'],
                $feedback_data['feedback'] === ''
                    ? null
                    : $text_factory->markdown($feedback_data['feedback']),
                $feedback_data['feedback_legacy']
            );

            $clone->specific_feedbacks[$feedback_data['id']] = $feedback;
        }

        return $clone;
    }

    public function getFeedbackBestResponse(): ?Markdown
    {
        return $this->feedback_best_response;
    }

    public function withFeedbackBestResponse(
        Markdown $feedback_best_response
    ): static {
        $clone = clone $this;
        $clone->feedback_best_response = $feedback_best_response;
        return $clone;
    }

    public function getFeedbackBestResponseLegacy(): string
    {
        return $this->feedback_best_response_legacy;
    }

    public function getFeedbackOtherResponse(): ?Markdown
    {
        return $this->feedback_other_response;
    }

    public function withFeedbackOtherResponse(
        Markdown $feedback_other_response
    ): static {
        $clone = clone $this;
        $clone->feedback_other_response = $feedback_other_response;
        return $clone;
    }

    public function getFeedbackOtherResponseLegacy(): string
    {
        return $this->feedback_other_response_legacy;
    }

    public function displaysLegacyTexts(): bool
    {
        return $this->feedback_best_response === null
                && $this->feedback_best_response_legacy !== ''
            || $this->feedback_other_response === null
                && $this->feedback_other_response_legacy !== ''
            || $this->specificFeedbacksDisplayLegacyTexts();
    }

    public function getSpecificFeedbackForId(
        Uuid $id
    ): ?SpecificTextFeedback {
        return $this->specific_feedbacks[$id->toString()] ?? null;
    }

    public function getSpecificFeedbackForConditionOrNew(
        UuidFactory $uuid_factory,
        Uuid $answer_form_id,
        Uuid $parent_id,
        string $condition
    ): SpecificTextFeedback {
        $feedback = array_filter(
            $this->specific_feedbacks,
            fn(SpecificTextFeedback $v): bool => $v->getParentId()->toString() === $parent_id->toString()
                && $v->getCondition() === $condition
        );

        return $feedback !== []
            ? current($feedback)
            : new SpecificTextFeedback(
                $uuid_factory->uuid4(),
                $answer_form_id,
                $parent_id,
                $condition
            );
    }

    /**
     * @var array<string,TextFeedbackTypes>
     */
    public function getSpecificFeedbacks(): array
    {
        return $this->specific_feedbacks;
    }

    public function withSpecificFeedback(
        SpecificTextFeedback $specific_feedback
    ): static {
        $clone = clone $this;
        $clone->specific_feedbacks[$specific_feedback->getId()->toString()] = $specific_feedback;
        return $clone;
    }

    public function withoutSpecificFeedback(
        SpecificTextFeedback $specific_feedback
    ): static {
        $clone = clone $this;
        unset($clone->specific_feedbacks[$specific_feedback->getId()->toString()]);
        return $clone;
    }

    #[\Override]
    public function getParticipantOutput(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        Properties $properties,
        ?Response $response,
        RequiredCapabilities $required_capabilities
    ): ?FeedbackView {
        if ($response === null) {
            return [];
        }

        $generic_feedback = [];
        if ($required_capabilities->isMarkingRequired()) {
            $generic_feedback[] = $response->isBest()
                ? $this->getRenderedFeedbackBestResponse(
                    $lng,
                    $refinery,
                    $ui_factory
                ) : $this->getRenderedFeedbackOtherResponse(
                    $lng,
                    $refinery,
                    $ui_factory
                );
        }

        $feedback = [
            ...$generic_feedback,
            ...$this->getSpecificFeedbackParticipantOutput(
                $ui_factory,
                $refinery,
                $properties,
                $response
            )
        ];

        if ($feedback === []) {
            return null;
        }

        return new FeedbackView(
            $ui_factory->panel()->standard(
                $lng->txt('feedback'),
                $feedback
            )
        );
    }

    #[\Override]
    public function getFeedbackClientSideEndPoint(): string
    {
        return 'il.questions.textFeedback.retrieve';
    }

    #[\Override]
    public function getAllFeedbacksForClientSideCode(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        RequiredCapabilities $required_capabilities,
        Properties $properties
    ): array {
        $feedbacks = [];
        if ($required_capabilities->isMarkingRequired()) {
            $feedbacks[self::KEY_BEST_RESPONSE] = $ui_renderer->render(
                $this->getRenderedFeedbackBestResponse(
                    $lng,
                    $refinery,
                    $ui_factory
                )
            );

            $feedbacks[self::KEY_OTHER_RESPONSE] = $ui_renderer->render(
                $this->getRenderedFeedbackOtherResponse(
                    $lng,
                    $refinery,
                    $ui_factory
                )
            );
        }

        return [
            self::KEY_PANEL_TITEL => $lng->txt('feedback'),
            self::KEY_SPECIFIC_FEEDBACK_END_POINT => $this->getSpecificFeedbackClientSideEndPoint(),
            ...$feedbacks,
            ...$this->getSpecificFeedbackForClientSideCode(
                $lng,
                $refinery,
                $ui_factory,
                $properties
            )
        ];
    }

    public function toStorage(
        PersistenceFactory $persistence_factory,
        TableDefinitions $feedback_table_definitions,
        TableNameBuilder $feedback_table_names_builder,
        Uuid $answer_form_id,
        Manipulate $manipulate
    ): Manipulate {
        $manipulate_with_feedback = $manipulate
            ->withAdditionalStatement(
                $this->buildReplaceForGenericFeedback(
                    $persistence_factory,
                    $feedback_table_definitions,
                    $feedback_table_names_builder,
                    $answer_form_id
                )
            )->withAdditionalStatement(
                $this->buildDeleteForSpecificFeedback(
                    $persistence_factory,
                    $feedback_table_definitions,
                    $feedback_table_names_builder,
                    $answer_form_id
                )
            );

        $specific_feedback_replace = $this->buildReplaceForSpecificFeedback(
            $persistence_factory,
            $feedback_table_definitions,
            $feedback_table_names_builder
        );

        if ($specific_feedback_replace === null) {
            return $manipulate_with_feedback;
        }

        return $manipulate_with_feedback->withAdditionalStatement(
            $specific_feedback_replace
        );
    }

    private function buildReplaceForGenericFeedback(
        PersistenceFactory $persistence_factory,
        TableDefinitions $feedback_table_definitions,
        TableNameBuilder $feedback_table_names_builder,
        Uuid $answer_form_id
    ): Replace {
        return $persistence_factory->replace(
            $feedback_table_definitions->getColumns(
                $feedback_table_names_builder,
                TableTypes::FeedbackGeneric
            ),
            [
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $answer_form_id->toString()
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->feedback_best_response?->getRawRepresentation() ?? ''
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->feedback_best_response_legacy
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->feedback_other_response?->getRawRepresentation() ?? ''
                ),
                $persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $this->feedback_other_response_legacy
                )
            ]
        );
    }

    private function buildDeleteForSpecificFeedback(
        PersistenceFactory $persistence_factory,
        TableDefinitions $feedback_table_definitions,
        TableNameBuilder $feedback_table_names_builder,
        Uuid $answer_form_id
    ): Delete {
        return $persistence_factory->delete(
            $persistence_factory->table(
                $feedback_table_names_builder,
                TableTypes::FeedbackSpecific
            ),
            [
                $persistence_factory->where(
                    $feedback_table_definitions->getForeignKeyColumn(
                        $feedback_table_names_builder,
                        TableTypes::FeedbackSpecific
                    ),
                    new Value(
                        FieldDefinition::T_TEXT,
                        $answer_form_id->toString()
                    )
                )
            ]
        );
    }

    private function buildReplaceForSpecificFeedback(
        PersistenceFactory $persistence_factory,
        TableDefinitions $feedback_table_definitions,
        TableNameBuilder $feedback_table_names_builder
    ): ?Replace {
        return array_reduce(
            $this->specific_feedbacks,
            fn(?Replace $c, SpecificTextFeedback $v) => $c === null
                ? $persistence_factory->replace(
                    $feedback_table_definitions->getColumns(
                        $feedback_table_names_builder,
                        TableTypes::FeedbackSpecific
                    ),
                    $v->toStorage($persistence_factory)
                ) : $c->withAdditionalValues(
                    $v->toStorage($persistence_factory)
                )
        );
    }

    private function getRenderedFeedbackBestResponse(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory
    ): Component {
        if ($this->feedback_best_response === null) {
            return $ui_factory->messageBox()->success(
                $this->feedback_best_response_legacy === ''
                    ? $lng->txt('best_response_given')
                    : $this->feedback_best_response_legacy
            );
        }

        $rendered_markdown = $refinery->string()->markdown()->toHTML()
            ->transform($this->feedback_best_response->getRawRepresentation());

        return $ui_factory->messageBox()->success(
            $rendered_markdown === ''
                ? $lng->txt('best_response_given')
                : $rendered_markdown
        );
    }

    private function getRenderedFeedbackOtherResponse(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory
    ): Component {
        if ($this->feedback_other_response === null) {
            return $ui_factory->messageBox()->info(
                $this->feedback_other_response_legacy === ''
                    ? $lng->txt('other_response_given')
                    : $this->feedback_other_response_legacy
            );
        }

        $rendered_markdown = $refinery->string()->markdown()->toHTML()
            ->transform($this->feedback_other_response->getRawRepresentation());

        return $ui_factory->messageBox()->info(
            $rendered_markdown === ''
                ? $lng->txt('other_response_given')
                : $rendered_markdown
        );
    }
}
