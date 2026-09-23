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
 * Table of the external tools ILIAS may launch (LTI 1.1 and LTI Advantage). It serves two screens:
 * the administration lists the global or the user defined tools and acts on them, and the creation of
 * an LTI object lists the tools the user may pick and links each title to the command that creates it.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolTable implements DataRetrieval
{
    private const array CATEGORIES = ["organisation", "communication", "content", "assessment", "feedback"];
    private const string ACTION_EDIT = "edit";
    private const string ACTION_ACCEPT = "accept";
    private const string ACTION_RESET = "reset";
    private const string ACTION_CONFIRM_DELETE = "confirm_delete";
    private const string ACTION_DELETE = "delete";

    private URLBuilder $url_builder;
    private URLBuilderToken $action_token;
    private URLBuilderToken $id_token;

    private function __construct(
        private readonly ilLanguage $lng,
        private readonly ilObjUser $user,
        private readonly Factory $ui_factory,
        private readonly Renderer $ui_renderer,
        private readonly ilUIService $ui_service,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrlInterface $ctrl,
        private readonly ServerRequestInterface $request,
        private readonly int $scope,
        private readonly bool $writable,
        private readonly ?object $select_gui = null,
        private readonly string $select_cmd = ''
    ) {
        $this->lng->loadLanguageModule("rep");
        $this->url_builder = new URLBuilder(new DataFactory()->uri((string) $this->request->getUri()));
        [$this->url_builder, $this->action_token, $this->id_token] = $this->url_builder->acquireParameters(
            ["lti", "tool"],
            "action",
            "ids"
        );
    }

    public static function forAdministration(
        ilLanguage $lng,
        ilObjUser $user,
        Factory $ui_factory,
        Renderer $ui_renderer,
        ilUIService $ui_service,
        ilGlobalTemplateInterface $tpl,
        ilCtrlInterface $ctrl,
        ServerRequestInterface $request,
        bool $global,
        bool $writable
    ): self {
        return new self(
            $lng,
            $user,
            $ui_factory,
            $ui_renderer,
            $ui_service,
            $tpl,
            $ctrl,
            $request,
            $global ? ilLTITool::SCOPE_GLOBAL : ilLTITool::SCOPE_USER,
            $writable
        );
    }

    /**
     * Lists the tools the user may create an object for. Each title links to the given command of the
     * given GUI, with the tool in the parameter tool_id.
     */
    public static function forSelection(
        ilLanguage $lng,
        ilObjUser $user,
        Factory $ui_factory,
        Renderer $ui_renderer,
        ilUIService $ui_service,
        ilGlobalTemplateInterface $tpl,
        ilCtrlInterface $ctrl,
        ServerRequestInterface $request,
        object $gui,
        string $cmd
    ): self {
        return new self(
            $lng,
            $user,
            $ui_factory,
            $ui_renderer,
            $ui_service,
            $tpl,
            $ctrl,
            $request,
            ilLTITool::SCOPE_SELECTABLE,
            false,
            $gui,
            $cmd
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

        switch ($action) {
            case self::ACTION_EDIT:
                $this->ctrl->setParameter($gui, "tool_id", reset($ids));
                $this->ctrl->redirect($gui, $edit_cmd);
                break;

            case self::ACTION_ACCEPT:
            case self::ACTION_RESET:
                $accept = $action === self::ACTION_ACCEPT;
                // nothing changes if one of the selected providers has no creator or already has the scope
                if (ilLTITool::countWithFixedScope($ids, $accept) > 0) {
                    $this->tpl->setOnScreenMessage(
                        "failure",
                        $this->lng->txt($accept ? "lti_at_least_one_not_acceptable_as_global" : "lti_at_least_one_not_resetable_to_usr_def"),
                        true
                    );
                    break;
                }
                ilLTITool::updateScope($ids, $accept, $this->user->getId());
                $this->tpl->setOnScreenMessage(
                    "success",
                    $this->lng->txt($accept ? "lti_success_accept_as_global" : "lti_success_reset_to_usr_def"),
                    true
                );
                break;

            case self::ACTION_CONFIRM_DELETE:
                $this->showDeleteModal($ids);
                exit();

            case self::ACTION_DELETE:
                if (ilLTITool::countUsages($ids) > 0) {
                    $this->tpl->setOnScreenMessage("failure", $this->lng->txt("lti_at_least_one_prov_has_usages"), true);
                    break;
                }
                ilLTITool::delete($ids);
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
            "lti_tool_table_" . $this->tableSuffix(),
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
            "title" => $this->select_gui === null
                ? $column->text($this->lng->txt("title"))
                : $column->link($this->lng->txt("title")),
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
            ->withId("lti_tool_table_" . $this->tableSuffix())
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

        $rows = ilLTITool::getRows(
            $this->scope,
            $this->buildFilter($filter_data),
            $this->user->getId(),
            $order_by,
            $range->getLength(),
            $range->getStart()
        );

        $categories = $this->getCategoryOptions();
        foreach ($rows as $row) {
            yield $row_builder->buildDataRow((string) $row["id"], [
                "title" => $this->buildTitle((int) $row["id"], (string) $row["title"]),
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
        return ilLTITool::countRows($this->scope, $this->buildFilter($filter_data), $this->user->getId());
    }

    /**
     * @return array
     */
    private function getActions(): array
    {
        $action = $this->ui_factory->table()->action();
        $global = $this->scope === ilLTITool::SCOPE_GLOBAL;
        $scope_action = $global ? self::ACTION_RESET : self::ACTION_ACCEPT;
        $scope_label = $global ? "lti_action_reset_provider_to_user_scope" : "lti_action_accept_provider_as_global";

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

    private function showDeleteModal(array $ids): void
    {
        $items = [];
        foreach (ilLTITool::lookupTitles($ids) as $id => $title) {
            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard((string) $id, $title);
        }
        $delete_url = $this->url_builder
            ->withParameter($this->action_token, self::ACTION_DELETE)
            ->withParameter($this->id_token, array_map("strval", $ids))
            ->buildURI();

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt("confirm"),
                $this->lng->txt("lti_confirm_delete_providers"),
                (string) $delete_url
            )->withAffectedItems($items)
        );
    }

    /**
     * Translates the filter of the table into the columns of lti_ext_provider.
     *
     * @param mixed $filter_data
     * @return array
     */
    private function buildFilter(mixed $filter_data): array
    {
        $input = is_array($filter_data) ? $filter_data : [];
        $filter = [
            "title" => (string) ($input["title"] ?? ""),
            "keywords" => (string) ($input["keywords"] ?? ""),
        ];

        $yes_no_columns = ["outcome" => "has_outcome", "internal" => "external_provider", "with_key" => "provider_key_customizable"];
        foreach ($yes_no_columns as $name => $column) {
            if (in_array($input[$name] ?? "", ["yes", "no"], true)) {
                // internal and with_key are shown as the negation of the stored flag
                $filter[$column] = ($input[$name] === "yes") === ($name === "outcome");
            }
        }
        if (in_array($input["category"] ?? "", self::CATEGORIES, true)) {
            $filter["category"] = $input["category"];
        }
        if (isset(self::getVersionOptions($this->lng)[$input["version"] ?? ""])) {
            $filter["lti_version"] = $input["version"];
        }

        return $filter;
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
            ilLTITool::VERSION_1P1 => $lng->txt("lti_version_1p1_deprecated"),
            ilLTITool::VERSION_ADVANTAGE => $lng->txt("lti_version_advantage"),
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

    private function tableSuffix(): string
    {
        return match ($this->scope) {
            ilLTITool::SCOPE_USER => "user",
            ilLTITool::SCOPE_SELECTABLE => "select",
            default => "global",
        };
    }

    /**
     * The escaped title, or a link that creates an object for the tool when the table is a selection.
     */
    private function buildTitle(int $id, string $title): mixed
    {
        if ($this->select_gui === null) {
            return htmlspecialchars($title);
        }

        $this->ctrl->setParameter($this->select_gui, "tool_id", $id);

        return $this->ui_factory->link()->standard(
            $title,
            $this->ctrl->getLinkTarget($this->select_gui, $this->select_cmd)
        );
    }

    private function getCreator(int $creator, bool $exists, string $name): string
    {
        if ($creator === 0) {
            return "";
        }

        return $exists ? $name : $this->lng->txt("deleted_user");
    }
}
