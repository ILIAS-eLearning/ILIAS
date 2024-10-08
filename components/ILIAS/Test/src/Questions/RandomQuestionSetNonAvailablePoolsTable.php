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
use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilTestRandomQuestionSetConfigGUI;
use ilTestRandomQuestionSetNonAvailablePool;
use ilTestRandomQuestionSetSourcePoolDefinitionList as ilPoolDefinitionList;

/**
 * Class RandomQuestionSetNonAvailablePoolsTable
 */
class RandomQuestionSetNonAvailablePoolsTable implements DataRetrieval
{
    protected array $data = [];

    /**
     * @param \ilCtrlInterface $ctrl
     * @param \ilLanguage $lng
     * @param UIFactory $ui_factory
     * @param DataFactory $data_factory
     * @param ServerRequest $request
     */
    public function __construct(
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly \ilLanguage $lng,
        protected readonly UIFactory $ui_factory,
        protected readonly DataFactory $data_factory,
        protected readonly ServerRequest $request
    ) {
    }

    /**
     * @param DataRowBuilder $row_builder
     * @param array $visible_column_ids
     * @param Range $range
     * @param Order $order
     * @param array|null $filter_data
     * @param array|null $additional_parameters
     * @return Generator
     */
    public function getRows(DataRowBuilder $row_builder, array $visible_column_ids, Range $range, Order $order, ?array $filter_data, ?array $additional_parameters): Generator
    {
        $data = $this->data;
        $data = array_slice($data, $range->getStart(), $range->getLength());
        foreach ($data as $record) {
            $derive = $record['status'] === ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_LOST;

            $record['status'] = $this->lng->txt('tst_non_avail_pool_msg_status_' . $record['status']);
            yield $row_builder
                ->buildDataRow((string) $record['id'], $record)
                ->withDisabledAction('derive_pool', !$derive);
        }
    }

    /**
     * @param array|null $filter_data
     * @param array|null $additional_parameters
     * @return int|null
     */
    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->data);
    }

    /**
     * @param ilPoolDefinitionList $source_pool_definition_list
     */
    public function setData(ilPoolDefinitionList $source_pool_definition_list): void
    {
        $this->data = array_map(fn($pool) => [
            'id' => $pool->getId(),
            'title' => $pool->getTitle(),
            'path' => $pool->getPath(),
            'status' => $pool->getUnavailabilityStatus(),
        ], $source_pool_definition_list->getNonAvailablePools());
    }

    /**
     * @return DataTable
     */
    public function getComponent(): DataTable
    {
        return $this->ui_factory->table()
            ->data($this->lng->txt('tst_non_avail_pools_table'), $this->getColumns(), $this)
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
        return [
            'derive_pool' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('tst_derive_new_pool'),
                ...$this->getActionURI(ilTestRandomQuestionSetConfigGUI::CMD_SELECT_DERIVATION_TARGET)
            )
        ];
    }

    /**
     * @param string $cmd
     * @return array{URLBuilder, URLBuilderToken}
     */
    protected function getActionURI(string $cmd): array
    {
        $builder = new URLBuilder($this->buildTargetURI($cmd));
        return $builder->acquireParameters(['derive_pool'], 'ids');
    }

    /**
     * @param string $cmd
     * @return URI
     */
    protected function buildTargetURI(string $cmd): URI
    {
        $target = $this->ctrl->getLinkTargetByClass(ilTestRandomQuestionSetConfigGUI::class, $cmd);
        $path = parse_url($target, PHP_URL_PATH);
        $query = parse_url($target, PHP_URL_QUERY);
        return $this->data_factory->uri((string) ServerRequest::getUriFromGlobals()->withPath($path)->withQuery($query));
    }
}
