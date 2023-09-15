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
class ilPCParagraphEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    public function getEditorElements(
        UIWrapper $ui_wrapper,
        string $page_type,
        ilPageObjectGUI $page_gui,
        int $style_id
    ): array {
        $cfg = $page_gui->getPageConfig();
        $menu = ilPageObjectGUI::getTinyMenu(
            $page_type,
            $cfg->getEnableInternalLinks(),
            $cfg->getEnableWikiLinks(),
            $cfg->getEnableKeywords(),
            $style_id,
            true,
            true,
            $cfg->getEnableAnchors(),
            true,
            $cfg->getEnableUserLinks(),
            $ui_wrapper
        );

        return [
            "menu" => $menu
        ];
    }

    public function getEditComponentForm(
        UIWrapper $ui_wrapper,
        string $page_type,
        \ilPageObjectGUI $page_gui,
        int $style_id,
        string $pcid
    ): string {
        return "";
    }
}
