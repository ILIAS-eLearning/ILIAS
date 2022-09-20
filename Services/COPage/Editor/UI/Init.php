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

namespace ILIAS\COPage\Editor\UI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class Init
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    public function initUI(
        \ilGlobalTemplateInterface $main_tpl,
        string $openPlaceHolderPcId = ""
    ): void {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $main_tpl->addOnLoadCode("il.copg.editor.init('" .
            ILIAS_HTTP_PATH . "/" . $ctrl->getLinkTargetByClass(["ilPageEditorGUI", "ilPageEditorServerAdapterGUI"], "invokeServer") . "','" .
            $this->ctrl->getFormActionByClass("ilPageEditorGUI")
            . "', '" . $openPlaceHolderPcId . "');");

        $lang_vars = ["cont_last_update", "cont_error", "cont_sel_el_cut_use_paste", "cont_sel_el_copied_use_paste",
                      "cont_ed_new_col_before", "cont_ed_new_col_after", "cont_ed_col_left", "cont_ed_col_right", "cont_ed_delete_col",
                      "cont_ed_new_row_before", "cont_ed_new_row_after", "cont_ed_row_up", "cont_ed_row_down", "cont_ed_delete_row", "cont_saving"
        ];

        foreach ($lang_vars as $l) {
            $lng->toJS($l);
        }

        if (DEVMODE == 1) {
            $main_tpl->addJavaScript("./node_modules/tinymce/tinymce.js");
        } else {
            $main_tpl->addJavaScript("./node_modules/tinymce/tinymce.min.js");
        }

        \ilYuiUtil::initConnection();
        $main_tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");

        // ensure that form.js is loaded which is needed for file input (js that shows file names)
        $dummy = new \ilPropertyFormGUI();
        $dummy->getHTML();
    }
}
