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

use ILIAS\COPage\PC\EditGUIRequest;

/**
 * User interface class for page content map editor
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCIIMTriggerEditorGUI: ilInternalLinkGUI
 */
class ilPCIIMTriggerEditorGUI extends ilPCImageMapEditorGUI
{
    public function __construct(
        ilPCInteractiveImage $a_content_obj,
        ilPageObject $a_page,
        EditGUIRequest $request
    ) {
        iljQueryUtil::initjQueryUI();
        parent::__construct($a_content_obj, $a_page, $request);

        $this->main_tpl->addJavaScript("./Services/COPage/js/ilCOPagePres.js");
        $this->main_tpl->addJavaScript("./Services/COPage/js/ilCOPagePCInteractiveImage.js");

        ilAccordionGUI::addJavaScript();
        ilAccordionGUI::addCss();
    }

    public function getParentNodeName(): string
    {
        return "InteractiveImage";
    }

    public function getEditorTitle(): string
    {
        $lng = $this->lng;

        return $lng->txt("cont_pc_iim");
    }

    /**
     * Get trigger table
     */
    public function getImageMapTableHTML(): string
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;


        $ilToolbar->addText($lng->txt("cont_drag_element_click_save"));
        $ilToolbar->setId("drag_toolbar");
        $ilToolbar->setHidden(true);
        $ilToolbar->addButton($lng->txt("save"), "#", "", null, "", "save_pos_button");

        $ilToolbar->addButton(
            $lng->txt("cancel"),
            $ilCtrl->getLinkTarget($this, "editMapAreas")
        );

        $image_map_table = new ilPCIIMTriggerTableGUI(
            $this,
            "editMapAreas",
            $this->content_obj,
            $this->getParentNodeName()
        );
        return $image_map_table->getHTML();
    }

    public function getToolbar(): ilToolbarGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // toolbar
        $tb = new ilToolbarGUI();
        $tb->setFormAction($ilCtrl->getFormAction($this));
        $options = array(
            "Rect" => $lng->txt("cont_Rect"),
            "Circle" => $lng->txt("cont_Circle"),
            "Poly" => $lng->txt("cont_Poly"),
            "Marker" => $lng->txt("cont_marker")
            );
        $si = new ilSelectInputGUI($lng->txt("cont_trigger_area"), "shape");
        $si->setOptions($options);
        $tb->addInputItem($si, true);
        $tb->addFormButton($lng->txt("add"), "addNewArea");

        return $tb;
    }

    public function addNewArea(): string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->edit_request->getString("shape") == "Marker") {
            $this->content_obj->addTriggerMarker();
            $this->page->update();
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_saved_map_data"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        } else {
            return parent::addNewArea();
        }
        return "";
    }

    /**
     * Init area editing form.
     */
    public function initAreaEditingForm(
        string $a_edit_property
    ): ilPropertyFormGUI {
        $lng = $this->lng;
        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);

        // name
        if ($a_edit_property != "link" && $a_edit_property != "shape") {
            $ti = new ilTextInputGUI($lng->txt("cont_name"), "area_name");
            $ti->setMaxLength(200);
            $ti->setSize(20);
            //$ti->setRequired(true);
            $form->addItem($ti);
        }

        // save and cancel commands
        if ($a_edit_property == "") {
            $form->setTitle($lng->txt("cont_new_trigger_area"));
        } else {
            $form->setTitle($lng->txt("cont_new_area"));
        }
        $form->addCommandButton("saveArea", $lng->txt("save"));

        return $form;
    }

    /**
     * Save new or updated map area
     */
    public function saveArea(): string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        switch ($this->map_repo->getMode()) {
            // save edited shape
            case "edit_shape":
                $this->std_alias_item->setShape(
                    $this->map->getAreaNr(),
                    $this->map->getAreaType(),
                    $this->map->getCoords()
                );
                $this->page->update();
                break;

                // save new area
            default:
                $area_type = $this->map->getAreaType();
                $coords = $this->map->getCoords();
                $this->content_obj->addTriggerArea(
                    $this->std_alias_item,
                    $area_type,
                    $coords,
                    $this->edit_request->getString("area_name")
                );
                $this->page->update();
                break;
        }

        //$this->initMapParameters();
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_saved_map_area"), true);
        $ilCtrl->redirect($this, "editMapAreas");
        return "";
    }

    /**
     * Update trigger
     */
    public function updateTrigger(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->content_obj->setTriggerOverlays(
            $this->edit_request->getStringArray("ov")
        );
        $this->content_obj->setTriggerPopups(
            $this->edit_request->getStringArray("pop")
        );
        $this->content_obj->setTriggerOverlayPositions(
            $this->edit_request->getStringArray("ovpos")
        );
        $this->content_obj->setTriggerMarkerPositions(
            $this->edit_request->getStringArray("markpos")
        );
        $this->content_obj->setTriggerPopupPositions(
            $this->edit_request->getStringArray("poppos")
        );
        $this->content_obj->setTriggerPopupSize(
            $this->edit_request->getStringArray("popsize")
        );
        $this->content_obj->setTriggerTitles(
            $this->edit_request->getStringArray("title")
        );
        $this->page->update();
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }

    /**
     * Confirm trigger deletion
     */
    public function confirmDeleteTrigger(): void
    {
        $ilCtrl = $this->ctrl;
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;

        $trigger = $this->edit_request->getStringArray("tr");
        $titles = $this->edit_request->getStringArray("title");

        if (count($trigger) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_delete_triggers"));
            $cgui->setCancel($lng->txt("cancel"), "editMapAreas");
            $cgui->setConfirm($lng->txt("delete"), "deleteTrigger");

            foreach ($trigger as $i) {
                $cgui->addItem("tr[]", $i, $titles[$i]);
            }
            $main_tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete trigger
     * @throws ilDateTimeException
     */
    public function deleteTrigger(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $trigger = $this->edit_request->getStringArray("tr");

        if (count($trigger) > 0) {
            foreach ($trigger as $tr_nr) {
                $this->content_obj->deleteTrigger($this->std_alias_item, $tr_nr);
            }
            $this->page->update();
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_areas_deleted"), true);
        }

        $ilCtrl->redirect($this, "editMapAreas");
    }

    /**
     * Get additional page xml (to be overwritten)
     */
    public function getAdditionalPageXML(): string
    {
        return $this->page->getMultimediaXML();
    }

    public function outputPostProcessing(string $a_output): string
    {
        // for question html get the page gui object
        $pg_gui = new ilPageObjectGUI($this->page->getParentType(), $this->page->getId());
        $pg_gui->setOutputMode(ilPageObjectGUI::PREVIEW);
        $pg_gui->getPageConfig()->setEnableSelfAssessment(true);
        //		$pg_gui->initSelfAssessmentRendering(true);		// todo: solve in other way
        $qhtml = $pg_gui->getQuestionHTML();
        if (is_array($qhtml)) {
            foreach ($qhtml as $k => $h) {
                $a_output = str_replace($pg_gui->pl_start . "Question;il__qst_$k" . $pg_gui->pl_end, " " . $h, $a_output);
            }
        }

        return $a_output;
    }
}
