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

namespace ILIAS\AccessControl\Log;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Input\Container\Filter\Filter;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Data\Factory as DataFactory;
use Psr\Http\Message\RequestInterface;

class Table implements DataRetrieval
{
    private const FILTER_ID = 'perm_table_filter';
    private const FILTER_FIELD_ACTION = 'action';
    private const FILTER_FIELD_PERIOD = 'period';

    private const COLUMN_DATE = 'created';
    private const COLUMN_NAME = 'name';
    private const COLUMN_LOGIN = 'login';
    private const COLUMN_ACTION = 'action';
    private const COLUMN_CHANGES = 'changes';

    /**
     * @var ?array<string, string|array>
     */
    private ?array $filter_data;

    private array $action_map = [];
    private array $operations = [];

    public function __construct(
        private readonly \ilRbacLog $rbac_log,
        private readonly UIFactory $ui_factory,
        private readonly DataFactory $data_factory,
        private readonly \ilLanguage $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilUIService $ui_service,
        private readonly \ilObjectDefinition $object_definition,
        private readonly RequestInterface $request,
        \ilRbacReview $rbac_review,
        private readonly \ilObjUser $current_user,
        private readonly \ilObjectGUI $gui_object
    ) {
        $this->action_map = [
            \ilRbacLog::EDIT_PERMISSIONS => $this->lng->txt('rbac_log_edit_permissions'),
            \ilRbacLog::MOVE_OBJECT => $this->lng->txt('rbac_log_move_object'),
            \ilRbacLog::LINK_OBJECT => $this->lng->txt('rbac_log_link_object'),
            \ilRbacLog::COPY_OBJECT => $this->lng->txt('rbac_log_copy_object'),
            \ilRbacLog::CREATE_OBJECT => $this->lng->txt('rbac_log_create_object'),
            \ilRbacLog::EDIT_TEMPLATE => $this->lng->txt('rbac_log_edit_template'),
            \ilRbacLog::EDIT_TEMPLATE_EXISTING => $this->lng->txt('rbac_log_edit_template_existing'),
            \ilRbacLog::CHANGE_OWNER => $this->lng->txt('rbac_log_change_owner')
        ];

        foreach ($rbac_review->getOperations() as $op) {
            $this->operations[$op['ops_id']] = $op['operation'];
        }
    }

    public function getTableAndFilter(): array
    {
        return [
            $this->getFilter(),
            $this->getTable()
        ];
    }

    private function getTable(): DataTable
    {
        $cf = $this->ui_factory->table()->column();

        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('rbac_log'),
            [
                self::COLUMN_DATE => $cf->date(
                    $this->lng->txt('date'),
                    $this->buildUserDateTimeFormat()
                ),
                self::COLUMN_NAME => $cf->text($this->lng->txt('name')),
                self::COLUMN_LOGIN => $cf->text($this->lng->txt('login')),
                self::COLUMN_ACTION => $cf->text($this->lng->txt('action')),
                self::COLUMN_CHANGES => $cf->text($this->lng->txt('rbac_changes'))
                    ->withIsSortable(false)
            ],
        )->withRequest($this->request);
    }

    private function getFilter(): Filter
    {
        $ff = $this->ui_factory->input()->field();

        $inputs = [
            self::FILTER_FIELD_ACTION => $ff->multiSelect(
                $this->lng->txt('action'),
                $this->action_map
            ),
            self::FILTER_FIELD_PERIOD => $ff->duration($this->lng->txt('date'))
        ];

        $active = array_fill(0, count($inputs), true);

        $filter = $this->ui_service->filter()->standard(
            self::FILTER_ID,
            $this->ctrl->getFormActionByClass([get_class($this->gui_object), \ilPermissionGUI::class], 'log'),
            $inputs,
            $active,
            true,
            true
        );
        $this->filter_data = $this->applyFilterValuesTrafos($this->ui_service->filter()->getData($filter));
        return $filter;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $log_data = $this->rbac_log->getLogItems(
            $this->getRefId(),
            $range,
            $order,
            $this->filter_data
        );

        foreach ($log_data as $entry) {
            $user_data = \ilObjUser::_lookupName($entry['user_id']);
            yield $row_builder->buildDataRow(
                (string) $entry['log_id'],
                [
                    self::COLUMN_DATE => (new \DateTimeImmutable('@' . $entry['created']))
                        ->setTimezone(new \DateTimeZone($this->current_user->getTimeZone())),
                    self::COLUMN_NAME => "{$user_data['lastname']}, {$user_data['firstname']}",
                    self::COLUMN_LOGIN => $user_data['login'],
                    self::COLUMN_ACTION => $this->action_map[$entry['action']] ?? '',
                    self::COLUMN_CHANGES => $this->buildChangeColumn($entry['action'], $entry['data'] ?? [])
                ]
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return $this->rbac_log->getLogItemsCount($this->getRefId(), $filter_data);
    }

    private function getRefId(): int
    {
        // special case: role folder should display root folder entries
        if ($this->gui_object->getRefId() === ROLE_FOLDER_ID) {
            return ROOT_FOLDER_ID;
        }
        return $this->gui_object->getRefId();
    }

    private function buildUserDateTimeFormat(): DateFormat
    {
        $user_format = $this->current_user->getDateFormat();
        if ($this->current_user->getTimeFormat() == \ilCalendarSettings::TIME_FORMAT_24) {
            return $this->data_factory->dateFormat()->withTime24($user_format);
        }
        return $this->data_factory->dateFormat()->withTime12($user_format);
    }

    private function applyFilterValuesTrafos(array $filter_values): array
    {
        $transformed_values = [
            'action' => $filter_values['action']
        ];
        if (isset($filter_values['period'][0])) {
            $transformed_values['from'] = (new \DateTimeImmutable(
                $filter_values['period'][0],
                new \DateTimeZone($this->current_user->getTimeZone())
            ))->getTimestamp();
        }
        if (isset($filter_values['period'][1])) {
            $transformed_values['to'] = (new \DateTimeImmutable(
                $filter_values['period'][1] . '23:59:59',
                new \DateTimeZone($this->current_user->getTimeZone())
            ))->getTimestamp();
        }
        return $transformed_values;
    }

    private function buildChangeColumn(int $action, array $data): string
    {
        if ($action === \ilRbacLog::CHANGE_OWNER) {
            $user_name = \ilObjUser::_lookupFullname($data[0] ?? 0);
            return "{$this->lng->txt('rbac_log_changed_owner')}: {$user_name}";
        }

        if ($action === \ilRbacLog::EDIT_TEMPLATE) {
            return $this->parseChangesTemplate($data);
        }

        return $this->parseChangesFaPa($data);
    }

    private function parseChangesFaPa(array $raw): string
    {
        $result = [];

        if (isset($raw['src']) && is_int($raw['src'])) {
            $obj_id = \ilObject::_lookupObjectId($raw['src']);
            if ($obj_id) {
                $result[] = "{$this->lng->txt('rbac_log_source_object')}: "
                    . '<a href="' . \ilLink::_getLink($raw['src']) . '">'
                    . \ilObject::_lookupTitle($obj_id) . '</a>';
            }

            // added only
            foreach ($raw['ops'] as $role_id => $ops) {
                foreach ($ops as $op) {
                    $result[] = sprintf(
                        $this->lng->txt('rbac_log_operation_add'),
                        \ilObjRole::_getTranslation(\ilObject::_lookupTitle((int) $role_id))
                    ) . ': ' . $this->getOPCaption($this->gui_object->getObject()->getType(), $op);
                }
            }
        } elseif (isset($raw['ops'])) {
            foreach ($raw['ops'] as $role_id => $actions) {
                foreach ($actions as $action => $ops) {
                    foreach ((array) $ops as $op) {
                        $result[] = sprintf(
                            $this->lng->txt('rbac_log_operation_' . $action),
                            \ilObjRole::_getTranslation(\ilObject::_lookupTitle((int) $role_id))
                        ) . ': ' . $this->getOPCaption($this->gui_object->getObject()->getType(), $op);
                    }
                }
            }
        }

        if (isset($raw['inht'])) {
            foreach ($raw['inht'] as $action => $role_ids) {
                foreach ((array) $role_ids as $role_id) {
                    $result[] = sprintf(
                        $this->lng->txt('rbac_log_inheritance_' . $action),
                        \ilObjRole::_getTranslation(\ilObject::_lookupTitle((int) $role_id))
                    );
                }
            }
        }

        return implode('<br>', $result);
    }

    private function parseChangesTemplate(array $raw): string
    {
        $result = [];
        foreach ($raw as $type => $actions) {
            foreach ($actions as $action => $ops) {
                foreach ($ops as $op) {
                    $result[] = sprintf(
                        $this->lng->txt('rbac_log_operation_' . $action),
                        $this->lng->txt('obj_' . $type)
                    ) . ': ' . $this->getOPCaption($type, $op);
                }
            }
        }
        return implode('<br>', $result);
    }

    private function getOPCaption(string $type, array|int|string $op): string
    {
        if (is_array($op)) {
            return array_reduce(
                $op,
                fn(string $c, array|int|string $v) => $c === ''
                    ? $this->getOPCaption($type, $v)
                    : $c . ',' . $this->getOPCaption($type, $v),
                ''
            );
        }

        if (!isset($this->operations[$op])) {
            return '';
        }

        $op_id = $this->operations[$op];
        if (substr($op_id, 0, 7) !== 'create_') {
            return $this->getNonCreateTranslation($type, $op_id);
        }

        return $this->getCreateTranslation($type, $op_id);
    }

    private function getNonCreateTranslation(string $type, string $op_id): string
    {
        $perm = $this->getTranslationFromPlugin($type, $op_id);
        if ($this->isTranslated($perm, $op_id)) {
            return $perm;
        }

        if ($this->lng->exists($type . '_' . $op_id . '_short')) {
            return $this->lng->txt($type . '_' . $op_id . '_short');
        }

        return $this->lng->txt($op_id);
    }

    private function getCreateTranslation(string $type, string $op_id): string
    {
        $obj_type = substr($op_id, 7, strlen($op_id));
        $perm = $this->getTranslationFromPlugin($obj_type, $op_id);

        if ($this->isTranslated($perm, $op_id)) {
            return $perm;
        }

        return $this->lng->txt('rbac_' . $op_id);
    }

    private function getTranslationFromPlugin(string $type, string $op_id): ?string
    {
        if ($this->object_definition->isPlugin($type)) {
            return \ilObjectPlugin::lookupTxtById($type, $op_id);
        }
        return null;
    }

    private function isTranslated(?string $perm, string $op_id): bool
    {
        return $perm !== null && strpos($perm, $op_id) === false;
    }
}
