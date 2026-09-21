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
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Table of the repository objects that use a global provider of ILIAS as LTI consumer.
 * Shows the columns of the former table, plus the LTI version.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
readonly class ilLTIAdministrationConsumerUsageTable implements DataRetrieval
{
    public function __construct(
        private ilDBInterface $db,
        private ilLanguage $lng,
        private Factory $ui_factory,
        private ilUIService $ui_service,
        private StaticUrl $static_url
    ) {
    }

    public function getFilter(string $action): Filter
    {
        $field = $this->ui_factory->input()->field();
        $inputs = [
            "title" => $field->text($this->lng->txt("tbl_lti_prov_title")),
            "used_by" => $field->text($this->lng->txt("tbl_lti_prov_used_by")),
            "trashed" => $field->select($this->lng->txt("tbl_lti_prov_usages_trashed"), [
                "yes" => $this->lng->txt("yes"),
                "no" => $this->lng->txt("no"),
            ]),
            "version" => $field->select(
                $this->lng->txt("lti_con_version"),
                ilLTIAdministrationConsumerProviderTable::getVersionOptions($this->lng)
            ),
        ];

        return $this->ui_service->filter()->standard(
            "lti_consumer_usage_table",
            $action,
            $inputs,
            array_fill(0, count($inputs), true),
            true
        );
    }

    public function getTable(Filter $filter, ServerRequestInterface $request): DataTable
    {
        $column = $this->ui_factory->table()->column();
        $icon = $this->ui_factory->symbol()->icon();

        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt("tbl_provider_usage_header"),
            [
                "title" => $column->text($this->lng->txt("tbl_lti_prov_title")),
                "trashed" => $column->boolean(
                    $this->lng->txt("tbl_lti_prov_usages_trashed"),
                    $icon->custom("assets/images/standard/icon_ok.svg", $this->lng->txt("icon_ok")),
                    $icon->custom("assets/images/standard/icon_not_ok.svg", $this->lng->txt("icon_not_ok"))
                )->withIsOptional(true),
                "used_by" => $column->link($this->lng->txt("tbl_lti_prov_used_by"))->withIsOptional(true),
                "version" => $column->text($this->lng->txt("lti_con_version"))->withIsOptional(true),
            ]
        )
            ->withId("lti_consumer_usage_table")
            ->withOrder(new Order("title", Order::ASC))
            ->withRange(new Range(0, 20))
            ->withFilter($this->ui_service->filter()->getData($filter))
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
        $order_columns = [
            "title" => "p.title",
            "trashed" => "trashed",
            "used_by" => "used_by",
            "version" => "p.lti_version",
        ];
        $order_by = ($order_columns[$order_field] ?? "p.title") . ($order_direction === Order::DESC ? " DESC" : " ASC");

        $this->db->setLimit($range->getLength(), $range->getStart());
        $result = $this->db->query(
            "SELECT p.title, p.lti_version, r.ref_id, r.deleted IS NOT NULL trashed, od.type, od.title used_by"
            . $this->getFrom($filter_data)
            . " ORDER BY " . $order_by
        );
        $versions = ilLTIAdministrationConsumerProviderTable::getVersionOptions($this->lng);
        while ($row = $this->db->fetchAssoc($result)) {
            $link = (string) $this->static_url->builder()->build((string) $row["type"], new ReferenceId((int) $row["ref_id"]));
            yield $row_builder->buildDataRow((string) $row["ref_id"], [
                "title" => htmlspecialchars((string) $row["title"]),
                "trashed" => (bool) $row["trashed"],
                "used_by" => $this->ui_factory->link()->standard((string) $row["used_by"], $link),
                "version" => $versions[$row["lti_version"]] ?? (string) $row["lti_version"],
            ]);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return (int) $this->db->fetchAssoc($this->db->query("SELECT COUNT(*) cnt" . $this->getFrom($filter_data)))["cnt"];
    }

    private function getFrom(mixed $filter_data): string
    {
        $filter = is_array($filter_data) ? $filter_data : [];
        $conditions = ["p.global = 1"];

        if ((string) ($filter["title"] ?? "") !== "") {
            $conditions[] = $this->db->like("p.title", "text", "%" . $filter["title"] . "%");
        }
        if ((string) ($filter["used_by"] ?? "") !== "") {
            $conditions[] = $this->db->like("od.title", "text", "%" . $filter["used_by"] . "%");
        }
        if (in_array($filter["trashed"] ?? "", ["yes", "no"], true)) {
            $conditions[] = "r.deleted IS " . ($filter["trashed"] === "yes" ? "NOT NULL" : "NULL");
        }
        if (isset(ilLTIAdministrationConsumerProviderTable::getVersionOptions($this->lng)[$filter["version"] ?? ""])) {
            $conditions[] = "p.lti_version = " . $this->db->quote($filter["version"], "text");
        }

        return " FROM lti_ext_provider p"
            . " JOIN lti_consumer_settings s ON s.provider_id = p.id"
            . " JOIN object_reference r ON r.obj_id = s.obj_id"
            . " JOIN object_data od ON od.obj_id = s.obj_id"
            . " WHERE " . implode(" AND ", $conditions);
    }
}
