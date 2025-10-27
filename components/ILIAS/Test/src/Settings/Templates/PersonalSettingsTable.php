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

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly DataFactory $data_factory,
        private readonly \ilObjUser $user,
        private readonly RequestDataCollector $test_request,
        private readonly PersonalSettingsTableActions $table_actions,
        private readonly URLBuilder $url_builder,
        private readonly PersonalSettingsRepository $repository,
    ) {
    }

    public function perform(): ?Modal
    {
        return $this->table_actions->perform(...$this->acquireParameters());
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
    private function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();

        return [
            'name' => $column_factory->text($this->lng->txt('personal_settings_name')),
            'description' => $column_factory->text($this->lng->txt('personal_settings_description'))->withIsSortable(false),
            'tstamp' => $column_factory->date($this->lng->txt('personal_settings_timestamp'), $this->user->getDateTimeFormat()),
            'author' => $column_factory->text($this->lng->txt('personal_settings_author'))
        ];
    }

    public function getComponent(): DataTable
    {
        return $this->ui_factory->table()
            ->data($this, $this->lng->txt('personal_settings_templates_available'), $this->getColumns())
            ->withRequest($this->test_request->getRequest())
            ->withActions($this->table_actions->getActions(...$this->acquireParameters()))
            ->withOrder(new Order('tstamp', Order::DESC))
            ->withId(self::ID);
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        foreach ($this->repository->getForUser($range, $order) as $template) {
            $row = [
                'name' => $template->getName(),
                'tstamp' => $template->getCreatedAt(),
                'description' => $template->getDescription(),
                'author' => $template->getAuthor(),
            ];

            yield $row_builder->buildDataRow((string) $template->getId(), $row);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->repository->countForUser();
    }
}
