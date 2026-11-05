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

namespace ILIAS\Skill\Table;

use Generator;
use ILIAS\Data;
use ILIAS\Skill\Access;
use ILIAS\Skill\Profile;
use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\Skill\Service\SkillInternalManagerService;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;

class ProfileLevelsTable implements UI\Component\Table\OrderingRetrieval
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilSkillTreeRepository $tree_repo;
    protected SkillInternalManagerService $skill_manager;
    protected Profile\SkillProfileManager $profile_manager;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected string $requested_table_action = "";
    /**
     * @var string[]
     */
    protected array $requested_table_skill_ids = [];
    protected ?array $records = null;

    /**
     * @var array<string, array{base_skill_id: int, tref_id: int}>
     */
    protected array $skill_ids_by_row_id = [];

    public function __construct(
        protected Profile\SkillProfile $profile,
        protected Access\SkillTreeAccess $tree_access_manager
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->profile_manager = $this->skill_manager->getProfileManager();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $this->requested_table_action = $this->admin_gui_request->getTableProfileLevelsAction();
        $this->requested_table_skill_ids = $this->admin_gui_request->getTableProfileLevelsSkillIds();
    }

    public function getComponent(): UI\Component\Table\Ordering
    {
        if ($this->requested_table_action === "removeLevelAssignments") {
            $this->renderRemoveLevelsModal();
            exit();
        }

        $target = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilskillprofilegui", "saveLevelOrder")
        );

        return $this->ui_fac->table()
            ->ordering($this, $target, $this->lng->txt("skmg_target_levels"), $this->getColumns())
            ->withId(str_replace('\\', '', self::class) . "_" . $this->profile->getId())
            ->withActions($this->getActions())
            ->withRequest($this->request)
            ->withOrderingDisabled(
                !$this->tree_access_manager->hasManageProfilesPermission() || empty($this->getRecords())
            );
    }

    public function getRows(
        UI\Component\Table\OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): Generator {
        foreach ($this->getRecords() as $row_id => $record) {
            yield $row_builder->buildOrderingRow($row_id, $record);
        }
    }

    /**
     * @param string[] $ordered_row_ids
     */
    public function setOrder(array $ordered_row_ids): void
    {
        if (!$this->tree_access_manager->hasManageProfilesPermission()) {
            return;
        }

        $records = $this->getRecords();
        // Reject incomplete or modified submissions; the manager expects a permutation of this profile's rows.
        if (count($ordered_row_ids) !== count($records)
            || count(array_unique($ordered_row_ids)) !== count($records)
            || array_diff($ordered_row_ids, array_keys($records)) !== []) {
            return;
        }

        $order = [];
        foreach ($ordered_row_ids as $position => $row_id) {
            $skill_ids = $this->skill_ids_by_row_id[$row_id];
            // The profile manager expects base_skill_id_tref_id keys, not the Ordering Table's row IDs.
            $order[$skill_ids["base_skill_id"] . "_" . $skill_ids["tref_id"]] = $position + 1;
        }

        $this->profile_manager->updateSkillOrder($this->profile->getId(), $order);
    }

    protected function getColumns(): array
    {
        return [
            "skill" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_skill")),
            "level" => $this->ui_fac->table()->column()->text($this->lng->txt("skmg_level")),
        ];
    }

    protected function getActions(): array
    {
        if (!$this->tree_access_manager->hasManageProfilesPermission() || empty($this->getRecords())) {
            return [];
        }

        $query_params_namespace = ["skl_profile_levels_table"];

        $uri_edit = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass("ilskillprofilegui", "updateLevelOfSelectedSkill")
        );
        $url_builder_edit = new UI\URLBuilder($uri_edit);
        [$url_builder_edit, $row_id_token_edit] = $url_builder_edit->acquireParameter(
            $query_params_namespace,
            "skill_ids"
        );

        $uri_remove = $this->df->uri($this->request->getUri()->__toString());
        $url_builder_remove = new UI\URLBuilder($uri_remove);
        [$url_builder_remove, $action_parameter_token_remove, $row_id_token_remove] = $url_builder_remove->acquireParameters(
            $query_params_namespace,
            "action",
            "skill_ids"
        );

        return [
            "edit" => $this->ui_fac->table()->action()->single(
                $this->lng->txt("edit"),
                $url_builder_edit,
                $row_id_token_edit
            ),
            "remove" => $this->ui_fac->table()->action()->multi(
                $this->lng->txt("skmg_remove_levels"),
                $url_builder_remove->withParameter($action_parameter_token_remove, "removeLevelAssignments"),
                $row_id_token_remove
            )->withAsync(),
        ];
    }

    protected function renderRemoveLevelsModal(): void
    {
        $items = [];
        $records = $this->getRecords();
        foreach ($this->requested_table_skill_ids as $row_id) {
            if ($row_id === "ALL_OBJECTS") {
                foreach ($records as $r_id => $record) {
                    $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                        $r_id,
                        $record["skill"] . ": " . $record["level"]
                    );
                }
            } else {
                $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                    $row_id,
                    $records[$row_id]["skill"] . ": " . $records[$row_id]["level"]
                );
            }
        }

        echo($this->ui_ren->renderAsync([
            $this->ui_fac->modal()->interruptive(
                "",
                empty($items) ? $this->lng->txt("no_checkbox") : $this->lng->txt("skmg_confirm_remove_level_ass"),
                $this->ctrl->getFormActionByClass("ilskillprofilegui", "removeLevelAssignments")
            )
                ->withAffectedItems($items)
                ->withActionButtonLabel(empty($items) ? $this->lng->txt("ok") : $this->lng->txt("remove"))
        ]));
    }

    protected function getRecords(): array
    {
        if ($this->records !== null) {
            return $this->records;
        }

        $this->records = [];
        foreach ($this->profile_manager->getSkillLevels($this->profile->getId()) as $level) {
            $base_skill_id = $level->getBaseSkillId();
            $tref_id = $level->getTrefId();
            $row_id = $base_skill_id . ":" . $tref_id;
            $tree_id = $this->tree_repo->getTreeIdForNodeId($base_skill_id);
            $node_manager = $this->skill_manager->getTreeNodeManager($tree_id);

            $this->records[$row_id] = [
                "skill" => $node_manager->getWrittenPath($base_skill_id, $tref_id),
                "level" => \ilBasicSkill::lookupLevelTitle($level->getLevelId()),
            ];
            $this->skill_ids_by_row_id[$row_id] = [
                "base_skill_id" => $base_skill_id,
                "tref_id" => $tref_id,
            ];
        }

        return $this->records;
    }
}
