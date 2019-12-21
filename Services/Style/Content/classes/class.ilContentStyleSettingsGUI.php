<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings UI class for system styles
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilContentStyleSettingsGUI: ilObjStyleSheetGUI
 * @ingroup ServicesStyle
 */
class ilContentStyleSettingsGUI
{
    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ILIAS\DI\Container
     */
    protected $DIC;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * Constructor
     */
    public function __construct(ilObjStyleSettingsGUI $a_parent_gui)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();

        $this->parent_gui = $a_parent_gui;
        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ref_id = (int) $_GET["ref_id"];
        $this->obj_id = (int) $_GET["obj_id"];		// note that reference ID is the id of the style settings node and object ID may be a style sheet object ID

        
        include_once("./Services/Style/Content/classes/class.ilContentStyleSettings.php");
        $this->cs_settings = new ilContentStyleSettings();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("edit");

        switch ($next_class) {
            case "ilobjstylesheetgui":
                $this->ctrl->setReturn($this, "edit");
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheetGUI.php");
                $style_gui = new ilObjStyleSheetGUI("", $this->obj_id, false, false);
                $this->ctrl->forwardCommand($style_gui);
                break;

            default:
                $this->parent_gui->prepareOutput();
                if (in_array($cmd, array("edit", "delete", "toggleGlobalDefault", "toggleGlobalFixed", "setScope", "saveScope", "saveActiveStyles",
                    "createStyle", "moveLMStyles", "moveIndividualStyles", "deleteStyle", "cancelDelete", "confirmedDelete"))) {
                    $this->$cmd();
                } else {
                    die("Unknown command " . $cmd);
                }
        }
    }

    /**
     * Check permission
     *
     * @param string $a_perm permission(s)
     * @return bool
     * @throws ilObjectException
     */
    public function checkPermission($a_perm, $a_throw_exc = true)
    {
        if (!$this->rbacsystem->checkAccess($a_perm, $this->ref_id)) {
            if ($a_throw_exc) {
                include_once "Services/Object/exceptions/class.ilObjectException.php";
                throw new ilObjectException($this->lng->txt("permission_denied"));
            }
            return false;
        }
        return true;
    }

    /**
     * Create new style
     */
    public function createStyle()
    {
        $ilCtrl = $this->ctrl;

        //	$ilCtrl->setParameterByClass("ilobjstylesheetgui", "new_type", "sty");
        $ilCtrl->redirectByClass("ilobjstylesheetgui", "create");
    }

    /**
     * Show styles
     */
    public function edit()
    {
        $this->checkPermission("visible,read");

        // this may not be cool, if styles are organised as (independent) Service
        include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");

        $from_styles = $to_styles = $data = array();
        $styles = $this->cs_settings->getStyles();
        foreach ($styles as $style) {
            $style["active"] = ilObjStyleSheet::_lookupActive($style["id"]);
            $style["lm_nr"] = ilObjContentObject::_getNrOfAssignedLMs($style["id"]);
            $data[$style["title"] . ":" . $style["id"]]
                = $style;
            if ($style["lm_nr"] > 0) {
                $from_styles[$style["id"]] = $style["title"];
            }
            if ($style["active"] > 0) {
                $to_styles[$style["id"]] = $style["title"];
            }
        }

        // number of individual styles
        if ($fixed_style <= 0) {
            $data[-1] =
                array("title" => $this->lng->txt("sty_individual_styles"),
                    "id" => 0, "lm_nr" => ilObjContentObject::_getNrLMsIndividualStyles());
            $from_styles[-1] = $this->lng->txt("sty_individual_styles");
        }

        // number of default style (fallback default style)
        if ($default_style <= 0 && $fixed_style <= 0) {
            $data[0] =
                array("title" => $this->lng->txt("sty_default_style"),
                    "id" => 0, "lm_nr" => ilObjContentObject::_getNrLMsNoStyle());
            $from_styles[0] = $this->lng->txt("sty_default_style");
            $to_styles[0] = $this->lng->txt("sty_default_style");
        }

        if ($this->checkPermission("sty_write_content", false)) {
            $this->toolbar->addButton(
                $this->lng->txt("sty_add_content_style"),
                $this->ctrl->getLinkTarget($this, "createStyle")
            );
            $this->toolbar->addSeparator();
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");

            // from styles selector
            $si = new ilSelectInputGUI($this->lng->txt("sty_move_lm_styles") . ": " . $this->lng->txt("sty_from"), "from_style");
            $si->setOptions($from_styles);
            $this->toolbar->addInputItem($si, true);

            // from styles selector
            $si = new ilSelectInputGUI($this->lng->txt("sty_to"), "to_style");
            $si->setOptions($to_styles);
            $this->toolbar->addInputItem($si, true);
            $this->toolbar->addFormButton($this->lng->txt("sty_move_style"), "moveLMStyles");

            $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        }

        include_once("./Services/Style/Content/classes/class.ilContentStylesTableGUI.php");
        $table = new ilContentStylesTableGUI($this, "edit", $data, $this->cs_settings);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * move learning modules from one style to another
     */
    public function moveLMStyles()
    {
        $this->checkPermission("sty_write_content");

        if ($_POST["from_style"] == -1) {
            $this->confirmDeleteIndividualStyles();
            return;
        }

        include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
        ilObjContentObject::_moveLMStyles($_POST["from_style"], $_POST["to_style"]);
        $this->ctrl->redirect($this, "edit");
    }


    /**
     * move all learning modules with individual styles to new style
     */
    public function moveIndividualStyles()
    {
        $this->checkPermission("sty_write_content");

        include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
        ilObjContentObject::_moveLMStyles(-1, $_GET["to_style"]);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     *
     */
    public function confirmDeleteIndividualStyles()
    {
        $this->checkPermission("sty_write_content");

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

        $this->ctrl->setParameter($this, "to_style", $_POST["to_style"]);

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("sty_confirm_del_ind_styles") . ": " .
            sprintf(
                $this->lng->txt("sty_confirm_del_ind_styles_desc"),
                ilObject::_lookupTitle($_POST["to_style"])
            ));
        $cgui->setCancel($this->lng->txt("cancel"), "edit");
        $cgui->setConfirm($this->lng->txt("ok"), "moveIndividualStyles");
        $this->tpl->setContent($cgui->getHTML());
    }



    /**
     * display deletion confirmation screen
     */
    public function deleteStyle()
    {
        $this->checkPermission("sty_write_content");

        if (!isset($_POST["id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "edit");
        }

        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");

        foreach ($_POST["id"] as $id) {
            $caption =  ilUtil::getImageTagByType("sty", $this->tpl->tplPath) .
                " " . ilObject::_lookupTitle($id);

            $cgui->addItem("id[]", $id, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());
    }


    /**
     * delete selected style objects
     */
    public function confirmedDelete()
    {
        $this->checkPermission("sty_write_content");

        foreach ($_POST["id"] as $id) {
            include_once("./Services/Style/Content/classes/class.ilContentStyleSettings.php");
            $set = new ilContentStyleSettings();
            $set->removeStyle($id);
            $set->update();

            $style_obj = ilObjectFactory::getInstanceByObjId($id);
            $style_obj->delete();
        }

        $this->ctrl->redirect($this, "edit");
    }


    /**
     * Toggle global default style
     */
    public function toggleGlobalDefault()
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;

        $this->checkPermission("sty_write_content");

        if ($_GET["id"] > 0) {
            $ilSetting->delete("fixed_content_style_id");
            $def_style = $ilSetting->get("default_content_style_id");

            if ($def_style != $_GET["id"]) {
                $ilSetting->set("default_content_style_id", (int) $_GET["id"]);
            } else {
                $ilSetting->delete("default_content_style_id");
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", "", false, false));
    }

    /**
     * Toggle global fixed style
     */
    public function toggleGlobalFixed()
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;

        $this->checkPermission("sty_write_content");

        if ($_GET["id"] > 0) {
            $ilSetting->delete("default_content_style_id");
            $fixed_style = $ilSetting->get("fixed_content_style_id");
            if ($fixed_style == (int) $_GET["id"]) {
                $ilSetting->delete("fixed_content_style_id");
            } else {
                $ilSetting->set("fixed_content_style_id", (int) $_GET["id"]);
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", "", false, false));
    }


    /**
     * Save active styles
     */
    public function saveActiveStyles()
    {
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $styles = $this->cs_settings->getStyles();
        foreach ($styles as $style) {
            if ($_POST["std_" . $style["id"]] == 1) {
                ilObjStyleSheet::_writeActive((int) $style["id"], 1);
            } else {
                ilObjStyleSheet::_writeActive((int) $style["id"], 0);
            }
        }
        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", "", false, false));
    }

    /**
     * show possible action (form buttons)
     *
     * @param	boolean
     * @access	public
     */
    public function showActions($with_subobjects = false)
    {

        // delete
        $this->tpl->setCurrentBlock("tbl_action_btn");
        $this->tpl->setVariable("BTN_NAME", "deleteStyle");
        $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
        $this->tpl->parseCurrentBlock();

        // set global default
        $this->tpl->setCurrentBlock("tbl_action_btn");
        $this->tpl->setVariable("BTN_NAME", "toggleGlobalDefault");
        $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("toggleGlobalDefault"));
        $this->tpl->parseCurrentBlock();

        // set global default
        $this->tpl->setCurrentBlock("tbl_action_btn");
        $this->tpl->setVariable("BTN_NAME", "toggleGlobalFixed");
        $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("toggleGlobalFixed"));
        $this->tpl->parseCurrentBlock();

        // set global default
        $this->tpl->setCurrentBlock("tbl_action_btn");
        $this->tpl->setVariable("BTN_NAME", "setScope");
        $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("sty_set_scope"));
        $this->tpl->parseCurrentBlock();

        // save active styles
        $this->tpl->setCurrentBlock("tbl_action_btn");
        $this->tpl->setVariable("BTN_NAME", "saveActiveStyles");
        $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("sty_save_active_styles"));
        $this->tpl->parseCurrentBlock();

        if ($with_subobjects === true) {
            $this->showPossibleSubObjects();
        }

        $this->tpl->setCurrentBlock("tbl_action_row");
        $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
        $this->tpl->parseCurrentBlock();
    }

    /**
     * cancel deletion of object
     *
     * @access	public
     */
    public function cancelDelete()
    {
        ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Set scope
     */
    public function setScope()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("sty_write_content");

        $ilCtrl->saveParameter($this, "id");
        include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
        $exp = new ilRepositorySelectorExplorerGUI(
            $this,
            "setScope",
            $this,
            "saveScope",
            "cat"
        );
        $exp->setTypeWhiteList(array("root", "cat"));
        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }

    /**
     * Save scope for style
     */
    public function saveScope()
    {
        $tree = $this->tree;

        $this->checkPermission("sty_write_content");

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        if ($_GET["cat"] == $tree->readRootId()) {
            $_GET["cat"] = "";
        }
        ilObjStyleSheet::_writeScope($_GET["id"], $_GET["cat"]);

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", "", false, false));
    }
}
