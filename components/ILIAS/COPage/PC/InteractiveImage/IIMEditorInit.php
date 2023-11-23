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

namespace ILIAS\COPage\PC\InteractiveImage;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class IIMEditorInit
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

        $lang_vars = ["delete", "rename", "save", "cont_add_popup", "add", "cont_iim_add_overlay"];
        foreach ($lang_vars as $l) {
            $lng->toJS($l);
        }

        // ensure that form.js is loaded which is needed for file input (js that shows file names)
        $dummy = new \ilPropertyFormGUI();
        $dummy->getHTML();
        // ensure modal.js from ui framework is loaded
        $this->ui->renderer()->render(
            $this->ui->factory()->modal()->roundtrip("", $this->ui->factory()->legacy(""))
        );
    }

    protected function sanitizeAttribute(string $attr): string
    {
        return str_replace(["<", ">", "'", "\""], "", $attr);
    }

    public function getInitHtml(): string
    {
        $ctrl = $this->ctrl;

        $p1 = $this->sanitizeAttribute(
            ILIAS_HTTP_PATH . "/" . $ctrl->getLinkTargetByClass(["ilPageEditorGUI", "ilPageEditorServerAdapterGUI"], "invokeServer")
        );
        $p2 = $this->sanitizeAttribute($ctrl->getFormActionByClass("ilPCInteractiveImageGUI"));

        $init_span = <<<EOT
<span id='il-copg-iim-init'
	data-endpoint='$p1'
	data-formaction='$p2'
></span><div id='il-copg-iim-main'></div>
EOT;

        $module_tag = <<<EOT
<script type="module" src="./components/ILIAS/COPage/PC/InteractiveImage/js/editor/src/editor.js"></script>
EOT;
        return $init_span . $module_tag;
    }

}
