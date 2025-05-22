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

namespace ILIAS\Test\Questions;

use Generator;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ilTestRandomQuestionSetSourcePoolDefinitionList as PoolDefinitionList;
use ilTestRandomQuestionSetConfigGUI as ConfigGUI;
use Psr\Http\Message\ServerRequestInterface;

class RandomQuestionSetNonAvailablePoolsTable implements DataRetrieval
{
    public function __construct(
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly \ilLanguage $lng,
        protected readonly UIFactory $ui_factory,
        protected readonly DataFactory $data_factory,
        protected readonly ServerRequestInterface $request,
        protected readonly PoolDefinitionList $pool_definition_list
    ) {
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        foreach ($this->getData($range, $order) as $record) {
            $derive = $record['status'] === \ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_LOST;

            $record['status'] = $this->lng->txt('tst_non_avail_pool_msg_status_' . $record['status']);
            yield $row_builder
                ->buildDataRow((string) $record['id'], $record)
                ->withDisabledAction('derive_pool', !$derive);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->pool_definition_list->getNonAvailablePools());
    }

    protected function getData(Range $range, Order $order): array
    {
        $data = array_map(fn($pool) => [
            'id' => $pool->getId(),
            'title' => $pool->getTitle(),
            'path' => $pool->getPath(),
            'status' => $pool->getUnavailabilityStatus(),
        ], $this->pool_definition_list->getNonAvailablePools());
        return array_slice($data, $range->getStart(), $range->getLength());
    }

    public function getComponent(): DataTable
    {
        return $this->ui_factory->table()
            ->data($this, $this->lng->txt('tst_non_avail_pools_table'), $this->getColumns())
            ->withRequest($this->request)
            ->withActions($this->getActions())
            ->withId('tst_non_avail_pools_table');
    }

    /**
     * @return array<string, Column>
     */
    protected function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        return [
            'title' => $column_factory->text($this->lng->txt('title')),
            'path' => $column_factory->text($this->lng->txt('path')),
            'status' => $column_factory->text($this->lng->txt('status')),
        ];
    }

    /**
     * @return array<string, Action>
     */
    protected function getActions(): array
    {
        $target = $this->data_factory->uri((string) $this->request->getUri());
        $url_builder = new URLBuilder($target);
        [$url_builder, $id_token] = $url_builder->acquireParameters(
            ['derive_pool'],
            'ids'
        );
        return [
            'derive_pool' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('tst_derive_new_pool'),
                $url_builder->withURI($target->withParameter('cmd', ConfigGUI::CMD_SELECT_DERIVATION_TARGET)),
                $id_token
            )
        ];
    }
}
