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

declare(strict_types=1);

namespace ILIAS\COPage\PC\InteractiveImage;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InteractiveImageCommandActionHandler implements Server\CommandActionHandler
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

            case "save.trigger.properties":
                return $this->saveTriggerProperties($query['pc_id'], $body);

            case "save.trigger.overlay":
                return $this->saveTriggerOverlay($query['pc_id'], $body);

            case "save.trigger.popup":
                return $this->saveTriggerPopup($query['pc_id'], $body);

            case "upload.overlay":
                return $this->uploadOverlay($query['pc_id'], $body);

            case "delete.overlay":
                return $this->deleteOverlay($query['pc_id'], $body);

            case "save.popup":
                return $this->savePopup($query['pc_id'], $body);

            case "delete.popup":
                return $this->deletePopup($query['pc_id'], $body);

            case "save.settings":
                return $this->saveSettings($query['pc_id'], $body);

            default:
                throw new Exception("Unknown action " . $body["action"]);
        }
    }

    protected function insertCommand(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $hier_id = "pg";
        $pc_id = "";
        if (!in_array($body["after_pcid"], ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$body["after_pcid"]]);
            $hier_id = $hier_ids[$body["after_pcid"]];
            $pc_id = $body["after_pcid"];
        }

        $iim = new \ilPCInteractiveImage($page);
        $iim->create();
        $iim_gui = new \ilPCInteractiveImageGUI($page, $iim, "", "");
        $iim_gui->setPageConfig($page->getPageConfig());
        $form = $iim_gui->getImportFormAdapter();
        if ($form->isValid()) {
            $iim->createFromMobId(
                $page,
                (int) $form->getData("input_file"),
                $hier_id,
                $pc_id
            );
            if (($body["pcid"] ?? "") !== "") {
                $iim->writePCId($body["pcid"]);
            }
        }
        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }
    protected function saveTriggerProperties(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        /** @var \ilPCInteractiveImage $pc */
        $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($pc_id);
        $pc->setTriggerProperties((string) $body["data"]["trigger_nr"], $body["data"]["title"], $body["data"]["shape_type"], $body["data"]["coords"]);
        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }

    protected function saveTriggerOverlay(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        /** @var \ilPCInteractiveImage $pc */
        $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($pc_id);
        $pc->setTriggerOverlay((string) $body["data"]["trigger_nr"], (string) $body["data"]["overlay"], (string) $body["data"]["coords"]);
        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }

    protected function saveTriggerPopup(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        /** @var \ilPCInteractiveImage $pc */
        $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($pc_id);
        $pc->setTriggerPopup((string) $body["data"]["trigger_nr"], (string) $body["data"]["popup"], (string) $body["data"]["position"], (string) $body["data"]["size"]);
        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }

    protected function getStandardResponse($updated, \ilPCInteractiveImage $pc): Server\Response
    {
        $error = false;
        if ($updated !== true) {
            if (is_array($updated)) {
                $error = implode("<br />", $updated);
            } elseif (is_string($updated)) {
                $error = $updated;
            } else {
                $error = print_r($updated, true);
            }
        }

        $data = new \stdClass();
        $data->error = $error;
        $data->model = $pc->getIIMModel();
        $data->backgroundImage = $pc->getBackgroundImage();
        return new Server\Response($data);
    }

    protected function uploadOverlay(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        /** @var \ilPCInteractiveImage $pc */
        $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($pc_id);

        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }

    protected function deleteOverlay(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        $pc = $this->getPCInteractiveImage($pc_id);
        $pc->deleteOverlay($body["data"]["overlay"]);
        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }

    protected function getPCInteractiveImage(string $pc_id): \ilPCInteractiveImage
    {
        $pg = $this->page_gui->getPageObject();
        return $this->page_gui->getPageObject()->getContentObjectForPcId($pc_id);
    }

    protected function getPCInteractiveImageGUI(string $pc_id): \ilPCInteractiveImageGUI
    {
        $pg = $this->page_gui->getPageObject();
        $iim = $this->page_gui->getPageObject()->getContentObjectForPcId($pc_id);
        $iim_gui = new \ilPCInteractiveImageGUI($pg, $iim, "", $pc_id);
        $iim_gui->setPageConfig($pg->getPageConfig());
        return $iim_gui;
    }

    protected function savePopup(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        $form_adapter = $this->getPCInteractiveImageGUI($pc_id)
                             ->getPopupFormAdapter();
        $pc = $this->getPCInteractiveImage($pc_id);
        if ($form_adapter->isValid()) {
            $title = $form_adapter->getData("title");
            if ($body['nr'] == "") {
                $pc->addContentPopup($title);
            } else {
                $pc->saveContentPopupTitle($body['nr'], $title);
            }
        }
        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }

    protected function deletePopup(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        $pc = $this->getPCInteractiveImage($pc_id);
        $pc->deletePopupByNr($body["data"]["nr"]);
        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }

    protected function saveSettings(string $pc_id, array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        /** @var \ilPCInteractiveImage $pc */
        $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($pc_id);
        $form_adapter = $this->getPCInteractiveImageGUI($pc_id)
                             ->getBackgroundPropertiesFormAdapter();

        if ($form_adapter->isValid()) {
            $caption = $form_adapter->getData("caption");
            $std_alias_item = $pc->getStandardAliasItem();
            $std_alias_item->setCaption($caption);
        }

        $updated = $page->update();

        return $this->getStandardResponse($updated, $pc);
    }


}
