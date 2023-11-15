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
use ILIAS\Skill\Profile;
use ILIAS\Skill\Service;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ProfileTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected ArrayBasedRequestWrapper $query;
    protected Data\Factory $df;
    protected Access\SkillTreeAccess $tree_access_manager;
    protected Profile\SkillProfileManager $profile_manager;
    protected Service\SkillAdminGUIRequest $admin_gui_request;
    protected string $requested_table_profile_action = "";

    /**
     * @var string[]
     */
    protected array $requested_table_profile_ids = [];
    protected int $skill_tree_id = 0;

    public function __construct(int $ref_id, int $skill_tree_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->query = $DIC->http()->wrapper()->query();
        $this->df = new Data\Factory();
        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager($ref_id);
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $this->requested_table_profile_action = $this->admin_gui_request->getTableProfileAction();
        $this->requested_table_profile_ids = $this->admin_gui_request->getTableProfileIds();
        $this->skill_tree_id = $skill_tree_id;
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        if ($this->requested_table_profile_action === "deleteProfiles") {
            $items = [];
            foreach ($this->requested_table_profile_ids as $id) {
                if ($id === "ALL_OBJECTS") {
                    $profiles = $this->skill_tree_id
                        ? $this->profile_manager->getProfilesForSkillTree($this->skill_tree_id)
                        : $this->profile_manager->getProfilesForAllSkillTrees();
                    foreach ($profiles as $profile) {
                        $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                            (string) $profile->getId(),
                            $profile->getTitle()
                        );
                    }
                } else {
                    $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                        $id,
                        $this->profile_manager->lookupTitle((int) $id)
                    );
                }
            }
            echo($this->ui_ren->renderAsync([
                $this->ui_fac->modal()->interruptive(
                    "",
                    empty($items) ? $this->lng->txt("no_checkbox") : $this->lng->txt("skmg_delete_profiles"),
                    $this->ctrl->getFormActionByClass("ilskillprofilegui", "deleteProfiles")
                )
                    ->withAffectedItems($items)
                    ->withActionButtonLabel(empty($items) ? $this->lng->txt("ok") : $this->lng->txt("delete"))
            ]));
            exit();
        }

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("skmg_skill_profiles"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("title")),
            "context" => $this->ui_fac->table()->column()->text($this->lng->txt("context"))
                                      ->withIsSortable(false),
            "users" => $this->ui_fac->table()->column()->text($this->lng->txt("users"))
                                    ->withIsSortable(false),
            "roles" => $this->ui_fac->table()->column()->text($this->lng->txt("roles"))
                                    ->withIsSortable(false),
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["skl_profile_table"];

        $uri_edit = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilskillprofilegui", "showLevelsWithTableContext")
        );
        $url_builder_edit = new UI\URLBuilder($uri_edit);
        list($url_builder_edit, $action_parameter_token_edit, $row_id_token_edit) =
            $url_builder_edit->acquireParameters(
                $query_params_namespace,
                "action",
                "profile_ids"
            );

        $url_builder_delete = new UI\URLBuilder($this->df->uri($this->request->getUri()->__toString()));
        list($url_builder_delete, $action_parameter_token_delete, $row_id_token_delete) =
            $url_builder_delete->acquireParameters(
                $query_params_namespace,
                "action",
                "profile_ids"
            );

        $uri_export = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilskillprofilegui", "exportProfiles")
        );
        $url_builder_export = new UI\URLBuilder($uri_export);
        list($url_builder_export, $action_parameter_token_export, $row_id_token_export) =
            $url_builder_export->acquireParameters(
                $query_params_namespace,
                "action",
                "profile_ids"
            );

        $actions = [
            "edit" => $this->ui_fac->table()->action()->single(
                $this->tree_access_manager->hasManageProfilesPermission() ? $this->lng->txt("edit") : $this->lng->txt("show"),
                $url_builder_edit->withParameter($action_parameter_token_edit, "editProfile"),
                $row_id_token_edit
            )
        ];
        if ($this->tree_access_manager->hasManageProfilesPermission()) {
            $actions["delete"] = $this->ui_fac->table()->action()->multi(
                $this->lng->txt("delete"),
                $url_builder_delete->withParameter($action_parameter_token_delete, "deleteProfiles"),
                $row_id_token_delete
            )
                                              ->withAsync();
            $actions["export"] = $this->ui_fac->table()->action()->multi(
                $this->lng->txt("export"),
                $url_builder_export->withParameter($action_parameter_token_export, "exportProfiles"),
                $row_id_token_export
            );
        }

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->lng,
            $this->skill_tree_id,
            $this->profile_manager
        ) implements UI\Component\Table\DataRetrieval {
            use TableRecords;

            public function __construct(
                protected \ilLanguage $lng,
                protected int $skill_tree_id,
                protected Profile\SkillProfileManager $skill_profile_manager
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
                $records = $this->getRecords($range, $order);
                foreach ($records as $idx => $record) {
                    $row_id = (string) $record["profile_id"];

                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->getRecords());
            }

            protected function getRecords(Data\Range $range = null, Data\Order $order = null): array
            {
                if ($this->skill_tree_id) {
                    $profiles = $this->skill_profile_manager->getProfilesForSkillTree($this->skill_tree_id);
                } else {
                    $profiles = $this->skill_profile_manager->getProfilesForAllSkillTrees();
                }

                $records = [];
                $i = 0;
                foreach ($profiles as $profile) {
                    $records[$i]["profile_id"] = $profile->getId();
                    $records[$i]["title"] = $profile->getTitle();
                    $profile_ref_id = $this->skill_profile_manager->lookupRefId($profile->getId());
                    $profile_obj_id = \ilContainerReference::_lookupObjectId($profile_ref_id);
                    $profile_obj_title = \ilObject::_lookupTitle($profile_obj_id);
                    if ($profile_ref_id > 0) {
                        $records[$i]["context"] = $this->lng->txt("skmg_context_local") . " (" . $profile_obj_title . ")";
                    } else {
                        $records[$i]["context"] = $this->lng->txt("skmg_context_global");
                    }
                    $records[$i]["users"] = $this->skill_profile_manager->countUsers($profile->getId());
                    $records[$i]["roles"] = $this->skill_profile_manager->countRoles($profile->getId());
                    $i++;
                }

                if ($order) {
                    $records = $this->orderRecords($records, $order);
                }

                if ($range) {
                    $records = $this->limitRecords($records, $range);
                }

                return $records;
            }
        };

        return $data_retrieval;
    }
}
