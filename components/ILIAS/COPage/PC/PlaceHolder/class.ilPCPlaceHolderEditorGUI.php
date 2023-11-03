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

declare(strict_types=1);

use ILIAS\COPage\Editor\Server\UIWrapper;
use ILIAS\COPage\Editor\Components\PageComponentEditor;

class ilPCPlaceHolderEditorGUI implements PageComponentEditor
{
    protected ilCtrlInterface $ctrl;
    protected \ilLanguage $lng;

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
        $form = $this->getCreationForm($page_gui, $ui_wrapper);

        return [
            "creation_form" => $form,
            "icon" => $ui_wrapper->getRenderedIcon("peplh")
        ];
    }

    public function getEditComponentForm(
        UIWrapper $ui_wrapper,
        string $page_type,
        \ilPageObjectGUI $page_gui,
        int $style_id,
        string $pcid
    ): string {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $page = $page_gui->getPageObject();
        $page->addHierIDs();
        $hier_id = $page->getHierIdForPcId($pcid);
        $ph = $page->getContentObjectForPcId($pcid);


        $ph_gui = new ilPCPlaceHolderGUI($page_gui->getPageObject(), $ph, $hier_id, $pcid);
        $ph_gui->setPageConfig($page_gui->getPageConfig());
        $form = $ph_gui->initCreationForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.update", $lng->txt("save")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }

    protected function getCreationForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper
    ): string {
        $lng = $this->lng;

        $plach_gui = new ilPCPlaceHolderGUI($page_gui->getPageObject(), null, "", "");

        /** @var ilPropertyFormGUI $form */
        $form = $plach_gui->initCreationForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.save", $lng->txt("insert")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }
}
