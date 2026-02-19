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

namespace ILIAS\Questions\AnswerForm;

use ILIAS\Questions\AnswerForm\Definition;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;

class Factory
{
    /**
     * @var array<string, \ILIAS\Questions\AnswerForm\Definition> $available_answer_form_types
     */
    private readonly array $available_answer_form_types;

    /**
     * @param array<\ILIAS\Questions\AnswerForm\Definition> $available_answer_form_types
     */
    public function __construct(
        private readonly UuidFactory $uuid_factory,
        array $available_answer_form_types
    ) {
        $this->available_answer_form_types = array_reduce(
            $available_answer_form_types,
            function (array $c, Definition $v) {
                $c[$this->getHashedClass($v::class)] = $v;
                return $c;
            },
            []
        );
    }

    /**
     *
     * @return array<string>
     */
    public function getAvailableDefinitions(): array
    {
        return array_values($this->available_answer_form_types);
    }

    public function getDefinitionForClass(
        string $class
    ): Definition {
        $definition = $this->available_answer_form_types[$this->getHashedClass($class)] ?? null;
        if ($definition === null) {
            throw new InvalidArgumentException('This type of answer form does not exist.');
        }
        return $definition;
    }

    /**
     * @return array<string, \ILIAS\Questions\AnswerForm\Definition>
     */
    public function getAnswerFormTypesArrayForSelect(
        Language $lng
    ): array {
        return array_reduce(
            $this->available_answer_form_types,
            function (array $c, Definition $v) use ($lng): array {
                $c[$this->getHashedClass($v::class)] = $v->getLabel($lng);
                return $c;
            },
            []
        );
    }

    public function getHashedClass(string $class): string
    {
        return md5($class);
    }

    public function buildTypeDefinitionFromSelectValue(
        string $value
    ): Definition {
        $type_definition = $this->available_answer_form_types[$value] ?? null;
        if ($type_definition === null) {
            throw new InvalidArgumentException('This type of answer form does not exist.');
        }
        return $type_definition;
    }

    public function getDefaultTypeGenericProperties(
        Uuid $question_id,
        Definition $type_definition,
        ?Uuid $answer_form_id = null
    ): TypeGenericProperties {
        return new TypeGenericProperties(
            $answer_form_id ?? $this->uuid_factory->uuid4(),
            $question_id,
            $type_definition
        );
    }

    public function buildTypeGenericPropertiesFromDatabase(
        array $db_values
    ): TypeGenericProperties {
        return new TypeGenericProperties(
            $this->uuid_factory->fromString($db_values['id']),
            $this->uuid_factory->fromString($db_values['question_id']),
            $this->getDefinitionForClass($db_values['type']),
            $db_values['available_points'],
            $db_values['image_size'],
            $db_values['shuffle_answer_options'] === 1,
            $db_values['additional_text'],
            $db_values['additional_text_legacy']
        );
    }
}
