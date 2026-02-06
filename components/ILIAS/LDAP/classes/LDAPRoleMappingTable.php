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
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Implementation\Component\Table\Action\Action;

class LDAPRoleMappingTable implements DataRetrieval
{
    /** @var list<array{
     * 'id': int,
     * 'title': string,
     * 'role': string,
     * 'dn': string,
     * 'url': string,
     * 'member_attribute': string,
     * 'info': string
     * }>|null */
    private ?array $records = null;

    public function __construct(
        private readonly ServerRequestInterface $http_request,
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly int $server_id,
        private readonly ilObjectDataCache $object_data_cache,
        private readonly ilRbacReview $rbac_review,
        private readonly \ILIAS\Data\URI $action_url,
        private readonly bool $has_write_access
    ) {
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
            yield $row_builder->buildDataRow((string) $record['id'], $record);
        }
    }

    public function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $mapping_instance = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId($this->server_id);
            $mappings = $mapping_instance->getMappings();
            foreach ($mappings as $item) {
                $title = $this->object_data_cache->lookupTitle($this->rbac_review->getObjectOfRole((int) $item['role']));
                $this->records[] = [
                    'id' => $item['mapping_id'],
                    'title' => ilStr::shortenTextExtended($title, 30, true),
                    'role' => $item['role_name'],
                    'dn' => $item['dn'],
                    'url' => $item['url'],
                    'member_attribute' => $item['member_attribute'],
                    'info' => ilLegacyFormElementsUtil::prepareFormOutput($item['info'])
                ];
            }
        }
    }

    public function getComponent(): DataTable
    {
        $query_params_namespace = ['ldap', 'role', 'mapping'];
        $url_builder = new URLBuilder($this->action_url);
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'mapping_ids'
        );

        return $this->ui_factory->table()
            ->data(
                $this,
                $this->lng->txt('mail_templates'),
                $this->getColumns()
            )
            ->withTitle($this->lng->txt('ldap_role_group_assignments'))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withId(
                'ldap_role_mapping_table'
            )
            ->withOrder(new Order('title', Order::DESC))
            ->withRange(new Range(0, 100))
            ->withRequest($this->http_request);
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
     * @return list<array{
     *     'id': int,
     *     'title': string,
     *     'role': string,
     *     'dn': string,
     *     'url': string,
     *     'member_attribute': string,
     *     'info': string
     * }>
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
     * @return array<string, Action>
     */
    public function getActions(URLBuilder $url_builder, URLBuilderToken $action_parameter_token, URLBuilderToken $row_id_token): array
    {
        $actions = [];

        if ($this->has_write_access) {
            $actions['delete'] = $this->ui_factory->table()->action()->multi(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token, 'confirmDeleteRoleMapping'),
                $row_id_token
            );

            $actions['copy'] = $this->ui_factory->table()->action()->single(
                $this->lng->txt('copy'),
                $url_builder->withParameter($action_parameter_token, 'addRoleMapping'),
                $row_id_token
            );
        }

        $actions['edit'] = $this->ui_factory->table()->action()->single(
            $this->has_write_access ? $this->lng->txt('edit') : $this->lng->txt('view'),
            $url_builder->withParameter($action_parameter_token, 'editRoleMapping'),
            $row_id_token
        );

        return $actions;
    }

    /**
     * @param list<array{
     *      'id': int,
     *      'title': string,
     *      'role': string,
     *      'dn': string,
     *      'url': string,
     *      'member_attribute': string,
     *      'info': string
     *  }> $records
     * @return list<array{
     *      'id': int,
     *      'title': string,
     *      'role': string,
     *      'dn': string,
     *      'url': string,
     *      'member_attribute': string,
     *      'info': string
     *  }>>
     */
    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    /**
     * @param list<array{
     *      'id': int,
     *      'title': string,
     *      'role': string,
     *      'dn': string,
     *      'url': string,
     *      'member_attribute': string,
     *      'info': string
     *  }> $records
     * @return list<array{
     *      'id': int,
     *      'title': string,
     *      'role': string,
     *      'dn': string,
     *      'url': string,
     *      'member_attribute': string,
     *      'info': string
     *  }>
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
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        return [
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt('title')),
            'role' => $this->ui_factory->table()->column()->text($this->lng->txt('obj_role')),
            'dn' => $this->ui_factory->table()->column()->text($this->lng->txt('ldap_group_dn')),
            'url' => $this->ui_factory->table()->column()->text($this->lng->txt('ldap_server')),
            'member_attribute' => $this->ui_factory->table()->column()->text($this->lng->txt('ldap_group_member')),
            'info' => $this->ui_factory->table()->column()->text($this->lng->txt('ldap_info_text')),
        ];
    }
}
