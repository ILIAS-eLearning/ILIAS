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

namespace ILIAS\Notes\Export;

/**
 * Helper UI class for notes/comments handling in (HTML) exports
 * @author Alexander Killing <killing@leifos.de>
 */
class ExportHelperGUI
{
    protected \ilLanguage $lng;
    protected \ILIAS\DI\UIServices $ui;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ui = $DIC->ui();
    }

    public function getCommentIncludeModalDialog(
        string $title,
        string $message,
        string $export_cmd,
        string $export_with_comments_cmd,
        bool $js = false
    ) : \ILIAS\UI\Component\Modal\RoundTrip {
        $ui = $this->ui;
        $factory = $ui->factory();

        $mess = $factory->messageBox()->confirmation($message);
        $modal = $factory->modal()->roundtrip($title, [$mess]);

        $b1 = $factory->button()
            ->standard($this->lng->txt("no"), "")
            ->withAdditionalOnLoadCode(static function ($id) use ($export_cmd, $js) : string {
                $cmd_js = ($js)
                    ? $export_cmd
                    : "window.location.href='$export_cmd'";
                return "document.querySelector('#$id').addEventListener('click', (e) => {
                    $('#$id').closest('.modal-content').find('button.close').click();
                    $cmd_js
                });";
            });

        $b2 = $factory->button()
            ->standard($this->lng->txt("yes"), "")
            ->withAdditionalOnLoadCode(static function ($id) use ($export_with_comments_cmd, $js) : string {
                $cmd_js = ($js)
                    ? $export_with_comments_cmd
                    : "window.location.href='$export_with_comments_cmd'";
                return "document.querySelector('#$id').addEventListener('click', (e) => {
                    $('#$id').closest('.modal-content').find('button.close').click();
                    $cmd_js
                });";
            });

        $modal = $modal->withActionButtons([$b1, $b2]);

        return $modal;
    }
}
