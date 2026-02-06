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
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Implementation\Component\Table\Action\Action;

class LDAPRoleAssignmentTable implements DataRetrieval
{
    /** @var list<array{
     * 'id': int,
     * 'type': string,
     * 'condition': string,
     * 'add': Icon,
     * 'remove': Icon,
     * 'role': string,
     * }>|null */
    private ?array $records = null;

    public function __construct(
        private readonly ServerRequestInterface $http_request,
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly \ILIAS\Data\URI $action_url,
        private readonly int $server_id,
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
            $icons = [
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', '', 'small'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', '', 'small')
            ];

            $rule_objs = ilLDAPRoleAssignmentRule::_getRules($this->server_id);
            /** @var ilLDAPRoleAssignmentRule $rule */
            foreach ($rule_objs as $rule) {
                switch ($rule->getType()) {
                    case ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE:
                        $type = $this->lng->txt('ldap_role_by_attribute');
                        break;

                    case ilLDAPRoleAssignmentRule::TYPE_GROUP:
                        $type = $this->lng->txt('ldap_role_by_group');
                        break;

                    case ilLDAPRoleAssignmentRule::TYPE_PLUGIN:
                        $type = $this->lng->txt('ldap_role_by_plugin');
                        break;
                }
                $this->records[] = [
                    'id' => $rule->getRuleId(),
                    'type' => $type ?? '',
                    'condition' => $rule->conditionToString(),
                    'add' => $icons[(int) $rule->isAddOnUpdateEnabled()],
                    'remove' => $icons[(int) $rule->isRemoveOnUpdateEnabled()],
                    'role' => ilObject::_lookupTitle($rule->getRoleId()),
                ];
            }
        }
    }

    public function getComponent(): DataTable
    {
        $query_params_namespace = ['ldap', 'role', 'assignment'];
        $url_builder = new URLBuilder($this->action_url);
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'rule_ids'
        );

        return $this->ui_factory->table()
            ->data(
                $this,
                $this->lng->txt('ldap_tbl_role_ass'),
                $this->getColumns()
            )
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withId('ldap_role_assignment_table')
            ->withOrder(new Order('type', Order::DESC))
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
     *     'type': string,
     *     'condition': string,
     *     'add': Icon,
     *     'remove': Icon,
     *     'role': string,
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
     * @return array<string, Action[]>
     */
    public function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        $actions = [];
        if ($this->has_write_access) {
            $actions['delete'] = $this->ui_factory->table()->action()->multi(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token, 'confirmDeleteRules'),
                $row_id_token
            );
        }

        $actions['edit'] = $this->ui_factory->table()->action()->single(
            $this->has_write_access ? $this->lng->txt('edit') : $this->lng->txt('view'),
            $url_builder->withParameter($action_parameter_token, 'editRoleAssignment'),
            $row_id_token
        );

        return $actions;
    }

    /**
     * @param list<array{
     *      'id': int,
     *      'type': string,
     *      'condition': string,
     *      'add': Icon,
     *      'remove': Icon,
     *      'role': string,
     *  }> $records
     * @return list<array{
     *      'id': int,
     *      'type': string,
     *      'condition': string,
     *      'add': Icon,
     *      'remove': Icon,
     *      'role': string,
     *  }>
     */
    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    /**
     * @param list<array{
     *      'id': int,
     *      'type': string,
     *      'condition': string,
     *      'add': Icon,
     *      'remove': Icon,
     *      'role': string,
     *  }> $records
     * @return list<array{
     *      'id': int,
     *      'type': string,
     *      'condition': string,
     *      'add': Icon,
     *      'remove': Icon,
     *      'role': string,
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
            'type' => $this->ui_factory->table()->column()->text($this->lng->txt('ldap_rule_type')),
            'role' => $this->ui_factory->table()->column()->text($this->lng->txt('ldap_ilias_role')),
            'condition' => $this->ui_factory->table()->column()->text($this->lng->txt('ldap_rule_condition')),
            'add' => $this->ui_factory->table()->column()->statusIcon($this->lng->txt('ldap_add_roles'))
                ->withIsSortable(false),
            'remove' => $this->ui_factory->table()->column()->statusIcon($this->lng->txt('ldap_remove_roles'))
                ->withIsSortable(false),
        ];
    }
}
