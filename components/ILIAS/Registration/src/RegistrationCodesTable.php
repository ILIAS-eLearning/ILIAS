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

namespace ILIAS\Registration;

use ilObject;
use Generator;
use ilObjUser;
use ilLanguage;
use DateTimeZone;
use ilRbacReview;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use DateTimeImmutable;
use ilCalendarSettings;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Implementation\Component\Table\Action\Action;

class RegistrationCodesTable implements DataRetrieval
{
    public function __construct(
        private readonly ServerRequestInterface $http_request,
        private readonly ilLanguage $lng,
        private readonly UIFactory $ui_factory,
        private readonly DataFactory $data_factory,
        private readonly ilRbacReview $rbac_review,
        private readonly string $action,
        private readonly ilObjUser $actor,
        private readonly RegistrationCodeRepository $code_repository,
        private readonly bool $has_permission_to_delete = false,
    ) {
    }

    /**
     * @param array{code: string, role: int, generated: string, access_limitation: string} $filter_data
     */
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $records = $this->getRecords($range, $order, $filter_data);
        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['code_id'], $record);
        }
    }

    public function getTableComponent(RegistrationFilterComponent $filter): DataTable
    {
        $query_params_namespace = ['registration', 'codes'];
        $table_uri = $this->data_factory->uri(ILIAS_HTTP_PATH . '/' . $this->action);
        $url_builder = new URLBuilder($table_uri);
        /** @var URLBuilder $url_builder */
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'code_ids',
        );

        return $this->ui_factory->table()
            ->data(
                $this,
                '',
                $this->getColumns()
            )
            ->withId(
                'registration_code'
            )
            ->withFilter($filter->getFilterData()->getData())
            ->withOrder(new Order('generated', Order::DESC))
            ->withRange(new Range(0, 100))
            ->withRequest($this->http_request)
            ->withActions($this->getActions($url_builder, $action_parameter_token, $row_id_token));
    }

    /**
     * @param array{code: string, role: int, generated: string, access_limitation: string} $filter_data
     */
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return $this->code_repository->getTotalCodeCount(
            (new CodeFilter())->withData($filter_data)
        );
    }

    /**
     * @param array{code: string, role: int, generated: string, access_limitation: string} $filter_data
     * @return list<array{
     *     code: string,
     *     code_id: int,
     *     generated: DateTimeImmutable,
     *     used: string|null,
     *     role: string,
     *     role_local: string|null,
     *     alimit: string|null
     * }>
     */
    private function getRecords(Range $range, Order $order, ?array $filter_data): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn(array $ret, string $key, string $value): array => [$key, $value]
        );
        $filter = (new CodeFilter())->withData($filter_data);

        $codes_data = $this->code_repository->getCodesData(
            $order_field,
            $order_direction,
            $range->getStart(),
            $range->getLength(),
            $filter
        );

        if (\count($codes_data) === 0 && $range->getStart() > 0) {
            $codes_data = $this->code_repository->getCodesData(
                $order_field,
                $order_direction,
                0,
                $range->getLength(),
                $filter
            );
        }

        if ((int) $this->actor->getTimeFormat() === ilCalendarSettings::TIME_FORMAT_12) {
            $date_format = $this->data_factory->dateFormat()->withTime12($this->actor->getDateFormat());
        } else {
            $date_format = $this->data_factory->dateFormat()->withTime24($this->actor->getDateFormat());
        }

        $role_map = [];
        foreach ($this->rbac_review->getGlobalRoles() as $role_id) {
            if (!\in_array($role_id, [SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID], true)) {
                $role_map[$role_id] = ilObject::_lookupTitle($role_id);
            }
        }

        $result = [];
        foreach ($codes_data as $k => $code) {
            $result[$k]['code'] = $code['code'];
            $result[$k]['code_id'] = (int) $code['code_id'];

            $result[$k]['generated'] = (new DateTimeImmutable('@' . $code['generated']))->setTimezone(
                new DateTimeZone($this->actor->getTimeZone())
            );
            if ($code['used']) {
                $result[$k]['used'] = $date_format->applyTo(
                    (new DateTimeImmutable('@' . $code['used']))->setTimezone(
                        new DateTimeZone($this->actor->getTimeZone())
                    )
                );
            } else {
                $result[$k]['used'] = null;
            }

            if ($code['role']) {
                $result[$k]['role'] = $role_map[$code['role']] ?? $this->lng->txt('deleted');
            } else {
                $result[$k]['role'] = '';
            }

            if (\is_string($code['role_local'])) {
                $local = [];
                foreach (explode(';', $code['role_local']) as $role_id) {
                    $role = ilObject::_lookupTitle((int) $role_id);
                    if ($role) {
                        $local[] = $role;
                    }
                }
                if (\count($local)) {
                    sort($local);
                    $result[$k]['role_local'] = implode('<br />', $local);
                }
            } else {
                $result[$k]['role_local'] = '';
            }

            if ($code['alimit']) {
                switch ($code['alimit']) {
                    case 'unlimited':
                        $result[$k]['alimit'] = $this->lng->txt('reg_access_limitation_none');
                        break;

                    case 'absolute':
                        $result[$k]['alimit'] = $this->lng->txt('reg_access_limitation_mode_absolute_target')
                            . ': '
                            . (
                                $code['alimitdt'] === null
                                ? '-'
                                : $this->actor->getDateFormat()->applyTo(
                                    (new DateTimeImmutable($code['alimitdt']))->setTimezone(
                                        new DateTimeZone($this->actor->getTimeZone())
                                    )
                                )
                            );
                        break;

                    case 'relative':
                        $limit_caption = [];
                        $limit = unserialize($code['alimitdt'], ['allowed_classes' => false]);
                        if ((int) $limit['d']) {
                            $limit_caption[] = (int) $limit['d'] . ' ' . $this->lng->txt('days');
                        }
                        if ((int) $limit['m']) {
                            $limit_caption[] = (int) $limit['m'] . ' ' . $this->lng->txt('months');
                        }
                        if ((int) $limit['y']) {
                            $limit_caption[] = (int) $limit['y'] . ' ' . $this->lng->txt('years');
                        }
                        if (\count($limit_caption)) {
                            $result[$k]['alimit'] = $this->lng->txt('reg_access_limitation_mode_relative_target') .
                                ': ' . implode(', ', $limit_caption);
                        }
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * @return array<string, Action>
     */
    public function getActions(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token
    ): array {
        $actions = [
            $this->ui_factory->table()->action()->multi(
                $this->lng->txt('registration_codes_export'),
                $url_builder->withParameter($action_parameter_token, 'exportCodes'),
                $row_id_token
            ),
        ];
        if ($this->has_permission_to_delete) {
            $actions[] = $this->ui_factory->table()->action()->multi(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_parameter_token, 'deleteConfirmation'),
                $row_id_token
            );
        }

        return $actions;
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        if ((int) $this->actor->getTimeFormat() === ilCalendarSettings::TIME_FORMAT_12) {
            $date_format = $this->data_factory->dateFormat()->withTime12($this->actor->getDateFormat());
        } else {
            $date_format = $this->data_factory->dateFormat()->withTime24($this->actor->getDateFormat());
        }

        return [
            'code' => $this->ui_factory->table()->column()
                ->text($this->lng->txt('registration_code')),
            'role' => $this->ui_factory->table()->column()
                ->text($this->lng->txt('registration_codes_roles')),
            'role_local' => $this->ui_factory->table()->column()
                ->text($this->lng->txt('registration_codes_roles_local'))
                ->withIsSortable(false),
            'alimit' => $this->ui_factory->table()->column()
                ->text($this->lng->txt('reg_access_limitations'))
                ->withIsSortable(false),
            'generated' => $this->ui_factory->table()->column()
                ->date($this->lng->txt('registration_generated'), $date_format),
            'used' => $this->ui_factory->table()->column()
                ->text($this->lng->txt('registration_used')),
        ];
    }
}
