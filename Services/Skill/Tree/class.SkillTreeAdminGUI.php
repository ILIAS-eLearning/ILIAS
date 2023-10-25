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

// only after ilCtrl is able to handle namespaces
//namespace ILIAS\Skill\Tree;

use ILIAS\DI\UIServices;
use ILIAS\Skill\Tree;
use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\Skill\Service\SkillInternalManagerService;
use ILIAS\Skill\Access\SkillManagementAccess;
use ILIAS\UI\Component\Input\Container\Form;
use Psr\Http\Message\RequestInterface;

/**
 * Skill tree administration
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls SkillTreeAdminGUI: ilObjSkillTreeGUI
 */
class SkillTreeAdminGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;
    protected \ILIAS\Data\Factory $df;
    protected RequestInterface $request;
    protected int $requested_ref_id = 0;
    protected ilTabsGUI $tabs;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected SkillInternalManagerService $skill_manager;
    protected Tree\SkillTreeManager $skill_tree_manager;
    protected Tree\SkillTreeFactory $skill_tree_factory;
    protected SkillManagementAccess $skill_management_access_manager;

    public function __construct(SkillInternalManagerService $skill_manager)
    {
        global $DIC;

        $this->toolbar = $DIC->toolbar();
        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->df = new \ILIAS\Data\Factory();
        $this->request = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();

        $this->requested_ref_id = $this->admin_gui_request->getRefId();

        $this->skill_manager = $skill_manager;
        $this->skill_tree_manager = $this->skill_manager->getTreeManager();
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->skill_management_access_manager = $this->skill_manager->getManagementAccessManager($this->requested_ref_id);
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listTrees");

        switch ($next_class) {
            case "ilobjskilltreegui":
                $this->tabs->clearTargets();
                $gui = new ilObjSkillTreeGUI([], $this->requested_ref_id, true, false);
                $gui->init($this->skill_manager);
                $ctrl->forwardCommand($gui);
                break;

            default:
                if (in_array($cmd, ["listTrees", "createSkillTree", "updateTree", "createTree"])) {
                    $this->$cmd();
                }
        }
    }

    protected function listTrees(): void
    {
        $mtpl = $this->main_tpl;
        $toolbar = $this->toolbar;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $add_tree_button = $this->ui_fac->button()->standard(
            $lng->txt("skmg_add_skill_tree"),
            $ctrl->getLinkTargetByClass("ilobjskilltreegui", "create")
        );

        if ($this->skill_management_access_manager->hasCreateTreePermission()) {
            $toolbar->addComponent($add_tree_button);
        }

        $table = $this->getTreeTable();

        $mtpl->setContent($this->ui_ren->render($table));
    }

    protected function getTreeTable(): \ILIAS\UI\Component\Table\Data
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $columns = [
            "title" => $this->ui_fac->table()->column()->text($lng->txt("title"))
        ];

        $query_params_namespace = ["skl_tree_table"];

        $uri_edit = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $ctrl->getLinkTargetByClass("ilobjskilltreegui", "editSkills")
        );
        $url_builder_edit = new \ILIAS\UI\URLBuilder($uri_edit);
        list($url_builder_edit, $action_parameter_token_edit, $row_id_token_edit) =
            $url_builder_edit->acquireParameters(
                $query_params_namespace,
                "action",
                "tree_ids"
            );

        $uri_delete = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $ctrl->getLinkTargetByClass("ilobjskilltreegui", "delete")
        );
        $url_builder_delete = new \ILIAS\UI\URLBuilder($uri_delete);
        list($url_builder_delete, $action_parameter_token_delete, $row_id_token_delete) =
            $url_builder_delete->acquireParameters(
                $query_params_namespace,
                "action",
                "tree_ids"
            );

        $actions = [
            "edit" => $this->ui_fac->table()->action()->single(
                $lng->txt("edit"),
                $url_builder_edit->withParameter($action_parameter_token_edit, "editTree"),
                $row_id_token_edit
            )
        ];
        if ($this->skill_management_access_manager->hasCreateTreePermission()) {
            $actions["delete"] = $this->ui_fac->table()->action()->multi(
                $lng->txt("delete"),
                $url_builder_delete->withParameter($action_parameter_token_delete, "deleteTrees"),
                $row_id_token_delete
            );
        }

        $data_retrieval = new class (
            $this->skill_manager,
            $this->skill_tree_manager,
            $this->skill_tree_factory,
            $this->skill_management_access_manager
        ) implements \ILIAS\UI\Component\Table\DataRetrieval {
            public function __construct(
                protected SkillInternalManagerService $skill_manager,
                protected Tree\SkillTreeManager $skill_tree_manager,
                protected Tree\SkillTreeFactory $skill_tree_factory,
                protected SkillManagementAccess $skill_management_access_manager
            ) {
            }

            public function getRows(
                \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                \ILIAS\Data\Range $range,
                \ILIAS\Data\Order $order,
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

            protected function getRecords(\ILIAS\Data\Order $order): array
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
                    /** @var ilObjSkillTree $tree_obj */
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

        $table = $this->ui_fac->table()
                              ->data($lng->txt("skmg_skill_trees"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }
}
