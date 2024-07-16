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

namespace ILIAS\Test\Scoring\Marks;

use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

class MarkSchemaTable implements DataRetrieval
{
    public const DELETE_ACTION_NAME = 'delete';
    public const EDIT_ACTION_NAME = 'edit';

    public function __construct(
        private MarkSchema $mark_schema,
        private bool $marks_editable,
        private \ilLanguage $lng,
        private URLBuilder $url_builder,
        private URLBuilderToken $action_parameter_token,
        private URLBuilderToken $row_id_token,
        private UIFactory $ui_factory
    ) {
    }

    public function getTable(): DataTable
    {
        $f = $this->ui_factory->table();

        $table = $f->data(
            $this->lng->txt('mark_schema'),
            [
                'name' => $f->column()->text($this->lng->txt('tst_mark_short_form')),
                'official_name' => $f->column()->text($this->lng->txt('tst_mark_official_form')),
                'minimum_level' => $f->column()->text($this->lng->txt('tst_mark_minimum_level')),
                'passed' => $f->column()->boolean(
                    $this->lng->txt('tst_mark_passed'),
                    $this->ui_factory->symbol()->icon()->custom(
                        'templates/default/images/standard/icon_checked.svg',
                        $this->lng->txt('yes'),
                        'small'
                    ),
                    $this->ui_factory->symbol()->icon()->custom(
                        'templates/default/images/standard/icon_unchecked.svg',
                        $this->lng->txt('no'),
                        'small'
                    )
                )
            ],
            $this
        )->withActions(
            [
                self::EDIT_ACTION_NAME => $f->action()->single(
                    $this->lng->txt('edit'),
                    $this->url_builder->withParameter($this->action_parameter_token, self::EDIT_ACTION_NAME),
                    $this->row_id_token
                )->withAsync(),
                self::DELETE_ACTION_NAME => $f->action()->standard(
                    $this->lng->txt('delete'),
                    $this->url_builder->withParameter($this->action_parameter_token, self::DELETE_ACTION_NAME),
                    $this->row_id_token
                )->withAsync()
            ]
        );

        return $table;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->mark_schema->getMarkSteps() as $index => $mark) {
            yield $row_builder->buildDataRow(
                (string) $index,
                [
                    'name' => $mark->getShortName(),
                    'official_name' => $mark->getOfficialName(),
                    'minimum_level' => $mark->getMinimumLevel(),
                    'passed' => $mark->getPassed()
                ]
            )->withDisabledAction('edit', !$this->marks_editable)
            ->withDisabledAction('delete', !$this->marks_editable);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->mark_schema->getMarkSteps());
    }
}
