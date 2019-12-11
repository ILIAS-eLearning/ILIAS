<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPCImageMapEditorGUI.php");

/**
* User interface class for page content map editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPCIIMTriggerEditorGUI: ilInternalLinkGUI
*
* @ingroup ServicesCOPage
*/
class ilPCIIMTriggerEditorGUI extends ilPCImageMapEditorGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
    * Constructor
    */
    public function __construct($a_content_obj, $a_page)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->ctrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];
        
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQueryUI();

        $tpl->addJavascript("./Services/COPage/js/ilCOPagePres.js");
        $tpl->addJavascript("./Services/COPage/js/ilCOPagePCInteractiveImage.js");

        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        ilAccordionGUI::addJavaScript();
        ilAccordionGUI::addCss();

        parent::__construct($a_content_obj, $a_page);
    }
    
    /**
     * Get parent node name
     *
     * @return string name of parent node
     */
    public function getParentNodeName()
    {
        return "InteractiveImage";
    }

    /**
     * Get editor title
     *
     * @return string editor title
     */
    public function getEditorTitle()
    {
        $lng = $this->lng;
        
        return $lng->txt("cont_pc_iim");
    }

    /**
     * Get trigger table
     */
    public function getImageMapTableHTML()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        

        $ilToolbar->addText($lng->txt("cont_drag_element_click_save"));
        $ilToolbar->setId("drag_toolbar");
        $ilToolbar->setHidden(true);
        $ilToolbar->addButton($lng->txt("save"), "#", "", "", "", "save_pos_button");
        
        $ilToolbar->addButton(
            $lng->txt("cancel"),
            $ilCtrl->getLinkTarget($this, "editMapAreas")
        );
        
        include_once("./Services/COPage/classes/class.ilPCIIMTriggerTableGUI.php");
        $image_map_table = new ilPCIIMTriggerTableGUI(
            $this,
            "editMapAreas",
            $this->content_obj,
            $this->getParentNodeName()
        );
        return $image_map_table->getHTML();
    }

    /**
     * Get toolbar
     *
     * @return object toolbar
     */
    public function getToolbar()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        // toolbar
        $tb = new ilToolbarGUI();
        $tb->setFormAction($ilCtrl->getFormAction($this));
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
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

    /**
     * Add new area
     *
     * @param
     * @return
     */
    public function addNewArea()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if ($_POST["shape"] == "Marker") {
            $this->content_obj->addTriggerMarker();
            $this->updated = $this->page->update();
            ilUtil::sendSuccess($lng->txt("cont_saved_map_data"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        } else {
            return parent::addNewArea();
        }
    }
    
    /**
     * Init area editing form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initAreaEditingForm($a_edit_property)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
            $form->addCommandButton("saveArea", $lng->txt("save"));
        } else {
            $form->setTitle($lng->txt("cont_new_area"));
            $form->addCommandButton("saveArea", $lng->txt("save"));
        }
                    
        return $form;
    }

    /**
     * Save new or updated map area
     */
    public function saveArea()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        switch ($_SESSION["il_map_edit_mode"]) {
            // save edited shape
            case "edit_shape":
                $this->std_alias_item->setShape(
                    $_SESSION["il_map_area_nr"],
                    $_SESSION["il_map_edit_area_type"],
                    $_SESSION["il_map_edit_coords"]
                );
                $this->updated = $this->page->update();
                break;

            // save new area
            default:
                $area_type = $_SESSION["il_map_edit_area_type"];
                $coords = $_SESSION["il_map_edit_coords"];
                $this->content_obj->addTriggerArea(
                    $this->std_alias_item,
                    $area_type,
                    $coords,
                    ilUtil::stripSlashes($_POST["area_name"]),
                    $link
                );
                $this->updated = $this->page->update();
                break;
        }

        //$this->initMapParameters();
        ilUtil::sendSuccess($lng->txt("cont_saved_map_area"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }
    
    /**
     * Update trigger
     */
    public function updateTrigger()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->content_obj->setTriggerOverlays($_POST["ov"]);
        $this->content_obj->setTriggerPopups($_POST["pop"]);
        $this->content_obj->setTriggerOverlayPositions($_POST["ovpos"]);
        $this->content_obj->setTriggerMarkerPositions($_POST["markpos"]);
        $this->content_obj->setTriggerPopupPositions($_POST["poppos"]);
        $this->content_obj->setTriggerPopupSize($_POST["popsize"]);
        $this->content_obj->setTriggerTitles($_POST["title"]);
        $this->updated = $this->page->update();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }
    
    /**
     * Confirm trigger deletion
     */
    public function confirmDeleteTrigger()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
            
        if (!is_array($_POST["tr"]) || count($_POST["tr"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        } else {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_delete_triggers"));
            $cgui->setCancel($lng->txt("cancel"), "editMapAreas");
            $cgui->setConfirm($lng->txt("delete"), "deleteTrigger");
            
            foreach ($_POST["tr"] as $i) {
                $cgui->addItem("tr[]", $i, $_POST["title"][$i]);
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete trigger
     */
    public function deleteTrigger()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (is_array($_POST["tr"]) && count($_POST["tr"]) > 0) {
            foreach ($_POST["tr"] as $tr_nr) {
                $this->content_obj->deleteTrigger($this->std_alias_item, $tr_nr);
            }
            $this->updated = $this->page->update();
            ilUtil::sendSuccess($lng->txt("cont_areas_deleted"), true);
        }

        $ilCtrl->redirect($this, "editMapAreas");
    }

    /**
     * Get additional page xml (to be overwritten)
     *
     * @return string additional page xml
     */
    public function getAdditionalPageXML()
    {
        return $this->page->getMultimediaXML();
    }
    
    /**
     * Output post processing
     *
     * @param
     * @return
     */
    public function outputPostProcessing($a_output)
    {

        // for question html get the page gui object
        include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
        $pg_gui = new ilPageObjectGUI($this->page->getParentType(), $this->page->getId());
        $pg_gui->setOutputMode(IL_PAGE_PREVIEW);
        $pg_gui->getPageConfig()->setEnableSelfAssessment(true);
        //		$pg_gui->initSelfAssessmentRendering(true);		// todo: solve in other way
        $qhtml = $pg_gui->getQuestionHTML();
        if (is_array($qhtml)) {
            foreach ($qhtml as $k => $h) {
                $a_output = str_replace($pg_gui->pl_start . "Question;il__qst_$k" . $pg_gui->pl_end, " " . $h, $a_output);
            }
        }
        //		$a_output = $pg_gui->selfAssessmentRendering($a_output);

        return $a_output;
    }
}
