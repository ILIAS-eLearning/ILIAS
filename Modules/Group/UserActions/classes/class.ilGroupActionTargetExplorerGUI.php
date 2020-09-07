<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
 * Action target explorer
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 * @ingroup ModulesGroup
 */
class ilGroupActionTargetExplorerGUI extends ilRepositorySelectorExplorerGUI
{
    /**
     * @var bool
     */
    protected $select_parent = false;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_select_parent = false)
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

    /**
     * Set clickable type
     *
     * @param string $a_val clickable type
     */
    public function setClickableType($a_val)
    {
        $this->clickable_type = $a_val;
    }
    
    /**
     * Get clickable type
     *
     * @return string clickable type
     */
    public function getClickableType()
    {
        return $this->clickable_type;
    }

    /**
     * Get onclick attribute
     */
    public function getNodeOnClick($a_node)
    {
        if ($this->select_parent) {
            $this->ctrl->setParameter($this->parent_obj, "grp_act_par_ref_id", $a_node["child"]);
            $url = $this->ctrl->getLinkTarget($this->parent_obj, "createGroup", "", true, false);
            return "il.Group.UserActions.initCreationForm(event, '$url'); return false;";
        }
        $this->ctrl->setParameter($this->parent_obj, "grp_act_ref_id", $a_node["child"]);
        $url = $this->ctrl->getLinkTarget($this->parent_obj, "confirmAddUser", "", true, false);
        return "event.stopPropagation(); il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content'); return false;";
    }

    /**
     * @inheritdoc
     */
    public function getNodeHref($a_node)
    {
        return "";
    }


    /**
     * Is node clickable?
     *
     * @param array $a_node node data
     * @return boolean node clickable true/false
     */
    public function isNodeClickable($a_node)
    {
        if ($this->select_parent) {
            if ($this->access->checkAccess("create", "", $a_node["child"], "grp")) {
                return true;
            }
        } else {
            if ($a_node["type"] == $this->getClickableType() &&
                $this->access->checkAccess("manage_members", "", $a_node["child"])) {
                return true;
            }
        }
        return false;
    }
}
