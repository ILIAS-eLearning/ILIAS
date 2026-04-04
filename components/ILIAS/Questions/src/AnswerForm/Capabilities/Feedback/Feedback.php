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

namespace ILIAS\Questions\AnswerForm\Capabilities\Feedback;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Persistence\Delete;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Question\Response;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\Text\Markdown;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;

abstract class Feedback
{
    private ?Markdown $feedback_best_response = null;
    private string $feedback_best_response_legacy = '';
    private ?Markdown $feedback_other_response = null;
    private string $feedback_other_response_legacy = '';

    private array $specific_feedbacks = [];

    abstract public function getAdditionalInputs(
        Language $lng,
        UIFactory $ui_factory,
        bool $set_legacy_texts_as_values
    ): ?array;

    abstract public function getSpecificFeedbackTable(
        Environment $environment
    ): ?OverviewTable;

    abstract public function getSpecificFeedbackParticipantOutput(
        Response $response,
        string $answer_id
    ): array;

    abstract public function specificFeedbackInputsHaveLegacyTexts(): bool;

    abstract public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): static;

    public function withGenericFeedbackFromDatabase(
        TextFactory $text_factory,
        ?array $database_data
    ): static {
        if ($database_data === null) {
            return $this;
        }
        $clone = clone $this;
        $clone->feedback_best_response = $text_factory->markdown(
            $database_data[0]['feedback_best_response']
        );
        $clone->feedback_best_response_legacy = $database_data[0]['feedback_best_response_legacy'];
        $clone->feedback_other_response = $text_factory->markdown(
            $database_data[0]['feedback_other_response']
        );
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
            $feedback = new SpecificFeedback(
                $uuid_factory->fromString($feedback_data['id']),
                $uuid_factory->fromString($feedback_data['answer_form_id']),
                $uuid_factory->fromString($feedback_data['parent_id']),
                $feedback_data['condition'],
                $text_factory->markdown($feedback_data['feedback']),
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

    public function hasLegacyTexts(): bool
    {
        return $this->feedback_best_response_legacy !== ''
            || $this->feedback_other_response_legacy !== ''
            || $this->specificFeedbackInputsHaveLegacyTexts();
    }

    public function getSpecificFeedbackForId(
        Uuid $id
    ): ?SpecificFeedback {
        return $this->specific_feedbacks[$id->toString()];
    }

    public function getSpecificFeedbackForConditionOrNew(
        UuidFactory $uuid_factory,
        Uuid $answer_form_id,
        Uuid $parent_id,
        string $condition
    ): SpecificFeedback {
        $feedback = array_filter(
            $this->specific_feedbacks,
            fn(SpecificFeedback $v): bool => $v->getParentId()->toString() === $parent_id->toString()
                && $v->getCondition() === $condition
        );

        return $feedback !== []
            ? current($feedback)
            : new SpecificFeedback(
                $uuid_factory->uuid4(),
                $answer_form_id,
                $parent_id,
                $condition
            );
    }

    public function getSpecificFeedbacks(): array
    {
        return $this->specific_feedbacks;
    }

    public function withSpecificFeedback(
        SpecificFeedback $specific_feedback
    ): static {
        $clone = clone $this;
        $clone->specific_feedbacks[$specific_feedback->getId()->toString()] = $specific_feedback;
        return $clone;
    }

    public function withoutSpecificFeedback(
        SpecificFeedback $specific_feedback
    ): static {
        $clone = clone $this;
        unset($clone->specific_feedbacks[$specific_feedback->getId()->toString()]);
        return $clone;
    }

    public function getGenericFeedbackParticipantOutput(
        Response $response
    ): array {

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
            fn(?Replace $c, SpecificFeedback $v) => $c === null
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
}
