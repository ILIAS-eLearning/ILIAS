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

namespace ILIAS\COPage\Editor\Components\Table;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;
use ILIAS\COPage\Editor\Server\Response;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TableCommandActionHandler implements Server\CommandActionHandler
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

    public function handle(array $query, array $body): Server\Response
    {
        switch ($body["action"]) {
            case "insert":
                return $this->insertCommand($body);

            case "update.data":
                return $this->updateDataCommand($body);

            case "modify.table":
                return $this->modifyTableCommand($body);

            case "update":
                return $this->updateCommand($body);

            case "set.properties":
                return $this->setCellProperties($body);

            case "toggle.merge":
                return $this->toggleMerge($body);

            default:
                throw new Exception("Unknown action " . $body["action"]);
        }
    }

    protected function insertCommand(array $body): Server\Response
    {
        if (($body["import"] ?? "") === "1") {
            return $this->importSpreadsheet($body);
        }

        $page = $this->page_gui->getPageObject();

        $hier_id = "pg";
        $pc_id = "";
        if (!in_array($body["after_pcid"], ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$body["after_pcid"]]);
            $hier_id = $hier_ids[$body["after_pcid"]];
            $pc_id = $body["after_pcid"];
        }

        $tab = new \ilPCDataTable($page);
        $tab->create($page, $hier_id, $pc_id);
        $tab->setLanguage($this->user->getLanguage());


        $tab->addRows(
            (int) ($body["nr_rows"] ?? 1),
            (int) ($body["nr_cols"] ?? 1)
        );


        $this->setRowHeaderAndCharacteristic($tab, $body);

        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function setRowHeaderAndCharacteristic(\ilPCDataTable $tab, array $body) : void
    {
        if ($body["has_row_header"] ?? false) {
            $tab->setHeaderRows(1);
        }
        $characteristic = ($body["characteristic"] ?? "");
        if ($characteristic === "" && isset($body["import_characteristic"])) {
            $characteristic = $body["import_characteristic"];
        }
        if ($characteristic === "") {
            $characteristic = "StandardTable";
        }
        if (strpos($characteristic, ":") > 0) {
            $t = explode(":", $characteristic);
            $tab->setTemplate($t[2]);
            $tab->setClass("");
        } else {
            $tab->setClass($characteristic);
            $tab->setTemplate("");
        }
    }

    protected function importSpreadsheet(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $hier_id = "pg";
        $pc_id = "";
        if (!in_array($body["after_pcid"], ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$body["after_pcid"]]);
            $hier_id = $hier_ids[$body["after_pcid"]];
            $pc_id = $body["after_pcid"];
        }

        $tab = new \ilPCDataTable($page);
        $tab->create($page, $hier_id, $pc_id);
        $tab->setLanguage($this->user->getLanguage());

        $this->setRowHeaderAndCharacteristic($tab, $body);

        $table_data = $body["import_table"] ?? "";

        $tab->importSpreadsheet($this->user->getLanguage(), trim($table_data));

        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function updateDataCommand(array $body): Server\Response
    {
        $updated = $this->updateData($body["data"]["pcid"], $body["data"]["content"]);
        if ($body["data"]["redirect"]) {
            return $this->ui_wrapper->sendPage($this->page_gui, $updated);
        } else {
            return $this->sendUpdateResponse($this->page_gui, $updated, $body["data"]["pcid"]);
        }
    }

    /**
     * @param string|bool|array $updated
     * @throws \ilDateTimeException
     */
    public function sendUpdateResponse(
        \ilPageObjectGUI $page_gui,
        $updated,
        string $pcid
    ): Server\Response {
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
     * @return array|bool
     * @throws \ilDateTimeException
     */
    protected function updateData(
        string $pcid,
        array $content
    ) {
        $page = $this->page_gui->getPageObject();
        $table = $page->getContentObjectForPcId($pcid);

        $data = [];
        $updated = true;
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

        if ($updated) {
            $table->setData($data);
            $updated = $page->update();
        }

        return $updated;
    }


    protected function modifyTableCommand(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        $page->addHierIDs();

        /** @var $td \ilPCTableData */
        $table = $page->getContentObjectForPcId($body["data"]["tablePcid"]);


        if ($table->getType() == "dtab" && $body["data"]["modification"] !== "none") {
            $this->updateData($body["data"]["tablePcid"], $body["data"]["content"]);
        }

        $page->addHierIDs();


        /** @var $td \ilPCTableData */
        if ($body["data"]["modification"] !== "none") {
            $td = $page->getContentObjectForPcId($body["data"]["cellPcid"]);
        }

        $cnt = $body["data"]["cnt"] ?? 1;
        switch ($body["data"]["modification"]) {
            case "col.before":
                $td->newColBefore($cnt);
                break;
            case "col.after":
                $td->newColAfter($cnt);
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
                $td->newRowBefore($cnt);
                break;
            case "row.after":
                $td->newRowAfter($cnt);
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
            case "none":
                break;
        }

        $page->update();

        return $this->sendTable($this->page_gui, $body["data"]["tablePcid"]);
    }

    /**
     * Send whole table as response
     */
    public function sendTable(
        \ilPageObjectGUI $page_gui,
        string $pcid
    ): Server\Response {
        $page = $page_gui->getPageObject();
        $page->addHierIDs();
        $table = $page->getContentObjectForPcId($pcid);
        if ($table->getType() == "dtab") {
            $table_gui = new \ilPCDataTableGUI(
                $page_gui->getPageObject(),
                $table,
                $page->getHierIdForPcId($pcid),
                $pcid
            );
        } else {
            $table_gui = new \ilPCTableGUI(
                $page_gui->getPageObject(),
                $table,
                $page->getHierIdForPcId($pcid),
                $pcid
            );
        }
        $table_gui->setStyleId($page_gui->getStyleId());
        $data = new \stdClass();
        $data->renderedContent = $table_gui->getEditDataTable();
        $data->pcModel = $page_gui->getPageObject()->getPCModel();
        return new Server\Response($data);
    }

    protected function updateCommand(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();

        /** @var \ilPCDataTable $tab */
        $tab = $page->getContentObjectForPcId($body["pcid"]);

        $this->setRowHeaderAndCharacteristic($tab, $body);

        $header_row = (bool) ($body["has_row_header"] ?? false);
        if ($tab->getHeaderRows() === 0 && $header_row) {
            $tab->setHeaderRows(1);
        }
        if ($tab->getHeaderRows() > 0 && !$header_row) {
            $tab->setHeaderRows(0);
        }

        $updated = $page->update();
        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function setCellProperties(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();

        /** @var \ilPCDataTable $tab */
        $tab = $page->getContentObjectForPcId($body["pcid"]);
        $top = (int) ($body["top"] ?? -1);
        $bottom = (int) ($body["bottom"] ?? -1);
        $left = (int) ($body["left"] ?? -1);
        $right = (int) ($body["right"] ?? -1);
        if ($top !== -1 && $bottom !== -1 && $left !== -1 && $right !== -1) {
            for ($i = $top; $i <= $bottom; $i++) {
                for ($j = $left; $j <= $right; $j++) {
                    $td_node = $tab->getTableDataNode($i, $j);
                    if ($td_node) {
                        // set class
                        if (isset($body["style_cb"])) {
                            $class = $body["style"] ?? "";
                            if ($class === "") {
                                $td_node->remove_attribute("Class");
                            } else {
                                $td_node->set_attribute("Class", $class);
                            }
                        }
                        // set width
                        if (isset($body["width_cb"])) {
                            $width = $body["width"] ?? "";
                            if ($width === "") {
                                $td_node->remove_attribute("Width");
                            } else {
                                $td_node->set_attribute("Width", $width);
                            }
                        }
                        // set alignment
                        if (isset($body["al_cb"])) {
                            $alignment = $body["alignment"] ?? "";
                            if ($alignment === "") {
                                $td_node->remove_attribute("HorizontalAlign");
                            } else {
                                $td_node->set_attribute("HorizontalAlign", $alignment);
                            }
                        }

                    }
                }
            }
        }
        $updated = $page->update();
        return $this->sendTable($this->page_gui, $body["pcid"]);
    }

    protected function toggleMerge(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $data = $body["data"];

        /** @var \ilPCDataTable $tab */
        $tab = $page->getContentObjectForPcId($data["pcid"]);
        $top = (int) ($data["top"] ?? -1);
        $bottom = (int) ($data["bottom"] ?? -1);
        $left = (int) ($data["left"] ?? -1);
        $right = (int) ($data["right"] ?? -1);

        $td_node = $tab->getTableDataNode($top, $left);
        $td_node->set_attribute("ColSpan", $right - $left + 1);
        $td_node->set_attribute("RowSpan", $bottom - $top + 1);

        $tab->fixHideAndSpans();

        $updated = $page->update();
        return $this->sendTable($this->page_gui, $data["pcid"]);
    }
}
