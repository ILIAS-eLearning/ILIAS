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

namespace ILIAS\COPage\Editor\Components\SourceCode;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SourceCodeCommandActionHandler implements Server\CommandActionHandler
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

            case "update":
                return $this->updateCommand($body);

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

        $manual = ((string) ($body["form_input_1"] ?? "") === "manual");

        $src = new \ilPCSourceCode($page);
        $src->create($page, $hier_id, $pc_id);
        if (($body["pcid"] ?? "") !== "") {
            $src->writePCId($body["pcid"]);
        }
        $src_gui = new \ilPCSourceCodeGUI($page, $src, "", "");
        $src_gui->setPageConfig($page->getPageConfig());
        $src->setLanguage($this->user->getLanguage());
        $src->setCharacteristic('Code');

        $updated = true;

        if ($manual) {
            $form = $src_gui->getManualFormAdapter();
            if ($form->isValid()) {
                $src->setDownloadTitle(str_replace('"', '', $form->getData("title")));
                $src->setSubCharacteristic($form->getData("subchar"));
                $src->setShowLineNumbers($form->getData("linenumbers") ? "y" : "n");
                $updated = $page->update();
            }
        } else {
            $form = $src_gui->getImportFormAdapter();
            if ($form->isValid()) {
                $src->importFile((string) $form->getData("input_file"));
                $src->setDownloadTitle(str_replace('"', '', $form->getData("title")));
                $src->setSubCharacteristic($form->getData("subchar"));
                $src->setShowLineNumbers($form->getData("linenumbers") ? "y" : "n");
                $updated = $page->update();
            }
        }

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function updateCommand(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();

            /** @var \ilPCSourceCode $pc_src */
        $pc_src = $page->getContentObjectForPcId($body["pcid"]);
        $src_gui = new \ilPCSourceCodeGUI($page, $pc_src, "", $body["pcid"]);

        $form = $src_gui->getEditingFormAdapter();
        if ($form->isValid()) {
            $pc_src->setDownloadTitle(str_replace('"', '', $form->getData("title")));
            $pc_src->setSubCharacteristic($form->getData("subchar"));
            $pc_src->setShowLineNumbers($form->getData("linenumbers") ? "y" : "n");
            $pc_src->setText(
                $pc_src->input2xml($body["code"], 0, false)
            );
            $updated = $page->update();
        }
        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }
}
