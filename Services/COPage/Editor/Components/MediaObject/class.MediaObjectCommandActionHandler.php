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

namespace ILIAS\COPage\Editor\Components\MediaObject;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaObjectCommandActionHandler implements Server\CommandActionHandler
{
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilPageObjectGUI $page_gui;
    protected \ilObjUser $user;
    protected Server\UIWrapper $ui_wrapper;

    public function __construct(\ilPageObjectGUI $page_gui)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();

        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
    }

    public function handle(array $query, array $body) : Server\Response
    {
        switch ($body["action"]) {
            case "insert":
                return $this->insertCommand($body);

            case "update":
                return $this->updateCommand($body);

            default:
                throw new Exception("Unknown action " . $body["action"]);
        }
    }

    protected function insertCommand(array $body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $hier_id = "pg";
        $pc_id = "";
        if (!in_array($body["after_pcid"], ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$body["after_pcid"]]);
            $hier_id = $hier_ids[$body["after_pcid"]];
            $pc_id = $body["after_pcid"];
        }


        // if (!$mob_gui->checkFormInput()) {
        $pc_media = new \ilPCMediaObject($page);
        $pc_media->createMediaObject();
        $mob = $pc_media->getMediaObject();

        $mob_gui = new \ilObjMediaObjectGUI("");
        $mob_gui->initForm("create");
        if ($mob_gui->checkFormInput()) {
            $mob_gui->setObjectPerCreationForm($mob);
            $media_item = $mob->getMediaItem("Standard");
            if (!is_null($media_item)) {
                $pc_media->createAlias($page, $hier_id, $pc_id);
            }
        }
        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function updateCommand(array $body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        /** @var \ilPCMediaObject $pc_media */
        $pc_media = $page->getContentObjectForPcId($body["pcid"]);
        $quick_edit = new \ilPCMediaObjectQuickEdit($pc_media);

        $quick_edit->setTitle(\ilUtil::stripSlashes($body["standard_title"] ?? ""));
        $quick_edit->setClass(\ilUtil::stripSlashes($body["characteristic"] ?? ""));
        $quick_edit->setHorizontalAlign(\ilUtil::stripSlashes($body["horizontal_align"] ?? ""));

        $quick_edit->setUseFullscreen((bool) ($body["fullscreen"]));
        $quick_edit->setCaption(\ilUtil::stripSlashes($body["standard_caption"] ?? ""));
        $quick_edit->setTextRepresentation(\ilUtil::stripSlashes($body["text_representation"] ?? ""));

        $pc_media->getMediaObject()->update();
        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }
}
