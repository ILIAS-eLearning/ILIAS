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
class ilPCSectionEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
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
    ) : array {
        $form = $this->getCreationForm($page_gui, $ui_wrapper, $style_id);
        return [
            "creation_form" => $form,
            "icon" => $ui_wrapper->getRenderedIcon("pesc")
        ];
    }

    protected function getCreationForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper,
        int $style_id
    ) : string {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $sec_gui = new ilPCSectionGUI($page_gui->getPageObject(), null, "", "");
        $sec_gui->setStyleId($style_id);
        $sec_gui->setPageConfig($page_gui->getPageConfig());

        $html = $ctrl->getHTML(
            $sec_gui,
            [
            "form" => true,
            "ui_wrapper" => $ui_wrapper,
            "buttons" => [["Page", "component.save", $lng->txt("insert")],
                ["Page", "component.cancel", $lng->txt("cancel")]]
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
    ) : string {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $page = $page_gui->getPageObject();
        $page->addHierIDs();
        $hier_id = $page->getHierIdForPcId($pcid);
        $sec = $page->getContentObjectForPcId($pcid);


        $sec_gui = new ilPCSectionGUI($page_gui->getPageObject(), $sec, $hier_id, $pcid);
        $sec_gui->setStyleId($style_id);
        $sec_gui->setPageConfig($page_gui->getPageConfig());

        $html = $ctrl->getHTML(
            $sec_gui,
            [
                "form" => true,
                "ui_wrapper" => $ui_wrapper,
                "buttons" => [["Page", "component.update", $lng->txt("save")],
                              ["Page", "component.cancel", $lng->txt("cancel")]]
            ]
        );

        return $html;
    }
}
