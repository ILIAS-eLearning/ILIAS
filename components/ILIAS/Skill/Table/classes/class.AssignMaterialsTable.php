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
use ILIAS\Skill\Node;
use ILIAS\Skill\Personal;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class AssignMaterialsTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected \ilObjUser $user;
    protected \ilWorkspaceTree $ws_tree;
    protected \ilWorkspaceAccessHandler $ws_access;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilSkillTreeRepository $tree_repo;
    protected Node\SkillTreeNodeManager $node_manager;
    protected Personal\AssignedMaterialManager $assigned_material_manager;
    protected int $top_skill_id = 0;
    protected int $tref_id = 0;
    protected int $basic_skill_id = 0;

    public function __construct(int $top_skill_id, int $tref_id, int $basic_skill_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->ws_tree = new \ilWorkspaceTree($this->user->getId());
        if (!$this->ws_tree->readRootId()) {
            $this->ws_tree->createTreeForUser($this->user->getId());
        }
        $this->ws_access = new \ilWorkspaceAccessHandler();

        $this->top_skill_id = $top_skill_id;
        $this->tref_id = $tref_id;
        $this->basic_skill_id = $basic_skill_id;

        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $tree_id = $this->tree_repo->getTreeIdForNodeId($this->basic_skill_id);
        $this->node_manager = $DIC->skills()->internal()->manager()->getTreeNodeManager($tree_id);
        $this->assigned_material_manager = $DIC->skills()->internal()->manager()->getAssignedMaterialManager();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        $title = $this->node_manager->getWrittenPath($this->basic_skill_id);
        $table = $this->ui_fac->table()
                              ->data($title, $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_skill_level"))
                                    ->withIsSortable(false),
            "description" => $this->ui_fac->table()->column()->text($this->lng->txt("description"))
                                          ->withIsSortable(false),
            "resources" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_materials"))
                                        ->withIsSortable(false)
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["skl_assign_materials_table"];

        $uri_assign = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass(
                "ilpersonalskillsgui",
                "assignMaterial"
            )
        );
        $url_builder_assign = new UI\URLBuilder($uri_assign);
        list($url_builder_assign, $action_parameter_token_assign, $row_id_token_assign) =
            $url_builder_assign->acquireParameters(
                $query_params_namespace,
                "action",
                "level_ids"
            );

        $actions = [
            "assign" => $this->ui_fac->table()->action()->single(
                $this->lng->txt("skmg_assign_materials"),
                $url_builder_assign->withParameter($action_parameter_token_assign, "assignMaterials"),
                $row_id_token_assign
            )
        ];

        foreach ($this->assigned_material_manager->getAllAssignedMaterialsForSkill(
            $this->user->getId(),
            $this->basic_skill_id,
            $this->tref_id,
        ) as $material) {
            $obj_id = $this->ws_tree->lookupObjectId($material->getWorkspaceId());

            $uri_open = $this->df->uri($this->ws_access->getGotoLink($material->getWorkspaceId(), $obj_id));
            $url_builder_open = new UI\URLBuilder($uri_open);
            list($url_builder_open, $action_parameter_token_open, $row_id_token_open) =
                $url_builder_open->acquireParameters(
                    $query_params_namespace,
                    "action",
                    "level_ids"
                );

            $uri_remove = $this->df->uri(
                ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass(
                    "ilpersonalskillsgui",
                    "removeMaterial"
                )
            );
            $url_builder_remove = new UI\URLBuilder($uri_remove);
            list($url_builder_remove, $action_parameter_token_remove, $row_id_token_remove, $wsp_token_remove) =
                $url_builder_remove->acquireParameters(
                    $query_params_namespace,
                    "action",
                    "level_ids",
                    "wsp_id"
                );
            $url_builder_remove = $url_builder_remove->withParameter($wsp_token_remove, (string) $material->getWorkspaceId());

            $actions["open_" . $material->getLevelId() . "_" . $material->getWorkspaceId()] =
                $this->ui_fac->table()->action()->single(
                    $this->lng->txt("skmg_open") . " '" . \ilObject::_lookupTitle($obj_id) . "'",
                    $url_builder_open,
                    $row_id_token_open
                );
            $actions["remove_" . $material->getLevelId() . "_" . $material->getWorkspaceId()] =
                $this->ui_fac->table()->action()->single(
                    $this->lng->txt("skmg_remove") . " '" . \ilObject::_lookupTitle($obj_id) . "'",
                    $url_builder_remove->withParameter($action_parameter_token_remove, "removeMaterial"),
                    $row_id_token_remove
                );
        }

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->basic_skill_id,
            $this->tref_id,
            $this->user,
            $this->ws_tree,
            $this->assigned_material_manager
        ) implements UI\Component\Table\DataRetrieval {
            public function __construct(
                protected int $basic_skill_id,
                protected int $tref_id,
                protected \ilObjUser $user,
                protected \ilWorkspaceTree $ws_tree,
                protected Personal\AssignedMaterialManager $assigned_material_manager
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
                    $row_id = $record["id"];
                    $res_ids = $record["res_ids"];

                    $data_row = $row_builder->buildDataRow((string) $row_id, $record);
                    foreach ($this->assigned_material_manager->getAllAssignedMaterialsForSkill(
                        $this->user->getId(),
                        $this->basic_skill_id,
                        $this->tref_id,
                    ) as $material) {
                        if (!in_array($material->getWorkspaceId(), $res_ids) || $row_id != $material->getLevelId()) {
                            $data_row = $data_row->withDisabledAction(
                                "open_" . $material->getLevelId() . "_" . $material->getWorkspaceId()
                            );
                            $data_row = $data_row->withDisabledAction(
                                "remove_" . $material->getLevelId() . "_" . $material->getWorkspaceId()
                            );
                        }
                    }

                    yield $data_row;
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
                $skill = \ilSkillTreeNodeFactory::getInstance($this->basic_skill_id);
                $records = [];
                $i = 0;
                foreach ($skill->getLevelData() as $level) {
                    $records[$i]["id"] = $level["id"];
                    $records[$i]["title"] = $level["title"];
                    $records[$i]["description"] = $level["description"];

                    $materials = $this->assigned_material_manager->getAssignedMaterials(
                        $this->user->getId(),
                        $this->tref_id,
                        (int) $level["id"]
                    );
                    $wsp_ids = [];
                    $obj_titles = [];
                    foreach ($materials as $m) {
                        $wsp_ids[] = $m->getWorkspaceId();
                        $obj_id = $this->ws_tree->lookupObjectId($m->getWorkspaceId());
                        $obj_titles[] = \ilObject::_lookupTitle($obj_id);
                    }
                    $records[$i]["res_ids"] = $wsp_ids;
                    $records[$i]["resources"] = implode(", ", $obj_titles);

                    $i++;
                }

                return $records;
            }
        };

        return $data_retrieval;
    }
}
