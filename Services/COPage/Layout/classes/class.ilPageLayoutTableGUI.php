<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for page layouts
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
*/
class ilPageLayoutTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;


    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $rbacsystem = $DIC->rbac()->system();

        $lng->loadLanguageModule("content");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn("", "", "2%");
        
        $this->addColumn($lng->txt("active"));
        $this->addColumn($lng->txt("thumbnail"));
        $this->addColumn($lng->txt("title"));
        $this->addColumn($lng->txt("description"));
        $this->addColumn($lng->txt("obj_sty"));
        $this->addColumn($lng->txt("type"));
        $this->addColumn($lng->txt("modules"));
        $this->addColumn($lng->txt("actions"));
        
        // show command buttons, if write permission is given
        if ($a_parent_obj->checkPermission("sty_write_page_layout", false)) {
            $this->addMultiCommand("activate", $lng->txt("activate"));
            $this->addMultiCommand("deactivate", $lng->txt("deactivate"));
            $this->addMultiCommand("deletePgl", $lng->txt("delete"));
            $this->addCommandButton("savePageLayoutTypes", $lng->txt("cont_save_types"));
        }
        
        $this->getPageLayouts();
        
        $this->setSelectAllCheckbox("pglayout");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.stys_pglayout_table_row.html",
            "Services/COPage/Layout"
        );
        $this->setTitle($lng->txt("page_layouts"));
        
        //build form
        /*
        $opts = ilUtil::formSelect(12,"new_type",array($lng->txt("page_layout")));
        $this->tpl->setCurrentBlock("add_object");
        $this->tpl->setVariable("SELECT_OBJTYPE", $opts);
        $this->tpl->setVariable("BTN_NAME", "createPgGUI");
        $this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
        $this->tpl->parseCurrentBlock();
        */
    }
    
    /**
    * Get a List of all Page Layouts
    */
    public function getPageLayouts()
    {
        $this->setData(ilPageLayout::getLayoutsAsArray());
        $this->all_mods = ilPageLayout::getAvailableModules();
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        // action
        $ilCtrl->setParameter($this->parent_obj, "layout_id", $a_set['layout_id']);

        if ($this->parent_obj->checkPermission("sty_write_page_layout", false)) {
            $this->tpl->setCurrentBlock("action");
            $this->tpl->setVariable(
                "HREF_ACTION",
                $ilCtrl->getLinkTarget($this->parent_obj, "exportLayout")
            );
            $this->tpl->setVariable("TXT_ACTION", $lng->txt("export"));
            $this->tpl->parseCurrentBlock();
        }

        $ilCtrl->setParameter($this->parent_obj, "layout_id", "");
        
        // modules
        $this->tpl->setCurrentBlock("mod");
        foreach ($this->all_mods as $mod_id => $mod_caption) {
            if (($mod_id == ilPageLayout::MODULE_SCORM && $a_set["mod_scorm"]) ||
                ($mod_id == ilPageLayout::MODULE_PORTFOLIO && $a_set["mod_portfolio"])) {
                $this->tpl->setVariable("MOD_STATUS", " checked=\"checked\"");
            }
            $this->tpl->setVariable("MODULE_ID", $mod_id);
            $this->tpl->setVariable("LAYOUT_ID", $a_set["layout_id"]);
            $this->tpl->setVariable("MOD_NAME", $mod_caption);
            $this->tpl->parseCurrentBlock();
        }
        
        if ($a_set['active']) {
            $this->tpl->setVariable("IMG_ACTIVE", ilUtil::getImagePath("icon_ok.svg"));
        } else {
            $this->tpl->setVariable("IMG_ACTIVE", ilUtil::getImagePath("icon_not_ok.svg"));
        }
        $this->tpl->setVariable("VAL_TITLE", $a_set['title']);
        $this->tpl->setVariable("VAL_DESCRIPTION", $a_set['description']);
        $this->tpl->setVariable("CHECKBOX_ID", $a_set['layout_id']);
        
        $ilCtrl->setParameter($this->parent_obj, "obj_id", $a_set['layout_id']);
        if ($this->parent_obj->checkPermission("sty_write_page_layout", false)) {
            $this->tpl->setVariable("HREF_EDIT_PGLAYOUT", $ilCtrl->getLinkTarget($this->parent_obj, "editPg"));
        }
        
        $pgl_obj = new ilPageLayout($a_set['layout_id']);
        $this->tpl->setVariable("VAL_PREVIEW_HTML", $pgl_obj->getPreview());

        if ($a_set["style_id"] > 0) {
            $this->tpl->setVariable(
                "STYLE",
                ilObject::_lookupTitle($a_set["style_id"])
            );
        }

        $this->tpl->setVariable(
            "TYPE",
            ilUtil::formSelect(
                $a_set["special_page"],
                "type[" . $a_set["layout_id"] . "]",
                array("0" => $lng->txt("cont_layout_template"),
                    "1" => $lng->txt("cont_special_page")),
                false,
                true
            )
        );
    }
}
