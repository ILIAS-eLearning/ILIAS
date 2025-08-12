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

namespace ILIAS\Test\Settings\Templates;

use Generator;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Language\Language;
use ILIAS\Test\RequestDataCollector;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;

class PersonalSettingsTable implements DataRetrieval
{
    private const string ID = 'pst';
    private ?array $records = null;

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly DataFactory $data_factory,
        private readonly RequestDataCollector $test_request,
        private readonly PersonalSettingsTableActions $table_actions,
        private readonly URLBuilder $url_builder,
        private readonly PersonalSettingsRepository $repository,
    ) {
    }

    public function execute(): ?Modal
    {
        return $this->table_actions->execute(...$this->acquireParameters());
    }

    private function acquireParameters(): array
    {
        return $this->url_builder->acquireParameters(
            [self::ID],
            PersonalSettingsTableActions::ROW_ID_PARAMETER,
            PersonalSettingsTableActions::ACTION_PARAMETER,
            PersonalSettingsTableActions::ACTION_TYPE_PARAMETER
        );
    }

    /**
     * @return array<string, Column>
     */
    protected function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $date_format = $this->data_factory->dateFormat()->withTime24($this->data_factory->dateFormat()->standard());

        return [
            'name' => $column_factory->text($this->lng->txt('title')),
            'author' => $column_factory->text($this->lng->txt('author')),
            'description' => $column_factory->text($this->lng->txt('description'))->withIsSortable(false),
            'timestamp' => $column_factory->date($this->lng->txt('created'), $date_format),
        ];
    }

    public function getComponent(): DataTable
    {
        return $this->ui_factory->table()
            ->data($this, $this->lng->txt('personal_settings'), $this->getColumns())
            ->withRequest($this->test_request->getRequest())
            ->withActions($this->table_actions->getActions(...$this->acquireParameters()))
            ->withOrder(new Order('timestamp', Order::DESC))
            ->withId(self::ID);
    }

    private function getRecords(): array
    {
        $this->records ??= $this->repository->getTemplatesForUser();
        return $this->records;
    }

    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    private function sortRecords(array $records, Order $order): array
    {
        uasort($records, static function (PersonalSettingsTemplate $a, PersonalSettingsTemplate $b) use ($order): int {
            foreach ($order->get() as $subject => $direction) {
                $position = match ($subject) {
                    'name' => $a->getName() <=> $b->getName(),
                    'timestamp' => $a->getCreatedAt() <=> $b->getCreatedAt(),
                    'author' => $a->getAuthor() <=> $b->getAuthor(),
                };

                if ($position !== 0) {
                    return $direction === 'DESC' ? $position * -1 : $position;
                }
            }

            return 0;
        });

        return $records;
    }

    private function getViewControlledRecords(Range $range, Order $order): array
    {
        return $this->limitRecords(
            $this->sortRecords(
                $this->getRecords(),
                $order
            ),
            $range
        );
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        foreach ($this->getViewControlledRecords($range, $order) as $template) {
            $row = [
                'name' => $template->getName(),
                'timestamp' => $template->getCreatedAt(),
                'description' => $template->getDescription(),
                'author' => $template->getAuthor(),
            ];

            yield $row_builder->buildDataRow((string) $template->getId(), $row);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getRecords());
    }
}
