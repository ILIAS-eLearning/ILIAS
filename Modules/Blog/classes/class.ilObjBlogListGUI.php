<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\UI\Component\Modal\Modal;

/**
 * Class ilObjBlogListGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBlogListGUI extends ilObjectListGUI
{
    private ?Modal $comment_modal = null;

    public function init()
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
    
    public function getCommands()
    {
        $commands = parent::getCommands();
        
        // #10182 - handle edit and contribute
        $permissions = array();
        foreach ($commands as $idx => $item) {
            if ($item["lang_var"] == "edit" && $item["granted"]) {
                $permissions[$item["permission"]] = $idx;
            }
        }
        if (sizeof($permissions) == 2) {
            unset($commands[$permissions["contribute"]]);
        }
        
        return $commands;
    }

    public function insertCommand($a_href, $a_text, $a_frame = "", $a_img = "", $a_cmd = "", $a_onclick = "")
    {
        $ctrl = $this->ctrl;

        if ($a_cmd != "export" || !ilObjBlogAccess::isCommentsExportPossible($this->obj_id)) {
            parent::insertCommand($a_href, $a_text, $a_frame, $a_img, $a_cmd, $a_onclick);
            return;
        }

        // #11099
        $chksum = md5($a_href . $a_text);
        if ($a_href == "#" ||
            !in_array($chksum, $this->prevent_duplicate_commands)) {
            if ($a_href != "#") {
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
                    $a_text,
                    "",
                    $a_href,
                    $a_img,
                    $a_text,
                    $a_frame,
                    "",
                    $prevent_background_click,
                    "( function() { $(document).trigger('" . $signal . "', {'id': '" . $signal . "','triggerer':$(this), 'options': JSON.parse('[]')}); return false;})()"
                );
            }
        }
    }

    public function getListItemHTML(
        $a_ref_id,
        $a_obj_id,
        $a_title,
        $a_description,
        $a_use_asynch = false,
        $a_get_asynch_commands = false,
        $a_asynch_url = ""
    ) {
        $html = parent::getListItemHTML(
            $a_ref_id,
            $a_obj_id,
            $a_title,
            $a_description,
            $a_use_asynch,
            $a_get_asynch_commands,
            $a_asynch_url
        );

        if (!is_null($this->comment_modal)) {
            global $DIC;
            $renderer = $DIC->ui()->renderer();
            $html .= $renderer->render($this->comment_modal);
        }
        return $html;
    }
}
