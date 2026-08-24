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

use ILIAS\UI\Component\Modal\Modal;

/**
 * Class ilObjBlogListGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBlogListGUI extends ilObjectListGUI
{
    private ?Modal $comment_modal = null;
    protected \ILIAS\Blog\InternalService $blog_service;

    public function __construct(int $context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;
        parent::__construct($context);
        $this->blog_service = $DIC->blog()->internal();
    }

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
        $blog_service = $this->blog_service;

        $tpl = $this->ui->mainTemplate();
        $export_possible = $blog_service->domain()
                               ->export()
                               ->manager()
                               ->isCommentsExportPossible($this->obj_id);
        if ($cmd === "export"
            && $export_possible
            && (bool) $this->settings->get('item_cmd_asynch')) {
            $href = $this->getCommandLink("forwardExport");
            $cmd = "forwardExport";
            $onclick = "";
        }
        if ($cmd !== "export" || !$export_possible) {
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

            if ($export_possible) {

                $modal = $this->getModalTemplate();
                $signal = $modal["show"];
                $tpl->addJavaScript('assets/js/blog.js');
                $tpl->addOnLoadCode('il.blog.setModalTemplate("' . $signal . '", "' . addslashes(json_encode($modal["template"])) . '");');


                $action = $this->ui->factory()
                                   ->button()
                                   ->shy($text, "#");

                $action = $action->withOnLoadCode(function ($id) use ($onclick, $signal): string {
                    return "document.getElementById('$id').addEventListener('click', (event) => {event.preventDefault(); il.blog.showModal('$signal');});";
                });
                $this->current_actions[] = $action;
            }
        }
    }


    public function getModalTemplate(): array
    {
        $ctrl = $this->ctrl;
        $ui = $this->ui;

        $comment_export_helper = new \ILIAS\Notes\Export\ExportHelperGUI();
        $this->lng->loadLanguageModule("note");
        $modal = $comment_export_helper->getCommentIncludeModalDialog(
            'HTML Export',
            $this->lng->txt("note_html_export_include_comments"),
            $ctrl->getLinkTargetByClass([ilRepositoryGUI::class, ilObjBlogGUI::class], "export"),
            $ctrl->getLinkTargetByClass([ilRepositoryGUI::class, ilObjBlogGUI::class], "exportWithComments")
        );

        $modalt["show"] = $modal->getShowSignal()->getId();
        $modalt["close"] = $modal->getCloseSignal()->getId();
        $modalt["template"] = $ui->renderer()->renderAsync($modal);

        return $modalt;
    }

    public function getCommandLink(string $cmd): string
    {
        if ($this->context == self::CONTEXT_REPOSITORY || $this->context == self::CONTEXT_SEARCH) {
            $id_par = "ref_id";
        } else {
            $id_par = "wsp_id";
        }
        switch ($cmd) {
            case "render":
                $this->ctrl->setParameterByClass(ilObjBlogGUI::class, $id_par, $this->ref_id);
                return $this->ctrl->getLinkTargetByClass(
                    [ilObjBlogGUI::class, \ILIAS\Blog\Editing\EditingGUI::class],
                    ""
                );
                break;
            case "preview":
                $this->ctrl->setParameterByClass(ilObjBlogGUI::class, $id_par, $this->ref_id);
                return $this->ctrl->getLinkTargetByClass(
                    [ilObjBlogGUI::class, \ILIAS\Blog\Presentation\PresentationGUI::class],
                    ""
                );
                break;
        }
        return parent::getCommandLink($cmd);
    }


}
