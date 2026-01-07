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

namespace ILIAS\Badge;

use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ilLanguage;
use ilGlobalTemplateInterface;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Services;
use Psr\Http\Message\RequestInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use Generator;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\URLBuilderToken;
use ilBadgeHandler;
use ilBadgeAuto;
use ILIAS\UI\Component\Table\Column\Column;

class ilBadgeTypesTableGUI implements DataRetrieval
{
    private readonly Factory $factory;
    private readonly Renderer $renderer;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ServerRequestInterface|RequestInterface $request;
    private readonly Services $http;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    /**
     * @var null|list<array{"id": string, "comp": string, "name": string, "manual": bool, "active": bool, "activity": bool}>
     */
    private ?array $cached_records = null;

    public function __construct(protected bool $a_has_write = false)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('cmps');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->request = $DIC->http()->request();
        $this->http = $DIC->http();
    }

    /**
     * @return list<array{"id": string, "comp": string, "name": string, "manual": bool, "active": bool, "activity": bool}>
     */
    private function getRecords(): array
    {
        if ($this->cached_records !== null) {
            return $this->cached_records;
        }

        $rows = [];
        $handler = ilBadgeHandler::getInstance();
        $inactive = $handler->getInactiveTypes();

        foreach ($handler->getComponents() as $component) {
            $provider = $handler->getProviderInstance($component);
            if ($provider) {
                foreach ($provider->getBadgeTypes() as $badge_obj) {
                    $id = $handler->getUniqueTypeId($component, $badge_obj);

                    $rows[] = [
                        'id' => $id,
                        'comp' => $handler->getComponentCaption($component),
                        'name' => $badge_obj->getCaption(),
                        'manual' => !$badge_obj instanceof ilBadgeAuto,
                        'active' => !\in_array($id, $inactive, true),
                        'activity' => \in_array('bdga', $badge_obj->getValidObjectTypes(), true)
                    ];
                }
            }
        }

        $this->cached_records = $rows;

        return $rows;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $records = $this->getRecords();

        if ($order) {
            [$order_field, $order_direction] = $order->join(
                [],
                fn($ret, $key, $value) => [$key, $value]
            );

            usort($records, static function (array $left, array $right) use ($order_field): int {
                if (\in_array($order_field, ['name', 'comp'], true)) {
                    return \ilStr::strCmp(
                        $left[$order_field],
                        $right[$order_field]
                    );
                }

                if (\in_array($order_field, ['manual', 'activity', 'active'], true)) {
                    return $right[$order_field] <=> $left[$order_field];
                }

                return $left[$order_field] <=> $right[$order_field];
            });

            if ($order_direction === Order::DESC) {
                $records = array_reverse($records);
            }
        }

        if ($range) {
            $records = \array_slice($records, $range->getStart(), $range->getLength());
        }

        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['id'], $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return \count($this->getRecords());
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        return [
            'name' => $this->factory->table()->column()->text($this->lng->txt('name')),
            'comp' => $this->factory->table()->column()->text($this->lng->txt('cmps_component')),
            'manual' => $this->factory->table()->column()->boolean(
                $this->lng->txt('badge_manual'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )->withOrderingLabels(
                $this->lng->txt('badge_sort_manual_awarding_first'),
                $this->lng->txt('badge_sort_manual_awarding_last')
            ),
            'activity' => $this->factory->table()->column()->boolean(
                $this->lng->txt('badge_activity_badges'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )->withOrderingLabels(
                $this->lng->txt('badge_sort_activity_badges_first'),
                $this->lng->txt('badge_sort_activity_badges_last')
            ),
            'active' => $this->factory->table()->column()->boolean(
                $this->lng->txt('active'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )->withOrderingLabels(
                $this->lng->txt('badge_sort_active_badges_first'),
                $this->lng->txt('badge_sort_active_badges_last')
            )
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        return $this->a_has_write ? [
            'badge_type_activate' => $this->factory->table()->action()->multi(
                $this->lng->txt('activate'),
                $url_builder->withParameter($action_parameter_token, 'badge_type_activate'),
                $row_id_token
            ),
            'badge_type_deactivate' =>
                $this->factory->table()->action()->multi(
                    $this->lng->txt('deactivate'),
                    $url_builder->withParameter($action_parameter_token, 'badge_type_deactivate'),
                    $row_id_token
                )
        ] : [];
    }

    public function renderTable(string $url): void
    {
        $df = new \ILIAS\Data\Factory();

        $table_uri = $df->uri($url);
        $url_builder = new URLBuilder($table_uri);
        $query_params_namespace = ['tid'];

        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'id',
        );

        $table = $this->factory
            ->table()
            ->data($this, $this->lng->txt('badge_types'), $this->getColumns())
            ->withId(str_replace('\\', '', self::class))
            ->withOrder(new Order('name', Order::ASC))
            ->withRange(new Range(0, 100))
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token))
            ->withRequest($this->request);

        $out = [$table];

        $query = $this->http->wrapper()->query();
        if ($query->has($action_parameter_token->getName())) {
            $action = $query->retrieve($action_parameter_token->getName(), $this->refinery->to()->string());
            $ids = $query->retrieve($row_id_token->getName(), $this->refinery->custom()->transformation(fn($v) => $v));
            $listing = $this->factory->listing()->characteristicValue()->text([
                'table_action' => $action,
                'id' => print_r($ids, true),
            ]);

            $out[] = $this->factory->divider()->horizontal();
            $out[] = $listing;
        }

        $this->tpl->setContent($this->renderer->render($out));
    }
}
