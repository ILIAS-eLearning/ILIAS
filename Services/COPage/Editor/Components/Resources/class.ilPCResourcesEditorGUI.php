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

use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCResourcesEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }

    public function getEditorElements(
        UIWrapper $ui_wrapper,
        string $page_type,
        ilPageObjectGUI $page_gui,
        int $style_id
    ): array {
        $form = $this->getCreationForm($page_gui, $ui_wrapper, $style_id);
        return [
            "creation_form" => $form,
            "icon" => $ui_wrapper->getRenderedIcon("perl")
        ];
    }

    protected function getCreationForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper,
        int $style_id
    ): string {
        $lng = $this->lng;

        $res_gui = new ilPCResourcesGUI($page_gui->getPageObject(), null, "", "");

        /** @var ilPropertyFormGUI $form */
        $form = $res_gui->initCreationForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.save", $lng->txt("insert")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }

    public function getEditComponentForm(
        UIWrapper $ui_wrapper,
        string $page_type,
        \ilPageObjectGUI $page_gui,
        int $style_id,
        string $pcid
    ): string {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        /** @var ilPCResources $pc_res */
        $pc_res = $page_gui->getPageObject()->getContentObjectForPcId($pcid);
        $res_gui = new ilPCResourcesGUI($page_gui->getPageObject(), $pc_res, "", $pcid);

        /** @var ilPropertyFormGUI $form */
        $form = $res_gui->initEditingForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [["Page", "component.update", $lng->txt("save")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );

        return $html;
    }
}
