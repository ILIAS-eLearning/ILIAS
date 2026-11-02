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

namespace ILIAS\Questions\AnswerFormTypes\Cloze;

use ILIAS\Questions\AnswerForm\Capabilities\TypeSpecification;
use ILIAS\Questions\AnswerForm\Definition as DefinitionInterface;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Factory as PropertiesFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\AnswerForm as Response;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\Factory as ResponseFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Views\Edit;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\AnswerForm\Persistence\TableDefinitions;
use ILIAS\Language\Language;
use ILIAS\Data\UUID\Uuid;

class Definition implements DefinitionInterface
{
    /**
     * @param array<string, TypeSpecification> $available_capabilities
     */
    private readonly array $available_capabilities;

    /**
     * @param list<TypeSpecification> $available_capabilities
     */
    public function __construct(
        private readonly TableDefinitions $table_definitions,
        array $available_capabilities,
        private readonly PropertiesFactory $properties_factory,
        private readonly ResponseFactory $response_factory,
        private readonly Edit $edit_view
    ) {
        $this->available_capabilities = array_reduce(
            $available_capabilities,
            function (array $c, TypeSpecification $v): array {
                $c[$v::getCapabilityIdentifier()] = $v;
                return $c;
            },
            []
        );
    }

    #[\Override]
    public function getLabel(
        Language $lng
    ): string {
        return $lng->txt('cloze');
    }

    #[\Override]
    public function buildProperties(
        TypeGenericProperties $type_generic_data,
        ?Query $query
    ): Properties {
        return $this->properties_factory->fromData(
            $type_generic_data,
            $query
        );
    }

    #[\Override]
    public function buildResponse(
        Uuid $response_id,
        AnswerFormProperties $answer_form_properties,
        ?Query $query
    ): Response {
        return $this->response_factory->fromQuery(
            $response_id,
            $answer_form_properties,
            $query
        );
    }

    #[\Override]
    public function buildResponseFromPreviewData(
        Uuid $response_id,
        AnswerFormProperties $answer_form_properties,
        array $preview_data
    ): Response {
        return $this->response_factory->fromPreviewData(
            $response_id,
            $answer_form_properties,
            $preview_data
        );
    }

    #[\Override]
    public function getTableDefinitions(): TableDefinitions
    {
        return $this->table_definitions;
    }

    #[\Override]
    public function hasCapability(
        string $capability_identifier
    ): bool {
        return array_key_exists($capability_identifier, $this->available_capabilities);
    }

    #[\Override]
    public function getCapability(
        string $capability_identifier
    ): ?TypeSpecification {
        return $this->available_capabilities[$capability_identifier] ?? null;
    }

    #[\Override]
    public function getEditView(): Edit
    {
        return $this->edit_view;
    }

    #[\Override]
    public function initializeAttemptData(
        Attempt $attempt,
        AnswerFormProperties $answer_form_properties
    ): Attempt {
        return $answer_form_properties->getGaps()->initializeAttemptData($attempt);
    }
}
