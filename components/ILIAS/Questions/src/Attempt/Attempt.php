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

namespace ILIAS\Questions\Attempt;

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Refinery\Random\Seed\GivenSeed;

class Attempt implements AdditionalAttemptData
{
    public const string KEY_ADDITONAL_DATA = 'additional_data';
    public const string KEY_RESPONSES = 'responses';

    /**
     * @var array<Response>
     */
    private array $responses = [];

    public function __construct(
        private readonly Uuid $identifier,
        private readonly int $shuffle_questions_seed,
        private array $additional_data = []
    ) {
    }

    public function getId(): Uuid
    {
        return $this->identifier;
    }

    public function getShuffleQuestionsSeed(): GivenSeed
    {
        return new GivenSeed($this->shuffle_questions_seed);
    }

    public function getAdditionalDataFor(
        Uuid $parent_id
    ): ?string {
        if (!isset($this->additional_data[$parent_id->toString()])) {
            return null;
        }
        return $this->additional_data[$parent_id->toString()];
    }

    public function withAdditionalData(
        Uuid $parent_id,
        string $data
    ): self {
        if (isset($this->additional_data[$parent_id->toString()])) {
            throw new InvalidArgumentException(
                'This is a storage for data that stays constant accross the test run.'
                . 'Data cannot be changed one it is set.'
            );
        }

        $clone = clone $this;
        $clone->additional_data[$parent_id->toString()] = $data;
        return $clone;
    }

    public function getResponseForQuestion(
        Uuid $question_id
    ): ?Response {
        return $this->responses[$question_id->toString()] ?? null;
    }

    public function withResponse(
        Response $response
    ): self {
        $clone = clone $this;
        $clone->responses[$response->getQuestionId()->toString()] = $response;
        return $clone;
    }

    public function basicDataToStorage(
        PersistenceFactory $persistence_factory,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder
    ): Insert {
        return $persistence_factory->insert(
            $table_definitions->getColumns(
                $table_names_builder,
                TableTypes::AttemptData
            ),
            [
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->identifier->toString()
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $this->shuffle_questions_seed
                )
            ]
        );
    }

    public function additionalDataToStorage(
        PersistenceFactory $persistence_factory,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder
    ): ?Insert {
        return array_reduce(
            array_keys($this->additional_data),
            fn(?Insert $c, string $v): Insert
                => $this->buildAdditionalDataInsert(
                    $persistence_factory,
                    $table_definitions,
                    $table_names_builder,
                    $c,
                    $v
                )
        );
    }

    public function toPreviewStorage(): string
    {
        return json_encode(
            [
                self::KEY_ADDITONAL_DATA => $this->additional_data,
                self::KEY_RESPONSES => array_map(
                    fn(Response $v): array => $v->toPreviewStorage(),
                    $this->responses
                )
            ]
        );
    }

    private function buildAdditionalDataInsert(
        PersistenceFactory $persistence_factory,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder,
        ?Insert $insert,
        string $parent_id
    ): Insert {
        if ($insert === null) {
            return $persistence_factory->insert(
                $table_definitions->getColumns(
                    $table_names_builder,
                    TableTypes::AdditionalAttemptData
                ),
                $this->buildAdditionalDataValuesArray(
                    $persistence_factory,
                    $parent_id
                )
            );
        }

        return $insert->withAdditionalValues(
            $this->buildAdditionalDataValuesArray(
                $persistence_factory,
                $parent_id
            )
        );
    }

    private function buildAdditionalDataValuesArray(
        PersistenceFactory $persistence_factory,
        string $parent_id
    ): array {
        return [
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $this->identifier->toString()
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $parent_id
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $this->additional_data[$parent_id]
            )
        ];
    }
}
