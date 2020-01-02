<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* Table for object role permissions
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesObject
*/
class ilObjectOwnershipManagementTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    protected $user_id; // [int]

    public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id, array $a_data = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->user_id = (int) $a_user_id;
        $this->setId('objownmgmt'); // #16373
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("path"), "path");
        $this->addColumn($lng->txt("action"), "");

        // $this->setTitle($this->lng->txt(''));
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.obj_ownership_row.html", "Services/Object");
        $this->setDisableFilterHiding(true);
        
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
            
        $this->initItems($a_data);
    }
    
    protected function initItems($a_data)
    {
        $ilAccess = $this->access;
        $tree = $this->tree;
                
        $data = array();
        
        if (is_array($a_data) && sizeof($a_data)) {
            if (!$this->user_id) {
                $is_admin = $ilAccess->checkAccess("visible", "", SYSTEM_FOLDER_ID);
            }

            foreach ($a_data as $id => $item) {
                // workspace objects won't have references
                $refs = ilObject::_getAllReferences($id);
                if ($refs) {
                    foreach ($refs as $idx => $ref_id) {
                        // objects in trash are hidden
                        if (!$tree->isDeleted($ref_id)) {
                            if ($this->user_id) {
                                $readable = $ilAccess->checkAccessOfUser($this->user_id, "read", "", $ref_id, $a_type);
                            } else {
                                $readable = $is_admin;
                            }

                            $data[$ref_id] = array("obj_id" => $id,
                                "ref_id" => $ref_id,
                                "type" => ilObject::_lookupType($id),
                                "title" => $item,
                                "path" => $this->buildPath($ref_id),
                                "readable" => $readable);
                        }
                    }
                }
            }
        }

        $this->setData($data);
    }
    
    public function fillRow($row)
    {
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
                
        // #11050
        if (!$objDefinition->isPlugin($row["type"])) {
            $txt_type = $lng->txt("obj_" . $row["type"]);
        } else {
            include_once("./Services/Component/classes/class.ilPlugin.php");
            $txt_type = ilObjectPlugin::lookupTxtById($row["type"], "obj_" . $row["type"]);
        }
        
        $this->tpl->setVariable("TITLE", $row["title"]);
        $this->tpl->setVariable("ALT_ICON", $txt_type);
        $this->tpl->setVariable("SRC_ICON", ilObject::_getIcon("", "tiny", $row["type"]));
        $this->tpl->setVariable("PATH", $row["path"]);
        
        if ($row["readable"]) {
            $this->tpl->setCurrentBlock("actions");
            $this->tpl->setVariable("ACTIONS", $this->buildActions($row["ref_id"], $row["type"]));
            $this->tpl->parseCurrentBlock();
        }
    }
    
    protected function buildActions($a_ref_id, $a_type)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
        
        include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
        $agui = new ilAdvancedSelectionListGUI();
        $agui->setId($this->id . "-" . $a_ref_id);
        $agui->setListTitle($lng->txt("actions"));
        
        $ilCtrl->setParameter($this->parent_obj, "ownid", $a_ref_id);
                
        include_once "Services/Link/classes/class.ilLink.php";
        $agui->addItem(
            $lng->txt("show"),
            "",
            ilLink::_getLink($a_ref_id, $a_type),
            "",
            "",
            "_blank"
        );
        
        $agui->addItem(
            $lng->txt("move"),
            "",
            $ilCtrl->getLinkTarget($this->parent_obj, "move"),
            "",
            "",
            ""
        );
        
        $agui->addItem(
            $lng->txt("change_owner"),
            "",
            $ilCtrl->getLinkTarget($this->parent_obj, "changeOwner"),
            "",
            "",
            ""
        );
        
        if (!in_array($a_type, array("crsr", "catr", "grpr")) && $objDefinition->allowExport($a_type)) {
            $agui->addItem(
                $lng->txt("export"),
                "",
                $ilCtrl->getLinkTarget($this->parent_obj, "export"),
                "",
                "",
                ""
            );
        }
        
        $agui->addItem(
            $lng->txt("delete"),
            "",
            $ilCtrl->getLinkTarget($this->parent_obj, "delete"),
            "",
            "",
            ""
        );
        
        $ilCtrl->setParameter($this->parent_obj, "ownid", "");
                            
        return $agui->getHTML();
    }
    
    protected function buildPath($a_ref_id)
    {
        $tree = $this->tree;

        $path = "...";
        $counter = 0;
        $path_full = $tree->getPathFull($a_ref_id);
        foreach ($path_full as $data) {
            if (++$counter < (count($path_full)-2)) {
                continue;
            }
            if ($a_ref_id != $data['ref_id']) {
                $path .= " &raquo; " . $data['title'];
            }
        }
        
        return $path;
    }
}
