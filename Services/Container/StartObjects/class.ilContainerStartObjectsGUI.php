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

use ILIAS\Container\StandardGUIRequest;

/**
 * Class ilContainerStartObjectsGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilContainerStartObjectsGUI: ilContainerStartObjectsPageGUI
 */
class ilContainerStartObjectsGUI
{
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilAccessHandler $access;
    protected ilSetting $settings;
    protected ilToolbarGUI $toolbar;
    protected ilObject $object;
    protected ilContainerStartObjects $start_object;
    protected StandardGUIRequest $request;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

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

        $this->start_object = new ilContainerStartObjects(
            $this->object->getRefId(),
            $this->object->getId()
        );

        $this->request = $DIC->container()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->lng->loadLanguageModule("crs");
        $cs = $DIC->contentStyle();
        $this->content_style_domain = $cs->domain()->styleForRefId($a_parent_obj->getRefId());
        $this->content_style_gui = $cs->gui();
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case "ilcontainerstartobjectspagegui":
                $this->checkPermission("write");
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "listStructure")
                );

                if (!ilContainerStartObjectsPage::_exists("cstr", $this->object->getId())) {
                    // doesn't exist -> create new one
                    $new_page_object = new ilContainerStartObjectsPage();
                    $new_page_object->setParentId($this->object->getId());
                    $new_page_object->setId($this->object->getId());
                    $new_page_object->createFromXML();
                    unset($new_page_object);
                }

                $this->content_style_gui->addCss(
                    $this->tpl,
                    $this->object->getRefId()
                );

                $this->ctrl->setReturnByClass("ilcontainerstartobjectspagegui", "edit");
                $pgui = new ilContainerStartObjectsPageGUI($this->object->getId());
                $pgui->setStyleId(
                    $this->content_style_domain->getEffectiveStyleId()
                );

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

    protected function checkPermission(string $a_cmd): void
    {
        $ilAccess = $this->access;

        $ref_id = $this->object->getRefId();
        if (!$ilAccess->checkAccess($a_cmd, "", $ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            ilUtil::redirect("goto.php?target=" . $this->object->getType() . "_" . $ref_id);
        }
    }

    public function setTabs(string $a_active = "manage"): void
    {
        $ilSetting = $this->settings;

        $this->tabs_gui->addSubTab(
            "manage",
            $this->lng->txt("cntr_manage"),
            $this->ctrl->getLinkTarget($this, "listStructure")
        );

        $this->tabs_gui->activateSubTab($a_active);
    }

    protected function listStructureObject(): void
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
            $ilToolbar->addButton(
                $this->lng->txt("cntr_text_media_editor"),
                $this->ctrl->getLinkTargetByClass("ilContainerStartObjectsPageGUI", "edit")
            );
        }

        $table = new ilContainerStartObjectsTableGUI($this, 'listStructure', $this->start_object);
        $this->tpl->setContent($table->getHTML());
    }

    protected function saveSortingObject(): void
    {
        $pos = $this->request->getStartObjPositions();
        if (is_array($pos)) {
            asort($pos);
            $counter = 0;
            foreach (array_keys($pos) as $start_id) {
                $counter += 10;
                $this->start_object->setObjectPos($start_id, $counter);
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('cntr_saved_sorting'), true);
        }

        $this->ctrl->redirect($this, "listStructure");
    }

    protected function askDeleteStarterObject(): void
    {
        if (count($this->request->getStartObjIds()) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, "listStructure");
        }

        $this->checkPermission('write');
        $this->setTabs();

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this, "listStructure"));
        $cgui->setHeaderText($this->lng->txt("crs_starter_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "listStructure");
        $cgui->setConfirm($this->lng->txt("remove"), "deleteStarter");

        // list objects that should be deleted
        $all = $this->start_object->getStartObjects();
        foreach ($this->request->getStartObjIds() as $starter_id) {
            $obj_id = ilObject::_lookupObjId($all[$starter_id]["item_ref_id"]);
            $title = ilObject::_lookupTitle($obj_id);
            $icon = ilObject::_getIcon($obj_id, "tiny");
            $alt = $this->lng->txt('obj_' . ilObject::_lookupType($obj_id));
            $cgui->addItem("starter[]", (string) $starter_id, $title, $icon, $alt);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    protected function deleteStarterObject(): void
    {
        $this->checkPermission('write');

        if (count($this->request->getStartObjIds()) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
        } else {
            foreach ($this->request->getStartObjIds() as $starter_id) {
                $this->start_object->delete((int) $starter_id);
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_starter_deleted'), true);
        }

        $this->ctrl->redirect($this, "listStructure");
    }

    protected function selectStarterObject(): void
    {
        $this->checkPermission('write');
        $this->setTabs();

        $table = new ilContainerStartObjectsTableGUI($this, 'selectStarter', $this->start_object);
        $this->tpl->setContent($table->getHTML());
    }

    protected function addStarterObject(): void
    {
        $this->checkPermission('write');

        if (count($this->request->getStartObjIds()) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, "selectStarter");
        }

        $added = 0;
        foreach ($this->request->getStartObjIds() as $item_ref_id) {
            if (!$this->start_object->exists($item_ref_id)) {
                ++$added;
                $this->start_object->add($item_ref_id);
            }
        }
        if ($added) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_added_starters'), true);
            $this->ctrl->redirect($this, "listStructure");
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_starters_already_assigned'), true);
            $this->ctrl->redirect($this, "selectStarter");
        }
    }
}
