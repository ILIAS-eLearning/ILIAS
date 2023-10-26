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
use ILIAS\Skill\Access;
use ILIAS\Skill\Service;
use ILIAS\Skill\Tree;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class TreeTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected Service\SkillInternalManagerService $skill_manager;
    protected Access\SkillManagementAccess $skill_management_access_manager;
    protected Tree\SkillTreeManager $skill_tree_manager;
    protected Tree\SkillTreeFactory $skill_tree_factory;

    public function __construct(int $ref_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->skill_management_access_manager = $this->skill_manager->getManagementAccessManager($ref_id);
        $this->skill_tree_manager = $this->skill_manager->getTreeManager();
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("skmg_skill_trees"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("title"))
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["skl_tree_table"];

        $uri_edit = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilobjskilltreegui", "editSkills")
        );
        $url_builder_edit = new UI\URLBuilder($uri_edit);
        list($url_builder_edit, $action_parameter_token_edit, $row_id_token_edit) =
            $url_builder_edit->acquireParameters(
                $query_params_namespace,
                "action",
                "tree_ids"
            );

        $uri_delete = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilobjskilltreegui", "delete")
        );
        $url_builder_delete = new UI\URLBuilder($uri_delete);
        list($url_builder_delete, $action_parameter_token_delete, $row_id_token_delete) =
            $url_builder_delete->acquireParameters(
                $query_params_namespace,
                "action",
                "tree_ids"
            );

        $actions = [
            "edit" => $this->ui_fac->table()->action()->single(
                $this->lng->txt("edit"),
                $url_builder_edit->withParameter($action_parameter_token_edit, "editTree"),
                $row_id_token_edit
            )
        ];
        if ($this->skill_management_access_manager->hasCreateTreePermission()) {
            $actions["delete"] = $this->ui_fac->table()->action()->multi(
                $this->lng->txt("delete"),
                $url_builder_delete->withParameter($action_parameter_token_delete, "deleteTrees"),
                $row_id_token_delete
            );
        }

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->skill_manager,
            $this->skill_tree_manager,
            $this->skill_tree_factory
        ) implements UI\Component\Table\DataRetrieval {
            public function __construct(
                protected Service\SkillInternalManagerService $skill_manager,
                protected Tree\SkillTreeManager $skill_tree_manager,
                protected Tree\SkillTreeFactory $skill_tree_factory
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
                    $row_id = (string) $record["tree_id"];

                    yield $row_builder->buildDataRow($row_id, $record);
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
                $items = array_filter(array_map(
                    function (\ilObjSkillTree $skillTree): array {
                        $tree_access_manager = $this->skill_manager->getTreeAccessManager($skillTree->getRefId());
                        if ($tree_access_manager->hasVisibleTreePermission()) {
                            return [
                                "tree" => $skillTree
                            ];
                        }
                        return [];
                    },
                    iterator_to_array($this->skill_tree_manager->getTrees())
                ));

                $records = [];
                $i = 0;
                foreach ($items as $item) {
                    /** @var \ilObjSkillTree $tree_obj */
                    $tree_obj = $item["tree"];
                    $tree = $this->skill_tree_factory->getTreeById($tree_obj->getId());
                    $records[$i]["tree_id"] = $tree->readRootId();
                    $records[$i]["title"] = $tree_obj->getTitle();
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
