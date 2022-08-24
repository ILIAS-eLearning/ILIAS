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

/**
 * LM Menu Object Selector
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 */
class ilLMMenuObjectSelector extends ilExplorer
{
    public ilCtrl $ctrl;
    public array $selectable_types;
    public int $ref_id;
    protected object $gui_obj;
    protected int $menu_entry;

    public function __construct(
        string $a_target,
        object $a_gui_obj,
        int $menu_entry
    ) {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $this->menu_entry = $menu_entry;

        $this->ctrl = $ilCtrl;

        $this->gui_obj = $a_gui_obj;

        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";
        $this->setSessionExpandVariable("lm_menu_expand");
        $this->addFilter("rolf");
        $this->addFilter("adm");
    }

    public function setSelectableTypes(array $a_types): void
    {
        $this->selectable_types = $a_types;
    }

    public function setRefId(int $a_ref_id): void
    {
        $this->ref_id = $a_ref_id;
    }

    /**
     * @param object|array $a_node_id
     */
    public function buildLinkTarget($a_node_id, string $a_type): string
    {
        if (in_array($a_type, $this->selectable_types)) {
            $this->ctrl->setParameter($this->gui_obj, 'link_ref_id', $a_node_id);
            if ($this->menu_entry > 0) {
                return $this->ctrl->getLinkTarget($this->gui_obj, 'editMenuEntry');
            } else {
                return $this->ctrl->getLinkTarget($this->gui_obj, 'addMenuEntry');
            }
        }
        return "";
    }

    public function isClickable(string $type, int $ref_id = 0): bool
    {
        return in_array($type, $this->selectable_types) && $ref_id !== $this->ref_id;
    }

    /**
     * @param int $a_parent_id
     */
    public function showChilds($a_parent_id): bool
    {
        $rbacsystem = $this->rbacsystem;

        if ($a_parent_id == 0) {
            return true;
        }

        if ($rbacsystem->checkAccess("read", $a_parent_id)) {
            return true;
        } else {
            return false;
        }
    }
}
