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
/**
 * Action target explorer
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup components\ILIASGroup
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

    public function setClickableType(string $a_val): void
    {
        $this->clickable_type = $a_val;
    }

    public function getClickableType(): string
    {
        return $this->clickable_type;
    }

    public function getNodeHref($a_node): string
    {
        return "#";
    }

    public function getNodeOnClick($a_node): string
    {
        if ($this->select_parent) {
            $this->ctrl->setParameter($this->parent_obj, "grp_act_par_ref_id", $a_node["child"]);
            $url = $this->ctrl->getLinkTarget($this->parent_obj, "createGroup", "", true, false);
            return "il.Group.UserActions.initCreationForm(event, '$url'); return false;";
        }
        $this->ctrl->setParameter($this->parent_obj, "grp_act_ref_id", $a_node["child"]);
        $url = $this->ctrl->getLinkTarget($this->parent_obj, "confirmAddUser", "", true, false);
        return "event.stopPropagation(); event.preventDefault(); il.repository.core.fetchReplaceInner( document.getElementById('il_grp_action_modal_content'),'$url'); return false;";
    }

    /**
     * Is node clickable?
     * @param array $a_node node data
     * @return bool node clickable true/false
     */
    public function isNodeClickable($a_node): bool
    {
        if ($this->select_parent) {
            if ($this->access->checkAccess("create", "", (int) $a_node["child"], "grp")) {
                return true;
            }
        } elseif ($a_node["type"] == $this->getClickableType() &&
            $this->access->checkAccess("manage_members", "", (int) $a_node["child"])) {
            return true;
        }
        return false;
    }
}
