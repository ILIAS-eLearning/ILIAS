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

    public function handle(array $query, array $body) : Server\Response
    {
        switch ($body["action"]) {
            case "update.data":
                return $this->updateDataCommand($body);

            case "modify.table":
                return $this->modifyTableCommand($body);

            default:
                throw new Exception("Unknown action " . $body["action"]);
        }
    }

    protected function updateDataCommand(array $body) : Server\Response
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
    ) : Server\Response {
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


    protected function modifyTableCommand(array $body) : Server\Response
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
     * Send whole table as response
     */
    public function sendTable(
        \ilPageObjectGUI $page_gui,
        string $pcid
    ) : Server\Response {
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

        $data = new \stdClass();
        $data->renderedContent = $table_gui->getEditDataTable();
        $data->pcModel = $page_gui->getPageObject()->getPCModel();
        return new Server\Response($data);
    }
}
