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

namespace ILIAS\COPage\Editor\Components\Section;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SectionCommandActionHandler implements Server\CommandActionHandler
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

        // if ($form->checkInput()) {
        $sec = new \ilPCSection($page);
        $sec->create($page, $hier_id, $pc_id);
        $sec_gui = new \ilPCSectionGUI($page, $sec, "", "");
        $sec_gui->setPageConfig($page->getPageConfig());

        $form = $sec_gui->initForm(true);

        // note: we  have everyting in _POST here, form works the usual way
        $updated = true;
        if ($form->checkInput()) {
            $sec_gui->setValuesFromForm($form);
            $updated = $page->update();
        }

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function updateCommand(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();
        $page->addHierIDs();
        $hier_id = $page->getHierIdForPcId($body["pcid"]);
        $sec = $page->getContentObjectForPcId($body["pcid"]);
        $sec_gui = new \ilPCSectionGUI($page, $sec, $hier_id, $body["pcid"]);
        $sec_gui->setPageConfig($page->getPageConfig());

        $form = $sec_gui->initForm(false);

        // note: we  have everyting in _POST here, form works the usual way
        $updated = true;
        if ($form->checkInput()) {
            $sec_gui->setValuesFromForm($form);
            $updated = $page->update();
        }

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }
}
