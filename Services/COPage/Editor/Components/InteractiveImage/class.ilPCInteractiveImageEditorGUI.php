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

use ILIAS\COPage\Editor\Components\PageComponentEditor;
use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCInteractiveImageEditorGUI implements PageComponentEditor
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

        return [
            "creation_form" => $this->getRenderedCreationForm(
                $ui_wrapper,
                $lng,
                $page_gui
            ),
            "icon" => $ui_wrapper->getRenderedIcon("peim")
        ];
    }

    protected function getRenderedCreationForm(
        UIWrapper $ui_wrapper,
        ilLanguage $lng,
        ilPageObjectGUI $page_gui
    ): string {
        $iim_gui = new ilPCInteractiveImageGUI($page_gui->getPageObject(), null, "", "");
        $form = $iim_gui->getImportFormAdapter();
        $html = $ui_wrapper->getRenderedAdapterForm(
            $form,
            [["Page", "component.save", $lng->txt("insert")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
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
        $html = "";
        return $html;
    }

}
