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
use ILIAS\Skill\Access\SkillTreeAccess;
use ILIAS\Skill\Node\SkillTreeNodeManager;
use ILIAS\UI;
use Psr\Http\Message\ServerRequestInterface;

class SkillCategoryTable implements UI\Component\Table\OrderingRetrieval
{
    public const int MODE_SCAT = 0;
    public const int MODE_SCTP = 1;

    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected UI\Factory $ui_fac;
    protected ServerRequestInterface $request;
    protected Data\Factory $df;
    protected \ilSkillTree $skill_tree;
    protected SkillTreeAccess $tree_access_manager;
    protected ?array $records = null;

    public function __construct(
        protected object $parent_obj,
        protected int $node_id,
        protected int $mode,
        protected int $tref_id,
        protected SkillTreeNodeManager $node_manager
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->df = new Data\Factory();
        $this->skill_tree = $DIC->skills()->internal()->repo()->getTreeRepo()->getTreeForNodeId($node_id);
        $this->tree_access_manager = $DIC->skills()->internal()->manager()->getTreeAccessManager(
            $DIC->skills()->internal()->gui()->admin_request()->getRefId()
        );

        $this->ctrl->setParameter($parent_obj, "tmpmode", $mode);
    }

    public function getComponent(): UI\Component\Table\Ordering
    {
        $target = $this->df->uri(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this->parent_obj, "saveOrder")
        );

        return $this->ui_fac->table()
            ->ordering($this, $target, $this->lng->txt("skmg_items"), $this->getColumns())
            ->withId(
                str_replace("\\", "", self::class) . "_" . $this->node_id . "_" . $this->mode . "_" . $this->tref_id
            )
            ->withActions($this->getActions())
            ->withRequest($this->request)
            ->withOrderingDisabled(
                !$this->hasManagePermission() || $this->tref_id !== 0 || empty($this->getRecords())
            );
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
        if (!$this->hasManagePermission() || $this->tref_id !== 0) {
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

        $this->node_manager->saveChildsOrder($this->node_id, $order, $this->mode === self::MODE_SCTP);
    }

    protected function getColumns(): array
    {
        return [
            "type" => $this->ui_fac->table()->column()->statusIcon($this->lng->txt("type")),
            "title" => $this->ui_fac->table()->column()->link($this->lng->txt("title")),
        ];
    }

    protected function getActions(): array
    {
        if (!$this->hasManagePermission() || $this->tref_id !== 0) {
            return [];
        }

        $query_params_namespace = ["skl_skill_cat_table"];

        $actions = [];
        $action_commands = [
            "cut" => "cutItems",
            "copy" => "copyItems",
            "delete" => "deleteNodes",
        ];
        if ($this->mode === self::MODE_SCAT) {
            $action_commands["export"] = "exportSelectedNodes";
        }

        foreach ($action_commands as $action => $command) {
            $uri = $this->df->uri(
                ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTarget($this->parent_obj, $command)
            );
            $url_builder = new UI\URLBuilder($uri);
            [$url_builder, $row_id_token] = $url_builder->acquireParameter(
                $query_params_namespace,
                "node_ids"
            );

            $actions[$action] = $this->ui_fac->table()->action()->multi(
                $this->lng->txt($action),
                $url_builder,
                $row_id_token
            );
        }

        return $actions;
    }

    protected function getRecords(): array
    {
        if ($this->records !== null) {
            return $this->records;
        }

        $this->records = [];
        $types = $this->mode === self::MODE_SCAT
            ? ["skrt", "skll", "scat", "sktr"]
            : ["skrt", "sktp", "sctp"];
        $childs = $this->skill_tree->getChildsByTypeFilter($this->node_id, $types);
        $childs = \ilArrayUtil::sortArray($childs, "order_nr", "asc", true);

        foreach ($childs as $child) {
            $child_id = (int) $child["child"];
            $icon_path = \ilSkillTreeNode::getIconPath(
                $child_id,
                $child["type"],
                "",
                \ilSkillTreeNode::_lookupStatus($child_id)
            );
            $icon = $this->ui_fac->symbol()->icon()->custom($icon_path, "", "medium");

            $this->records[(string) $child_id] = [
                "type" => $icon,
                "title" => $this->ui_fac->link()->standard(
                    $child["title"],
                    $this->getRowLink($child)
                ),
            ];
        }

        return $this->records;
    }

    protected function getRowLink(array $record): string
    {
        $child_id = (int) $record["child"];
        switch ($record["type"]) {
            // category
            case "scat":
                $this->ctrl->setParameterByClass("ilskillcategorygui", "node_id", $child_id);
                $target = $this->ctrl->getLinkTargetByClass("ilskillcategorygui", "listItems");
                $this->ctrl->setParameterByClass("ilskillcategorygui", "node_id", $this->node_id);
                return $target;

                // skill template reference
            case "sktr":
                $template_id = \ilSkillTemplateReference::_lookupTemplateId($child_id);
                $this->ctrl->setParameterByClass("ilskilltemplatereferencegui", "tref_id", $child_id);
                $this->ctrl->setParameterByClass("ilskilltemplatereferencegui", "node_id", $template_id);
                $target = $this->ctrl->getLinkTargetByClass("ilskilltemplatereferencegui", "listItems");
                $this->ctrl->setParameterByClass("ilskilltemplatereferencegui", "node_id", $this->node_id);
                $this->ctrl->setParameterByClass("ilskilltemplatereferencegui", "tref_id", $this->tref_id);
                return $target;

                // skill
            case "skll":
                $this->ctrl->setParameterByClass("ilbasicskillgui", "node_id", $child_id);
                $target = $this->ctrl->getLinkTargetByClass("ilbasicskillgui", "edit");
                $this->ctrl->setParameterByClass("ilbasicskillgui", "node_id", $this->node_id);
                return $target;

                // template
            case "sktp":
                $this->ctrl->setParameterByClass("ilbasicskilltemplategui", "node_id", $child_id);
                $target = $this->ctrl->getLinkTargetByClass("ilbasicskilltemplategui", "edit");
                $this->ctrl->setParameterByClass("ilbasicskilltemplategui", "node_id", $this->node_id);
                return $target;

                // template category
            case "sctp":
                $this->ctrl->setParameterByClass("ilskilltemplatecategorygui", "node_id", $child_id);
                $target = $this->ctrl->getLinkTargetByClass("ilskilltemplatecategorygui", "listItems");
                $this->ctrl->setParameterByClass("ilskilltemplatecategorygui", "node_id", $this->node_id);
                return $target;
        }

        return "";
    }

    protected function hasManagePermission(): bool
    {
        if ($this->mode === self::MODE_SCAT) {
            return $this->tree_access_manager->hasManageCompetencesPermission();
        }

        return $this->tree_access_manager->hasManageCompetenceTemplatesPermission();
    }

    /**
     * @param int[]|string[] $requested_node_ids
     * @return int[]
     */
    public function resolveNodeIds(array $requested_node_ids): array
    {
        $available_node_ids = array_map("intval", array_keys($this->getRecords()));
        if (in_array("ALL_OBJECTS", $requested_node_ids, true)) {
            return $available_node_ids;
        }

        $requested_node_ids = array_map("intval", $requested_node_ids);
        $resolved_node_ids = [];
        foreach ($available_node_ids as $available_node_id) {
            if (in_array($available_node_id, $requested_node_ids, true)) {
                $resolved_node_ids[] = $available_node_id;
            }
        }

        return $resolved_node_ids;
    }
}
