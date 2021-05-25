<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Notes\Export;

/**
 * Helper UI class for notes/comments handling in (HTML) exports
 * @author Alexander Killing <killing@leifos.de>
 */
class ExportHelperGUI
{
    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ui = $DIC->ui();
    }

    /**
     *
     *
     * @param
     * @return
     */
    public function getCommentIncludeModalDialog($title, $message, $export_cmd, $export_with_comments_cmd, $js = false)
    {
        $ui = $this->ui;
        $factory = $ui->factory();

        $mess = $factory->messageBox()->confirmation($message);
        $modal = $factory->modal()->roundtrip($title, [$mess]);

        $b1 = $factory->button()->standard($this->lng->txt("no"), "")
                      ->withAdditionalOnLoadCode(function ($id) use ($export_cmd, $js) {
                          $cmd_js = ($js)
                              ? $export_cmd
                              : "window.location.href='$export_cmd'";
                          return "document.querySelector('#$id').addEventListener('click', (e) => {
                    $('#$id').closest('.modal-content').find('button.close').click();
                    $cmd_js
                });";
                      });
        $b2 = $factory->button()->standard($this->lng->txt("yes"), "")
                      ->withAdditionalOnLoadCode(function ($id) use ($export_with_comments_cmd, $js) {
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
