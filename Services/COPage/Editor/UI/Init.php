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
    protected \ILIAS\DI\UIServices $ui;
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui = $DIC->ui();
    }

    public function initUI(
        \ilGlobalTemplateInterface $main_tpl
    ): void {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        \ILIAS\Repository\Form\FormAdapterGUI::initJavascript();

        $lang_vars = ["cont_last_update", "cont_error", "cont_sel_el_cut_use_paste", "cont_sel_el_copied_use_paste",
                      "cont_ed_new_col_before", "cont_ed_new_col_after", "cont_ed_col_left", "cont_ed_col_right", "cont_ed_delete_col",
                      "cont_ed_new_row_before", "cont_ed_new_row_after", "cont_ed_row_up", "cont_ed_row_down", "cont_ed_delete_row", "cont_saving",
                      "cont_ed_nr_cols", "cont_ed_nr_rows",
                      "cont_merge_cells", "cont_split_cell"
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
        // ensure modal.js from ui framework is loaded
        $this->ui->renderer()->render(
            $this->ui->factory()->modal()->roundtrip("", $this->ui->factory()->legacy(""))
        );
    }

    protected function sanitizeAttribute(string $attr) : string
    {
        return str_replace(["<", ">", "'", "\""], "", $attr);
    }

    public function getInitHtml(
        string $openPlaceHolderPcId = "",
        string $openFormPcId = "",
        string $openFormCName = ""
    ) : string
    {
        $ctrl = $this->ctrl;

        $p1 = $this->sanitizeAttribute(
            ILIAS_HTTP_PATH . "/" . $ctrl->getLinkTargetByClass(["ilPageEditorGUI", "ilPageEditorServerAdapterGUI"], "invokeServer")
        );
        $p2 = $this->sanitizeAttribute($ctrl->getFormActionByClass("ilPageEditorGUI"));
        $p3 = $this->sanitizeAttribute($openPlaceHolderPcId);
        $p4 = $this->sanitizeAttribute($openFormPcId);
        $p5 = $this->sanitizeAttribute($openFormCName);

        $init_span = <<<EOT
<span id='il-copg-init'
	data-endpoint='$p1'
	data-formaction='$p2'
	data-open-place-holder-pc-id='$p3'
	data-open-form-pc-id='$p4'
	data-open-form-cname='$p5'
></span>
EOT;

        $module_tag = <<<EOT
<script type="module" src="./Services/COPage/Editor/js/src/editor.js"></script>
EOT;
        return $init_span.$module_tag;
    }

}
