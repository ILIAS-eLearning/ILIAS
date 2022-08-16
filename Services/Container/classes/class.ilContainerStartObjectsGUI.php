<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContainerStartObjectsGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjCourseGUI.php 47058 2014-01-08 08:07:12Z mjansen $
 *
 * @ilCtrl_Calls ilContainerStartObjectsGUI: ilContainerStartObjectsPageGUI
 * @ingroup ServicesContainer
 */
class ilContainerStartObjectsGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    protected $object; // [ilObject]
    protected $start_object; // [ilContainerStartObjects]
    
    public function __construct(ilObject $a_parent_obj)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->toolbar = $DIC->toolbar();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        
        $this->ctrl = $ilCtrl;
        $this->tabs_gui = $ilTabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->object = $a_parent_obj;
                
        include_once "Services/Container/classes/class.ilContainerStartObjects.php";
        $this->start_object = new ilContainerStartObjects(
            $this->object->getRefId(),
            $this->object->getId()
        );
        
        $this->lng->loadLanguageModule("crs");
    }
    
    public function executeCommand()
    {
        // $this->prepareOutput();
    
        switch ($this->ctrl->getNextClass($this)) {
            case "ilcontainerstartobjectspagegui":
                $this->checkPermission("write");
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "listStructure")
                );
                
                include_once "Services/Container/classes/class.ilContainerStartObjectsPage.php";
                if (!ilContainerStartObjectsPage::_exists("cstr", $this->object->getId())) {
                    // doesn't exist -> create new one
                    $new_page_object = new ilContainerStartObjectsPage();
                    $new_page_object->setParentId($this->object->getId());
                    $new_page_object->setId($this->object->getId());
                    $new_page_object->createFromXML();
                    unset($new_page_object);
                }

                $this->tpl->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath(ilObjStyleSheet::getEffectiveContentStyleId(
                        $this->object->getStyleSheetId(),
                        $this->object->getType()
                    ))
                );

                $this->ctrl->setReturnByClass("ilcontainerstartobjectspagegui", "edit");
                include_once "Services/Container/classes/class.ilContainerStartObjectsPageGUI.php";
                $pgui = new ilContainerStartObjectsPageGUI($this->object->getId());
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $pgui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
                    $this->object->getStyleSheetId(),
                    $this->object->getType()
                ));

                $ret = $this->ctrl->forwardCommand($pgui);
                if ($ret) {
                    $this->tpl->setContent($ret);
                }
                break;
            
            default:
                $cmd = $this->ctrl->getCmd("listStructure");
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }
    
    protected function checkPermission($a_cmd)
    {
        $ilAccess = $this->access;
        
        $ref_id = $this->object->getRefId();
        if (!$ilAccess->checkAccess($a_cmd, "", $ref_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            ilUtil::redirect("goto.php?target=" . $this->object->getType() . "_" . $ref_id);
        }
    }
    
    public function setTabs($a_active = "manage")
    {
        $ilSetting = $this->settings;
        
        $this->tabs_gui->addSubTab(
            "manage",
            $this->lng->txt("cntr_manage"),
            $this->ctrl->getLinkTarget($this, "listStructure")
        );

        $this->tabs_gui->activateSubTab($a_active);
    }

    protected function listStructureObject()
    {
        $ilToolbar = $this->toolbar;
        $ilSetting = $this->settings;
        
        $this->checkPermission('write');
        $this->setTabs();
        
        $ilToolbar->addButton(
            $this->lng->txt('crs_add_starter'),
            $this->ctrl->getLinkTarget($this, 'selectStarter')
        );

        // :TODO: depending on this setting?
        if ($ilSetting->get("enable_cat_page_edit")) {
            $this->toolbar->addButton(
                $this->lng->txt("cntr_text_media_editor"),
                $this->ctrl->getLinkTargetByClass("ilContainerStartObjectsPageGUI", "edit")
            );
        }

        include_once './Services/Container/classes/class.ilContainerStartObjectsTableGUI.php';
        $table = new ilContainerStartObjectsTableGUI($this, 'listStructure', $this->start_object);
        $this->tpl->setContent($table->getHTML());
    }
    
    protected function saveSortingObject()
    {
        $pos = $_POST["pos"];
        if (is_array($pos)) {
            asort($pos);
            $counter = 0;
            foreach (array_keys($pos) as $start_id) {
                $counter += 10;
                $this->start_object->setObjectPos($start_id, $counter);
            }
            
            ilUtil::sendSuccess($this->lng->txt('cntr_saved_sorting'), true);
        }
        
        $this->ctrl->redirect($this, "listStructure");
    }
    
    protected function askDeleteStarterObject()
    {
        if (empty($_POST['starter'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, "listStructure");
        }
        
        $this->checkPermission('write');
        $this->setTabs();

        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this, "listStructure"));
        $cgui->setHeaderText($this->lng->txt("crs_starter_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "listStructure");
        $cgui->setConfirm($this->lng->txt("remove"), "deleteStarter");

        // list objects that should be deleted
        $all = $this->start_object->getStartObjects();
        foreach ($_POST['starter'] as $starter_id) {
            $obj_id = ilObject::_lookupObjId($all[$starter_id]["item_ref_id"]);
            $title = ilObject::_lookupTitle($obj_id);
            $icon = ilObject::_getIcon($obj_id, "tiny");
            $alt = $this->lng->txt('obj_' . ilObject::_lookupType($obj_id));
            $cgui->addItem("starter[]", $starter_id, $title, $icon, $alt);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    protected function deleteStarterObject()
    {
        $this->checkPermission('write');
        
        if (!count($_POST['starter'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
        } else {
            foreach ($_POST['starter'] as $starter_id) {
                $this->start_object->delete((int) $starter_id);
            }

            ilUtil::sendSuccess($this->lng->txt('crs_starter_deleted'), true);
        }
        
        $this->ctrl->redirect($this, "listStructure");
    }
        
    protected function selectStarterObject()
    {
        $this->checkPermission('write');
        $this->setTabs();
        
        include_once './Services/Container/classes/class.ilContainerStartObjectsTableGUI.php';
        $table = new ilContainerStartObjectsTableGUI($this, 'selectStarter', $this->start_object);
        $this->tpl->setContent($table->getHTML());
    }

    protected function addStarterObject()
    {
        $this->checkPermission('write');

        if (empty($_POST['starter'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, "selectStarter");
        }
            
        $added = 0;
        foreach ($_POST['starter'] as $item_ref_id) {
            if (!$this->start_object->exists($item_ref_id)) {
                ++$added;
                $this->start_object->add($item_ref_id);
            }
        }
        if ($added) {
            ilUtil::sendSuccess($this->lng->txt('crs_added_starters'), true);
            $this->ctrl->redirect($this, "listStructure");
        } else {
            ilUtil::sendFailure($this->lng->txt('crs_starters_already_assigned'), true);
            $this->ctrl->redirect($this, "selectStarter");
        }
    }
}
