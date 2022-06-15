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

namespace ILIAS\COPage\Editor\Components\Paragraph;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ParagraphCommandActionHandler implements Server\CommandActionHandler
{
    protected \ilPCParagraph $content_obj;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilPageObjectGUI $page_gui;
    protected \ilObjUser $user;
    protected ParagraphResponseFactory  $response_factory;
    protected Server\UIWrapper $ui_wrapper;

    public function __construct(\ilPageObjectGUI $page_gui)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();
        $this->response_factory = new ParagraphResponseFactory();
        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
    }

    public function handle(array $query, array $body) : Server\Response
    {
        switch ($body["action"]) {
            case "insert":
                return $this->insertCommand($body);

            case "update":
                return $this->updateCommand($body);

            case "update.auto":
                return $this->autoUpdateCommand($body);

            case "insert.auto":
                return $this->autoInsertCommand($body);

            case "split":
                return $this->split($body);

            case "cmd.sec.class":
                return $this->sectionClassCommand($body);

            case "cmd.merge.previous":
                return $this->mergePrevious($body);

            case "cmd.cancel":
                return $this->cancelCommand($body);

            case "delete":
                return $this->deleteCommand($body);

            default:
                throw new Exception("Unknown action " . $body["action"]);
        }
    }

    protected function insertCommand(
        array $body,
        bool $auto = false
    ) : Server\Response {
        $updated = $this->insertParagraph($body["data"]["pcid"], $body["data"]["after_pcid"], $body["data"]["content"], $body["data"]["characteristic"], $body["data"]["fromPlaceholder"]);

        return $this->response_factory->getResponseObject($this->page_gui, $updated, $body["data"]["pcid"]);
    }

    /**
     * Insert paragraph
     * @return array|bool|string
     * @throws \ilCOPagePCEditException
     * @throws \ilCOPageUnknownPCTypeException
     */
    protected function insertParagraph(
        string $pcid,
        string $after_pcid,
        string $content,
        string $characteristic,
        bool $from_placeholder = false
    ) {
        $page = $this->page_gui->getPageObject();

        $pcid = ":" . $pcid;
        $insert_id = $this->getFullIdForPCId($page, $after_pcid);
        $content = $this->getContentForSaving($pcid, $content, $characteristic);

        $this->content_obj = new \ilPCParagraph($page);
        return $this->content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($characteristic),
            \ilUtil::stripSlashes($pcid),
            $insert_id,
            $from_placeholder
        );
    }

    protected function autoInsertCommand(
        array $body
    ) : Server\Response {
        return $this->insertCommand($body, true);
    }

    protected function updateCommand(
        array $body,
        bool $auto = false
    ) : Server\Response {
        $updated = $this->updateParagraph($body["data"]["pcid"], $body["data"]["content"], $body["data"]["characteristic"]);
        return $this->response_factory->getResponseObject($this->page_gui, $updated, $body["data"]["pcid"]);
    }

    /**
     * Update paragraph
     * @return array|bool|string
     * @throws \ilCOPagePCEditException
     * @throws \ilCOPageUnknownPCTypeException
     */
    protected function updateParagraph(
        string $pcid,
        string $content,
        string $characteristic
    ) {
        $page = $this->page_gui->getPageObject();

        $pcid = $this->getFullIdForPCId($page, $pcid);
        $content = $this->getContentForSaving($pcid, $content, $characteristic);
        $this->content_obj = new \ilPCParagraph($page);
        return $this->content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($characteristic),
            \ilUtil::stripSlashes($pcid)
        );
    }

    protected function autoUpdateCommand(array $body) : Server\Response
    {
        return $this->updateCommand($body, true);
    }

    protected function split(
        array $body,
        bool $auto = false
    ) : Server\Response {
        $page = $this->page_gui->getPageObject();

        $pcid = ":" . $body["data"]["pcid"];
        $insert_id = "";
        if ($body["data"]["insert_mode"]) {
            $insert_id = $this->getFullIdForPCId($page, $body["data"]["after_pcid"]);
        }

        $content = $this->getContentForSaving($pcid, $body["data"]["text"], $body["data"]["characteristic"]);

        $content_obj = new \ilPCParagraph($page);
        $updated = $content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($body["data"]["characteristic"]),
            \ilUtil::stripSlashes($pcid),
            $insert_id,
            $body["data"]["fromPlaceholder"] ?? false
        );
        $current_after_id = $body["data"]["pcid"];
        $all_pc_ids[] = $current_after_id;

        foreach ($body["data"]["new_paragraphs"] as $p) {
            if ($updated === true) {
                $page->addHierIDs();
                $insert_id = $this->getFullIdForPCId($page, $current_after_id);
                $content = $this->getContentForSaving($p["pcid"], $p["model"]["text"], $p["model"]["characteristic"]);
                $content_obj = new \ilPCParagraph($page);
                $updated = $content_obj->saveJS(
                    $page,
                    $content,
                    \ilUtil::stripSlashes($p["model"]["characteristic"]),
                    ":" . \ilUtil::stripSlashes($p["pcid"]),
                    $insert_id
                );
                $all_pc_ids[] = $p["pcid"];
                $current_after_id = $p["pcid"];
            }
        }

        return $this->response_factory->getResponseObjectMulti($this->page_gui, $updated, $all_pc_ids);
    }

    /**
     * Get full id for pc id
     */
    protected function getFullIdForPCId(
        \ilPageObject $page,
        string $pc_id
    ) : string {
        $id = "pg:";
        if (!in_array($pc_id, ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$pc_id]);
            $id = $hier_ids[$pc_id] . ":" . $pc_id;
        }
        return $id;
    }

    protected function getContentForSaving(
        string $pcid,
        string $content,
        string $characteristic
    ) : string {
        $content = str_replace("&nbsp;", " ", $content);
        return "<div id='" .
            $pcid . "' class='ilc_text_block_" .
            $characteristic . "'>" . $content . "</div>";
    }

    /**
     * Section class
     * @throws \ilCOPagePCEditException
     * @throws \ilCOPageUnknownPCTypeException
     * @throws \ilDateTimeException
     */
    protected function sectionClassCommand(array $body) : Server\Response
    {
        $insert_mode = $body["data"]["insert_mode"];
        $after_pcid = $body["data"]["after_pcid"];
        $pcid = $body["data"]["pcid"];
        $content = $body["data"]["text"];
        $characteristic = $body["data"]["characteristic"];
        $old_section_characteristic = $body["data"]["old_section_characteristic"];
        $new_section_characteristic = $body["data"]["new_section_characteristic"];

        // first insert/update the current paragraph
        if (!$insert_mode) {
            $updated = $this->updateParagraph($pcid, $content, $characteristic);
        } else {
            $updated = $this->insertParagraph($pcid, $after_pcid, $content, $characteristic);
        }


        /** @var \ilPageObject $page */
        if ($updated) {
            $page = $this->page_gui->getPageObject();
            $page->addHierIDs();
            $parent = $page->getParentContentObjectForPcId($pcid);

            // case 1: parent section exists and new characteristic is not empty
            if (!is_null($parent) && $parent->getType() == "sec" && $new_section_characteristic != "") {
                $parent->setCharacteristic($new_section_characteristic);
                $updated = $page->update();
            }
            // case 2: move from none to section
            elseif ((is_null($parent) || $parent->getType() != "sec") && $old_section_characteristic == "" && $new_section_characteristic != "") {
                $sec = new \ilPCSection($page);
                $hier_ids = $page->getHierIdsForPCIds([$pcid]);
                $sec->create($page, $hier_ids[$pcid], $pcid);
                $sec->setCharacteristic($new_section_characteristic);
                $sec_pcid = $page->generatePcId();
                $sec->writePCId($sec_pcid);
                $updated = $page->update();
                $page->addHierIDs();
                $par = $page->getContentObjectForPcId($pcid);
                $sec = $page->getContentObjectForPcId($sec_pcid);
                // note: we want the pcid of the Section itself here
                $sec_node_pc_id = $sec->getNode()->first_child()->get_attribute("PCID");
                $hier_ids = $page->getHierIdsForPCIds([$sec_node_pc_id]);
                $node = $par->getNode();
                $node->unlink_node();
                $page->insertContentNode($node, $hier_ids[$sec_node_pc_id], IL_INSERT_CHILD, $sec_node_pc_id);
                $updated = $page->update();
            }            // case 3: move from section to none
            elseif ((!is_null($parent) && $parent->getType() == "sec") && $old_section_characteristic != "" && $new_section_characteristic == "") {
                // note: we want the pcid of the PageContent element of the Section here
                $sec_node_pc_id = $parent->getNode()->get_attribute("PCID");
                $sec_node_hier_id = $page->getHierIdForPCId($sec_node_pc_id);
                // all kids of the section
                $childs_reverse = array_reverse($parent->getNode()->first_child()->child_nodes());
                foreach ($childs_reverse as $child) {
                    // unlink kid
                    $child->unlink_node();
                    // insert after section
                    $page->insertContentNode($child, $sec_node_hier_id, IL_INSERT_AFTER, $sec_node_pc_id, true);
                }
                // unlink section
                $node = $parent->getNode();
                $node->unlink_node();
                $updated = $page->update();
            }
        }
        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    /**
     * Merge with previous paragraph
     */
    protected function mergePrevious(array $body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $updated = $this->updateParagraph(
            $body["data"]["previousPcid"],
            $body["data"]["newPreviousContent"],
            $body["data"]["previousCharacteristic"]
        );

        $page->addHierIDs();
        $hier_id = $page->getHierIdForPcId($body["data"]["pcid"]);
        $updated = $page->deleteContents(
            [$hier_id],
            true,
            $this->page_gui->getPageConfig()->getEnableSelfAssessment()
        );
        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    /**
     * Cancel paragraph
     * @throws \ilCOPagePCEditException
     * @throws \ilCOPageUnknownPCTypeException
     * @throws \ilDateTimeException
     */
    protected function cancelCommand(array $body) : Server\Response
    {
        $remove_section_for_pcid = $body["data"]["removeSectionFromPcid"];
        $par_text = $body["data"]["paragraphText"];
        $par_characteristic = $body["data"]["paragraphCharacteristic"];

        $page = $this->page_gui->getPageObject();
        $page->addHierIDs();
        $paragraph = $page->getContentObjectForPcId($remove_section_for_pcid);
        $parent = $page->getParentContentObjectForPcId($remove_section_for_pcid);
        $parent_pc_id = $parent->getPCId();

        $updated = true;

        // case 1: parent section exists and new characteristic is not empty
        if ($parent->getType() == "sec") {
            $updated = $this->updateParagraph($remove_section_for_pcid, $par_text, $par_characteristic);

            if ($updated) {
                $page->addHierIDs();
                $page->moveContentAfter($paragraph->getHierId(), $parent->getHierId());
                $updated = $page->update();
            }

            if ($updated) {
                $page->addHierIDs();
                $hid = $page->getHierIdForPcId($parent_pc_id);
                $updated = $page->deleteContents(
                    [$hid],
                    true,
                    $this->page_gui->getPageConfig()->getEnableSelfAssessment()
                );
            }

            if ($updated) {
                $updated = $page->update();
            }
        }
        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    /**
     * Delete paragraph
     */
    protected function deleteCommand(array $body) : Server\Response
    {
        $pcids = [$body["data"]["pcid"]];

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

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function getIdForPCId(string $pcid) : string
    {
        $page = $this->page_gui->getPageObject();
        $id = "pg:";
        if (!in_array($pcid, ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$pcid]);
            $id = $hier_ids[$pcid] . ":" . $pcid;
        }
        return $id;
    }
}
