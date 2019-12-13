<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Workspace access handler table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCountryTableGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilWorkspaceAccessTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $node_id; // [int]
    protected $handler; // [ilWorkspaceAccessHandler]

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_node_id current workspace object
     * @param object $a_handler workspace access handler
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_node_id, $a_handler)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->node_id = $a_node_id;
        $this->handler = $a_handler;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("il_tbl_wsacl");

        $this->setTitle($lng->txt("wsp_shared_table_title"));
                
        $this->addColumn($this->lng->txt("wsp_shared_with"), "title");
        $this->addColumn($this->lng->txt("details"), "type");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.access_row.html", "Services/PersonalWorkspace");

        $this->importData();
    }

    /**
     * Import data from DB
     */
    protected function importData()
    {
        include_once("./Services/User/classes/class.ilUserUtil.php");
        
        $data = array();
        foreach ($this->handler->getPermissions($this->node_id) as $obj_id) {
            // title is needed for proper sorting
            // special modes should always be on top!
            $title = null;
            
            switch ($obj_id) {
                case ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
                    $caption = $this->lng->txt("wsp_set_permission_registered");
                    $title = "0" . $caption;
                    break;
                
                case ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
                    $caption = $this->lng->txt("wsp_set_permission_all_password");
                    $title = "0" . $caption;
                    break;
                
                case ilWorkspaceAccessGUI::PERMISSION_ALL:
                    $caption = $this->lng->txt("wsp_set_permission_all");
                    $title = "0" . $caption;
                    break;
                                                
                default:
                    $type = ilObject::_lookupType($obj_id);
                    $type_txt = $this->lng->txt("obj_" . $type);
                    
                    if ($type === null) {
                        // invalid object/user
                    } elseif ($type != "usr") {
                        $title = $caption = ilObject::_lookupTitle($obj_id);
                    } else {
                        $caption = ilUserUtil::getNamePresentation($obj_id, false, true);
                        $title = strip_tags($caption);
                    }
                    break;
            }
            
            if ($title) {
                $data[] = array("id" => $obj_id,
                    "title" => $title,
                    "caption" => $caption,
                    "type" => $type_txt);
            }
        }
    
        $this->setData($data);
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        $ilCtrl = $this->ctrl;
        
        // properties
        $this->tpl->setVariable("TITLE", $a_set["caption"]);
        $this->tpl->setVariable("TYPE", $a_set["type"]);

        $ilCtrl->setParameter($this->parent_obj, "obj_id", $a_set["id"]);
        $this->tpl->setVariable(
            "HREF_CMD",
            $ilCtrl->getLinkTarget($this->parent_obj, "removePermission")
        );
        $this->tpl->setVariable("TXT_CMD", $this->lng->txt("remove"));
    }
}
