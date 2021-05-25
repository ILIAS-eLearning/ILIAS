<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\MediaObject;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaObjectCommandActionHandler implements Server\CommandActionHandler
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilPageObjectGUI
     */
    protected $page_gui;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var Server\UIWrapper
     */
    protected $ui_wrapper;

    public function __construct(\ilPageObjectGUI $page_gui)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();

        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
    }

    /**
     * @param $query
     * @param $body
     * @return Server\Response
     */
    public function handle($query, $body) : Server\Response
    {
        switch ($body["action"]) {
            case "insert":
                return $this->insertCommand($body);
                break;

            case "update":
                return $this->updateCommand($body);
                break;

            default:
                throw new Exception("Unknown action " . $body["action"]);
                break;
        }
    }

    /**
     * Insert command
     * @param $body
     * @return Server\Response
     */
    protected function insertCommand($body) : Server\Response
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
        \ilObjMediaObjectGUI::setObjectPerCreationForm($mob);
        $pc_media->createAlias($page, $hier_id, $pc_id);
        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    /**
     * Update command
     * @param $body
     * @return Server\Response
     */
    protected function updateCommand($body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();
        $pc_media = $page->getContentObjectForPcId($body["pcid"]);

        $quick_edit = new \ilPCMediaObjectQuickEdit($pc_media);

        $quick_edit->setTitle(\ilUtil::stripSlashes($body["standard_title"]));
        $quick_edit->setClass(\ilUtil::stripSlashes($body["characteristic"]));
        $quick_edit->setHorizontalAlign(\ilUtil::stripSlashes($body["horizontal_align"]));

        $quick_edit->setUseFullscreen((bool) ($body["fullscreen"]));
        $quick_edit->setCaption(\ilUtil::stripSlashes($body["standard_caption"]));
        $quick_edit->setTextRepresentation(\ilUtil::stripSlashes($body["text_representation"]));

        $pc_media->getMediaObject()->update();
        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }
}
