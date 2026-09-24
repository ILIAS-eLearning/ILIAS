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
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

/**
 * The latest grade of each user that the tool of an LTI object sent through LTI Advantage Assignment
 * and Grade Services. A user without the permission to read the outcomes only sees their own grade.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIObjectGradebookGUI implements DataRetrieval
{
    public const string CMD_SHOW = 'show';

    private const string TABLE_NAME = 'lti_consumer_grades';

    private readonly ILIAS\DI\Container $dic;
    private ?array $records = null;

    public function __construct(private readonly ilObjLTITool $object)
    {
        global $DIC;

        $this->dic = $DIC;
    }

    /**
     * @throws ilObjectException
     */
    public function executeCommand(): void
    {
        if (!$this->object->getTool()->isGradeSynchronization()) {
            throw new ilObjectException('the tool of this object does not synchronize grades');
        }

        $lng = $this->dic->language();
        $column = $this->dic->ui()->factory()->table()->column();
        $date_format = new ILIAS\Data\Factory()->dateFormat()->withTime24($this->dic->user()->getDateFormat());

        $table = $this->dic->ui()->factory()->table()->data($this, $lng->txt('tab_grade_synchronization'), [
            'lti_timestamp' => $column->date($lng->txt('tbl_grade_date'), $date_format),
            'actor' => $column->text($lng->txt('tbl_grade_actor')),
            'score' => $column->text($lng->txt('tbl_grade_score')),
            'activity_progress' => $column->text($lng->txt('tbl_grade_activity_progress')),
            'grading_progress' => $column->text($lng->txt('tbl_grade_grading_progress')),
            'stored' => $column->date($lng->txt('tbl_grade_stored'), $date_format),
        ])
            ->withId('lti_gradebook')
            ->withOrder(new Order('lti_timestamp', Order::DESC))
            ->withRange(new Range(0, 20))
            ->withRequest($this->dic->http()->request());

        $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render($table));
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
        $lng = $this->dic->language();
        $rows = array_map(static fn(array $record): array => [
            'id' => (string) $record['id'],
            'lti_timestamp' => new DateTimeImmutable((string) $record['lti_timestamp']),
            'actor' => ilObjUser::_lookupFullname((int) $record['usr_id']),
            'score' => $record['score_given'] . ' / ' . $record['score_maximum'],
            'activity_progress' => $lng->txt('grade_activity_progress_' . strtolower((string) $record['activity_progress'])),
            'grading_progress' => $lng->txt('grade_grading_progress_' . strtolower((string) $record['grading_progress'])),
            'stored' => new DateTimeImmutable((string) $record['stored']),
        ], $this->getRecords());

        [$field, $direction] = $order->join([], static fn(array $result, string $key, string $value): array => [$key, $value]);
        usort($rows, static fn(array $left, array $right): int => $left[$field] <=> $right[$field]);
        if ($direction === Order::DESC) {
            $rows = array_reverse($rows);
        }

        foreach (array_slice($rows, $range->getStart(), $range->getLength()) as $row) {
            yield $row_builder->buildDataRow($row['id'], $row);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getRecords());
    }

    /**
     * @return array the latest grade of each user, only the one of the current user without the
     *               permission to read the outcomes
     */
    private function getRecords(): array
    {
        if ($this->records !== null) {
            return $this->records;
        }

        $db = $this->dic->database();
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE obj_id = ' . $db->quote($this->object->getId(), 'integer');
        if (!ilObjLTIToolAccess::hasOutcomesAccess($this->object)) {
            $query .= ' AND usr_id = ' . $db->quote($this->dic->user()->getId(), 'integer');
        }
        $result = $db->query($query . ' ORDER BY lti_timestamp DESC, stored DESC, id DESC');

        $this->records = [];
        while ($row = $db->fetchAssoc($result)) {
            $this->records[(int) $row['usr_id']] ??= $row;
        }
        $this->records = array_values($this->records);

        return $this->records;
    }
}
