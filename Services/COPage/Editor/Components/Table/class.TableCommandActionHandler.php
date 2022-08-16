<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Table;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TableCommandActionHandler implements Server\CommandActionHandler
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
//                return $this->insertCommand($body);
                break;

            case "update.data":
                return $this->updateDataCommand($body);
                break;

            case "modify.table":
                return $this->modifyTableCommand($body);
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
    /*
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

        return $this->ui_wrapper->sendPage($this->page_gui);
    }*/

    /**
     * Update command
     * @param $body
     * @return Server\Response
     */
    /*
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
        $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui);
    }*/

    /**
     * Update command
     * @param $body
     * @return Server\Response
     */
    protected function updateDataCommand($body) : Server\Response
    {
        $updated = $this->updateData($body["data"]["pcid"], $body["data"]["content"]);
        if ($body["data"]["redirect"]) {
            return $this->ui_wrapper->sendPage($this->page_gui, $updated);
        } else {
            return $this->sendUpdateResponse($this->page_gui, $updated, $body["data"]["pcid"]);
        }
    }

    /**
     * Get reponse data object
     * @param \ilPageObjectGUI $page_gui
     * @param                  $updated
     * @param string           $pcid
     * @return Server\Response
     */
    public function sendUpdateResponse(\ilPageObjectGUI $page_gui, $updated, string $pcid) : Server\Response
    {
        $error = null;

        $last_change = null;
        if ($updated !== true) {
            if (is_array($updated)) {
                $error = implode("<br />", $updated);
            } elseif (is_string($updated)) {
                $error = $updated;
            } else {
                $error = print_r($updated, true);
            }
        } else {
            $last_change = $page_gui->getPageObject()->getLastChange();
        }

        $data = new \stdClass();
        $data->error = $error;
        if ($last_change) {
            $lu = new \ilDateTime($last_change, IL_CAL_DATETIME);
            \ilDatePresentation::setUseRelativeDates(false);
            $data->last_update = \ilDatePresentation::formatDate($lu, true);
        }

        return new Server\Response($data);
    }


    /**
     * Update data
     * @param $pcid
     * @param $content
     */
    protected function updateData($pcid, $content)
    {
        $page = $this->page_gui->getPageObject();
        $table = $page->getContentObjectForPcId($pcid);

        $data = [];
        $updated = true;
        if (is_array($content)) {
            foreach ($content as $i => $row) {
                if (is_array($row)) {
                    foreach ($row as $j => $cell) {
                        $text = "<div>" . $cell . "</div>";
                        if ($updated) {
                            // determine cell content
                            $text = \ilPCParagraph::handleAjaxContent($text);
                            $data[$i][$j] = $text;
                            $updated = ($text !== false);
                            $text = $text["text"];
                        }

                        if ($updated) {
                            $text = \ilPCParagraph::_input2xml(
                                $text,
                                $table->getLanguage(),
                                true,
                                false
                            );
                            $text = \ilPCParagraph::handleAjaxContentPost($text);

                            $data[$i][$j] = $text;
                        }
                    }
                }
            }
        }

        if ($updated) {
            $table->setData($data);
            $updated = $page->update();
        }

        return $updated;
    }


    /**
     * Update command
     * @param $body
     * @return Server\Response
     */
    protected function modifyTableCommand($body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();
        $page->addHierIDs();

        /** @var $td \ilPCTableData */
        $table = $page->getContentObjectForPcId($body["data"]["tablePcid"]);


        if ($table->getType() == "dtab") {
            $this->updateData($body["data"]["tablePcid"], $body["data"]["content"]);
        }

        $page->addHierIDs();


        /** @var $td \ilPCTableData */
        $td = $page->getContentObjectForPcId($body["data"]["cellPcid"]);

        switch ($body["data"]["modification"]) {
            case "col.before":
                $td->newColBefore();
                break;
            case "col.after":
                $td->newColAfter();
                break;
            case "col.left":
                $td->moveColLeft();
                break;
            case "col.right":
                $td->moveColRight();
                break;
            case "col.delete":
                $td->deleteCol();
                break;
            case "row.before":
                $td->newRowBefore();
                break;
            case "row.after":
                $td->newRowAfter();
                break;
            case "row.up":
                $td->moveRowUp();
                break;
            case "row.down":
                $td->moveRowDown();
                break;
            case "row.delete":
                $td->deleteRow();
                break;
        }

        $page->update();

        return $this->sendTable($this->page_gui, $body["data"]["tablePcid"]);
    }

    /**
     * Send whole page as response
     * @param $page_gui
     * @param $pcid
     * @return Server\Response
     */
    public function sendTable($page_gui, $pcid) : Server\Response
    {
        $page = $page_gui->getPageObject();
        $page->addHierIds();
        $table = $page->getContentObjectForPcId($pcid);
        if ($table->getType() == "dtab") {
            $table_gui = new \ilPCDataTableGUI(
                $page_gui->getPageObject(),
                $table,
                $page->getHierIdForPCId($pcid),
                $pcid
            );
        } else {
            $table_gui = new \ilPCTableGUI(
                $page_gui->getPageObject(),
                $table,
                $page->getHierIdForPCId($pcid),
                $pcid
            );
        }

        $data = new \stdClass();
        $data->renderedContent = $table_gui->getEditDataTable();
        $data->pcModel = $page_gui->getPageObject()->getPCModel();
        return new Server\Response($data);
    }
}
