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
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Skill\Access;
use ILIAS\Skill\Profile;
use ILIAS\Skill\Service;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ProfileUserAssignmentTable
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected ArrayBasedRequestWrapper $query;
    protected Data\Factory $df;
    protected Service\SkillAdminGUIRequest $admin_gui_request;
    protected Profile\SkillProfile $profile;
    protected Access\SkillTreeAccess $tree_access_manager;
    protected Profile\SkillProfileManager $profile_manager;
    protected string $requested_table_profile_user_ass_action = "";
    /**
     * @var string[]
     */
    protected array $requested_table_profile_user_ass_ids = [];

    public function __construct(Profile\SkillProfile $profile, Access\SkillTreeAccess $tree_access_manager)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->query = $DIC->http()->wrapper()->query();
        $this->df = new Data\Factory();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();

        $this->profile = $profile;
        $this->tree_access_manager = $tree_access_manager;
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
        $this->requested_table_profile_user_ass_action = $this->admin_gui_request->getTableProfileUserAssignmentAction();
        $this->requested_table_profile_user_ass_ids = $this->admin_gui_request->getTableProfileUserAssignmentIds();
    }

    public function getComponent(): UI\Component\Table\Data
    {
        $columns = $this->getColumns();
        $actions = $this->getActions();
        $data_retrieval = $this->getDataRetrieval();

        if ($this->requested_table_profile_user_ass_action === "removeUsers") {
            $items = [];
            foreach ($this->requested_table_profile_user_ass_ids as $id) {
                if ($id === "ALL_OBJECTS") {
                    $all_assignments = $this->profile_manager->getAssignments($this->profile->getId());
                    foreach ($all_assignments as $assignment) {
                        $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                            (string) $assignment->getId(),
                            $this->getAssignmentTitle($assignment->getId())
                        );
                    }
                } else {
                    $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                        $id,
                        $this->getAssignmentTitle((int) $id)
                    );
                }
            }
            echo($this->ui_ren->renderAsync([
                $this->ui_fac->modal()->interruptive(
                    "",
                    empty($items) ? $this->lng->txt("no_checkbox") : $this->lng->txt("skmg_confirm_user_removal"),
                    $this->ctrl->getFormActionByClass("ilskillprofilegui", "removeUsers")
                )
                             ->withAffectedItems($items)
                             ->withActionButtonLabel(empty($items) ? $this->lng->txt("ok") : $this->lng->txt("delete"))
            ]));
            exit();
        }

        $table = $this->ui_fac->table()
                              ->data($this->lng->txt("skmg_assigned_users"), $columns, $data_retrieval)
                              ->withActions($actions)
                              ->withRequest($this->request);

        return $table;
    }

    protected function getColumns(): array
    {
        $columns = [
            "type" => $this->ui_fac->table()->column()->text($this->lng->txt("type"))
                                   ->withIsSortable(false),
            "name" => $this->ui_fac->table()->column()->text($this->lng->txt("name")),
            "object" => $this->ui_fac->table()->column()->text($this->lng->txt("object"))
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        $query_params_namespace = ["skl_profile_user_assignment_table"];

        $url_builder_delete = new UI\URLBuilder($this->df->uri($this->request->getUri()->__toString()));
        list($url_builder_delete, $action_parameter_token_delete, $row_id_token_delete) =
            $url_builder_delete->acquireParameters(
                $query_params_namespace,
                "action",
                "ass_ids"
            );

        $actions = [];
        if ($this->tree_access_manager->hasManageProfilesPermission() && !$this->profile->getRefId() > 0) {
            $actions["delete"] = $this->ui_fac->table()->action()->multi(
                $this->lng->txt("delete"),
                $url_builder_delete->withParameter($action_parameter_token_delete, "removeUsers"),
                $row_id_token_delete
            )
                                              ->withAsync();
        }

        return $actions;
    }

    protected function getDataRetrieval(): UI\Component\Table\DataRetrieval
    {
        $data_retrieval = new class (
            $this->profile,
            $this->profile_manager
        ) implements UI\Component\Table\DataRetrieval {
            public function __construct(
                protected Profile\SkillProfile $profile,
                protected Profile\SkillProfileManager $profile_manager
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

                    yield $row_builder->buildDataRow((string) $row_id, $record);
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
                $assignments = $this->profile_manager->getAssignments($this->profile->getId());

                $records = [];
                $i = 0;
                foreach ($assignments as $assignment) {
                    $records[$i]["id"] = $assignment->getId();
                    $records[$i]["type"] = $assignment->getType();
                    $records[$i]["name"] = $assignment->getName();
                    $records[$i]["object"] = ($assignment instanceof Profile\SkillProfileRoleAssignment)
                        ? $assignment->getObjTitle()
                        : "";

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

    protected function getAssignmentTitle(int $obj_id): string
    {
        $type = \ilObject::_lookupType($obj_id);
        switch ($type) {
            case "usr":
                $ass_title = \ilUserUtil::getNamePresentation($obj_id);
                break;

            case "role":
                $ass_title = \ilObjRole::_lookupTitle($obj_id);
                break;

            default:
                $ass_title = $this->lng->txt("not_available");
        }

        return $ass_title;
    }
}
