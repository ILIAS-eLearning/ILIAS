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
    protected const string KEY_SUGGESTION = 'suggestion';
    protected const string KEY_PLANNED = 'planned';
    protected const string KEY_ROW_ID = 'row_id';
    protected const string KEY_TYPE = 'type';
    protected const string KEY_TITLE_LINK = 'title_link';
    protected const string KEY_TITLE = 'title';
    protected const string ITEM_GROUP_TYPE = 'itgr';
    protected array $rows;
    protected string $username;

    public function __construct(
        protected readonly UIServices $ui,
        protected readonly ilObjUser $user,
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
            if ($item['timing_type'] != ilObjectActivation::TIMINGS_PRESETTING) {
                continue;
            }
            // no item groups
            if ($item['type'] == self::ITEM_GROUP_TYPE) {
                continue;
            }
            # Title
            $sess_values = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item['ref_id']));
            $title = (
                count($sess_values) > 0 &&
                $item['type'] == 'sess' &&
                !strlen($item['title'])
            )
                ? ilSessionAppointment::_appointmentToString(
                    $sess_values['start'],
                    $sess_values['end'],
                    (bool) $sess_values['fullday']
                )
                : $item['title'];

            # Title Link
            $title_link = (in_array($item[self::KEY_TYPE], ['fold', 'grp', 'sess']))
                ? ilLink::_getLink($item['ref_id'], $item['type'])
                : '';

            # Suggested Timing
            $suggested = [
                DateTimeImmutable::createFromTimestamp($item['suggestion_start']),
                DateTimeImmutable::createFromTimestamp($item['suggestion_end']),
            ];

            # Planned Timing
            $timing_user = new ilTimingUser($item['child'], $this->user_id);

            $planned = (
                ((int) $item['timing_type']) === ilObjectActivation::TIMINGS_PRESETTING &&
                !is_null($timing_user->getStart()->get(IL_CAL_UNIX)) &&
                !is_null($timing_user->getEnd()->get(IL_CAL_UNIX))
            )
                ? [
                    DateTimeImmutable::createFromTimestamp($timing_user->getStart()->get(IL_CAL_UNIX)),
                    DateTimeImmutable::createFromTimestamp($timing_user->getEnd()->get(IL_CAL_UNIX))
                ]
                : $suggested;

            $this->rows[] = [
                self::KEY_ROW_ID => $item['ref_id'],
                self::KEY_TITLE => $title,
                self::KEY_TITLE_LINK => $title_link,
                self::KEY_PLANNED => $planned,
                self::KEY_SUGGESTION => $suggested,
            ];
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
        foreach ($this->rows as $row) {
            yield $row_builder->buildDataRow(
                $row[self::KEY_ROW_ID] . '',
                [
                    HandlerInterface::TABLE_COL_TITLE => $this->ui->factory()->link()->standard(
                        $row[self::KEY_TITLE],
                        $row[self::KEY_TITLE_LINK]
                    )
                        ->withDisabled(strcmp($row[self::KEY_TITLE_LINK], '') === 0),
                    HandlerInterface::TABLE_COL_SUGGESTION => $row[self::KEY_SUGGESTION],
                    HandlerInterface::TABLE_COL_TIMINGS_PLANNED => $row[self::KEY_PLANNED],
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
        return $this->user->getDateFormat();
    }
}
