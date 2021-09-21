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
 ********************************************************************
 */

use Psr\Http\Message\ServerRequestInterface;

/**
 * TableGUI class for
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillCatTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    protected ilAccessHandler $access;
    protected int $tref_id;
    protected int $mode;
    protected ilSkillTree $skill_tree;
    protected int $obj_id;
    protected ServerRequestInterface $request;
    protected int $requested_obj_id;
    protected int $requested_tref_id;

    public const MODE_SCAT = 0;
    public const MODE_SCTP = 1;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        int $a_obj_id,
        int $a_mode = self::MODE_SCAT,
        int $a_tref_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->request = $DIC->http()->request();

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->tref_id = $a_tref_id;
        $ilCtrl->setParameter($a_parent_obj, "tmpmode", $a_mode);

        $params = $this->request->getQueryParams();
        $this->requested_obj_id = (int) ($params["obj_id"] ?? 0);
        $this->requested_tref_id = (int) ($params["tref_id"] ?? 0);
        
        $this->mode = $a_mode;
        $this->skill_tree = new ilSkillTree();
        $this->obj_id = $a_obj_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        if ($this->mode == self::MODE_SCAT) {
            $childs = $this->skill_tree->getChildsByTypeFilter(
                $a_obj_id,
                array("skrt", "skll", "scat", "sktr")
            );
            $childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
            $this->setData($childs);
        } elseif ($this->mode == self::MODE_SCTP) {
            $childs = $this->skill_tree->getChildsByTypeFilter(
                $a_obj_id,
                array("skrt", "sktp", "sctp")
            );
            $childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
            $this->setData($childs);
        }
        
        if ($this->obj_id != $this->skill_tree->readRootId()) {
            //			$this->setTitle(ilSkillTreeNode::_lookupTitle($this->obj_id));
        }
        $this->setTitle($lng->txt("skmg_items"));
        
        if ($this->tref_id == 0) {
            $this->addColumn($this->lng->txt(""), "", "1px", true);
        }
        $this->addColumn($this->lng->txt("type"), "", "1px");
        if ($this->tref_id == 0) {
            $this->addColumn($this->lng->txt("skmg_order"), "", "1px");
        }
        $this->addColumn($this->lng->txt("title"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_cat_row.html", "Services/Skill");

        if ($this->tref_id == 0 && $this->parent_obj->checkPermissionBool("write")) {
            $this->addMultiCommand("cutItems", $lng->txt("cut"));
            $this->addMultiCommand("copyItems", $lng->txt("copy"));
            $this->addMultiCommand("deleteNodes", $lng->txt("delete"));
            if ($a_mode == self::MODE_SCAT) {
                $this->addMultiCommand("exportSelectedNodes", $lng->txt("export"));
            }
            $this->addCommandButton("saveOrder", $lng->txt("skmg_save_order"));
        }
    }

    protected function fillRow($a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ret = "";
        switch ($a_set["type"]) {
            // category
            case "scat":
                $ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $a_set["child"]);
                $ret = $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "listItems");
                $ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $this->requested_obj_id);
                break;
                
            // skill template reference
            case "sktr":
                $tid = ilSkillTemplateReference::_lookupTemplateId($a_set["child"]);
                $ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "tref_id", $a_set["child"]);
                $ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $tid);
                $ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatereferencegui", "listItems");
                $ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $this->requested_obj_id);
                $ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "tref_id", $this->requested_tref_id);
                break;
                
            // skill
            case "skll":
                $ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $a_set["child"]);
                $ret = $ilCtrl->getLinkTargetByClass("ilbasicskillgui", "edit");
                $ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $this->requested_obj_id);
                break;
                
            // --------
                
            // template
            case "sktp":
                $ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $a_set["child"]);
                $ret = $ilCtrl->getLinkTargetByClass("ilbasicskilltemplategui", "edit");
                $ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $this->requested_obj_id);
                break;

            // template category
            case "sctp":
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $a_set["child"]);
                $ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "listItems");
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $this->requested_obj_id);
                break;
        }

        if ($this->tref_id == 0) {
            $this->tpl->setCurrentBlock("cb");
            $this->tpl->setVariable("CB_ID", $a_set["child"]);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("nr");
            $this->tpl->setVariable("OBJ_ID", $a_set["child"]);
            $this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("HREF_TITLE", $ret);
        
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $icon = ilSkillTreeNode::getIconPath(
            $a_set["child"],
            $a_set["type"],
            "",
            ilSkillTreeNode::_lookupStatus($a_set["child"])
        );
        $this->tpl->setVariable("ICON", ilUtil::img($icon, ""));
    }
}
