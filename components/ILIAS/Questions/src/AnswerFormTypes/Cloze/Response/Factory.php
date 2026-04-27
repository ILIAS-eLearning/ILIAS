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
use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Query;

class Factory
{
    public function __construct(
        private readonly PersistenceFactory $persistence_factory
    ) {
    }

    public function fromData(
        Uuid $response_id,
        Uuid $answer_form_id,
        ?Query $query
    ): AnswerForm {
        if ($query === null) {
            return new AnswerForm(
                $response_id,
                $answer_form_id
            );
        }

        return $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $query->getTableNameBuilder(
                    $this->definition
                        ->getTableDefinitions()
                        ->getTableSubNameSpace()
                ),
                AnswerFormSpecificTableTypes::Responses
            ),
            $query->getRefinery()->custom()->transformation(
                fn(array $vs): AnswerForm => new AnswerForm(
                    $this->definition->getTableDefinitions(),
                )
            )
        );
    }

}
