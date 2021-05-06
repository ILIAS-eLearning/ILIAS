<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Section;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SectionCommandActionHandler implements Server\CommandActionHandler
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
                return $this->insertCommand($body);
                break;

            case "update":
                return $this->updateCommand($body);
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

    /**
     * Update command
     * @param $body
     * @return Server\Response
     */
    protected function updateCommand($body) : Server\Response
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
