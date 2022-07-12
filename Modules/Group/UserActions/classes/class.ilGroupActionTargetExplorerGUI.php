<?php declare(strict_types=1);
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Action target explorer
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ModulesGroup
 */
class ilGroupActionTargetExplorerGUI extends ilRepositorySelectorExplorerGUI
{
    protected bool $select_parent = false;
    private string $clickable_type = '';

    /**
     * Constructor
     */
    public function __construct(object $a_parent_obj, string $a_parent_cmd, bool $a_select_parent = false)
    {
        global $DIC;

        $user = $DIC->user();

        parent::__construct($a_parent_obj, $a_parent_cmd, null, "", "");
        $this->select_parent = $a_select_parent;

        // open paths to objects with access
        if ($a_select_parent) {
            $ref_ids = ilUtil::_getObjectsByOperations(array("root", "crs", "cat"), "create_grp", $user->getId(), 5);
        } else {
            $ref_ids = ilUtil::_getObjectsByOperations("grp", "manage_members", $user->getId(), 5);
        }
        foreach ($ref_ids as $ref_id) {
            $this->setPathOpen($ref_id);
        }
    }

    public function setClickableType(string $a_val) : void
    {
        $this->clickable_type = $a_val;
    }
    
    public function getClickableType() : string
    {
        return $this->clickable_type;
    }

    public function getNodeHref($a_node) : string
    {
        return "#";
    }

    public function getNodeOnClick($a_node) : string
    {
        if ($this->select_parent) {
            $this->ctrl->setParameter($this->parent_obj, "grp_act_par_ref_id", $a_node["child"]);
            $url = $this->ctrl->getLinkTarget($this->parent_obj, "createGroup", "", true, false);
            return "il.Group.UserActions.initCreationForm(event, '$url'); return false;";
        }
        $this->ctrl->setParameter($this->parent_obj, "grp_act_ref_id", $a_node["child"]);
        $url = $this->ctrl->getLinkTarget($this->parent_obj, "confirmAddUser", "", true, false);
        return "event.stopPropagation(); event.preventDefault(); il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content'); return false;";
    }

    /**
     * Is node clickable?
     * @param array $a_node node data
     * @return bool node clickable true/false
     */
    public function isNodeClickable($a_node) : bool
    {
        if ($this->select_parent) {
            if ($this->access->checkAccess("create", "", $a_node["child"], "grp")) {
                return true;
            }
        } elseif ($a_node["type"] == $this->getClickableType() &&
            $this->access->checkAccess("manage_members", "", $a_node["child"])) {
            return true;
        }
        return false;
    }
}
