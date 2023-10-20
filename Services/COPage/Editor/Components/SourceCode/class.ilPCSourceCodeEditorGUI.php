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

use ILIAS\COPage\Editor\Components\PageComponentEditor;
use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCSourceCodeEditorGUI implements PageComponentEditor
{
    protected \ILIAS\COPage\InternalGUIService $gui;

    public function getEditorElements(
        UIWrapper $ui_wrapper,
        string $page_type,
        ilPageObjectGUI $page_gui,
        int $style_id
    ): array {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $this->gui = $DIC->copage()->internal()->gui();

        $acc = new ilAccordionGUI();
        $acc->addItem($lng->txt("cont_code_import_file"), $this->getRenderedImportForm($ui_wrapper, $lng, $page_gui));
        $acc->addItem($lng->txt("cont_code_manual_editing"), $this->getRenderedManualForm($ui_wrapper, $lng, $page_gui));
        $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        return [
            "creation_form" => $acc->getHTML(true),
            "icon" => $ui_wrapper->getRenderedIcon("pecd")
        ];
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

        /** @var ilPCSourceCode $pc_src */
        $pc_src = $page_gui->getPageObject()->getContentObjectForPcId($pcid);
        $src_gui = new ilPCSourceCodeGUI($page_gui->getPageObject(), $pc_src, "", $pcid);

        $form = $src_gui->getEditingFormAdapter();
        $html = $ui_wrapper->getRenderedAdapterForm(
            $form,
            [["Page", "component.update.back", $lng->txt("save")],
             ["Page", "component.back", $lng->txt("cancel")]],
            "copg-src-form"
        );
        return $html;
    }

    protected function getRenderedImportForm(
        UIWrapper $ui_wrapper,
        ilLanguage $lng,
        ilPageObjectGUI $page_gui
    ): string {
        $source_code_gui = new ilPCSourceCodeGUI($page_gui->getPageObject(), null, "", "");
        $form = $source_code_gui->getImportFormAdapter();
        $html = $ui_wrapper->getRenderedAdapterForm(
            $form,
            [["Page", "component.save", $lng->txt("insert")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );
        return $html;
    }

    protected function getRenderedManualForm(
        UIWrapper $ui_wrapper,
        ilLanguage $lng,
        ilPageObjectGUI $page_gui
    ): string {
        $source_code_gui = new ilPCSourceCodeGUI($page_gui->getPageObject(), null, "", "");
        $form = $source_code_gui->getManualFormAdapter();
        $html = $ui_wrapper->getRenderedAdapterForm(
            $form,
            [["Page", "component.save", $lng->txt("insert")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );
        return $html;
    }

    protected function getRenderedPoolBar(
        UIWrapper $ui_wrapper,
        ilLanguage $lng
    ): string {
        global $DIC;

        $ui = $DIC->ui();
        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        $buttons = [];

        $ctrl->setParameterByClass("ilpcmediaobjectgui", "subCmd", "poolSelection");
        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cont_choose_media_pool"),
            "media-action",
            "select.pool",
            [
                "url" => $ctrl->getLinkTargetByClass("ilpcmediaobjectgui", "insert")
            ],
            "MediaObject"
        );
        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cancel"),
            "form-button",
            "component.cancel",
            [
            ],
            "Page"
        );
        $ctrl->setParameterByClass("ilpcmediaobjectgui", "subCmd", "poolSelection");

        return $ui_wrapper->getRenderedFormFooter($buttons);
    }

    protected function getRenderedClipboardBar(
        UIWrapper $ui_wrapper,
        ilLanguage $lng,
        ilPageObjectGUI $page_gui
    ): string {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $return_cmd = $ctrl->getLinkTargetByClass("ilpageeditorgui", "insertFromClipboard");

        $ctrl->setParameterByClass("ileditclipboardgui", "returnCommand", rawurlencode($return_cmd));

        $buttons = [];

        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cont_open_clipboard"),
            "media-action",
            "open.clipboard",
            ["url" => $ctrl->getLinkTargetByClass([get_class($page_gui), "ileditclipboardgui"], "getObject")],
            "MediaObject"
        );

        $buttons[] = $ui_wrapper->getButton(
            $lng->txt("cancel"),
            "form-button",
            "component.cancel",
            [
            ],
            "Page"
        );

        return $ui_wrapper->getRenderedFormFooter($buttons);
    }
}
