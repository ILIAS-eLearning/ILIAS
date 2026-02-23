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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Table\DataRetrieval;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Implementation\Component\Table\Action\Action;
use ILIAS\Data\URI;

class ilMailTemplateTable implements DataRetrieval
{
    /** @var array<string, ilMailTemplateContext> */
    protected array $contexts = [];

    /** @var list<array{tpl_id: int, title: string, context: string, is_default: bool}>|null */
    private ?array $records = null;

    public function __construct(
        private readonly ServerRequestInterface $http_request,
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly Uri $table_uri,
        private readonly ilMailTemplateService $service,
        private readonly bool $read_only = false,
    ) {
        $this->contexts = ilMailTemplateContextService::getTemplateContexts();
    }

    public function getRows(
        ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $records = $this->getRecords($range, $order);
        foreach ($records as $record) {
            yield $row_builder->buildDataRow($record['tpl_id'], $record)
                ->withDisabledAction(
                    'setAsContextDefault',
                    isset($record['is_default']) && $record['is_default']
                )
                ->withDisabledAction(
                    'unsetAsContextDefault',
                    !isset($record['is_default']) || !$record['is_default']
                );
        }
    }

    public function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            foreach ($this->service->listAllTemplatesAsArray() as $item) {
                $this->records[] = [
                    'tpl_id' => (string) $item['tpl_id'],
                    'title' => $item['title'],
                    'context' => $this->getContext($item['context'], $item['is_default'] ?? false),
                    'is_default' => $item['is_default'] ?? false,
                ];
            }
        }
    }

    public function getComponent(): DataTable
    {
        $query_params_namespace = ['mail', 'template'];
        $url_builder = new URLBuilder($this->table_uri);
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'tpl_ids'
        );

        return $this->ui_factory->table()
            ->data(
                $this,
                $this->lng->txt('mail_templates'),
                $this->getColumns()
            )
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withId(
                'mail_man_tpl'
            )
            ->withOrder(new Order('title', Order::ASC))
            ->withRange(new Range(0, 100))
            ->withRequest($this->http_request);
    }

    public function getContext(string $value, bool $default = false): string
    {
        if (isset($this->contexts[$value])) {
            $is_default_suffix = '';
            if ($default) {
                $is_default_suffix = $this->lng->txt('mail_template_default');
            }

            return implode('', [
                $this->contexts[$value]->getTitle(),
                $is_default_suffix,
            ]);
        }

        return $this->lng->txt('mail_template_orphaned_context');
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $this->initRecords();

        return count((array) $this->records);
    }

    /**
     * @return list<array{tpl_id: int, title: string, context: string, is_default: bool}>
     */
    private function getRecords(Range $range, Order $order): array
    {
        $this->initRecords();
        $records = $this->records;

        if ($order) {
            $records = $this->orderRecords($records, $order);
        }

        if ($range) {
            $records = $this->limitRecords($records, $range);
        }

        return $records;
    }

    /**
     * @return Action[]
     */
    public function getActions(URLBuilder $url_builder, URLBuilderToken $action_parameter_token, URLBuilderToken $row_id_token): array
    {
        $actions = [];
        if ($this->contexts !== []) {
            $actions['edit'] = $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $url_builder->withParameter($action_parameter_token, 'showEditTemplateForm'),
                $row_id_token
            );
        } else {
            $actions['view'] = $this->ui_factory->table()->action()->single(
                $this->lng->txt('view'),
                $url_builder->withParameter($action_parameter_token, 'showEditTemplateForm'),
                $row_id_token
            );
        }

        if (!$this->read_only) {
            $actions['delete'] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token, 'confirmDeleteTemplate'),
                $row_id_token
            );
        }

        $actions['unsetAsContextDefault'] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('mail_template_unset_as_default'),
            $url_builder->withParameter($action_parameter_token, 'unsetAsContextDefault'),
            $row_id_token
        );

        $actions['setAsContextDefault'] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('mail_template_set_as_default'),
            $url_builder->withParameter($action_parameter_token, 'setAsContextDefault'),
            $row_id_token
        );

        return $actions;
    }

    /**
     * @param  list<array{tpl_id: int, title: string, context: string, is_default: bool}> $records
     * @return list<array{tpl_id: int, title: string, context: string, is_default: bool}>
     */
    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    /**
     * @param  list<array{tpl_id: int, title: string, context: string, is_default: bool}> $records
     * @return list<array{tpl_id: int, title: string, context: string, is_default: bool}>
     */
    private function orderRecords(array $records, Order $order): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );
        usort($records, static fn(array $left, array $right): int => ilStr::strCmp(
            $left[$order_field] ?? '',
            $right[$order_field] ?? ''
        ));

        if ($order_direction === Order::DESC) {
            $records = array_reverse($records);
        }

        return $records;
    }

    /**
     * @return array{
     *     title: \ILIAS\UI\Component\Table\Column\Text,
     *     context: \ILIAS\UI\Component\Table\Column\Text
     * }
     */
    private function getColumns(): array
    {
        return [
            'title' => $this->ui_factory->table()->column()
                ->text($this->lng->txt('title')),
            'context' => $this->ui_factory->table()->column()
                ->text($this->lng->txt('mail_template_context'))
        ];
    }
}
