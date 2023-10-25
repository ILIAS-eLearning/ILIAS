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

namespace ILIAS\COPage\Editor\Components\Tabs;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TabsCommandActionHandler implements Server\CommandActionHandler
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
        $tabs = new \ilPCTabs($page);
        $tabs->create($page, $hier_id, $pc_id);

        $type = $body["type"];
        $tabs->setTabType($type);
        $tabs->setTemplate("");
        switch ($type) {
            case \ilPCTabs::ACCORDION_VER:
                $t = explode(":", $body["vaccord_templ"] ?? "");
                $tabs->setTemplate($t[2] ?? "");
                $tabs->setBehavior($body["vbehavior"]);
                //$tabs->setHorizontalAlign($body["valign"]);
                break;
            case \ilPCTabs::CAROUSEL:
                $autotime = ($body["auto_time"] != "")
                    ? (int) $body["auto_time"]
                    : null;
                $t = explode(":", $body["carousel_templ"]);
                $tabs->setTemplate($t[2] ?? "");
                //$tabs->setHorizontalAlign($body["calign"]);
                $tabs->setAutoTime($autotime);
                $tabs->setRandomStart((bool) ($body["rand_start"] ?? false));
                break;
        }

        for ($i = 0; $i < (int) $body["nr"]; $i++) {
            $tabs->addTab($this->lng->txt("cont_new_tab"));
        }
        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

    protected function updateCommand(array $body): Server\Response
    {
        $page = $this->page_gui->getPageObject();

        /** @var \ilPCTabs $tabs */
        $tabs = $page->getContentObjectForPcId($body["pcid"]);

        $type = $body["type"];
        $tabs->setTabType($type);
        $tabs->setTemplate("");
        switch ($type) {
            case \ilPCTabs::ACCORDION_VER:
                $t = explode(":", $body["vaccord_templ"] ?? "");
                $tabs->setTemplate($t[2] ?? "");
                $tabs->setBehavior($body["vbehavior"]);
                //$tabs->setHorizontalAlign($body["valign"]);
                break;
            case \ilPCTabs::CAROUSEL:
                $autotime = ($body["auto_time"] != "")
                    ? (int) $body["auto_time"]
                    : null;
                $t = explode(":", $body["carousel_templ"]);
                $tabs->setTemplate($t[2] ?? "");
                //$tabs->setHorizontalAlign($body["calign"]);
                $tabs->setAutoTime($autotime);
                $tabs->setRandomStart((bool) ($body["rand_start"] ?? false));
                break;
        }

        $updated = $page->update();

        return $this->ui_wrapper->sendPage($this->page_gui, $updated);
    }

}
