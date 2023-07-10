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
 * User Interface for Section Editing
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCSectionGUI: ilPropertyFormGUI
 */
class ilPCSectionGUI extends ilPageContentGUI
{
    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj = null,
        string $a_hier_id = "",
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);

        $this->setCharacteristics(ilPCSectionGUI::_getStandardCharacteristics());
    }

    public function getHTML(array $params): string
    {
        $this->getCharacteristicsOfCurrentStyle(["section"]);

        if ($params["form"] == true) {
            $insert = !($this->content_obj);
            $form = $this->initForm($insert);
            $form->setShowTopButtons(false);

            $onload_code = [];
            $char = $form->getItemByPostVar("characteristic");
            $onload_code = array_merge($onload_code, $char->getOnloadCode());

            $from = $form->getItemByPostVar("active_from");
            $from->setSideBySide(false);
            $onload_code = array_merge($onload_code, $from->getOnloadCode());

            $to = $form->getItemByPostVar("active_to");
            $to->setSideBySide(false);
            $onload_code = array_merge($onload_code, $to->getOnloadCode());

            $link = $form->getItemByPostVar("link");
            $onload_code = array_merge($onload_code, $link->getOnloadCode());

            $rep_sel = $form->getItemByPostVar("permission_ref_id");
            $on_load_code3 = "";
            $on_load_code4 = [];
            if ($rep_sel) {
                $exp = $rep_sel->getExplorerGUI();
                $this->ctrl->setParameterByClass("ilformpropertydispatchgui", "postvar", "permission_ref_id");
                $onload_code = array_merge($onload_code, [$exp->getOnloadCode()]);

                $this->ctrl->setParameterByClass("ilformpropertydispatchgui", "postvar", "");
                $onload_code = array_merge($onload_code, $rep_sel->getOnloadCode());
            }

            $html = $params["ui_wrapper"]->getRenderedForm(
                $form,
                $params["buttons"]
            );

            $html .= "<script>" .
                implode("\n", $onload_code) .
                "</script>";

            return $html;
        }
        return "";
    }

    public static function _getStandardCharacteristics(): array
    {
        global $DIC;

        $lng = $DIC->language();

        return array("Block" => $lng->txt("cont_Block"),
            "Mnemonic" => $lng->txt("cont_Mnemonic"),
            "Remark" => $lng->txt("cont_Remark"),
            "Example" => $lng->txt("cont_Example"),
            "Additional" => $lng->txt("cont_Additional"),
            "Special" => $lng->txt("cont_Special"),

            "Attention" => $lng->txt("cont_Attention"),
            "Background" => $lng->txt("cont_Background"),
            "Citation" => $lng->txt("cont_Citation"),
            "Confirmation" => $lng->txt("cont_Confirmation"),
            "Information" => $lng->txt("cont_Information"),
            "Interaction" => $lng->txt("cont_Interaction"),
            "Link" => $lng->txt("cont_Link"),
            "Literature" => $lng->txt("cont_Literature"),
            "Separator" => $lng->txt("cont_Separator"),
            "StandardCenter" => $lng->txt("cont_StandardCenter"),

            "Excursus" => $lng->txt("cont_Excursus"),
            "AdvancedKnowledge" => $lng->txt("cont_AdvancedKnowledge"));
    }

    public static function _getCharacteristics(string $a_style_id): array
    {
        $std_chars = ilPCSectionGUI::_getStandardCharacteristics();
        $chars = $std_chars;
        if ($a_style_id > 0 &&
            ilObject::_lookupType($a_style_id) == "sty") {
            $style = new ilObjStyleSheet($a_style_id);
            $chars = $style->getCharacteristics("section");
            $new_chars = array();
            foreach ($chars as $char) {
                if (($std_chars[$char] ?? "") != "") {	// keep lang vars for standard chars
                    $new_chars[$char] = $std_chars[$char];
                } else {
                    $new_chars[$char] = $char;
                }
                asort($new_chars);
            }
            $chars = $new_chars;
        }
        return $chars;
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ret = "";

        $this->getCharacteristicsOfCurrentStyle(["section"]);	// scorm-2004

        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilpropertyformgui":
                $form = $this->initForm(true);
                $this->ctrl->forwardCommand($form);
                break;

            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    public function insert(ilPropertyFormGUI $a_form = null): void
    {
        $this->edit(true, $a_form);
    }

    public function edit(
        bool $a_insert = false,
        ilPropertyFormGUI $a_form = null
    ): void {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm($a_insert);
        }

        $tpl->setContent($a_form->getHTML());
    }

    public function initForm(
        bool $a_insert = false
    ): ilPropertyFormGUI {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $a_seleted_value = "";

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_section"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_section"));
        }

        // characteristic selection
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_characteristic"),
            "characteristic"
        );
        $chars = $this->getCharacteristics();
        if (is_object($this->content_obj)) {
            if (($chars[$a_seleted_value] ?? "") == "" && ($this->content_obj->getCharacteristic() != "")) {
                $chars = array_merge(
                    array($this->content_obj->getCharacteristic() => $this->content_obj->getCharacteristic()),
                    $chars
                );
            }
        }

        $selected = ($a_insert)
            ? "Block"
            : $this->content_obj->getCharacteristic();

        foreach ($chars as $k => $char) {
            $html = '<div class="ilCOPgEditStyleSelectionItem"><div class="ilc_section_' . $k . '" style="' . self::$style_selector_reset . '">' .
                $char . '</div></div>';
            $char_prop->addOption($k, $char, $html);
        }

        $char_prop->setValue($selected);
        $form->addItem($char_prop);

        // link input
        $ac = new ilLinkInputGUI($this->lng->txt('cont_link'), 'link');
        if ($this->getPageConfig()->getEnableInternalLinks()) {
            $ac->setAllowedLinkTypes(ilLinkInputGUI::BOTH);
        } else {
            $ac->setAllowedLinkTypes(ilLinkInputGUI::EXT);
        }
        $ac->setRequired(false);
        $ac->setInfo($this->lng->txt("copg_sec_link_info"));
        $ac->setInternalLinkDefault(
            $this->getPageConfig()->getIntLinkHelpDefaultType(),
            $this->getPageConfig()->getIntLinkHelpDefaultId()
        );
        $link_types = array();
        foreach ($this->getPageConfig()->getIntLinkFilters() as $f) {
            $link_types[] = $f;
        }
        $ac->setInternalLinkFilterTypes($link_types);
        $ac->setFilterWhiteList(
            $this->getPageConfig()->getIntLinkFilterWhiteList()
        );

        if (!$a_insert) {
            $l = $this->content_obj->getLink();
            if ($l["LinkType"] == "IntLink") {
                $ac->setValueByIntLinkAttributes($l["Type"], $l["Target"], $l["TargetFrame"]);
            }
            if ($l["LinkType"] == "ExtLink") {
                $ac->setValue($l["Href"]);
            }
        }
        $form->addItem($ac);

        // activation

        // active from
        $dt_prop = new ilDateTimeInputGUI($lng->txt("cont_active_from"), "active_from");
        if (!$a_insert && ($from = $this->content_obj->getActiveFrom()) != "") {
            $dt_prop->setDate(new ilDateTime($from, IL_CAL_UNIX));
        }
        $dt_prop->setShowTime(true);
        $form->addItem($dt_prop);

        // active to
        $dt_prop = new ilDateTimeInputGUI($lng->txt("cont_active_to"), "active_to");
        if (!$a_insert && ($to = $this->content_obj->getActiveTo()) != "") {
            $dt_prop->setDate(new ilDateTime($to, IL_CAL_UNIX));
        }
        $dt_prop->setShowTime(true);
        $form->addItem($dt_prop);

        // rep selector
        if ($this->getPageConfig()->getEnablePermissionChecks()) {
            $rs = new ilRepositorySelector2InputGUI($lng->txt("cont_permission_object"), "permission_ref_id", false, $form);
            //$rs->setParent($this);
            $rs->setParentForm($form);
            $form->addItem($rs);

            // permission
            $options = array(
                "read" => $lng->txt("read"),
                "write" => $lng->txt("write"),
                "visible" => $lng->txt("visible"),
                "no_read" => $lng->txt("cont_no_read")
            );
            $si = new ilSelectInputGUI($lng->txt("permission"), "permission");
            $si->setInfo($lng->txt("cont_permission_object_desc"));
            $si->setOptions($options);
            $form->addItem($si);

            if (!$a_insert) {
                $si->setValue($this->content_obj->getPermission());
                $rs->setValue($this->content_obj->getPermissionRefId());
            }
        }

        // protection
        if ($this->getPageConfig()->getSectionProtection() == ilPageConfig::SEC_PROTECT_EDITABLE) {
            $cb = new ilCheckboxInputGUI($lng->txt("cont_sec_protected"), "protected");
            $cb->setInfo($this->getPageConfig()->getSectionProtectionInfo());
            if (!$a_insert) {
                $cb->setChecked($this->content_obj->getProtected());
            }
            $form->addItem($cb);
        }

        // save/cancel buttons
        if ($a_insert) {
            $form->addCommandButton("create_section", $lng->txt("save"));
            $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $form->addCommandButton("update", $lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
        }
        return $form;
    }

    public function create(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $this->content_obj = new ilPCSection($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

            $this->setValuesFromForm($form);

            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->insert($form);
    }

    public function update(): void
    {
        $form = $this->initForm(false);
        if ($form->checkInput()) {
            $this->setValuesFromForm($form);

            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->pg_obj->addHierIDs();
        $this->edit(false, $form);
    }

    public function setValuesFromForm(ilPropertyFormGUI $form): void
    {
        $this->content_obj->setCharacteristic($form->getInput("characteristic"));

        $from = $form->getItemByPostVar("active_from")->getDate();
        if ($from) {
            $this->content_obj->setActiveFrom($from->get(IL_CAL_UNIX));
        } else {
            $this->content_obj->setActiveFrom(0);
        }

        $to = $form->getItemByPostVar("active_to")->getDate();
        if ($to) {
            $this->content_obj->setActiveTo($to->get(IL_CAL_UNIX));
        } else {
            $this->content_obj->setActiveTo(0);
        }

        if ($this->getPageConfig()->getEnablePermissionChecks()) {
            $this->content_obj->setPermissionRefId((int) $form->getInput("permission_ref_id"));
            $this->content_obj->setPermission($form->getInput("permission"));
        }

        if ($form->getInput("link_mode") == "ext" && $form->getInput("link") != "") {
            $this->content_obj->setExtLink($form->getInput("link"));
        } elseif ($form->getInput("link_mode") == "int" && $form->getInput("link") != "") {
            $la = $form->getItemByPostVar("link")->getIntLinkAttributes();
            if (($la["Type"] ?? "") != "") {
                $this->content_obj->setIntLink($la["Type"], $la["Target"], $la["TargetFrame"]);
            }
        } else {
            $this->content_obj->setNoLink();
        }

        if ($this->getPageConfig()->getSectionProtection() == ilPageConfig::SEC_PROTECT_EDITABLE) {
            $this->content_obj->setProtected($form->getInput("protected"));
        }
    }
}
