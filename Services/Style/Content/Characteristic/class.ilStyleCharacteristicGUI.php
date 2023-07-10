<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\Style\Content;
use ILIAS\Style\Content\Access;
use ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * Characteristics UI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStyleCharacteristicGUI
{
    protected string $requested_char;
    protected Content\StandardGUIRequest $request;
    protected ilObjStyleSheet $object;
    protected string $super_type;
    protected string $current_tag;
    protected string $current_class;        // "XXX:hover"
    protected string $current_base_class;   // "XXX"
    protected string $current_pseudo_class;        // "hover"
    protected string $style_type;
    protected int $mq_id;
    protected Content\CharacteristicManager $manager;
    protected Access\StyleAccessManager $access_manager;
    protected Content\ImageManager $image_manager;
    protected Content\InternalGUIService $gui_service;
    protected Content\InternalDomainService $domain_service;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        Content\InternalDomainService $domain_service,
        Content\InternalGUIService $gui_service,
        ilObjStyleSheet $style_sheet,
        string $super_type,
        Access\StyleAccessManager $access_manager,
        Content\CharacteristicManager $manager,
        Content\ImageManager $image_manager
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->gui_service = $gui_service;
        $this->domain_service = $domain_service;
        $this->access_manager = $access_manager;
        $this->object = $style_sheet;
        $this->super_type = $super_type;
        $this->manager = $manager;
        $this->image_manager = $image_manager;
        $this->request = $gui_service->standardRequest();

        $cur = explode(".", $this->request->getTag());
        $this->current_tag = (string) $cur[0];
        $this->current_class = (string) ($cur[1] ?? "");

        $t = explode(":", $cur[1] ?? "");
        $this->current_base_class = (string) $t[0];
        $this->current_pseudo_class = (string) ($t[1] ?? "");

        $this->style_type = $this->request->getStyleType();
        $this->requested_char = $this->request->getCharacteristic();
        $this->mq_id = $this->request->getMediaQueryId();
    }

    protected function extractParametersOfTag(
        bool $a_custom = false
    ): array {
        $style = $this->object->getStyle();
        $parameters = array();
        foreach ($style as $tag) {
            foreach ($tag as $par) {
                if ($par["tag"] == $this->current_tag && $par["class"] == $this->current_class
                    && $par["type"] == $this->style_type && $this->mq_id == (int) $par["mq_id"]
                    && (int) $a_custom == (int) $par["custom"]) {
                    $parameters[$par["parameter"]] = $par["value"];
                }
            }
        }
        return $parameters;
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $ctrl = $this->gui_service->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();
        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "listCharacteristics", "addCharacteristic", "saveCharacteristic",
                    "deleteCharacteristicConfirmation", "cancelCharacteristicDeletion", "deleteCharacteristic",
                    "copyCharacteristics", "pasteCharacteristicsOverview", "pasteCharacteristics",
                    "pasteCharacteristicsWithinStyle", "pasteCharacteristicsFromOtherStyle",
                    "saveStatus", "setOutdated", "removeOutdated",
                    "editTagStyle", "refreshTagStyle", "updateTagStyle",
                    "editTagTitles", "saveTagTitles"])) {
                    $this->$cmd();
                }
        }
    }

    public function listCharacteristics(): void
    {
        $lng = $this->domain_service->lng();
        $ilTabs = $this->gui_service->tabs();
        $ilCtrl = $this->gui_service->ctrl();
        $ilToolbar = $this->gui_service->toolbar();
        $tpl = $this->gui_service->mainTemplate();

        $this->setListSubTabs();

        // output characteristics
        $chars = $this->object->getCharacteristics();

        $style_type = ($this->super_type != "")
            ? $this->super_type
            : "text_block";
        $ilCtrl->setParameter($this, "style_type", $style_type);
        $ilTabs->activateSubTab("sty_" . $style_type . "_char");

        // add new style?
        $all_super_types = ilObjStyleSheet::_getStyleSuperTypes();
        $subtypes = $all_super_types[$style_type];
        $expandable = false;
        foreach ($subtypes as $t) {
            if (ilObjStyleSheet::_isExpandable($t)) {
                $expandable = true;
            }
        }
        if ($expandable && $this->access_manager->checkWrite()) {
            $ilToolbar->addButton(
                $lng->txt("sty_add_characteristic"),
                $ilCtrl->getLinkTarget($this, "addCharacteristic")
            );
        }

        if ($this->manager->hasCopiedCharacteristics($style_type)) {
            if ($expandable) {
                $ilToolbar->addSeparator();
            }
            $ilToolbar->addButton(
                $lng->txt("sty_paste_style_classes"),
                $ilCtrl->getLinkTarget($this, "pasteCharacteristicsOverview")
            );
        }

        $table_gui = $this->gui_service->characteristic()->CharacteristicTableGUI(
            $this,
            "edit",
            $style_type,
            $this->object,
            $this->manager,
            $this->access_manager
        );

        $tpl->setContent($table_gui->getHTML());
    }

    public function setListSubTabs(): void
    {
        $lng = $this->domain_service->lng();
        $tabs = $this->gui_service->tabs();
        $ctrl = $this->gui_service->ctrl();

        $types = ilObjStyleSheet::_getStyleSuperTypes();

        foreach ($types as $super_type => $t) {
            // text block characteristics
            $ctrl->setParameter($this, "style_type", $super_type);
            $tabs->addSubTab(
                "sty_" . $super_type . "_char",
                $lng->txt("sty_" . $super_type . "_char"),
                $ctrl->getLinkTarget($this, "listCharacteristics")
            );
        }

        $ctrl->setParameter($this, "style_type", $this->style_type);
    }

    public function addCharacteristic(): void
    {
        $tpl = $this->gui_service->mainTemplate();

        $form = $this->initCharacteristicForm();
        $tpl->setContent($form->getHTML());
    }

    public function saveCharacteristic(): void
    {
        $ilCtrl = $this->gui_service->ctrl();
        $tpl = $this->gui_service->mainTemplate();
        $lng = $this->domain_service->lng();

        $form = $this->initCharacteristicForm();

        if ($form->checkInput()) {
            $new_characteristic = $form->getInput("new_characteristic");
            $type = $form->getInput("type");
            if ($this->object->characteristicExists($new_characteristic, $this->style_type)) {
                $char_input = $form->getItemByPostVar("new_characteristic");
                $char_input->setAlert($lng->txt("sty_characteristic_already_exists"));
            } else {
                $this->object->addCharacteristic($type, $new_characteristic);
                $this->main_tpl->setOnScreenMessage('info', $lng->txt("sty_added_characteristic"), true);
                $ilCtrl->setParameter(
                    $this,
                    "tag",
                    ilObjStyleSheet::_determineTag($type) . "." . $new_characteristic
                );
                $ilCtrl->setParameter($this, "style_type", $type);
                $ilCtrl->redirectByClass("ilstylecharacteristicgui", "editTagStyle");
            }
        }
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }

    public function deleteCharacteristicConfirmation(): void
    {
        $ilCtrl = $this->gui_service->ctrl();
        $tpl = $this->gui_service->mainTemplate();
        $lng = $this->domain_service->lng();

        //var_dump($_POST);

        $chars = $this->request->getCharacteristics();
        if (count($chars) == 0) {
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "edit");
        } else {
            // check whether there are any core style classes included
            $core_styles = ilObjStyleSheet::_getCoreStyles();
            foreach ($chars as $char) {
                if (!empty($core_styles[$char])) {
                    $this->deleteCoreCharMessage();
                    return;
                }
            }

            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("sty_confirm_char_deletion"));
            $cgui->setCancel($lng->txt("cancel"), "cancelCharacteristicDeletion");
            $cgui->setConfirm($lng->txt("delete"), "deleteCharacteristic");

            foreach ($chars as $char) {
                $char_comp = explode(".", $char);
                $cgui->addItem("char[]", $char, $char_comp[2]);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    // Message that appears, when user tries to delete core characteristics
    public function deleteCoreCharMessage(): void
    {
        $ilCtrl = $this->gui_service->ctrl();
        $tpl = $this->gui_service->mainTemplate();
        $lng = $this->domain_service->lng();

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this));


        $core_styles = ilObjStyleSheet::_getCoreStyles();
        $cnt = 0;

        $chars = $this->request->getCharacteristics();
        foreach ($chars as $char) {
            if (!empty($core_styles[$char])) {
                $cnt++;
                $char_comp = explode(".", $char);
                $cgui->addItem("", "", $char_comp[2]);
            } else {
                $cgui->addHiddenItem("char[]", $char);
            }
        }
        $all_core_styles = ($cnt == count($chars));

        if ($all_core_styles) {
            $cgui->setHeaderText($lng->txt("sty_all_styles_obligatory"));
            $cgui->setCancel($lng->txt("back"), "cancelCharacteristicDeletion");
        } else {
            $cgui->setHeaderText($lng->txt("sty_some_styles_obligatory_delete_rest"));
            $cgui->setCancel($lng->txt("cancel"), "cancelCharacteristicDeletion");
            $cgui->setConfirm($lng->txt("sty_delete_other_selected"), "deleteCharacteristicConfirmation");
        }

        $tpl->setContent($cgui->getHTML());
    }

    public function cancelCharacteristicDeletion(): void
    {
        $ilCtrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        $this->main_tpl->setOnScreenMessage('info', $lng->txt("action_aborted"), true);
        $ilCtrl->redirect($this, "listCharacteristics");
    }

    /**
     * Delete one or multiple style characteristic
     * @throws Content\ContentStyleNoPermissionException
     */
    public function deleteCharacteristic(): void
    {
        $ilCtrl = $this->gui_service->ctrl();

        $chars = $this->request->getCharacteristics();
        foreach ($chars as $char) {
            $char_comp = explode(".", $char);
            $type = $char_comp[0];
            $tag = $char_comp[1];
            $class = $char_comp[2];

            $this->manager->deleteCharacteristic(
                $type,
                $class
            );
        }

        $ilCtrl->redirect($this, "listCharacteristics");
    }

    /**
     * Init tag style editing form
     */
    public function initCharacteristicForm(): ilPropertyFormGUI
    {
        $lng = $this->domain_service->lng();
        $ilCtrl = $this->gui_service->ctrl();

        $form = new ilPropertyFormGUI();

        // title
        $txt_input = new ilRegExpInputGUI($lng->txt("title"), "new_characteristic");
        $txt_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
        $txt_input->setNoMatchMessage($lng->txt("sty_msg_characteristic_must_only_include") . " A-Z, a-z, 0-9");
        $txt_input->setRequired(true);
        $form->addItem($txt_input);

        // type
        $all_super_types = ilObjStyleSheet::_getStyleSuperTypes();
        $types = $all_super_types[$this->super_type];
        $exp_types = array();
        foreach ($types as $t) {
            if (ilObjStyleSheet::_isExpandable($t)) {
                $exp_types[$t] = $lng->txt("sty_type_" . $t);
            }
        }
        if (count($exp_types) > 1) {
            $type_input = new ilSelectInputGUI($lng->txt("sty_type"), "type");
            $type_input->setOptions($exp_types);
            $type_input->setValue(key($exp_types));
            $form->addItem($type_input);
        } elseif (count($exp_types) == 1) {
            $hid_input = new ilHiddenInputGUI("type");
            $hid_input->setValue(key($exp_types));
            $form->addItem($hid_input);
        }

        $form->setTitle($lng->txt("sty_add_characteristic"));
        $form->addCommandButton("saveCharacteristic", $lng->txt("save"));
        $form->addCommandButton("edit", $lng->txt("cancel"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    protected function setTabs(): void
    {
        $tabs = $this->gui_service->tabs();
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        $tabs->clearTargets();

        // back to upper context
        $tabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTargetByClass("ilobjstylesheetgui", "edit")
        );

        // parameters
        $ctrl->setParameter($this, "tag", $this->current_tag . "." . $this->current_base_class);
        $tabs->addTab(
            "parameters",
            $lng->txt("sty_parameters"),
            $ctrl->getLinkTarget($this, "editTagStyle")
        );

        // titles
        $tabs->addTab(
            "titles",
            $lng->txt("sty_titles"),
            $ctrl->getLinkTarget($this, "editTagTitles")
        );
    }

    protected function setParameterSubTabs(): void
    {
        $tabs = $this->gui_service->tabs();
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        $pc = $this->object->_getPseudoClasses($this->current_tag);
        if (count($pc) > 0) {
            // style classes
            $ctrl->setParameter($this, "tag", $this->current_tag . "." . $this->current_base_class);
            $tabs->addSubTab(
                "sty_tag_normal",
                $lng->txt("sty_tag_normal"),
                $ctrl->getLinkTarget($this, "editTagStyle")
            );
            if ($this->current_pseudo_class == "") {
                $tabs->activateSubTab("sty_tag_normal");
            }

            foreach ($pc as $p) {
                // style classes
                $ctrl->setParameter(
                    $this,
                    "tag",
                    $this->current_tag . "." . $this->current_base_class . ":" . $p
                );
                $tabs->addSubTab(
                    "sty_tag_" . $p,
                    ":" . $p,
                    $ctrl->getLinkTarget($this, "editTagStyle")
                );
                if ($this->current_pseudo_class == $p) {
                    $tabs->activateSubTab("sty_tag_" . $p);
                }
            }
            $ctrl->setParameter($this, "tag", $this->current_tag . "." . $this->current_base_class);
        }
    }

    protected function editTagStyle(): void
    {
        $ilToolbar = $this->gui_service->toolbar();
        $lng = $this->domain_service->lng();
        $ilCtrl = $this->gui_service->ctrl();
        $tabs = $this->gui_service->tabs();

        $this->setTabs();
        $this->setParameterSubTabs();
        $tabs->activateTab("parameters");

        // media query selector
        $mqs = $this->object->getMediaQueries();
        if (count($mqs) > 0) {
            //
            $options = array(
                "" => $lng->txt("sty_default"),
            );
            foreach ($mqs as $mq) {
                $options[$mq["id"]] = $mq["mquery"];
            }
            $si = new ilSelectInputGUI("@media", "mq_id");
            $si->setOptions($options);
            $si->setValue($this->mq_id);
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $ilToolbar->addFormButton($lng->txt("sty_switch"), "switchMQuery");
        }

        $form = $this->initTagStyleForm();
        $this->getValues($form);
        $this->outputTagStyleEditScreen($form);
    }

    /**
     * Init tag style editing form
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws ilCtrlException
     * @throws ilFormException
     */
    protected function initTagStyleForm(): ilPropertyFormGUI
    {
        $lng = $this->domain_service->lng();
        $ilCtrl = $this->gui_service->ctrl();

        $ilCtrl->saveParameter($this, array("mq_id"));

        $form_gui = new ilPropertyFormGUI();

        $avail_pars = $this->object->getAvailableParameters();
        $groups = $this->object->getStyleParameterGroups();

        // output select lists
        foreach ($groups as $k => $group) {
            // filter groups of properties that should only be
            // displayed with matching tag
            $filtered_groups = ilObjStyleSheet::_getFilteredGroups();
            if (isset($filtered_groups[$k]) && !in_array($this->current_tag, $filtered_groups[$k])) {
                continue;
            }

            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($lng->txt("sty_" . $k));
            $form_gui->addItem($sh);

            foreach ($group as $par) {
                $basepar = explode(".", $par);
                $basepar = $basepar[0];

                $var = str_replace("-", "_", $basepar);

                switch (ilObjStyleSheet::_getStyleParameterInputType($par)) {
                    case "select":
                        $sel_input = new ilSelectInputGUI($lng->txt("sty_" . $var), $basepar);
                        $options = array("" => "");
                        foreach ($avail_pars[$par] as $p) {
                            $options[$p] = $p;
                        }
                        $sel_input->setOptions($options);
                        $form_gui->addItem($sel_input);
                        break;

                    case "text":
                        $text_input = new ilTextInputGUI($lng->txt("sty_" . $var), $basepar);
                        $text_input->setMaxLength(200);
                        $text_input->setSize(20);
                        $form_gui->addItem($text_input);
                        break;

                    case "fontsize":
                        $fs_input = new ilFontSizeInputGUI($lng->txt("sty_" . $var), $basepar);
                        $form_gui->addItem($fs_input);
                        break;

                    case "numeric_no_perc":
                    case "numeric":
                        $num_input = new ilNumericStyleValueInputGUI($lng->txt("sty_" . $var), $basepar);
                        if (ilObjStyleSheet::_getStyleParameterInputType($par) == "numeric_no_perc") {
                            $num_input->setAllowPercentage(false);
                        }
                        $form_gui->addItem($num_input);
                        break;

                    case "percentage":
                        $per_input = new ilNumberInputGUI($lng->txt("sty_" . $var), $basepar);
                        $per_input->setMinValue(0);
                        $per_input->setMaxValue(100);
                        $per_input->setMaxLength(3);
                        $per_input->setSize(3);
                        $form_gui->addItem($per_input);
                        break;

                    case "color":
                        $col_input = new ilColorPickerInputGUI($lng->txt("sty_" . $var), $basepar);
                        $col_input->setDefaultColor("");
                        $col_input->setAcceptNamedColors(true);
                        $form_gui->addItem($col_input);
                        break;

                    case "trbl_numeric":
                        $num_input = new ilTRBLNumericStyleValueInputGUI($lng->txt("sty_" . $var), $basepar);
                        if (ilObjStyleSheet::_getStyleParameterInputType($par) == "trbl_numeric_no_perc") {
                            $num_input->setAllowPercentage(false);
                        }
                        $form_gui->addItem($num_input);
                        break;

                    case "border_width":
                        $bw_input = new ilTRBLBorderWidthInputGUI($lng->txt("sty_" . $var), $basepar);
                        $form_gui->addItem($bw_input);
                        break;

                    case "border_style":
                        $bw_input = new ilTRBLBorderStyleInputGUI($lng->txt("sty_" . $var), $basepar);
                        $form_gui->addItem($bw_input);
                        break;

                    case "trbl_color":
                        $col_input = new ilTRBLColorPickerInputGUI($lng->txt("sty_" . $var), $basepar);
                        $col_input->setAcceptNamedColors(true);
                        $form_gui->addItem($col_input);
                        break;

                    case "background_image":
                        $im_input = new ilBackgroundImageInputGUI($lng->txt("sty_" . $var), $basepar);
                        $images = array();
                        foreach ($this->image_manager->getImages() as $entry) {
                            $images[] = $entry->getFilename();
                        }
                        $im_input->setImages($images);
                        $im_input->setInfo($lng->txt("sty_bg_img_info"));
                        $form_gui->addItem($im_input);
                        break;

                    case "background_position":
                        $im_input = new ilBackgroundPositionInputGUI($lng->txt("sty_" . $var), $basepar);
                        $form_gui->addItem($im_input);
                        break;
                }
            }
        }

        // custom parameters
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("sty_custom"));
        $form_gui->addItem($sh);

        // custom parameters
        $ti = new ilTextInputGUI($lng->txt("sty_custom_par"), "custom_par");
        $ti->setMaxLength(300);
        $ti->setSize(80);
        $ti->setMulti(true);
        $ti->setInfo($lng->txt("sty_custom_par_info"));
        $form_gui->addItem($ti);


        // save and cancel commands
        $form_gui->addCommandButton("updateTagStyle", $lng->txt("save_return"));
        $form_gui->addCommandButton("refreshTagStyle", $lng->txt("save_refresh"));

        $form_gui->setFormAction($ilCtrl->getFormAction($this));
        return $form_gui;
    }

    /**
     * FORM: Get current values from persistent object.
     */
    protected function getValues(ilPropertyFormGUI $form): void
    {
        $cur_parameters = $this->extractParametersOfTag();
        $parameters = ilObjStyleSheet::_getStyleParameters();
        foreach ($parameters as $p => $v) {
            $filtered_groups = ilObjStyleSheet::_getFilteredGroups();
            if (isset($filtered_groups[$v["group"]]) && !in_array($this->current_tag, $filtered_groups[$v["group"]])) {
                continue;
            }
            $p = explode(".", $p);
            $p = $p[0];
            $input = $form->getItemByPostVar($p);
            switch ($v["input"]) {
                case "":
                    break;

                case "trbl_numeric":
                case "border_width":
                case "border_style":
                case "trbl_color":
                    $input->setAllValue($cur_parameters[$v["subpar"][0]] ?? "");
                    $input->setTopValue($cur_parameters[$v["subpar"][1]] ?? "");
                    $input->setRightValue($cur_parameters[$v["subpar"][2]] ?? "");
                    $input->setBottomValue($cur_parameters[$v["subpar"][3]] ?? "");
                    $input->setLeftValue($cur_parameters[$v["subpar"][4]] ?? "");
                    break;

                default:
                    $input->setValue($cur_parameters[$p] ?? "");
                    break;
            }
        }

        $cust_parameters = $this->extractParametersOfTag(true);
        $vals = array();
        foreach ($cust_parameters as $k => $c) {
            $vals[] = $k . ": " . $c;
        }
        $input = $form->getItemByPostVar("custom_par");
        $input->setValue($vals);
    }

    protected function outputTagStyleEditScreen(ilPropertyFormGUI $form): void
    {
        $tpl = $this->gui_service->mainTemplate();

        // set style sheet
        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->object->getId())
        );

        $ts_tpl = new ilTemplate("tpl.style_tag_edit.html", true, true, "Services/Style/Content");

        $ts_tpl->setVariable(
            "EXAMPLE",
            ilObjStyleSheetGUI::getStyleExampleHTML($this->style_type, $this->current_class)
        );

        $ts_tpl->setVariable(
            "FORM",
            $form->getHTML()
        );

        $this->setTitle();

        $tpl->setContent($ts_tpl->get());
    }

    protected function setTitle(): void
    {
        $tpl = $this->gui_service->mainTemplate();
        $lng = $this->domain_service->lng();
        $tpl->setTitle($this->current_class . " (" . $lng->txt("sty_type_" . $this->style_type) . ")");
    }

    protected function refreshTagStyle(): void
    {
        $ilCtrl = $this->gui_service->ctrl();

        $form = $this->initTagStyleForm();

        if ($form->checkInput()) {
            $this->saveTagStyle($form);
            $ilCtrl->redirect($this, "editTagStyle");
        } else {
            $form->setValuesByPost();
            $this->outputTagStyleEditScreen($form);
        }
    }

    protected function updateTagStyle(): void
    {
        $ilCtrl = $this->gui_service->ctrl();

        $form = $this->initTagStyleForm();
        if ($form->checkInput()) {
            $this->saveTagStyle($form);
            $ilCtrl->redirectByClass("ilobjstylesheetgui", "edit");
        } else {
            $form->setValuesByPost();
            $this->outputTagStyleEditScreen($form);
        }
    }

    protected function saveTagStyle(ilPropertyFormGUI $form): void
    {
        $avail_pars = ilObjStyleSheet::_getStyleParameters($this->current_tag);
        foreach ($avail_pars as $par => $v) {
            $var = str_replace("-", "_", $par);
            $basepar_arr = explode(".", $par);
            $basepar = $basepar_arr[0];
            if (($basepar_arr[1] ?? "") != "" && $basepar_arr[1] != $this->current_tag) {
                continue;
            }

            switch ($v["input"]) {
                case "fontsize":
                case "numeric_no_perc":
                case "numeric":
                case "background_image":
                case "background_position":
                    $in = $form->getItemByPostVar($basepar);
                    $this->writeStylePar($basepar, (string) $in->getValue());
                    break;

                case "color":
                    $color = trim($form->getInput($basepar));
                    if ($color != "" && trim(substr($color, 0, 1)) != "!") {
                        $color = "#" . $color;
                    }
                    $this->writeStylePar($basepar, $color);
                    break;

                case "trbl_numeric":
                case "border_width":
                case "border_style":
                    $in = $form->getItemByPostVar($basepar);
                    $this->writeStylePar($v["subpar"][0], (string) $in->getAllValue());
                    $this->writeStylePar($v["subpar"][1], (string) $in->getTopValue());
                    $this->writeStylePar($v["subpar"][2], (string) $in->getRightValue());
                    $this->writeStylePar($v["subpar"][3], (string) $in->getBottomValue());
                    $this->writeStylePar($v["subpar"][4], (string) $in->getLeftValue());
                    break;

                case "trbl_color":
                    $in = $form->getItemByPostVar($basepar);
                    $tblr_p = array(0 => "getAllValue", 1 => "getTopValue", 2 => "getRightValue",
                                    3 => "getBottomValue", 4 => "getLeftValue");
                    foreach ($tblr_p as $k => $func) {
                        $val = trim($in->$func());
                        $val = (($in->getAcceptNamedColors() && substr($val, 0, 1) == "!")
                            || $val == "")
                            ? $val
                            : "#" . $val;
                        $this->writeStylePar($v["subpar"][$k], $val);
                    }
                    break;

                default:
                    $this->writeStylePar($basepar, (string) $form->getInput($basepar));
                    break;
            }
        }

        // write custom parameter
        $this->object->deleteCustomStylePars(
            $this->current_tag,
            $this->current_class,
            $this->style_type,
            $this->mq_id
        );
        $custom_par = $form->getInput("custom_par");
        foreach ($custom_par as $cpar) {
            $par_arr = explode(":", $cpar);
            if (count($par_arr) == 2) {
                $par = trim($par_arr[0]);
                $val = trim(str_replace(";", "", $par_arr[1]));
                $this->writeStylePar($par, $val, true);
            }
        }

        $this->object->update();
    }

    /**
     * Write style parameter
     */
    protected function writeStylePar(string $par, string $value, bool $a_custom = false): void
    {
        if ($this->style_type == "") {
            return;
        }

        $this->manager->replaceParameter(
            $this->current_tag,
            $this->current_class,
            $par,
            $value,
            $this->style_type,
            $this->mq_id,
            $a_custom
        );
    }

    protected function editTagTitles(): void
    {
        $this->setTabs();
        $tpl = $this->gui_service->mainTemplate();
        $ui = $this->gui_service->ui();
        $tabs = $this->gui_service->tabs();

        $form = $this->getTagTitlesForm();
        $this->setTitle();
        $tabs->activateTab("titles");
        $tpl->setContent($ui->renderer()->render($form));
    }

    /**
     * @throws ilCtrlException
     */
    protected function getTagTitlesForm(): Standard
    {
        $ui = $this->gui_service->ui();
        $f = $ui->factory();
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();
        $fields = [];

        $lng->loadLanguageModule("meta");

        $characteristic = $this->manager->getByKey(
            $this->style_type,
            $this->current_base_class
        );
        $titles = $characteristic->getTitles();

        foreach ($lng->getInstalledLanguages() as $l) {
            $fields["title_" . $l] = $f->input()->field()->text($lng->txt("title") .
            " - " . $lng->txt("meta_l_" . $l))
                                 ->withRequired(false)
                                 ->withValue($titles[$l] ?? "");
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("sty_titles"));

        $form_action = $ctrl->getLinkTarget($this, "saveTagTitles");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    public function saveTagTitles(): void
    {
        $request = $this->gui_service->http()->request();
        $form = $this->getTagTitlesForm();
        $lng = $this->domain_service->lng();
        $ctrl = $this->gui_service->ctrl();
        $manager = $this->manager;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (isset($data["sec"])) {
                $d = $data["sec"];
                $titles = [];
                foreach ($lng->getInstalledLanguages() as $l) {
                    $titles[$l] = $d["title_" . $l];
                }

                $manager->saveTitles(
                    $this->style_type,
                    $this->current_base_class,
                    $titles
                );

                $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            }
        }
        $ctrl->redirect($this, "editTagTitles");
    }

    /**
     * Save hide status for characteristics
     * @throws Content\ContentStyleNoPermissionException
     * @throws ilCtrlException
     */
    public function saveStatus(): void
    {
        $ilCtrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        $all_chars = $this->request->getAllCharacteristics();
        $hidden = $this->request->getHidden();
        $order = $this->request->getOrder();

        // save hide status
        foreach ($all_chars as $char) {
            $ca = explode(".", $char);
            $this->manager->saveHidden(
                $ca[0],
                $ca[2],
                (in_array($char, $hidden))
            );
        }

        // save order
        if (count($order) > 0) {
            $order_by_type = [];
            foreach ($order as $char => $order_nr) {
                $ca = explode(".", $char);
                $order_by_type[$ca[0]][$ca[2]] = $order_nr;
            }
            foreach ($order_by_type as $type => $order_nrs) {
                $this->manager->saveOrderNrs(
                    $type,
                    $order_nrs
                );
            }
        }

        $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listCharacteristics");
    }

    protected function setOutdated(): void
    {
        $lng = $this->domain_service->lng();
        $ctrl = $this->gui_service->ctrl();

        $chars = $this->request->getCharacteristics();
        if (count($chars) > 0) {
            foreach ($chars as $c) {
                $c_parts = explode(".", $c);
                if (!ilObjStyleSheet::isCoreStyle($c_parts[0], $c_parts[2])) {
                    $this->manager->saveOutdated(
                        $c_parts[0],
                        $c_parts[2],
                        true
                    );
                    $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
                }
            }
        } else {
            $this->manager->saveOutdated(
                $this->style_type,
                $this->requested_char,
                true
            );
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        }

        $ctrl->redirect($this, "listCharacteristics");
    }

    protected function removeOutdated(): void
    {
        $lng = $this->domain_service->lng();
        $ctrl = $this->gui_service->ctrl();
        $chars = $this->request->getCharacteristics();

        if (count($chars) > 0) {
            foreach ($chars as $c) {
                $c_parts = explode(".", $c);
                if (!ilObjStyleSheet::isCoreStyle($c_parts[0], $c_parts[2])) {
                    $this->manager->saveOutdated(
                        $c_parts[0],
                        $c_parts[2],
                        false
                    );
                    $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
                }
            }
        } else {
            $this->manager->saveOutdated(
                $this->style_type,
                $this->requested_char,
                false
            );
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        }

        $ctrl->redirect($this, "listCharacteristics");
    }

    public function copyCharacteristics(): void
    {
        $ilCtrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        $chars = $this->request->getCharacteristics();

        if (count($chars) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
        } else {
            $this->manager->setCopyCharacteristics(
                $this->style_type,
                $chars
            );
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("sty_copied_please_select_target"), true);
        }
        $ilCtrl->redirect($this, "listCharacteristics");
    }

    public function pasteCharacteristicsOverview(): void
    {
        $tpl = $this->gui_service->mainTemplate();
        $ilTabs = $this->gui_service->tabs();
        $ui = $this->gui_service->ui();

        $ilTabs->clearTargets();

        if ($this->manager->getCopyCharacteristicStyleId() ==
        $this->object->getId()) {
            $form = $this->getPasteWithinStyleForm();
        } else {
            $form = $this->getPasteFromOtherStyleForm();
        }
        $tpl->setContent($ui->renderer()->render($form));
    }

    /**
     * Init past within style form
     * @throws ilCtrlException
     */
    public function getPasteWithinStyleForm(): Standard
    {
        $ui = $this->gui_service->ui();
        $f = $ui->factory();
        $ctrl = $this->gui_service->ctrl();

        $sections = [];
        foreach ($this->manager->getCopyCharacteristics() as $char) {
            // section
            $char_text = explode(".", $char);
            $sections[$char] = $f->input()->field()->section(
                $this->getCharacterTitleFormFields($char),
                $char_text[2]
            );
        }

        $form_action = $ctrl->getLinkTarget($this, "pasteCharacteristicsWithinStyle");
        return $f->input()->container()->form()->standard($form_action, $sections);
    }

    /**
     * Init past from other style form
     * @throws ilCtrlException
     */
    public function getPasteFromOtherStyleForm(): Standard
    {
        $ui = $this->gui_service->ui();
        $lng = $this->domain_service->lng();
        $f = $ui->factory();
        $ctrl = $this->gui_service->ctrl();

        $sections = [];
        $fields = [];
        foreach ($this->manager->getCopyCharacteristics() as $char) {
            $char_text = explode(".", $char);
            $options = [];
            foreach ($this->manager->getByType($char_text[0]) as $c) {
                $options[$c->getCharacteristic()] = $c->getCharacteristic();
            }
            $group1 = $f->input()->field()->group(
                $this->getCharacterTitleFormFields($char),
                $lng->txt("sty_create_new_class")
            );
            $group2 = $f->input()->field()->group(
                [
                    "overwrite_class" => $f->input()->field()->select(
                        $lng->txt("sty_class"),
                        $options
                    )->withRequired(true)
                ],
                $lng->txt("sty_overwrite_existing_class")
            );
            $fields[$char] = $f->input()->field()->switchableGroup(
                [
                    "new_" . $char => $group1,
                    "overwrite_" . $char => $group2
                ],
                $char_text[2]
            )->withValue("new_" . $char);
        }
        $sections["sec"] = $f->input()->field()->section(
            $fields,
            $lng->txt("sty_paste_chars")
        );

        $form_action = $ctrl->getLinkTarget($this, "pasteCharacteristicsFromOtherStyle");
        return $f->input()->container()->form()->standard($form_action, $sections);
    }

    /**
     * Get character title form section
     */
    protected function getCharacterTitleFormFields(string $char): array
    {
        $ui = $this->gui_service->ui();
        $f = $ui->factory();
        $refinery = $this->domain_service->refinery();
        $lng = $this->domain_service->lng();
        $style_type = $this->style_type;
        $style_obj = $this->object;


        $lng->loadLanguageModule("meta");

        $char_regexp_constraint = $refinery->custom()->constraint(function ($v) use ($lng) {
            return (bool) preg_match("/^[a-zA-Z]+[a-zA-Z0-9]*$/", $v);
        }, $lng->txt("sty_msg_characteristic_must_only_include") . " A-Z, a-z, 0-9");

        $char_exists_constraint = $refinery->custom()->constraint(function ($v) use ($style_obj, $style_type) {
            return !$style_obj->characteristicExists($v, $style_type);
        }, $lng->txt("sty_characteristic_already_exists"));

        $fields = [];
        $fields["char_" . $char] = $f->input()->field()->text($lng->txt("sty_class_name"))
                                     ->withRequired(true)
                                     ->withAdditionalTransformation($char_regexp_constraint)
                                     ->withAdditionalTransformation($char_exists_constraint);

        foreach ($lng->getInstalledLanguages() as $l) {
            $fields["title_" . $char . "_" . $l] = $f->input()->field()->text($lng->txt("title") .
                " - " . $lng->txt("meta_l_" . $l))
                                                     ->withRequired(false)
                                                     ->withValue($titles[$l] ?? "");
        }

        return $fields;
    }

    public function pasteCharacteristicsWithinStyle(): void
    {
        $ui = $this->gui_service->ui();
        $request = $this->gui_service->http()->request();
        $form = $this->getPasteWithinStyleForm();
        $tpl = $this->gui_service->mainTemplate();
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();
        $manager = $this->manager;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_null($data)) {
                $tpl->setContent($ui->renderer()->render($form));
                return;
            }
            foreach ($this->manager->getCopyCharacteristics() as $char) {
                if (is_array($data[$char])) {
                    $d = $data[$char];
                    $titles = [];
                    foreach ($lng->getInstalledLanguages() as $l) {
                        $titles[$l] = $d["title_" . $char . "_" . $l];
                    }
                    $new_char = $d["char_" . $char];
                    $char_parts = explode(".", $char);

                    $manager->copyCharacteristicFromSource(
                        $this->manager->getCopyCharacteristicStyleId(),
                        $this->manager->getCopyCharacteristicStyleType(),
                        $char_parts[2],
                        $new_char,
                        $titles
                    );
                }
            }
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        }
        $ctrl->redirect($this, "listCharacteristics");
    }

    /**
     * @throws Content\ContentStyleNoPermissionException
     * @throws ilCtrlException
     */
    public function pasteCharacteristicsFromOtherStyle(): void
    {
        $request = $this->gui_service->http()->request();
        $form = $this->getPasteFromOtherStyleForm();
        $tpl = $this->gui_service->mainTemplate();
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();
        $ui = $this->gui_service->ui();
        $manager = $this->manager;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_null($data)) {
                $tpl->setContent($ui->renderer()->render($form));
                return;
            }
            foreach ($this->manager->getCopyCharacteristics() as $char) {
                if (is_array($data["sec"][$char])) {
                    $d = $data["sec"][$char];
                    $char_parts = explode(".", $char);

                    if (isset($d[1])) {
                        $d = $d[1];
                    }

                    // overwrite existing class
                    if (isset($d["overwrite_class"])) {
                        $manager->copyCharacteristicFromSource(
                            $manager->getCopyCharacteristicStyleId(),
                            $manager->getCopyCharacteristicStyleType(),
                            $char_parts[2],
                            $d["overwrite_class"],
                            []
                        );
                    } elseif (isset($d["char_" . $char])) {
                        $titles = [];
                        foreach ($lng->getInstalledLanguages() as $l) {
                            $titles[$l] = $d["title_" . $char . "_" . $l];
                        }
                        $new_char = $d["char_" . $char];
                        $manager->copyCharacteristicFromSource(
                            $manager->getCopyCharacteristicStyleId(),
                            $manager->getCopyCharacteristicStyleType(),
                            $char_parts[2],
                            $new_char,
                            $titles
                        );
                    }
                }
            }
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        }
        $ctrl->redirect($this, "listCharacteristics");
    }

    public function pasteCharacteristics(): void
    {
        $ilCtrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        $titles = $this->request->getTitles();
        $conflict_action = $this->request->getConflictAction();
        if (count($titles) > 0) {
            foreach ($titles as $from_char => $to_title) {
                $fc = explode(".", $from_char);

                if ($conflict_action[$from_char] == "overwrite" ||
                    !$this->object->characteristicExists($to_title, $fc[0])) {
                    // @todo check this
                    $this->manager->copyCharacteristicFromSource(
                        $this->request->getFromStyleId(),
                        $fc[0],
                        $fc[2],
                        $fc[2],
                        ["en" => $to_title]
                    );
                }
            }
            ilObjStyleSheet::_writeUpToDate($this->object->getId(), false);
            $this->manager->clearCopyCharacteristics();
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("sty_style_classes_copied"), true);
        }

        $ilCtrl->redirect($this, "listCharacteristics");
    }
}
