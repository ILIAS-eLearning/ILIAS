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

namespace ILIAS\LDAP\Server\UI;

readonly class ServerTable implements \ILIAS\UI\Component\Table\DataRetrieval
{
    private \ILIAS\UI\URLBuilder $url_builder;
    private \ILIAS\UI\URLBuilderToken $action_parameter_token;
    private \ILIAS\UI\URLBuilderToken $row_id_token;

    /**
     * @param list<array<string, string|int|float|null>> $servers
     */
    public function __construct(
        private array $servers,
        private \ilLDAPSettingsGUI $parent_gui,
        private \ILIAS\UI\Factory $ui_factory,
        private \ILIAS\UI\Renderer $ui_renderer,
        private \ilLanguage $lng,
        private \ilCtrlInterface $ctrl,
        private \Psr\Http\Message\ServerRequestInterface $http_request,
        private \ILIAS\Data\Factory $df,
        private string $parent_cmd,
        private bool $has_write_access
    ) {
        $form_action = $this->df->uri(
            \ilUtil::_getHttpPath() . '/' .
            $this->ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd)
        );

        [
            $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token
        ] = (new \ILIAS\UI\URLBuilder($form_action))->acquireParameters(
            ['ldap', 'servers'],
            'table_action',
            'server_id'
        );
    }

    /**
     * @return list<array<string, string|int|float|null>>
     */
    private function getRecords(\ILIAS\Data\Range $range, \ILIAS\Data\Order $order): array
    {
        $servers = $this->servers;

        [$order_field, $order_direction] = $order->join([], static function ($ret, $key, $value) {
            return [$key, $value];
        });

        array_walk(
            $servers,
            static function (array &$server): void {
                $server['user'] = \count(\ilObjUser::_getExternalAccountsByAuthMode('ldap_' . $server['server_id']));
            }
        );

        $records = \ilArrayUtil::sortArray(
            $servers,
            $order_field,
            strtolower($order_direction),
            \in_array($order_field, ['user', 'active'], true)
        );

        if ($order_field === 'active') {
            $records = array_reverse($records);
        }

        $records = \array_slice($records, $range->getStart(), $range->getLength());

        return $records;
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->getRecords($range, $order) as $server) {
            $title = $server['name'];
            if ($this->has_write_access) {
                $this->ctrl->setParameter($this->parent_gui, 'ldap_server_id', $server['server_id']);
                $title = $this->ui_renderer->render(
                    $this->ui_factory->link()->standard(
                        $title,
                        $this->ctrl->getLinkTarget($this->parent_gui, 'editServerSettings')
                    )
                );
            }

            yield $row_builder
                ->buildDataRow(
                    (string) $server['server_id'],
                    [
                        'title' => $title,
                        'active' => (bool) $server['active'],
                        'user' => $server['user']
                    ]
                )
                ->withDisabledAction(
                    'activateServer',
                    (bool) $server['active'],
                )
                ->withDisabledAction(
                    'deactivateServer',
                    !$server['active'],
                );
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return \count($this->servers);
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function getColumnDefinition(): array
    {
        return [
            'active' => $this->ui_factory
                ->table()
                ->column()
                ->boolean(
                    $this->lng->txt('status'),
                    $this->ui_factory->symbol()->icon()->custom(
                        'assets/images/standard/icon_ok.svg',
                        $this->lng->txt('active'),
                        'small'
                    ),
                    $this->ui_factory->symbol()->icon()->custom(
                        'assets/images/standard/icon_not_ok.svg',
                        $this->lng->txt('inactive'),
                        'small'
                    )
                )
                ->withIsSortable(true)
                ->withOrderingLabels(
                    "{$this->lng->txt('status')}, {$this->lng->txt('active')} {$this->lng->txt('order_option_first')}",
                    "{$this->lng->txt('status')}, {$this->lng->txt('inactive')} {$this->lng->txt('order_option_first')}"
                ),
            'title' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('title'))
                ->withIsSortable(true),
            'user' => $this->ui_factory
                ->table()
                ->column()
                ->number($this->lng->txt('user'))
                ->withIsSortable(true)
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(): array
    {
        if (!$this->has_write_access) {
            return [];
        }

        return [
            'editServerSettings' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $this->url_builder->withParameter($this->action_parameter_token, 'editServerSettings'),
                $this->row_id_token
            ),
            'activateServer' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('activate'),
                $this->url_builder->withParameter($this->action_parameter_token, 'activateServer'),
                $this->row_id_token
            ),
            'deactivateServer' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('deactivate'),
                $this->url_builder->withParameter($this->action_parameter_token, 'deactivateServer'),
                $this->row_id_token
            ),
            'confirmDeleteServerSettings' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter($this->action_parameter_token, 'confirmDeleteServerSettings'),
                $this->row_id_token
            )
        ];
    }

    public function getComponent(): \ILIAS\UI\Component\Table\Table
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this,
                $this->lng->txt('ldap_servers'),
                $this->getColumnDefinition(),
            )
            ->withId(self::class)
            ->withOrder(new \ILIAS\Data\Order('title', \ILIAS\Data\Order::ASC))
            ->withActions($this->getActions())
            ->withRequest($this->http_request);
    }
}
