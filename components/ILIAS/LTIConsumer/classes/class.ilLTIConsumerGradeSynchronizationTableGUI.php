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
use ILIAS\UI\Factory;

/**
 * Class ilLTIConsumerGradeSynchronizationTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerGradeSynchronizationTableGUI implements DataRetrieval
{
    protected ilLanguage $lng;
    protected Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected $request;
    protected bool $isMultiActorReport;
    private array $records = [];

    public function __construct(bool $isMultiActorReport)
    {
        global $DIC;

        $this->isMultiActorReport = $isMultiActorReport;

        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();

    }

    /**
     * @throws DateMalformedStringException
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
        $records = $this->applyOrdering($this->records, $order, $range);
        foreach ($records as $record) {
            $record['lti_timestamp'] = new DateTimeImmutable($record['lti_timestamp']);
            $record['score_given'] = $record['score_given'] . ' / ' . $record['score_maximum'];
            $record['activity_progress'] = $this->lng->txt('grade_activity_progress_' . strtolower($record['activity_progress']));
            $record['grading_progress'] = $this->lng->txt('grade_grading_progress_' . strtolower($record['grading_progress']));
            $record['stored'] = new DateTimeImmutable($record['stored']);

            yield $row_builder->buildDataRow((string) $record['id'], $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->records);
    }

    public function setRecords(array $records): void
    {
        $this->records = $records;
    }

    protected function applyOrdering(array $records, Order $order, ?Range $range = null): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );

        usort($records, static function (array $left, array $right) use ($order_field): int {
            $left_val = $left[$order_field] ?? '';
            $right_val = $right[$order_field] ?? '';

            if ($left_val instanceof DateTimeImmutable) {
                $left_val = $left_val->getTimestamp();
            }
            if ($right_val instanceof DateTimeImmutable) {
                $right_val = $right_val->getTimestamp();
            }

            return $left_val <=> $right_val;
        });

        if ($order_direction === Order::DESC) {
            $records = array_reverse($records);
        }

        if ($range !== null) {
            $records = array_slice($records, $range->getStart(), $range->getLength());
        }

        return $records;
    }

    public function getHTML(): string
    {
        $table = $this->ui_factory->table()
            ->data($this, "", $this->getColumns())
            ->withOrder(new Order("lti_timestamp", Order::DESC))
            ->withRange(new Range(0, 20))
            ->withRequest($this->request);

        return $this->ui_renderer->render($table);
    }

    private function getColumns(): array
    {
        global $DIC;
        $df = new \ILIAS\Data\Factory();


        return [
            "lti_timestamp" => $this->ui_factory->table()->column()->date($this->lng->txt('tbl_grade_date'), $df->dateFormat()->withTime24($DIC->user()->getDateFormat())),
            "actor" => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_grade_actor')),
            "score_given" => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_grade_score')),
            "activity_progress" => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_grade_activity_progress')),
            "grading_progress" => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_grade_grading_progress')),
            "stored" => $this->ui_factory->table()->column()->date($this->lng->txt('tbl_grade_stored'), $df->dateFormat()->withTime24($DIC->user()->getDateFormat()))
        ];
    }
}
