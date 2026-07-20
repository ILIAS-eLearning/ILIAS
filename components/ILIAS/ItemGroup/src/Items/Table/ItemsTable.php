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

namespace ILIAS\ItemGroup\Items\Table;

use Generator;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ilItemGroupItems;
use ilObjItemGroupGUI;
use ilUtil;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilObject;

class ItemsTable implements DataRetrieval
{
    public const string ID = 'itgr_items';

    public const string ROW_ID_PARAMETER = 'item_ref_id';

    public const string ACTION_PARAMETER = 'action';

    public const string ACTION_ASSIGN = 'assign';

    public const string ACTION_UNASSIGN = 'unassign';

    public const string LIST_MATERIALS_COMMAND = 'listMaterials';

    private const string ALL_OBJECTS = 'ALL_OBJECTS';

    private readonly ilItemGroupItems $item_group_items;

    /**
     * @var list<array{ref_id: int, obj_id: int, title: string, is_assigned: bool, other_assignments: \ILIAS\UI\Component\Listing\Unordered, sort_key: string}>
     */
    private ?array $records = null;

    public function __construct(
        private readonly ilCtrlInterface $ctrl,
        private readonly ilLanguage $lng,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly HttpServices $http,
        private readonly Refinery $refinery,
        private readonly int $item_group_ref_id,
        private readonly int $item_group_obj_id,
    ) {
        $this->item_group_items = new ilItemGroupItems($this->item_group_ref_id);
    }

    public function getComponent(URLBuilder $url_builder): Data
    {
        return $this->ui_factory->table()
            ->data($this, $this->lng->txt('itgr_assigned_materials'), $this->getColumns())
            ->withActions($this->getActions($url_builder))
            ->withOrder(new Order('is_assigned', Order::ASC))
            ->withRequest($this->http->request())
            ->withId(self::ID);
    }

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        return [$this->getComponent($url_builder)];
    }

    public function execute(URLBuilder $url_builder): void
    {
        [, $row_id_token, $action_token] = $this->acquireParameters($url_builder);

        if (
            !$this->http->wrapper()->query()->has($action_token->getName())
            && !$this->http->wrapper()->post()->has($action_token->getName())
        ) {
            $this->ctrl->redirectByClass(ilObjItemGroupGUI::class, self::LIST_MATERIALS_COMMAND);
        }

        $action = (string) $this->resolveParameter($action_token->getName());
        $selected_ref_ids = $this->resolveSelectedRefIds($row_id_token->getName());

        if ($selected_ref_ids === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('select_one'),
                true
            );
            $this->ctrl->redirectByClass(ilObjItemGroupGUI::class, self::LIST_MATERIALS_COMMAND);
        }

        $selected_ref_ids = array_values(
            array_intersect(
                $selected_ref_ids,
                $this->getAssignableRefIds()
            )
        );

        if ($selected_ref_ids === []) {
            $this->ctrl->redirectByClass(ilObjItemGroupGUI::class, self::LIST_MATERIALS_COMMAND);
        }

        if (!in_array($action, [self::ACTION_ASSIGN, self::ACTION_UNASSIGN], true)) {
            $this->ctrl->redirectByClass(ilObjItemGroupGUI::class, self::LIST_MATERIALS_COMMAND);
        }

        match ($action) {
            self::ACTION_ASSIGN => $this->assignItems($selected_ref_ids),
            self::ACTION_UNASSIGN => $this->unassignItems($selected_ref_ids),
            default => null,
        };

        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('msg_obj_modified'),
            true
        );
        $this->ctrl->redirectByClass(ilObjItemGroupGUI::class, self::LIST_MATERIALS_COMMAND);
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->loadRecords());
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
        $records = $this->limitRecords(
            $range,
            $this->sortRecords(
                $order,
                $this->loadRecords()
            )
        );

        foreach ($records as $record) {
            $row = $row_builder->buildDataRow(
                (string) $record['ref_id'],
                [
                    'title' => $this->buildTitleCell($record),
                    'is_assigned' => $record['is_assigned'],
                    'other_assignments' => $record['other_assignments'],
                ]
            );

            yield $row->withDisabledAction($record['is_assigned'] ? self::ACTION_ASSIGN : self::ACTION_UNASSIGN);
        }
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $icon_factory = $this->ui_factory->symbol()->icon();

        return [
            'title' => $column_factory->text($this->lng->txt('itgr_item'))->withIsSortable(true),
            'is_assigned' => $column_factory->boolean(
                $this->lng->txt('itgr_assignment'),
                $icon_factory->custom(
                    ilUtil::getImagePath('standard/icon_ok.svg'),
                    $this->lng->txt('yes')
                ),
                $icon_factory->custom(
                    ilUtil::getImagePath('standard/icon_not_ok.svg'),
                    $this->lng->txt('no')
                )
            )
                ->withIsSortable(true)
                ->withOrderingLabels(
                    $this->lng->txt('itgr_assigned_first'),
                    $this->lng->txt('itgr_unassigned_first')
                ),
            'other_assignments' => $column_factory
                ->listing($this->lng->txt('itgr_assignment_to_other_itgr'))
                ->withIsSortable(false),
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    private function getActions(URLBuilder $url_builder): array
    {
        $action_factory = $this->ui_factory->table()->action();
        [$url_builder, $row_id_token, $action_token] = $this->acquireParameters($url_builder);

        return [
            self::ACTION_ASSIGN => $action_factory->standard(
                $this->lng->txt('itgr_assign'),
                $url_builder->withParameter($action_token, self::ACTION_ASSIGN),
                $row_id_token
            ),
            self::ACTION_UNASSIGN => $action_factory->standard(
                $this->lng->txt('itgr_unassign'),
                $url_builder->withParameter($action_token, self::ACTION_UNASSIGN),
                $row_id_token
            ),
        ];
    }

    /**
     * @return array{URLBuilder, URLBuilderToken, URLBuilderToken}
     */
    private function acquireParameters(URLBuilder $url_builder): array
    {
        return $url_builder->acquireParameters(
            [self::ID],
            self::ROW_ID_PARAMETER,
            self::ACTION_PARAMETER
        );
    }

    /**
     * @return list<array{ref_id: int, obj_id: int, title: string, is_assigned: bool, other_assignments: \ILIAS\UI\Component\Listing\Unordered, sort_key: string}>
     */
    private function loadRecords(): array
    {
        if ($this->records !== null) {
            return $this->records;
        }

        $assigned_items = $this->item_group_items->getItems();
        $records = [];

        foreach ($this->item_group_items->getAssignableItems() as $item) {
            $ref_id = (int) ($item['child'] ?? $item['ref_id'] ?? 0);
            if ($ref_id === 0) {
                continue;
            }

            $is_assigned = in_array($ref_id, $assigned_items, true);
            $other_item_groups = ilItemGroupItems::getItemGroupsAssociatedWithItem(
                $ref_id,
                $this->item_group_obj_id
            );

            $records[] = [
                'ref_id' => $ref_id,
                'obj_id' => (int) ($item['obj_id'] ?? 0),
                'title' => (string) ($item['title'] ?? ''),
                'is_assigned' => $is_assigned,
                'other_assignments' => $this->ui_factory->listing()->unordered(
                    $this->buildOtherAssignmentLinks($other_item_groups)
                ),
                'sort_key' => (int) !$is_assigned . ($item['title'] ?? ''),
            ];
        }

        return $this->records = $records;
    }

    /**
     * @param array{ref_id: int, obj_id: int, title: string, is_assigned: bool, other_assignments: \ILIAS\UI\Component\Listing\Unordered, sort_key: string} $record
     */
    private function buildTitleCell(array $record): string
    {
        $icon = $this->ui_renderer->render(
            $this->ui_factory->symbol()->icon()->custom(
                ilObject::_getIcon($record['obj_id'], 'tiny'),
                $record['title']
            )
        );

        return "{$icon} {$record['title']}";
    }

    /**
     * @param array<int, string> $other_item_groups
     * @return list<\ILIAS\UI\Component\Link\Link>
     */
    private function buildOtherAssignmentLinks(array $other_item_groups): array
    {
        $links = [];

        foreach ($other_item_groups as $ref_id => $title) {
            $this->ctrl->setParameterByClass(ilObjItemGroupGUI::class, 'ref_id', $ref_id);
            $links[] = $this->ui_factory->link()->standard(
                $title,
                $this->ctrl->getLinkTargetByClass(ilObjItemGroupGUI::class, self::LIST_MATERIALS_COMMAND)
            );
        }

        $this->ctrl->clearParameterByClass(ilObjItemGroupGUI::class, 'ref_id');

        return $links;
    }

    /**
     * @param list<array{ref_id: int, obj_id: int, title: string, is_assigned: bool, other_assignments: \ILIAS\UI\Component\Listing\Unordered, sort_key: string}> $records
     * @return list<array{ref_id: int, obj_id: int, title: string, is_assigned: bool, other_assignments: \ILIAS\UI\Component\Listing\Unordered, sort_key: string}>
     */
    private function sortRecords(Order $order, array $records): array
    {
        $order_data = $order->get();
        if ($order_data === []) {
            usort(
                $records,
                static fn(array $a, array $b): int => strcmp($a['sort_key'], $b['sort_key'])
            );
            return $records;
        }

        foreach ($order_data as $key => $value) {
            $order_direction = $value === Order::DESC ? -1 : 1;
            $callable = match ($key) {
                'title' => static fn(array $a, array $b): int => strcmp($a['title'], $b['title']) * $order_direction,
                'is_assigned' => static fn(array $a, array $b): int => (($a['is_assigned'] ?? false) <=> ($b['is_assigned'] ?? false)) * -$order_direction,
                default => null,
            };

            if ($callable === null) {
                continue;
            }

            usort($records, $callable);
        }

        return $records;
    }

    /**
     * @param list<array{ref_id: int, obj_id: int, title: string, is_assigned: bool, other_assignments: \ILIAS\UI\Component\Listing\Unordered, sort_key: string}> $records
     * @return list<array{ref_id: int, obj_id: int, title: string, is_assigned: bool, other_assignments: \ILIAS\UI\Component\Listing\Unordered, sort_key: string}>
     */
    private function limitRecords(Range $range, array $records): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    /**
     * @param list<int> $selected_ref_ids
     */
    private function assignItems(array $selected_ref_ids): void
    {
        foreach ($selected_ref_ids as $ref_id) {
            $this->item_group_items->addItem($ref_id);
        }

        $this->item_group_items->update();
    }

    /**
     * @param list<int> $selected_ref_ids
     */
    private function unassignItems(array $selected_ref_ids): void
    {
        $this->item_group_items->setItems(
            array_values(
                array_diff(
                    $this->item_group_items->getItems(),
                    $selected_ref_ids
                )
            )
        );
        $this->item_group_items->update();
    }

    /**
     * @return list<int>
     */
    private function resolveSelectedRefIds(string $key): array
    {
        $value = $this->resolveRawParameter($key);
        if ($value === null || $value === '') {
            return [];
        }

        if ($this->isAllObjectsSelection($value)) {
            return $this->getAssignableRefIds();
        }

        if (!is_array($value)) {
            return [(int) $value];
        }

        return array_values(array_map(static fn(mixed $id): int => (int) $id, $value));
    }

    private function resolveParameter(string $key): mixed
    {
        $wrapper = $this->http->wrapper();
        $transformation = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->int(),
            $this->refinery->kindlyTo()->string(),
            $this->refinery->custom()->transformation(
                static fn(array $value): string|int|array => count($value) === 1 ? $value[0] : $value
            ),
        ]);

        if ($wrapper->post()->has($key)) {
            return $wrapper->post()->retrieve($key, $transformation);
        }

        if ($wrapper->query()->has($key)) {
            return $wrapper->query()->retrieve($key, $transformation);
        }

        return null;
    }

    private function resolveRawParameter(string $key): mixed
    {
        $wrapper = $this->http->wrapper();
        $transformation = $this->refinery->identity();

        $post_wrapper = $wrapper->post();
        if ($post_wrapper->has($key)) {
            return $post_wrapper->retrieve($key, $transformation);
        }

        $query_wrapper = $wrapper->query();
        if ($query_wrapper->has($key)) {
            return $query_wrapper->retrieve($key, $transformation);
        }

        return null;
    }

    private function isAllObjectsSelection(mixed $value): bool
    {
        return $value === self::ALL_OBJECTS || ($value[0] ?? null) === self::ALL_OBJECTS;
    }

    /**
     * @return list<int>
     */
    private function getAssignableRefIds(): array
    {
        return array_values(array_filter(
            array_map(
                static fn(array $item): int => (int) ($item['child'] ?? $item['ref_id'] ?? 0),
                $this->item_group_items->getAssignableItems()
            ),
            static fn(int $ref_id): bool => $ref_id > 0
        ));
    }
}
