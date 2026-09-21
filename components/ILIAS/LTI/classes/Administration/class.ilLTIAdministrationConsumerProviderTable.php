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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Table of the global or user defined providers of ILIAS as LTI consumer (LTI 1.1 and LTI Advantage).
 * Shows the columns, filters and actions of the former table, plus the LTI version.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIAdministrationConsumerProviderTable implements DataRetrieval
{
    private const string VERSION_1P1 = "LTI-1p0";
    private const string VERSION_ADVANTAGE = "1.3.0";
    private const array CATEGORIES = ["organisation", "communication", "content", "assessment", "feedback"];
    private const string ACTION_EDIT = "edit";
    private const string ACTION_ACCEPT = "accept";
    private const string ACTION_RESET = "reset";
    private const string ACTION_CONFIRM_DELETE = "confirm_delete";
    private const string ACTION_DELETE = "delete";

    private URLBuilder $url_builder;
    private URLBuilderToken $action_token;
    private URLBuilderToken $id_token;

    public function __construct(
        private readonly ilDBInterface $db,
        private readonly ilLanguage $lng,
        private readonly ilObjUser $user,
        private readonly Factory $ui_factory,
        private readonly Renderer $ui_renderer,
        private readonly ilUIService $ui_service,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrlInterface $ctrl,
        private readonly ServerRequestInterface $request,
        private readonly bool $global,
        private readonly bool $writable
    ) {
        $this->lng->loadLanguageModule("rep");
        $this->url_builder = new URLBuilder(new DataFactory()->uri((string) $this->request->getUri()));
        [$this->url_builder, $this->action_token, $this->id_token] = $this->url_builder->acquireParameters(
            ["lti", "provider"],
            "action",
            "ids"
        );
    }

    /**
     * Executes the table action of the current request, if any, and redirects to the given commands.
     * @throws ilCtrlException
     */
    public function handleAction(object $gui, string $return_cmd, string $edit_cmd): void
    {
        $query = $this->request->getQueryParams();
        $action = $query[$this->action_token->getName()] ?? null;
        if ($action === null || !$this->writable) {
            return;
        }
        $ids = array_filter(array_map("intval", (array) ($query[$this->id_token->getName()] ?? [])));
        if ($ids === []) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("lti_no_provider_selected"), true);
            $this->ctrl->redirect($gui, $return_cmd);
        }
        $in_ids = $this->db->in("id", $ids, false, "integer");

        switch ($action) {
            case self::ACTION_EDIT:
                $this->ctrl->setParameter($gui, "provider_id", reset($ids));
                $this->ctrl->redirect($gui, $edit_cmd);
                break;

            case self::ACTION_ACCEPT:
            case self::ACTION_RESET:
                $accept = $action === self::ACTION_ACCEPT;
                // nothing changes if one of the selected providers has no creator or already has the scope
                $invalid = $this->db->query(
                    "SELECT COUNT(*) cnt FROM lti_ext_provider WHERE " . $in_ids
                    . " AND (creator IS NULL OR creator = 0 OR global = " . $this->db->quote((int) $accept, "integer") . ")"
                );
                if ((int) $this->db->fetchAssoc($invalid)["cnt"] > 0) {
                    $this->tpl->setOnScreenMessage(
                        "failure",
                        $this->lng->txt($accept ? "lti_at_least_one_not_acceptable_as_global" : "lti_at_least_one_not_resetable_to_usr_def"),
                        true
                    );
                    break;
                }
                $this->db->manipulate(
                    "UPDATE lti_ext_provider SET global = " . $this->db->quote((int) $accept, "integer")
                    . ", accepted_by = " . $this->db->quote($accept ? $this->user->getId() : 0, "integer")
                    . " WHERE " . $in_ids
                );
                $this->tpl->setOnScreenMessage(
                    "success",
                    $this->lng->txt($accept ? "lti_success_accept_as_global" : "lti_success_reset_to_usr_def"),
                    true
                );
                break;

            case self::ACTION_CONFIRM_DELETE:
                $this->showDeleteModal($in_ids);
                exit();

            case self::ACTION_DELETE:
                $usages = $this->db->query(
                    "SELECT COUNT(*) cnt FROM lti_consumer_settings WHERE " . $this->db->in("provider_id", $ids, false, "integer")
                );
                if ((int) $this->db->fetchAssoc($usages)["cnt"] > 0) {
                    $this->tpl->setOnScreenMessage("failure", $this->lng->txt("lti_at_least_one_prov_has_usages"), true);
                    break;
                }
                $this->db->manipulate("DELETE FROM lti_ext_provider WHERE " . $in_ids);
                $this->tpl->setOnScreenMessage("success", $this->lng->txt("lti_success_delete_provider"), true);
                break;
        }
        $this->ctrl->redirect($gui, $return_cmd);
    }

    public function getFilter(string $action): Filter
    {
        $field = $this->ui_factory->input()->field();
        $yes_no = ["yes" => $this->lng->txt("yes"), "no" => $this->lng->txt("no")];
        $inputs = [
            "title" => $field->text($this->lng->txt("title")),
            "keywords" => $field->text($this->lng->txt("tbl_lti_prov_keywords")),
            "outcome" => $field->select($this->lng->txt("tbl_lti_prov_outcome"), $yes_no),
            "internal" => $field->select($this->lng->txt("tbl_lti_prov_internal"), $yes_no),
            "with_key" => $field->select($this->lng->txt("tbl_lti_prov_with_key"), $yes_no),
            "category" => $field->select($this->lng->txt("tbl_lti_prov_category"), $this->getCategoryOptions()),
            "version" => $field->select($this->lng->txt("lti_con_version"), self::getVersionOptions($this->lng)),
        ];

        return $this->ui_service->filter()->standard(
            "lti_consumer_provider_table_" . ($this->global ? "global" : "user"),
            $action,
            $inputs,
            array_fill(0, count($inputs), true),
            true
        );
    }

    public function getTable(Filter $filter): DataTable
    {
        $column = $this->ui_factory->table()->column();
        // every column but the title can be hidden; category and keywords are hidden by default because they are rarely used
        $table = $this->ui_factory->table()->data($this, $this->lng->txt("tbl_provider_header"), [
            "title" => $column->text($this->lng->txt("title")),
            "description" => $column->text($this->lng->txt("tbl_lti_prov_description"))->withIsOptional(true),
            "category" => $column->text($this->lng->txt("tbl_lti_prov_category"))->withIsOptional(true, false),
            "keywords" => $column->listing($this->lng->txt("tbl_lti_prov_keywords"))->withIsOptional(true, false),
            "outcome" => $column->text($this->lng->txt("tbl_lti_prov_outcome"))->withIsOptional(true, false),
            "internal" => $column->text($this->lng->txt("tbl_lti_prov_internal"))->withIsOptional(true, false),
            "with_key" => $column->text($this->lng->txt("tbl_lti_prov_with_key"))->withIsOptional(true),
            "availability" => $column->text($this->lng->txt("tbl_lti_prov_availability"))->withIsOptional(true),
            "own_provider" => $column->text($this->lng->txt("tbl_lti_prov_own_provider"))->withIsOptional(true, false),
            "provider_creator" => $column->text($this->lng->txt("tbl_lti_prov_provider_creator"))->withIsOptional(true, false),
            "usages_untrashed" => $column->number($this->lng->txt("tbl_lti_prov_usages_untrashed"))->withIsOptional(true),
            "usages_trashed" => $column->number($this->lng->txt("tbl_lti_prov_usages_trashed"))->withIsOptional(true, false),
            "version" => $column->text($this->lng->txt("lti_con_version"))->withIsOptional(true),
        ])
            ->withId("lti_consumer_provider_table_" . ($this->global ? "global" : "user"))
            ->withOrder(new Order("title", Order::ASC))
            ->withRange(new Range(0, 20))
            ->withFilter($this->ui_service->filter()->getData($filter))
            ->withRequest($this->request);

        return $this->writable ? $table->withActions($this->getActions()) : $table;
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
            "description" => "p.description",
            "category" => "p.category",
            "keywords" => "p.keywords",
            "outcome" => "p.has_outcome",
            "internal" => "p.external_provider",
            "with_key" => "p.provider_key_customizable",
            "availability" => "p.availability",
            "own_provider" => "own_provider",
            "provider_creator" => "creator_name",
            "usages_untrashed" => "usages_untrashed",
            "usages_trashed" => "usages_trashed",
            "version" => "p.lti_version",
        ];
        $order_by = ($order_columns[$order_field] ?? "p.title") . ($order_direction === Order::DESC ? " DESC" : " ASC");

        $this->db->setLimit($range->getLength(), $range->getStart());
        $result = $this->db->query(
            "SELECT p.id, p.title, p.description, p.category, p.keywords, p.has_outcome, p.external_provider,"
            . " p.provider_key_customizable, p.availability, p.creator, p.lti_version,"
            . " p.creator = " . $this->db->quote($this->user->getId(), "integer") . " own_provider,"
            . " u.usr_id creator_exists, TRIM(CONCAT_WS(' ', u.title, u.firstname, u.lastname)) creator_name,"
            . " (" . $this->getUsagesQuery(false) . ") usages_untrashed,"
            . " (" . $this->getUsagesQuery(true) . ") usages_trashed"
            . " FROM lti_ext_provider p LEFT JOIN usr_data u ON u.usr_id = p.creator"
            . " WHERE " . $this->getWhere($filter_data)
            . " ORDER BY " . $order_by
        );

        $categories = $this->getCategoryOptions();
        while ($row = $this->db->fetchAssoc($result)) {
            yield $row_builder->buildDataRow((string) $row["id"], [
                "title" => htmlspecialchars((string) $row["title"]),
                "description" => htmlspecialchars((string) $row["description"]),
                "category" => $categories[$row["category"]] ?? "",
                "keywords" => $this->ui_factory->listing()->unordered($this->getKeywords((string) $row["keywords"])),
                "outcome" => $row["has_outcome"] ? $this->lng->txt("yes") : "",
                "internal" => $row["external_provider"] ? "" : $this->lng->txt("yes"),
                "with_key" => $row["provider_key_customizable"] ? "" : $this->lng->txt("yes"),
                "availability" => $this->getAvailability((int) $row["availability"]),
                "own_provider" => $row["own_provider"] ? $this->lng->txt("yes") : "",
                "provider_creator" => $this->getCreator((int) $row["creator"], $row["creator_exists"] !== null, (string) $row["creator_name"]),
                "usages_untrashed" => (int) $row["usages_untrashed"],
                "usages_trashed" => (int) $row["usages_trashed"],
                "version" => self::getVersionOptions($this->lng)[$row["lti_version"]] ?? (string) $row["lti_version"],
            ]);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $result = $this->db->query("SELECT COUNT(*) cnt FROM lti_ext_provider p WHERE " . $this->getWhere($filter_data));

        return (int) $this->db->fetchAssoc($result)["cnt"];
    }

    /**
     * @return array
     */
    private function getActions(): array
    {
        $action = $this->ui_factory->table()->action();
        $scope_action = $this->global ? self::ACTION_RESET : self::ACTION_ACCEPT;
        $scope_label = $this->global ? "lti_action_reset_provider_to_user_scope" : "lti_action_accept_provider_as_global";

        return [
            self::ACTION_EDIT => $action->single(
                $this->lng->txt("edit"),
                $this->url_builder->withParameter($this->action_token, self::ACTION_EDIT),
                $this->id_token
            ),
            $scope_action => $action->standard(
                $this->lng->txt($scope_label),
                $this->url_builder->withParameter($this->action_token, $scope_action),
                $this->id_token
            ),
            self::ACTION_CONFIRM_DELETE => $action->standard(
                $this->lng->txt("lti_delete_provider"),
                $this->url_builder->withParameter($this->action_token, self::ACTION_CONFIRM_DELETE),
                $this->id_token
            )->withAsync(),
        ];
    }

    private function showDeleteModal(string $in_ids): void
    {
        $items = [];
        $ids = [];
        $result = $this->db->query("SELECT id, title FROM lti_ext_provider WHERE " . $in_ids);
        while ($row = $this->db->fetchAssoc($result)) {
            $ids[] = (string) $row["id"];
            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard((string) $row["id"], (string) $row["title"]);
        }
        $delete_url = $this->url_builder
            ->withParameter($this->action_token, self::ACTION_DELETE)
            ->withParameter($this->id_token, $ids)
            ->buildURI();

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt("confirm"),
                $this->lng->txt("lti_confirm_delete_providers"),
                (string) $delete_url
            )->withAffectedItems($items)
        );
    }

    private function getWhere(mixed $filter_data): string
    {
        $filter = is_array($filter_data) ? $filter_data : [];
        $conditions = ["p.global = " . $this->db->quote((int) $this->global, "integer")];

        if ((string) ($filter["title"] ?? "") !== "") {
            $conditions[] = $this->db->like("p.title", "text", "%" . $filter["title"] . "%");
        }
        if ((string) ($filter["keywords"] ?? "") !== "") {
            $conditions[] = $this->db->like("p.keywords", "text", "%" . $filter["keywords"] . "%");
        }
        $yes_no_columns = ["outcome" => "p.has_outcome", "internal" => "p.external_provider", "with_key" => "p.provider_key_customizable"];
        foreach ($yes_no_columns as $input => $db_column) {
            if (in_array($filter[$input] ?? "", ["yes", "no"], true)) {
                // internal and with_key are shown as the negation of the stored flag
                $value = ($filter[$input] === "yes") === ($input === "outcome");
                $conditions[] = $db_column . " = " . $this->db->quote((int) $value, "integer");
            }
        }
        if (in_array($filter["category"] ?? "", self::CATEGORIES, true)) {
            $conditions[] = "p.category = " . $this->db->quote($filter["category"], "text");
        }
        if (isset(self::getVersionOptions($this->lng)[$filter["version"] ?? ""])) {
            $conditions[] = "p.lti_version = " . $this->db->quote($filter["version"], "text");
        }

        return implode(" AND ", $conditions);
    }

    private function getUsagesQuery(bool $trashed): string
    {
        return "SELECT COUNT(s.obj_id) FROM lti_consumer_settings s"
            . " JOIN object_reference r ON r.obj_id = s.obj_id AND r.deleted IS " . ($trashed ? "NOT NULL" : "NULL")
            . " WHERE s.provider_id = p.id";
    }

    /**
     * @return array
     */
    private function getCategoryOptions(): array
    {
        $options = [];
        foreach (self::CATEGORIES as $category) {
            $options[$category] = $this->lng->txt("rep_add_new_def_grp_" . $category);
        }

        return $options;
    }

    /**
     * @param string $keywords
     * @return array
     */
    private function getKeywords(string $keywords): array
    {
        return array_values(array_filter(array_map("trim", explode(";", $keywords)), fn($keyword) => $keyword !== ""));
    }

    /**
     * @param ilLanguage $lng
     * @return array
     */
    public static function getVersionOptions(ilLanguage $lng): array
    {
        return [
            self::VERSION_1P1 => $lng->txt("lti_version_1p1_deprecated"),
            self::VERSION_ADVANTAGE => $lng->txt("lti_version_advantage"),
        ];
    }

    private function getAvailability(int $availability): string
    {
        return match ($availability) {
            2 => $this->lng->txt("lti_con_prov_availability_create"),
            1 => $this->lng->txt("lti_con_prov_availability_existing"),
            0 => $this->lng->txt("lti_con_prov_availability_non"),
            default => "",
        };
    }

    private function getCreator(int $creator, bool $exists, string $name): string
    {
        if ($creator === 0) {
            return "";
        }

        return $exists ? $name : $this->lng->txt("deleted_user");
    }
}
