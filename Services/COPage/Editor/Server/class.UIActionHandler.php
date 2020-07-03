<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

use ILIAS\DI\Exceptions\Exception;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class UIActionHandler implements ActionHandler
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

    function __construct(\ilPageObjectGUI $page_gui)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
    }

    /**
     * @param $query
     * @param $body
     * @return Response
     */
    public function handle($query, $body) : Response
    {
        $action = explode(".", $query["action"]);
        if ($action[0] === "ui") {
            switch ($action[1]) {
                case "all":
                    return $this->allCommand();
                    break;
            }
        }
        throw new Exception("Unknown action " . $query["action"]);
    }

    /**
     * All command
     * @param
     * @return
     */
    protected function allCommand() : Response
    {
        $f = $this->ui->factory();
        $dd = $f->dropdown()->standard([
            $f->link()->standard("label", "#")
        ]);
        $r = $this->ui->renderer();
        $o = new \stdClass();
        $o->addDropdown = $r->render($dd);
        $o->addCommands = $this->getAddCommands();
        return new Response($o);
    }

    /**
     *
     * @param
     * @return
     */
    protected function getAddCommands()
    {
        $lng = $this->lng;

        $commands = [];

        $config = $this->page_gui->getPageConfig();
        foreach ($config->getEnabledTopPCTypes() as $def) {
            $commands[$def["pc_type"]] = $lng->txt("cont_ed_insert_" . $def["pc_type"]);
        }
        return $commands;
    }

}