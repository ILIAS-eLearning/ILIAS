<?php

declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\Skill\Table;

use ILIAS\Data;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Skill\Access;
use ILIAS\Skill\Resource;
use ILIAS\Skill\Service;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class LevelResourcesTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected ArrayBasedRequestWrapper $query;
    protected Data\Factory $df;
    protected \ilTree $tree;
    protected Access\SkillTreeAccess $tree_access_manager;
    protected Resource\SkillResourcesManager $resource_manager;
    protected Service\SkillAdminGUIRequest $admin_gui_request;
    protected string $requested_table_action = "";
    /**
     * @var string[]
     */
    protected array $requested_table_rep_ref_ids = [];
    protected int $base_skill_id = 0;
    protected int $tref_id = 0;
    protected int $requested_level_id = 0;

    public function __construct(int $ref_id, int $base_skill_id, int $tref_id, int $requested_level_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->query = $DIC->http()->wrapper()->query();
        $this->df = new Data\Factory();
        $this->tree = $DIC->repositoryTree();
        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($ref_id);
        $this->resource_manager = $DIC->skills()->internal()->manager()->getResourceManager();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $this->requested_table_action = $this->admin_gui_request->getTableLevelResourcesAction();
        $this->requested_table_rep_ref_ids = $this->admin_gui_request->getTableRepoRefIds();
        $this->base_skill_id = $base_skill_id;
        $this->tref_id = $tref_id;
        $this->requested_level_id = $requested_level_id;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        if ($this->requested_table_action === "removeResources") {
            $items = [];
            foreach ($this->requested_table_rep_ref_ids as $id) {
                if ($id === "ALL_OBJECTS") {
                    $resources = $this->resource_manager->getResourcesOfLevel(
                        $this->base_skill_id,
                        $this->tref_id,
                        $this->requested_level_id
                    );
                    foreach ($resources as $resource) {
                        $obj_id = \ilObject::_lookupObjId($resource->getRepoRefId());
                        $obj_type = \ilObject::_lookupType($obj_id);
                        $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                            (string) $resource->getRepoRefId(),
                            \ilObject::_lookupTitle($obj_id),
                            $this->ui_fac->image()->standard(
                                \ilObject::_getIcon($obj_id, "small", $obj_type),
                                $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $obj_type)
                            )
                        );
                    }
                } else {
                    $obj_id = \ilObject::_lookupObjId((int) $id);
                    $obj_type = \ilObject::_lookupType($obj_id);
                    $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                        $id,
                        \ilObject::_lookupTitle($obj_id),
                        $this->ui_fac->image()->standard(
                            \ilObject::_getIcon($obj_id, "small", $obj_type),
                            $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $obj_type)
                        )
                    );
                }
            }
            echo($this->ui_ren->renderAsync([
                $this->ui_fac->modal()->interruptive(
                    "",
                    empty($items) ? $this->lng->txt("no_checkbox") : $this->lng->txt("skmg_confirm_level_resources_removal"),
                    $this->ctrl->getFormActionByClass("ilbasicskillgui", "removeLevelResources")
                )
                    ->withAffectedItems($items)
                    ->withActionButtonLabel(empty($items) ? $this->lng->txt("ok") : $this->lng->txt("delete"))
            ]));
            exit();
        }

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("skmg_suggested_resources"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "type" => $this->ui_fac->table()->column()->statusIcon($this->lng->txt("type"))
                                   ->withIsSortable(false),
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("title")),
            "path" => $this->ui_fac->table()->column()->text($this->lng->txt("path"))
                                   ->withIsSortable(false),
            "suggested" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_suggested"))
                                        ->withIsSortable(false),
            "lp_trigger" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_lp_triggers_level"))
                                         ->withIsSortable(false),
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["skl_level_resources_table"];

        $uri_suggested = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilbasicskillgui", "saveResourcesAsSuggested")
        );
        $url_builder_suggested = new UI\URLBuilder($uri_suggested);
        list($url_builder_suggested, $action_parameter_token_suggested, $row_id_token_suggested) =
            $url_builder_suggested->acquireParameters(
                $query_params_namespace,
                "action",
                "rep_ref_ids"
            );

        $uri_not_suggested = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilbasicskillgui", "saveResourcesAsNotSuggested")
        );
        $url_builder_not_suggested = new UI\URLBuilder($uri_not_suggested);
        list($url_builder_not_suggested, $action_parameter_token_not_suggested, $row_id_token_not_suggested) =
            $url_builder_not_suggested->acquireParameters(
                $query_params_namespace,
                "action",
                "rep_ref_ids"
            );

        $uri_trigger = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilbasicskillgui", "saveResourcesAsTrigger")
        );
        $url_builder_trigger = new UI\URLBuilder($uri_trigger);
        list($url_builder_trigger, $action_parameter_token_trigger, $row_id_token_trigger) =
            $url_builder_trigger->acquireParameters(
                $query_params_namespace,
                "action",
                "rep_ref_ids"
            );

        $uri_no_trigger = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilbasicskillgui", "saveResourcesAsNoTrigger")
        );
        $url_builder_no_trigger = new UI\URLBuilder($uri_no_trigger);
        list($url_builder_no_trigger, $action_parameter_token_no_trigger, $row_id_token_no_trigger) =
            $url_builder_no_trigger->acquireParameters(
                $query_params_namespace,
                "action",
                "rep_ref_ids"
            );

        $url_builder_remove = new UI\URLBuilder($this->df->uri($this->request->getUri()->__toString()));
        list($url_builder_remove, $action_parameter_token_remove, $row_id_token_remove) =
            $url_builder_remove->acquireParameters(
                $query_params_namespace,
                "action",
                "rep_ref_ids"
            );

        $actions = [];
        if ($this->tree_access_manager->hasManageCompetencesPermission()) {
            $actions = [
                "setSuggested" => $this->ui_fac->table()->action()->standard(
                    $this->lng->txt("skmg_set_as_suggested"),
                    $url_builder_suggested->withParameter($action_parameter_token_suggested, "setSuggested"),
                    $row_id_token_suggested
                ),
                "unsetSuggested" => $this->ui_fac->table()->action()->standard(
                    $this->lng->txt("skmg_set_as_no_suggested"),
                    $url_builder_not_suggested->withParameter($action_parameter_token_not_suggested, "unsetSuggested"),
                    $row_id_token_not_suggested
                ),
                "setTrigger" => $this->ui_fac->table()->action()->standard(
                    $this->lng->txt("skmg_set_as_lp_trigger"),
                    $url_builder_trigger->withParameter($action_parameter_token_trigger, "setTrigger"),
                    $row_id_token_trigger
                ),
                "unsetTrigger" => $this->ui_fac->table()->action()->standard(
                    $this->lng->txt("skmg_set_as_no_lp_trigger"),
                    $url_builder_no_trigger->withParameter($action_parameter_token_no_trigger, "unsetTrigger"),
                    $row_id_token_no_trigger
                ),
                "remove" => $this->ui_fac->table()->action()->multi(
                    $this->lng->txt("remove"),
                    $url_builder_remove->withParameter($action_parameter_token_remove, "removeResources"),
                    $row_id_token_remove
                )
                                         ->withAsync()
            ];
        }

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->lng,
            $this->ui_fac,
            $this->ui_ren,
            $this->tree,
            $this->resource_manager,
            $this->base_skill_id,
            $this->tref_id,
            $this->requested_level_id
        ) implements UI\Component\Table\DataRetrieval {
            public function __construct(
                protected \ilLanguage $lng,
                protected UI\Factory $ui_fac,
                protected UI\Renderer $ui_ren,
                protected \ilTree $tree,
                protected Resource\SkillResourcesManager $resource_manager,
                protected int $base_skill_id,
                protected int $tref_id,
                protected int $level_id
            ) {
            }

            public function getRows(
                UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Data\Range $range,
                Data\Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = $this->getRecords($order);
                foreach ($records as $idx => $record) {
                    $row_id = (string) $record["rep_ref_id"];

                    yield $row_builder->buildDataRow($row_id, $record)
                                      ->withDisabledAction("setSuggested", ($record["suggested"] === $this->lng->txt("yes")))
                                      ->withDisabledAction("unsetSuggested", ($record["suggested"] === $this->lng->txt("no")))
                                      ->withDisabledAction("setTrigger", ($record["lp_trigger"] === $this->lng->txt("yes")))
                                      ->withDisabledAction("setTrigger", ($record["lp_trigger"] === $this->lng->txt("not_available")))
                                      ->withDisabledAction("unsetTrigger", ($record["lp_trigger"] === $this->lng->txt("no")))
                                      ->withDisabledAction("unsetTrigger", ($record["lp_trigger"] === $this->lng->txt("not_available")));
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return null;
            }

            protected function getRecords(Data\Order $order): array
            {
                $resources = $this->resource_manager->getResourcesOfLevel(
                    $this->base_skill_id,
                    $this->tref_id,
                    $this->level_id
                );

                $records = [];
                $i = 0;
                foreach ($resources as $resource) {
                    $ref_id = $resource->getRepoRefId();
                    $obj_id = \ilObject::_lookupObjId($ref_id);
                    $obj_type = \ilObject::_lookupType($obj_id);

                    $records[$i]["rep_ref_id"] = $ref_id;
                    $records[$i]["title"] = \ilObject::_lookupTitle($obj_id);
                    $records[$i]["suggested"] = $resource->getImparting()
                        ? $this->lng->txt("yes")
                        : $this->lng->txt("no");

                    if (!\ilObjectLP::isSupportedObjectType($obj_type)) {
                        $trigger = $this->lng->txt("not_available");
                    } elseif ($resource->getTrigger()) {
                        $trigger = $this->lng->txt("yes");
                    } else {
                        $trigger = $this->lng->txt("no");
                    }
                    $records[$i]["lp_trigger"] = $trigger;

                    $icon = $this->ui_ren->render(
                        $this->ui_fac->symbol()->icon()->standard(
                            $obj_type,
                            $this->lng->txt("icon") . " " . $this->lng->txt($obj_type),
                            "medium"
                        )
                    );
                    $records[$i]["type"] = $icon;

                    $path = $this->tree->getPathFull($ref_id);
                    $path_items = [];
                    foreach ($path as $p) {
                        if ($p["type"] != "root" && $p["child"] != $ref_id) {
                            $path_items[] = $p["title"];
                        }
                    }
                    $records[$i]["path"] = implode(" > ", $path_items);

                    $i++;
                }

                list($order_field, $order_direction) = $order->join([], fn($ret, $key, $value) => [$key, $value]);
                usort($records, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
                if ($order_direction === "DESC") {
                    $records = array_reverse($records);
                }

                return $records;
            }
        };

        return $data_retrieval;
    }
}
