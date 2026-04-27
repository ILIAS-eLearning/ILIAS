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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Response;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Replace;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class AnswerInput
{
    public function __construct(
        private readonly Uuid $answer_input_id,
        private readonly ?Uuid $selected_answer_option,
        private readonly string $text
    ) {
    }

    public function getAnswerInputId(): Uuid
    {
        return $this->answer_input_id;
    }

    public function toStorage(
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder,
        PersistenceFactory $persistence_factory,
        ?Replace $insert,
        Uuid $id
    ): Insert {
        if ($insert === null) {
            return $persistence_factory->insert(
                $table_definitions->getColumns(
                    $table_names_builder,
                    AnswerFormSpecificTableTypes::Responses
                ),
                $this->buildValuesArrayForStorage($persistence_factory, $id)
            );
        }

        return $insert->withAdditionalValues(
            $this->buildValuesArrayForStorage($persistence_factory, $id)
        );
    }

    private function buildValuesArrayForStorage(
        PersistenceFactory $persistence_factory,
        Uuid $id
    ): array {
        return [
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->answer_input_id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->selected_answer_option?->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->text
            )
        ];
    }
}
