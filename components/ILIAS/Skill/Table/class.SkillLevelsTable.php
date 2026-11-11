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
use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;

class SkillLevelsTable implements UI\Component\Table\OrderingRetrieval
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilBasicSkill $skill;
    protected \ILIAS\Skill\Resource\SkillResourcesManager $resource_manager;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected string $requested_table_action = "";

    /**
     * @var string[]
     */
    protected array $requested_table_level_ids = [];

    protected ?array $records = null;

    public function __construct(
        protected int $skill_id,
        protected object $parent_obj,
        protected int $tref_id = 0,
        protected bool $in_use = false,
        protected bool $manage_perm = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->skill = new \ilBasicSkill($skill_id);
        $this->resource_manager = $DIC->skills()->internal()->manager()->getResourceManager();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $this->requested_table_action = $this->admin_gui_request->getTableSkillLevelAction();
        $this->requested_table_level_ids = $this->admin_gui_request->getTableSkillLevelIds();
    }

    public function getComponent(): UI\Component\Table\Ordering
    {
        if ($this->requested_table_action === "deleteLevels") {
            $this->renderDeleteLevelsModal();
            exit();
        }

        $target = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this->parent_obj, "updateLevelOrder")
        );

        return $this->ui_fac->table()
            ->ordering($this, $target, $this->lng->txt("skmg_skill_levels"), $this->getColumns())
            ->withId(str_replace("\\", "", self::class) . "_" . $this->skill_id . "_" . $this->tref_id)
            ->withActions($this->getActions())
            ->withRequest($this->request)
            ->withOrderingDisabled(!$this->isOrderingAllowed());
    }

    public function getRows(
        UI\Component\Table\OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): Generator {
        foreach ($this->getRecords() as $row_id => $record) {
            yield $row_builder->buildOrderingRow((string) $row_id, $record);
        }
    }

    /**
     * @param string[] $ordered_row_ids
     */
    public function setOrder(array $ordered_row_ids): void
    {
        if (!$this->isOrderingAllowed()) {
            return;
        }

        $record_ids = array_map("strval", array_keys($this->getRecords()));
        $ordered_row_ids = array_map("strval", $ordered_row_ids);
        if (count($ordered_row_ids) !== count($record_ids)
            || count(array_unique($ordered_row_ids)) !== count($record_ids)
            || array_diff($ordered_row_ids, $record_ids) !== []) {
            return;
        }

        $order = [];
        foreach ($ordered_row_ids as $position => $row_id) {
            $order[(int) $row_id] = $position + 1;
        }

        $this->skill->updateLevelOrder($order);
    }

    protected function isOrderingAllowed(): bool
    {
        return $this->manage_perm
            && !$this->in_use
            && $this->tref_id === 0
            && !empty($this->getRecords());
    }

    protected function getColumns(): array
    {
        $columns = [
            "title" => $this->ui_fac->table()->column()->text($this->lng->txt("title")),
            "description" => $this->ui_fac->table()->column()->text($this->lng->txt("description")),
            "suggested" => $this->ui_fac->table()->column()->listing($this->lng->txt("skmg_suggested")),
            "trigger" => $this->ui_fac->table()->column()->listing($this->lng->txt("skmg_lp_triggers_level")),
        ];

        return $columns;
    }

    protected function getActions(): array
    {
        if (!$this->manage_perm || empty($this->getRecords())) {
            return [];
        }

        $uri_edit = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget(
                $this->parent_obj,
                $this->tref_id === 0 ? "editLevel" : "showLevelResources"
            )
        );
        $url_builder_edit = new UI\URLBuilder($uri_edit);
        [$url_builder_edit, $action_parameter_token_edit, $row_id_token_edit] =
            $url_builder_edit->acquireParameters(
                ["skl_skill_level_table"],
                "action",
                "level_ids"
            );

        $actions = [
            "edit" => $this->ui_fac->table()->action()->single(
                $this->lng->txt("edit"),
                $url_builder_edit->withParameter($action_parameter_token_edit, "editLevel"),
                $row_id_token_edit
            ),
        ];

        if ($this->isDeletionAllowed()) {
            $uri_delete = $this->df->uri($this->request->getUri()->__toString());
            $url_builder_delete = new UI\URLBuilder($uri_delete);
            [$url_builder_delete, $action_parameter_token_delete, $row_id_token_delete] =
                $url_builder_delete->acquireParameters(
                    ["skl_skill_level_table"],
                    "action",
                    "level_ids"
                );

            $actions["delete"] = $this->ui_fac->table()->action()->multi(
                $this->lng->txt("delete"),
                $url_builder_delete->withParameter($action_parameter_token_delete, "deleteLevels"),
                $row_id_token_delete
            )->withAsync();
        }

        return $actions;
    }

    protected function isDeletionAllowed(): bool
    {
        return $this->manage_perm
            && !$this->in_use
            && $this->tref_id === 0
            && !empty($this->getRecords());
    }

    protected function renderDeleteLevelsModal(): void
    {
        $level_ids = $this->isDeletionAllowed()
            ? $this->resolveLevelIds($this->requested_table_level_ids)
            : [];
        $records = $this->getRecords();
        $items = [];

        foreach ($level_ids as $level_id) {
            $row_id = (string) $level_id;
            $items[] = $this->ui_fac->modal()->interruptiveItem()->standard(
                $row_id,
                $records[$row_id]["title"]
            );
        }

        echo($this->ui_ren->renderAsync([
            $this->ui_fac->modal()->interruptive(
                "",
                empty($items) ? $this->lng->txt("no_checkbox") : $this->lng->txt("skmg_really_delete_levels"),
                $this->ctrl->getFormAction($this->parent_obj, "deleteSkillLevels")
            )
                ->withAffectedItems($items)
                ->withActionButtonLabel(empty($items) ? $this->lng->txt("ok") : $this->lng->txt("delete"))
        ]));
    }

    protected function getRecords(): array
    {
        if ($this->records !== null) {
            return $this->records;
        }

        $resources_by_level = $this->resource_manager->getResources($this->skill_id, $this->tref_id);
        $this->records = [];

        foreach ($this->skill->getLevelData() as $level) {
            $level_id = (int) $level["id"];
            $resources = $resources_by_level[$level_id] ?? [];
            $suggested_resources = [];
            $trigger_resources = [];

            foreach ($resources as $resource) {
                $object_id = \ilObject::_lookupObjId($resource->getRepoRefId());
                $object_type = \ilObject::_lookupType($object_id);
                $object_icon = $this->ui_fac->symbol()->icon()->standard(
                    $object_type,
                    $this->lng->txt("icon") . " " . $this->lng->txt($object_type),
                    "medium"
                );
                $object_link = $this->ui_fac->link()->standard(
                    \ilObject::_lookupTitle($object_id),
                    \ilLink::_getStaticLink($resource->getRepoRefId())
                );
                $item = $this->ui_fac->item()->standard($object_link)->withLeadIcon($object_icon);

                if ($resource->getImparting()) {
                    $suggested_resources[] = $item;
                }

                if ($resource->getTrigger()) {
                    $trigger_resources[] = $item;
                }
            }

            $this->records[(string) $level_id] = [
                "title" => $level["title"],
                "description" => $level["description"],
                "suggested" => $this->ui_fac->listing()->unordered($suggested_resources),
                "trigger" => $this->ui_fac->listing()->unordered($trigger_resources),
            ];
        }

        return $this->records;
    }

    /**
     * @param int[]|string[] $requested_level_ids
     * @return int[]
     */
    public function resolveLevelIds(array $requested_level_ids): array
    {
        $available_level_ids = array_map("intval", array_keys($this->getRecords()));
        if (in_array("ALL_OBJECTS", $requested_level_ids, true)) {
            return $available_level_ids;
        }

        $requested_level_ids = array_map("intval", $requested_level_ids);
        $resolved_level_ids = [];
        foreach ($available_level_ids as $available_level_id) {
            if (in_array($available_level_id, $requested_level_ids, true)) {
                $resolved_level_ids[] = $available_level_id;
            }
        }

        return $resolved_level_ids;
    }
}
