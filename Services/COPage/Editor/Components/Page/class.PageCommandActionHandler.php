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

namespace ILIAS\COPage\Editor\Components\Page;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageCommandActionHandler implements Server\CommandActionHandler
{
    /**
     * @var array|bool|string
     */
    protected $updated;
    protected \ilPCParagraph $content_obj;
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

    /**
     * @throws Exception
     */
    public function handle(array $query, array $body): Server\Response
    {
        switch ($body["action"]) {
            case "cut":
                return $this->cutCommand($body);

            case "paste":
                return $this->pasteCommand($body);

            case "copy":
                return $this->copyCommand($body);

            case "drag.drop":
                return $this->dragDropCommand($body);

            case "format":
                return $this->format($body);

            case "delete":
                return $this->delete($body);

            case "activate":
                return $this->activate($body);

            case "list.edit":
                return $this->listEdit($body);
                break;

            default:
                throw new Exception("Unknown action " . $body["action"]);
        }
    }

    protected function cutCommand(array $body): Server\Response
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

    protected function pasteCommand(array $body): Server\Response
    {
        $target_pcid = $body["data"]["target_pcid"];
        $page = $this->page_gui->getPageObject();
        $updated = $page->pasteContents(
            $this->getIdForPCId($target_pcid),
            $page->getPageConfig()->getEnableSelfAssessment()
        );

        return $this->sendPage($updated);
    }

    protected function copyCommand(array $body): Server\Response
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

    protected function format(array $body): Server\Response
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

    protected function delete(array $body): Server\Response
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

    protected function dragDropCommand(array $body): Server\Response
    {
        $target = $body["data"]["target"];
        $source = $body["data"]["source"];

        $page = $this->page_gui->getPageObject();

        $source = explode(":", $source);
        $target = explode(":", $target);

        $updated = $page->moveContentAfter($source[0], $target[0], $source[1], $target[1]);

        return $this->sendPage($updated);
    }

    /**
     * Send whole page as response
     * @param bool|array $updated
     */
    protected function sendPage($updated): Server\Response
    {
        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    /**
     * Get id for pcid
     */
    protected function getIdForPCId(string $pcid): string
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
     * Get hier id for pcid
     */
    protected function getHierIdForPCId(string $pcid): string
    {
        $page = $this->page_gui->getPageObject();
        $id = "pg";
        if (!in_array($pcid, ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$pcid]);
            $id = $hier_ids[$pcid];
        }
        return $id;
    }

    protected function updateCommand(array $body): Server\Response
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

    protected function activate(array $body): Server\Response
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
    protected function listEdit($body): Server\Response
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
