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

use ILIAS\UI\Component\Modal\Modal;

/**
 * Class ilObjBlogListGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBlogListGUI extends ilObjectListGUI
{
    private ?Modal $comment_modal = null;

    public function init(): void
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true; // #10498
        $this->info_screen_enabled = true;
        $this->type = "blog";
        $this->gui_class_name = "ilobjbloggui";

        // general commands array
        $this->commands = ilObjBlogAccess::_getCommands();
    }

    public function getCommands(): array
    {
        $commands = parent::getCommands();

        // #10182 - handle edit and contribute
        $permissions = array();
        foreach ($commands as $idx => $item) {
            if ($item["lang_var"] === "edit" && $item["granted"]) {
                $permissions[$item["permission"]] = $idx;
            }
        }
        if (count($permissions) === 2) {
            unset($commands[$permissions["contribute"]]);
        }

        return $commands;
    }

    public function insertCommand(
        string $href,
        string $text,
        string $frame = "",
        string $img = "",
        string $cmd = "",
        string $onclick = ""
    ): void {
        $ctrl = $this->ctrl;

        if ($cmd !== "export" || !ilObjBlogAccess::isCommentsExportPossible($this->obj_id)) {
            parent::insertCommand($href, $text, $frame, $img, $cmd, $onclick);
            return;
        }

        // #11099
        $chksum = md5($href . $text);
        if ($href === "#" ||
            !in_array($chksum, $this->prevent_duplicate_commands)) {
            if ($href !== "#") {
                $this->prevent_duplicate_commands[] = $chksum;
            }

            $prevent_background_click = false;

            if (ilObjBlogAccess::isCommentsExportPossible($this->obj_id)) {
                $comment_export_helper = new \ILIAS\Notes\Export\ExportHelperGUI();
                $this->lng->loadLanguageModule("note");
                $this->comment_modal = $comment_export_helper->getCommentIncludeModalDialog(
                    'HTML Export',
                    $this->lng->txt("note_html_export_include_comments"),
                    $ctrl->getLinkTargetByClass("ilobjbloggui", "export"),
                    $ctrl->getLinkTargetByClass("ilobjbloggui", "exportWithComments")
                );
                $signal = $this->comment_modal->getShowSignal()->getId();
                $this->current_selection_list->addItem(
                    $text,
                    "",
                    $href,
                    $img,
                    $text,
                    $frame,
                    "",
                    $prevent_background_click,
                    "( function() { $(document).trigger('" . $signal . "', {'id': '" . $signal . "','triggerer':$(this), 'options': JSON.parse('[]')}); return false;})()"
                );
            }
        }
    }

    public function getListItemHTML(
        int $ref_id,
        int $obj_id,
        string $title,
        string $description,
        bool $use_async = false,
        bool $get_async_commands = false,
        string $async_url = ""
    ): string {
        $html = parent::getListItemHTML(
            $ref_id,
            $obj_id,
            $title,
            $description,
            $use_async,
            $get_async_commands,
            $async_url
        );

        if (!is_null($this->comment_modal)) {
            global $DIC;
            $renderer = $DIC->ui()->renderer();
            $html .= $renderer->render($this->comment_modal);
        }
        return $html;
    }
}
