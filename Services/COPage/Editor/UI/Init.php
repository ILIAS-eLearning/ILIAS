<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\UI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Init
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    public function initUI(\ilGlobalPageTemplate $main_tpl, string $openPlaceHolderPcId = "")
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $main_tpl->addOnloadCode("il.copg.editor.init('" .
            ILIAS_HTTP_PATH . "/" . $ctrl->getLinkTargetByClass(["ilPageEditorGUI", "ilPageEditorServerAdapterGUI"], "invokeServer") . "','" .
            $this->ctrl->getFormActionByClass("ilPageEditorGUI")
            . "', '".$openPlaceHolderPcId."');");

        $lang_vars = ["cont_last_update", "cont_error", "cont_sel_el_cut_use_paste", "cont_sel_el_copied_use_paste",
                      "cont_ed_new_col_before", "cont_ed_new_col_after", "cont_ed_col_left", "cont_ed_col_right", "cont_ed_delete_col",
                      "cont_ed_new_row_before", "cont_ed_new_row_after", "cont_ed_row_up", "cont_ed_row_down", "cont_ed_delete_row", "cont_saving"
        ];

        foreach ($lang_vars as $l) {
            $lng->toJS($l);
        }

        if (DEVMODE == 1) {
            $main_tpl->addJavascript("./node_modules/tinymce/tinymce.js");
        } else {
            $main_tpl->addJavascript("./node_modules/tinymce/tinymce.min.js");
        }

        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        \ilYuiUtil::initConnection();
        $main_tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");

        // ensure that form.js is loaded which is needed for file input (js that shows file names)
        $dummy = new \ilPropertyFormGUI();
        $dummy->getHTML();
    }
}
