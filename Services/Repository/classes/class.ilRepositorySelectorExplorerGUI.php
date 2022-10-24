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

use ILIAS\Repository\StandardGUIRequest;

/**
 * Explorer for selecting repository items.
 *
 * The implementation starts as a replacement for the often (ab)used ilSearchRootSelector class.
 * Clicking items triggers a "selection" command.
 * However ajax/checkbox/radio and use in an inputgui class should be implemented in the future, too.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRepositorySelectorExplorerGUI extends ilTreeExplorerGUI
{
    protected ilObjectDefinition $obj_definition;
    protected array $type_grps = [];
    protected array $session_materials = [];
    protected string $highlighted_node = "";
    protected array $clickable_types = [];
    protected array $selectable_types = [];
    protected ilAccessHandler $access;
    protected ?Closure $nc_modifier = null;
    protected ?string $selection_gui = null;
    protected string $selection_par;
    protected string $selection_cmd;
    protected StandardGUIRequest $request;
    protected int $cur_ref_id;

    /**
     * @param object|string[] $a_parent_obj parent gui class or class array
     * @param object|string $a_selection_gui gui class that should be called for the selection command
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        $a_selection_gui = null,
        string $a_selection_cmd = "selectObject",
        string $a_selection_par = "sel_ref_id",
        string $a_id = "rep_exp_sel",
        string $a_node_parameter_name = "node_id"
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $this->lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];
        $this->request = $DIC->repository()->internal()->gui()->standardRequest();
        $this->cur_ref_id = $this->request->getRefId();

        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();

        if (is_null($a_selection_gui)) {
            $a_selection_gui = $a_parent_obj;
        }

        $this->selection_gui = is_object($a_selection_gui)
            ? strtolower(get_class($a_selection_gui))
            : strtolower($a_selection_gui);
        $this->selection_cmd = $a_selection_cmd;
        $this->selection_par = $a_selection_par;
        parent::__construct($a_id, $a_parent_obj, $a_parent_cmd, $tree, $a_node_parameter_name);

        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setOrderField("title");

        // per default: all object types, except item groups
        $white = [];
        foreach ($objDefinition->getSubObjectsRecursively("root") as $rtype) {
            if ($rtype["name"] !== "itgr" && !$objDefinition->isSideBlock($rtype["name"])) {
                $white[] = $rtype["name"];
            }
        }
        $this->setTypeWhiteList($white);

        // always open the path to the current ref id
        $this->setPathOpen($this->tree->readRootId());
        if ($this->cur_ref_id > 0) {
            $this->setPathOpen($this->cur_ref_id);
        }
        $this->setChildLimit((int) $ilSetting->get("rep_tree_limit_number"));
    }

    public function setNodeContentModifier(Closure $a_val): void
    {
        $this->nc_modifier = $a_val;
    }

    public function getNodeContentModifier(): ?Closure
    {
        return $this->nc_modifier;
    }

    public function getNodeContent($a_node): string
    {
        $lng = $this->lng;

        $c = $this->getNodeContentModifier();
        if (is_callable($c)) {
            return $c($a_node);
        }

        $title = $a_node["title"];
        if ($title === "ILIAS" && (int) $a_node["child"] === (int) $this->getNodeId($this->getRootNode())) {
            $title = $lng->txt("repository");
        }

        return $title;
    }

    public function getNodeIcon($a_node): string
    {
        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }

    public function getNodeIconAlt($a_node): string
    {
        $lng = $this->lng;

        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            $title = $a_node["title"];
            if ($title === "ILIAS") {
                $title = $lng->txt("repository");
            }
            return $lng->txt("icon") . " " . $title;
        }


        return parent::getNodeIconAlt($a_node);
    }

    public function isNodeHighlighted($a_node): bool
    {
        if ($this->getHighlightedNode()) {
            if ((int) $this->getHighlightedNode() === (int) $a_node["child"]) {
                return true;
            }
            return false;
        }

        if ((int) $a_node["child"] === $this->cur_ref_id ||
            ($this->cur_ref_id === 0 && (int) $a_node["child"] === (int) $this->getNodeId($this->getRootNode()))) {
            return true;
        }
        return false;
    }

    public function getNodeHref($a_node): string
    {
        $ilCtrl = $this->ctrl;

        if ($this->select_postvar === "") {
            $ilCtrl->setParameterByClass($this->selection_gui, $this->selection_par, $a_node["child"]);
            $link = $ilCtrl->getLinkTargetByClass($this->selection_gui, $this->selection_cmd);
            $ilCtrl->setParameterByClass($this->selection_gui, $this->selection_par, "");
        } else {
            return "#";
        }

        return $link;
    }

    public function isNodeVisible($a_node): bool
    {
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess('visible', '', $a_node["child"])) {
            return false;
        }

        return true;
    }

    public function sortChilds(array $a_childs, $a_parent_node_id): array
    {
        $objDefinition = $this->obj_definition;

        $parent_obj_id = ilObject::_lookupObjId((int) $a_parent_node_id);

        if ($parent_obj_id > 0) {
            $parent_type = ilObject::_lookupType($parent_obj_id);
        } else {
            $parent_type = "dummy";
            $this->type_grps["dummy"] = ["root" => "dummy"];
        }

        if (empty($this->type_grps[$parent_type])) {
            $this->type_grps[$parent_type] =
                $objDefinition::getGroupedRepositoryObjectTypes($parent_type);
        }
        $group = [];

        foreach ($a_childs as $child) {
            $g = $objDefinition->getGroupOfObj($child["type"]);
            if ($g == "") {
                $g = $child["type"];
            }
            $group[$g][] = $child;
        }

        // #14587 - $objDefinition->getGroupedRepositoryObjectTypes does NOT include side blocks!
        $wl = $this->getTypeWhiteList();
        if (is_array($wl) && in_array("poll", $wl, true)) {
            $this->type_grps[$parent_type]["poll"] = [];
        }

        $childs = [];
        foreach ($this->type_grps[$parent_type] as $t => $g) {
            if (isset($group[$t])) {
                // do we have to sort this group??
                $sort = ilContainerSorting::_getInstance($parent_obj_id);
                $group = $sort->sortItems($group);

                foreach ($group[$t] as $k => $item) {
                    $childs[] = $item;
                }
            }
        }

        return $childs;
    }

    public function getChildsOfNode($a_parent_node_id): array
    {
        $ilAccess = $this->access;
        if (!$ilAccess->checkAccess("read", "", (int) $a_parent_node_id)) {
            return [];
        }

        return parent::getChildsOfNode($a_parent_node_id);
    }

    public function isNodeClickable($a_node): bool
    {
        $ilAccess = $this->access;

        if (!$ilAccess->hasUserRBACorAnyPositionAccess("read", $a_node["child"])) {
            return false;
        }

        if (is_array($this->getClickableTypes()) && count($this->getClickableTypes()) > 0) {
            return in_array($a_node["type"], $this->getClickableTypes(), true);
        }

        return true;
    }

    public function setHighlightedNode(string $a_value): void
    {
        $this->highlighted_node = $a_value;
    }

    public function getHighlightedNode(): string
    {
        return $this->highlighted_node;
    }

    public function setClickableTypes(array $a_types): void
    {
        $this->clickable_types = $a_types;
    }

    public function getClickableTypes(): array
    {
        return $this->clickable_types;
    }

    public function setSelectableTypes(array $a_types): void
    {
        $this->selectable_types = $a_types;
    }

    public function getSelectableTypes(): array
    {
        return $this->selectable_types;
    }

    protected function isNodeSelectable($a_node): bool
    {
        if (count($this->getSelectableTypes())) {
            return in_array($a_node['type'], $this->getSelectableTypes(), true);
        }
        return true;
    }
}
