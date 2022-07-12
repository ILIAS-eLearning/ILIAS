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
 * Class ilObjWorkspaceFolderTableGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjWorkspaceFolderTableGUI extends ilTable2GUI
{
    private int $node_id;
    private ilWorkspaceAccessHandler $access_handler;
    private array $shared_objects;
    protected ilObjUser $user;
    protected ilObjectDefinition $obj_definition;
    protected bool $admin = false;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_node_id,
        ilWorkspaceAccessHandler $a_access_handler,
        bool $admin = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];

        $this->node_id = $a_node_id;
        $this->setId("tbl_wfld");
        $this->access_handler = $a_access_handler;
        $this->admin = $admin;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        // $this->setTitle(":TODO:");
        $this->setLimit(999);

        $this->addColumn($this->lng->txt("content"));

        // $this->setEnableHeader(true);
        // $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.list_row.html", "Modules/WorkspaceFolder");
        //$this->disable("footer");
        // $this->setEnableTitle(true);
        $this->setEnableNumInfo(false);

        $this->getItems();
    }

    protected function getItems() : void
    {
        $ilUser = $this->user;
        
        $tree = new ilWorkspaceTree($ilUser->getId());
        $nodes = $tree->getChilds($this->node_id, "title");
                        
        if (sizeof($nodes)) {
            $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_WORKSPACE);
            foreach ($nodes as $node) {
                $preloader->addItem($node["obj_id"], $node["type"]);
            }
            $preloader->preload();
            unset($preloader);
        }
        
        $this->shared_objects = $this->access_handler->getObjectsIShare();
        
        $this->setData($nodes);
    }

    protected function fillRow(array $a_set) : void
    {
        $objDefinition = $this->obj_definition;
        $ilCtrl = $this->ctrl;
        
        $class = $objDefinition->getClassName($a_set["type"]);
        $full_class = "ilObj" . $class . "ListGUI";

        $item_list_gui = new $full_class(ilObjectListGUI::CONTEXT_WORKSPACE);
        
        $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
        $item_list_gui->enableDelete(true);
        $item_list_gui->enableCut(true);
        $item_list_gui->enableSubscribe(false);
        $item_list_gui->enableLink(false);
        $item_list_gui->enablePath(false);
        $item_list_gui->enableLinkedPath(false);
        $item_list_gui->enableSearchFragments(true);
        $item_list_gui->enableRelevance(false);
        $item_list_gui->enableIcon(true);
        $item_list_gui->enableTimings(false);
        $item_list_gui->enableCheckbox($this->admin);
        // $item_list_gui->setSeparateCommands(true);
        
        $item_list_gui->enableNotes(true);
        $item_list_gui->enableCopy($objDefinition->allowCopy($a_set["type"]));
        
        if ($a_set["type"] == "file") {
            $item_list_gui->enableRepositoryTransfer(true);
        }

        $item_list_gui->setContainerObject($this->parent_obj);
        
        if (in_array($a_set["type"], array("file", "blog"))) {
            // add "share" link
            $ilCtrl->setParameterByClass("ilworkspaceaccessgui", "wsp_id", $a_set["wsp_id"]);
            $share_link = $ilCtrl->getLinkTargetByClass(array("ilObj" . $class . "GUI", "ilworkspaceaccessgui"), "share");
            $item_list_gui->addCustomCommand($share_link, "wsp_permissions");
            
            // show "shared" status
            if (in_array($a_set["obj_id"], $this->shared_objects)) {
                $item_list_gui->addCustomProperty($this->lng->txt("status"), $this->lng->txt("wsp_status_shared"), true, true);
            }
        }

        if ($html = $item_list_gui->getListItemHTML(
            $a_set["wsp_id"],
            $a_set["obj_id"],
            $a_set["title"],
            $a_set["description"]
        )) {
            $this->tpl->setVariable("ITEM_LIST_NODE", $html);
        }
    }
}
