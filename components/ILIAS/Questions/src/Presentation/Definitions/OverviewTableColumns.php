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

use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableTypes;
use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Question\Persistence\TableTypes;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\Column\Factory as ColumnFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

enum OverviewTableColumns: string
{
    case Title = 'title';
    case AnswerFormTypes = 'type';

    public static function getTableColums(
        Language $lng,
        ColumnFactory $column_factory
    ): array {
        return [
            OverviewTableColumns::Title->value
                => $column_factory->link($lng->txt('title')),
            OverviewTableColumns::AnswerFormTypes->value
                => $column_factory->text(
                    $lng->txt('contained_answer_form_types')
                )->withIsOptional(true, true)
            ->withIsSortable(false),
        ];
    }

    public static function getFilterInputs(
        Language $lng,
        FieldFactory $field_factory,
        array $answer_form_types_array_for_select
    ): array {
        return [
            self::Title->value => $field_factory->text(
                $lng->txt('title')
            ),
            self::AnswerFormTypes->value => $field_factory->multiSelect(
                $lng->txt('contains_answer_form_types'),
                $answer_form_types_array_for_select
            )->withRequired(true),
        ];
    }

    public function getDatabaseColumn(
        PersistenceFactory $persistence_factory,
        TableNameBuilder $table_names_builder
    ): ?Column {
        return $persistence_factory->column(
            $persistence_factory->table(
                $table_names_builder,
                match($this) {
                    self::Title => TableTypes::Questions,
                    self::AnswerFormTypes => AnswerFormGenericTableTypes::AnswerForms
                }
            ),
            $this->value
        );
    }

    public function transformFilterValue(
        AnswerFormFactory $answer_form_factory,
        mixed $value
    ): mixed {
        return match($this) {
            self::AnswerFormTypes => array_map(
                fn(string $v): string => $answer_form_factory
                    ->getTypeDefinitionFromSelectValue($v)::class,
                $value
            ),
            default => $value
        };
    }
}
