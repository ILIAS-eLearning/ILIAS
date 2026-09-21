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
use ILIAS\Data\ReferenceId;
use ILIAS\StaticURL\Services as StaticUrl;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Table of the repository objects released for a platform that launches ILIAS as LTI provider.
 * Shows the columns of the former table.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
readonly class ilLTIAdministrationProviderReleasedObjectTable implements DataRetrieval
{
    public function __construct(
        private ilDBInterface $db,
        private ilLanguage $lng,
        private Factory $ui_factory,
        private StaticUrl $static_url
    ) {
    }

    public function getTable(ServerRequestInterface $request): DataTable
    {
        $column = $this->ui_factory->table()->column();

        return $this->ui_factory->table()->data($this, $this->lng->txt("lti_released_objects"), [
            "type" => $column->statusIcon($this->lng->txt("type")),
            "title" => $column->link($this->lng->txt("title")),
            "consumer" => $column->text($this->lng->txt("lti_consumer")),
        ])
            ->withId("lti_provider_released_object_table")
            ->withOrder(new Order("title", Order::ASC))
            ->withRange(new Range(0, 20))
            ->withRequest($request);
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
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $order_columns = ["type" => "od.type", "title" => "od.title", "consumer" => "c.title"];
        $order_by = ($order_columns[$order_field] ?? "od.title") . ($order_direction === Order::DESC ? " DESC" : " ASC");

        $this->db->setLimit($range->getLength(), $range->getStart());
        $result = $this->db->query(
            "SELECT l.consumer_pk, l.ref_id, od.type, od.title, c.title consumer" . $this->getFrom() . " ORDER BY " . $order_by
        );
        while ($row = $this->db->fetchAssoc($result)) {
            $link = (string) $this->static_url->builder()->build((string) $row["type"], new ReferenceId((int) $row["ref_id"]));
            yield $row_builder->buildDataRow((string) $row["consumer_pk"], [
                "type" => $this->ui_factory->symbol()->icon()->standard((string) $row["type"], (string) $row["type"], Icon::SMALL),
                "title" => $this->ui_factory->link()->standard((string) $row["title"], $link),
                "consumer" => htmlspecialchars((string) $row["consumer"]),
            ]);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return (int) $this->db->fetchAssoc($this->db->query("SELECT COUNT(*) cnt" . $this->getFrom()))["cnt"];
    }

    private function getFrom(): string
    {
        // releases of objects that no longer exist are listed too;
        // ref_id 0 is the LTI Advantage registration of a platform, not a released object
        return " FROM lti2_consumer l"
            . " JOIN lti_ext_consumer c ON c.id = l.ext_consumer_id"
            . " LEFT JOIN object_reference r ON r.ref_id = l.ref_id"
            . " LEFT JOIN object_data od ON od.obj_id = r.obj_id"
            . " WHERE l.enabled = 1 AND l.ref_id > 0 AND (od.type IS NULL OR od.type <> 'rolf')";
    }
}
