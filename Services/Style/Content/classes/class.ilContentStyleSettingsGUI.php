<?php declare(strict_types=1);

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

use ILIAS\Style\Content\StandardGUIRequest;

/**
 * Settings UI class for system styles
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilContentStyleSettingsGUI: ilObjStyleSheetGUI
 */
class ilContentStyleSettingsGUI
{
    protected ilContentStyleSettings $cs_settings;
    protected ilObjStyleSettingsGUI $parent_gui;
    protected int $obj_id;
    protected StandardGUIRequest $request;
    protected ilSetting $settings;
    protected ilTree $tree;
    protected ilCtrl $ctrl;
    protected ilRbacSystem $rbacsystem;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ILIAS\DI\Container $DIC;
    protected int $ref_id;

    public function __construct(ilObjStyleSettingsGUI $a_parent_gui)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();

        $this->parent_gui = $a_parent_gui;
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->contentStyle()
            ->internal()
            ->gui()
            ->standardRequest();


        $this->ref_id = $this->request->getRefId();
        $this->obj_id = $this->request->getObjId();		// note that reference ID is the id of the style settings node and object ID may be a style sheet object ID

        $this->cs_settings = new ilContentStyleSettings();
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("edit");

        switch ($next_class) {
            case "ilobjstylesheetgui":
                $this->ctrl->setReturn($this, "edit");
                $style_gui = new ilObjStyleSheetGUI("", $this->obj_id, false);
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
     * @throws ilObjectException
     */
    public function checkPermission(string $a_perm, bool $a_throw_exc = true) : bool
    {
        if (!$this->rbacsystem->checkAccess($a_perm, $this->ref_id)) {
            if ($a_throw_exc) {
                throw new ilObjectException($this->lng->txt("permission_denied"));
            }
            return false;
        }
        return true;
    }

    public function createStyle() : void
    {
        $ilCtrl = $this->ctrl;

        //	$ilCtrl->setParameterByClass("ilobjstylesheetgui", "new_type", "sty");
        $ilCtrl->redirectByClass("ilobjstylesheetgui", "create");
    }

    /**
     * List styles
     */
    public function edit() : void
    {
        $this->checkPermission("visible,read");

        // @todo: check these, they are checked later, but never (ILIAS 6) set
        $fixed_style = 0;
        $default_style = 0;

        // this may not be cool, if styles are organised as (independent) Service
        $from_styles = $to_styles = $data = array();
        $styles = $this->cs_settings->getStyles();
        foreach ($styles as $style) {
            $style["active"] = ilObjStyleSheet::_lookupActive((int) $style["id"]);
            $style["lm_nr"] = ilObjContentObject::_getNrOfAssignedLMs((int) $style["id"]);
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

        $table = new ilContentStylesTableGUI($this, "edit", $data);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * move learning modules from one style to another
     */
    public function moveLMStyles() : void
    {
        $this->checkPermission("sty_write_content");

        if ($this->request->getFromStyleId() == -1) {
            $this->confirmDeleteIndividualStyles();
            return;
        }

        ilObjContentObject::_moveLMStyles(
            $this->request->getFromStyleId(),
            $this->request->getToStyleId()
        );
        $this->ctrl->redirect($this, "edit");
    }


    /**
     * move all learning modules with individual styles to new style
     */
    public function moveIndividualStyles() : void
    {
        $this->checkPermission("sty_write_content");

        ilObjContentObject::_moveLMStyles(-1, $this->request->getToStyleId());
        $this->ctrl->redirect($this, "edit");
    }

    public function confirmDeleteIndividualStyles() : void
    {
        $this->checkPermission("sty_write_content");


        $this->ctrl->setParameter($this, "to_style", $this->request->getToStyleId());

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("sty_confirm_del_ind_styles") . ": " .
            sprintf(
                $this->lng->txt("sty_confirm_del_ind_styles_desc"),
                ilObject::_lookupTitle($this->request->getToStyleId())
            ));
        $cgui->setCancel($this->lng->txt("cancel"), "edit");
        $cgui->setConfirm($this->lng->txt("ok"), "moveIndividualStyles");
        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * display deletion confirmation screen
     */
    public function deleteStyle() : void
    {
        $this->checkPermission("sty_write_content");

        $ids = $this->request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "edit");
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");

        foreach ($ids as $id) {
            $caption = ilObject::_lookupTitle($id);

            $cgui->addItem("id[]", $id, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());
    }


    /**
     * delete selected style objects
     */
    public function confirmedDelete() : void
    {
        $this->checkPermission("sty_write_content");

        $ids = $this->request->getIds();
        foreach ($ids as $id) {
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
    public function toggleGlobalDefault() : void
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;

        $this->checkPermission("sty_write_content");

        if ($this->request->getId() > 0) {
            $ilSetting->delete("fixed_content_style_id");
            $def_style = $ilSetting->get("default_content_style_id");

            if ($def_style != $this->request->getId()) {
                $ilSetting->set("default_content_style_id", (string) $this->request->getId());
            } else {
                $ilSetting->delete("default_content_style_id");
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", ""));
    }

    /**
     * Toggle global fixed style
     */
    public function toggleGlobalFixed() : void
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;

        $this->checkPermission("sty_write_content");

        if ($this->request->getId() > 0) {
            $ilSetting->delete("default_content_style_id");
            $fixed_style = $ilSetting->get("fixed_content_style_id");
            if ($fixed_style == $this->request->getId()) {
                $ilSetting->delete("fixed_content_style_id");
            } else {
                $ilSetting->set("fixed_content_style_id", (string) $this->request->getId());
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", ""));
    }

    public function saveActiveStyles() : void
    {
        $styles = $this->cs_settings->getStyles();
        foreach ($styles as $style) {
            if ($this->request->getSelectedStandard($style["id"]) == 1) {
                ilObjStyleSheet::_writeActive((int) $style["id"], true);
            } else {
                ilObjStyleSheet::_writeActive((int) $style["id"], false);
            }
        }
        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", ""));
    }

    /**
     * show possible action (form buttons)
     */
    public function showActions(bool $with_subobjects = false) : void
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

        $this->tpl->setCurrentBlock("tbl_action_row");
        $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
        $this->tpl->parseCurrentBlock();
    }

    public function cancelDelete() : void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "edit");
    }

    public function setScope() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("sty_write_content");

        $ilCtrl->saveParameter($this, "id");
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

    public function saveScope() : void
    {
        $tree = $this->tree;

        $this->checkPermission("sty_write_content");

        $cat_id = $this->request->getCatId();
        if ($cat_id == $tree->readRootId()) {
            $cat_id = 0;
        }

        ilObjStyleSheet::_writeScope($this->request->getId(), $cat_id);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

        ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit", ""));
    }
}
