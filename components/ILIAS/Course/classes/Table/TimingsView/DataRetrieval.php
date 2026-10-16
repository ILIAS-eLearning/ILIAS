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

namespace ILIAS\Course\Table\TimingsView;

use DateTimeImmutable;
use Generator;
use ILIAS\Course\I\Table\TimingsView\DataRetrievalInterface;
use ILIAS\Course\I\Table\TimingsView\HandlerInterface;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ilLink;
use ilObject;
use ilObjectActivation;
use ilObjUser;
use ilSessionAppointment;
use ilTimingUser;

class DataRetrieval implements DataRetrievalInterface
{
    protected const string KEY_SUGGESTION_START = 'suggestion_start';
    protected const string KEY_SUGGESTION_END = 'suggestion_end';
    protected const string KEY_TIMING_START = 'timing_start';
    protected const string KEY_TIMING_END = 'timing_end';
    protected const string KEY_TIMING_TYPE = 'timing_type';
    protected const string KEY_REF_ID = 'ref_id';
    protected const string KEY_TYPE = 'type';
    protected const string KEY_TITLE_LINK = 'title_link';
    protected const string KEY_TITLE = 'title';
    protected const string KEY_TIMINGS_MODIFIED = 'timings_modified';
    protected const string ITEM_GROUP_TYPE = 'itgr';
    protected array $rows;
    protected string $username;

    public function __construct(
        protected readonly UIServices $ui,
        protected readonly ilObjUser $user,
        protected readonly DataFactory $data_factory,
        protected readonly int $user_id,
        protected readonly int $parent_object_ref_id,
    ) {
        $this->initRows();
    }

    final protected function initRows(): void
    {
        if (isset($this->rows)) {
            return;
        }
        $this->rows = [];
        $items = ilObjectActivation::getItems($this->parent_object_ref_id, false);
        foreach ($items as $item) {
            // hide objects without timings
            if ($item[self::KEY_TIMING_TYPE] != ilObjectActivation::TIMINGS_PRESETTING) {
                continue;
            }
            // no item groups
            if ($item[self::KEY_TYPE] == self::ITEM_GROUP_TYPE) {
                continue;
            }
            if ($item[self::KEY_TYPE] == 'sess' && !strlen($item[self::KEY_TITLE])) {
                $app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item[self::KEY_REF_ID]));
                $item[self::KEY_TITLE] = ilSessionAppointment::_appointmentToString(
                    $app_info['start'],
                    $app_info['end'],
                    (bool) $app_info['fullday']
                );
            }
            match ($item[self::KEY_TYPE]) {
                'fold', 'grp', 'sess' => $item[self::KEY_TITLE_LINK] = ilLink::_getLink($item[self::KEY_REF_ID], $item[self::KEY_TYPE]),
                default => $item[self::KEY_TITLE_LINK] = '',
            };
            $timing_user = new ilTimingUser($item['child'], $this->user_id);
            $item[self::KEY_SUGGESTION_START] = ($item[self::KEY_TIMING_TYPE] == ilObjectActivation::TIMINGS_PRESETTING)
                ? $timing_user->getStart()->get(IL_CAL_UNIX) ?? $item[self::KEY_SUGGESTION_START]
                : $item[self::KEY_SUGGESTION_START];
            $item[self::KEY_SUGGESTION_START] = DateTimeImmutable::createFromTimestamp($item[self::KEY_SUGGESTION_START])->setTime(0, 0);
            $item[self::KEY_SUGGESTION_END] = ($item[self::KEY_TIMING_TYPE] == ilObjectActivation::TIMINGS_PRESETTING)
                ? $timing_user->getEnd()->get(IL_CAL_UNIX) ?? $item[self::KEY_SUGGESTION_END]
                : $item[self::KEY_SUGGESTION_END];
            $item[self::KEY_SUGGESTION_END] = DateTimeImmutable::createFromTimestamp($item[self::KEY_SUGGESTION_END])->setTime(0, 0);
            $item[self::KEY_TIMING_START] = DateTimeImmutable::createFromTimestamp($item[self::KEY_TIMING_START])->setTime(0, 0);
            $item[self::KEY_TIMING_END] = DateTimeImmutable::createFromTimestamp($item[self::KEY_TIMING_END])->setTime(0, 0);
            $item[self::KEY_TIMINGS_MODIFIED] =
                $item[self::KEY_SUGGESTION_START] != $item[self::KEY_TIMING_START] ||
                $item[self::KEY_SUGGESTION_END] != $item[self::KEY_TIMING_END];
            $this->rows[] = $item;
        }
    }

    final public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $icons = [
            true => $this->ui->factory()->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small'),
            false => $this->ui->factory()->symbol()->icon()->custom('assets/images/standard/icon_unchecked.svg', '', 'small')
        ];
        foreach ($this->rows as $row) {
            yield $row_builder->buildDataRow(
                $row[self::KEY_REF_ID] . '',
                [
                    HandlerInterface::TABLE_COL_TITLE => $this->ui->factory()->link()->standard($row[self::KEY_TITLE], $row[self::KEY_TITLE_LINK])
                        ->withDisabled(strcmp($row[self::KEY_TITLE_LINK], '') === 0),
                    HandlerInterface::TABLE_COL_SUGGESTION_START => $row[self::KEY_SUGGESTION_START],
                    HandlerInterface::TABLE_COL_SUGGESTION_END => $row[self::KEY_SUGGESTION_END],
                    HandlerInterface::TABLE_COL_TIMINGS_MODIFIED => $icons[$row[self::KEY_TIMINGS_MODIFIED]],
                    HandlerInterface::TABLE_COL_TIMINGS_PLANNED_START => $row[self::KEY_TIMING_START],
                    HandlerInterface::TABLE_COL_TIMINGS_PLANNED_END => $row[self::KEY_TIMING_END]
                ]
            );
        }
    }

    final public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->rows);
    }

    final public function getUsername(): string
    {
        if (!isset($this->username)) {
            $name = ilObjUser::_lookupName($this->user_id);
            $this->username = $name['lastname'] . ', ' . $name['firstname'];
        }
        return $this->username;
    }

    final public function getDateFormat(): DateFormat
    {
        return $this->data_factory->dateFormat()->standard();
    }
}
