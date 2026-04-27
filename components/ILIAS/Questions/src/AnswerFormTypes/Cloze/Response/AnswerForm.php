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
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class AnswerForm implements Response
{
    /**
     * @var array<string, AnswerInput> $answer_input_responses
     */
    private readonly array $answer_input_responses;

    /**
     * @param array<AnswerInput> $answer_input_responses
     */
    public function __construct(
        private readonly TableDefinitions $table_definitions,
        private readonly Uuid $response_id,
        private readonly Uuid $answer_form_id,
        array $answer_input_responses
    ) {
        $this->answer_input_responses = array_reduce(
            $answer_input_responses,
            function (array $c, AnswerInput $v): array {
                $c[$v->getAnswerInputId()->toString()] = $v;
                return $c;
            },
            []
        );
    }

    #[\Override]
    public function getAnswerFormId(): Uuid
    {
        return $this->answer_form_id;
    }

    public function toStorage(
        PersistenceFactory $persistence_factory,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate->withAdditionalStatement(
            array_reduce(
                $this->answer_input_responses,
                fn(?Replace $c, AnswerInput $v): Insert => $v->toStorage(
                    $this->table_definitions,
                    $manipulate->getTableNameBuilder(
                        $this->table_definitions->getTableSubNameSpace()
                    ),
                    $persistence_factory,
                    $c,
                    $this->response_id
                )
            )
        );
    }

    public function toDelete(
        PersistenceFactory $persistence_factory,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate->withAdditionalStatement(
            $persistence_factory->delete(
                $persistence_factory->table(
                    $manipulate->getTableNameBuilder(
                        AnswerFormSpecificTableTypes::Responses
                    ),
                    AnswerFormSpecificTableTypes::Responses
                ),
                [
                    $persistence_factory->where(
                        $this->table_definitions->getIdColumn(
                            $persistence_factory,
                            AnswerFormSpecificTableTypes::Responses
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $this->response_id->toString()
                        )
                    )
                ]
            )
        );
    }
}
