<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Page;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageCommandActionHandler implements Server\CommandActionHandler
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
            case "cut":
                return $this->cutCommand($body);
                break;

            case "paste":
                return $this->pasteCommand($body);
                break;

            case "copy":
                return $this->copyCommand($body);
                break;

            case "drag.drop":
                return $this->dragDropCommand($body);
                break;

            case "format":
                return $this->format($body);
                break;

            case "delete":
                return $this->delete($body);
                break;

            case "activate":
                return $this->activate($body);
                break;

            case "list.edit":
                return $this->listEdit($body);
                break;

            default:
                throw new Exception("Unknown action " . $body["action"]);
                break;
        }
    }

    /**
     * All command
     * @param $body
     * @return Server\Response
     */
    protected function cutCommand($body) : Server\Response
    {
        $pcids = $body["data"]["pcids"];
        $page = $this->page_gui->getPageObject();

        $hids = array_map(
            function ($pcid) {
                return $this->getIdForPCId($pcid);
            },
            $pcids
        );

        $updated = $page->cutContents($hids);

        return $this->sendPage($updated);
    }

    /**
     * All command
     * @param $body
     * @return Server\Response
     */
    protected function pasteCommand($body) : Server\Response
    {
        $target_pcid = $body["data"]["target_pcid"];
        $page = $this->page_gui->getPageObject();
        $updated = $page->pasteContents($this->getIdForPCId($target_pcid),
            $page->getPageConfig()->getEnableSelfAssessment());

        return $this->sendPage($updated);
    }

    /**
     * Copy/paste command
     * @param $body
     * @return Server\Response
     */
    protected function copyCommand($body) : Server\Response
    {
        $pcids = $body["data"]["pcids"];
        $page = $this->page_gui->getPageObject();

        $hids = array_map(
            function ($pcid) {
                return $this->getIdForPCId($pcid);
            },
            $pcids
        );

        $page->copyContents($hids);

        return $this->sendPage(true);
    }

    /**
     * Format command
     * @param $body
     * @return Server\Response
     */
    protected function format($body) : Server\Response
    {
        $pcids = $body["data"]["pcids"];
        $par = $body["data"]["paragraph_format"];
        $sec = $body["data"]["section_format"];
        $med = $body["data"]["media_format"];
        $page = $this->page_gui->getPageObject();

        $hids = array_map(
            function ($pcid) {
                return $this->getIdForPCId($pcid);
            },
            $pcids
        );

        $updated = $page->assignCharacteristic($hids, $par, $sec, $med);
        return $this->sendPage($updated);
    }

    /**
     * Delete command
     * @param $body
     * @return Server\Response
     */
    protected function delete($body) : Server\Response
    {
        $pcids = $body["data"]["pcids"];
        $page = $this->page_gui->getPageObject();

        $hids = array_map(
            function ($pcid) {
                return $this->getIdForPCId($pcid);
            },
            $pcids
        );

        $updated = $page->deleteContents(
            $hids,
            true,
            $this->page_gui->getPageConfig()->getEnableSelfAssessment()
        );

        return $this->sendPage($updated);
    }

    /**
     * Drag and dropt command
     * @param $body
     * @return Server\Response
     */
    protected function dragDropCommand($body) : Server\Response
    {
        $target = $body["data"]["target"];
        $source = $body["data"]["source"];

        $page = $this->page_gui->getPageObject();

        /*
        $hids = array_map(
            function ($pcid) {
                return $this->getIdForPCId($pcid);
            },
            $pcids
        );*/

        $source = explode(":", $source);
        $target = explode(":", $target);

        $updated = $page->moveContentAfter($source[0], $target[0], $source[1], $target[1]);

        return $this->sendPage($updated);
    }

    /**
     * Send whole page as response
     * @return Server\Response
     */
    protected function sendPage($updated) : Server\Response
    {
        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    /**
     * Get id for pcid
     * @param
     * @return
     */
    protected function getIdForPCId($pcid)
    {
        $page = $this->page_gui->getPageObject();
        $id = "pg:";
        if (!in_array($pcid, ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$pcid]);
            $id = $hier_ids[$pcid] . ":" . $pcid;
        }
        return $id;
    }

    /**
     * Get id for pcid
     * @param
     * @return
     */
    protected function getHierIdForPCId($pcid)
    {
        $page = $this->page_gui->getPageObject();
        $id = "pg";
        if (!in_array($pcid, ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$pcid]);
            $id = $hier_ids[$pcid];
        }
        return $id;
    }


    /**
     * All command
     * @param $body
     * @return Server\Response
     */
    protected function updateCommand($body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $hier_ids = $page->getHierIdsForPCIds([$body["data"]["pcid"]]);
        $pcid = $hier_ids[$body["data"]["pcid"]] . ":" . $body["data"]["pcid"];

        $content = "<div id='" .
            $pcid . "' class='ilc_text_block_" .
            $body["data"]["characteristic"] . "'>" . $body["data"]["content"] . "</div>";

        $this->content_obj = new \ilPCParagraph($page);

        $this->updated = $this->content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($body["data"]["characteristic"]),
            \ilUtil::stripSlashes($pcid)
        );


        $data = new \stdClass();
        $data->renderedContent = "Test the rendered content";
        return new Server\Response($data);
    }

    /**
     * Activate command
     * @param $body
     * @return Server\Response
     */
    protected function activate($body) : Server\Response
    {
        $pcids = $body["data"]["pcids"];
        $page = $this->page_gui->getPageObject();

        $hids = array_map(
            function ($pcid) {
                return $this->getIdForPCId($pcid);
            },
            $pcids
        );

        $updated = $page->switchEnableMultiple(
            $hids,
            true,
            $this->page_gui->getPageConfig()->getEnableSelfAssessment()
        );


        return $this->sendPage($updated);
    }

    /**
     * Activate command
     * @param $body
     * @return Server\Response
     */
    protected function listEdit($body) : Server\Response
    {
        $pcid = $body["data"]["pcid"];
        $list_cmd = $body["data"]["list_cmd"];
        $page = $this->page_gui->getPageObject();

        $pc = $page->getContentObjectForPcId($pcid);

        $updated = true;
        switch ($list_cmd) {
            case "newItemAfter":
                $pc->newItemAfter();
                break;
            case "newItemBefore":
                $pc->newItemBefore();
                break;
            case "deleteItem":
                $pc->deleteItem();
                break;
            case "moveItemUp":
                $pc->moveItemUp();
                break;
            case "moveItemDown":
                $pc->moveItemDown();
                break;
        }
        $updated = $page->update();

        return $this->sendPage($updated);
    }

}
