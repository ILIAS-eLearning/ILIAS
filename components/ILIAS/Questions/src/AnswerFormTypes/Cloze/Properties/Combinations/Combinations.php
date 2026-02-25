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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Combinations;

use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\DataRowBuilder;

class Combinations
{
    private array $combinations;

    private array $deleted_combinations = [];

    public function __construct(
        private readonly Factory $combinations_factory,
        private readonly PersistenceFactory $persistence_factory,
        private readonly Uuid $answer_form_id,
        private bool $enabled,
        array $combinations
    ) {
        $this->combinations = array_reduce(
            $combinations,
            function (array $c, Combination $v): array {
                $c[$v->getId()->toString()] = $v;
                return $c;
            },
            []
        );
    }

    public function areCombinationsEnabled(): bool
    {
        return $this->enabled;
    }

    public function withCombinationsEnabled(
        bool $combinations_enabled
    ): self {
        $clone = clone $this;
        $clone->enabled = $combinations_enabled;
        return $clone;
    }

    public function getCombinationById(
        string $id
    ): ?Combination {
        return $this->combinations[$id] ?? null;
    }

    public function withAdditionalCombination(
        Combination $combination
    ): self {
        $clone = clone $this;
        $clone->combinations[$combination->getId()->toString()] = $combination;
        return $clone;
    }

    public function withoutCombination(
        string $id
    ): self {
        $clone = clone $this;
        $clone->deleted_combinations[] = $clone->combinations[$id];
        unset($clone->combinations[$id]);
        return $clone;
    }

    public function hasMatchingCombinationForAnswerOptionIds(
        array $vs
    ): bool {
        foreach ($this->combinations as $combination) {
            if ($combination->containsAnswerOptionsExactly($vs)) {
                return true;
            }
        }
        return false;
    }

    public function getEditView(
        \ilToolbarGUI $toolbar
    ): EditCombinations {
        return new EditCombinations(
            $toolbar,
            $this->combinations_factory
        );
    }

    public function toStorage(
        Manipulate $manipulate,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_name_builder
    ): Manipulate {
        return array_reduce(
            $this->combinations,
            fn(Manipulate $c, Combination $v): Manipulate => $v->toStorage(
                $this->answer_form_id,
                $this->persistence_factory,
                $table_definitions,
                $table_name_builder,
                $c
            ),
            array_reduce(
                $this->deleted_combinations,
                fn(Manipulate $c, Combination $v): Manipulate => $v->toDelete(
                    $this->persistence_factory,
                    $table_definitions,
                    $table_name_builder,
                    $manipulate
                ),
                $manipulate
            )
        );
    }

    public function toDelete(
        Manipulate $manipulate,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_name_builder
    ): Manipulate {
        return array_reduce(
            $this->combinations,
            fn(Manipulate $c, Combination $v): Manipulate => $c->withAdditionalStatement(
                $v->toDelete(
                    $this->persistence_factory,
                    $table_definitions,
                    $table_name_builder,
                    $manipulate
                )
            ),
            $manipulate
        );
    }

    public function toTableRows(
        Language $lng,
        DataRowBuilder $row_builder,
    ): \Generator {
        foreach ($this->combinations as $combination) {
            yield $combination->toTableRow($lng, $row_builder);
        }
    }

    public function getNumberOfCombinations(): int
    {
        return count($this->combinations);
    }
}
