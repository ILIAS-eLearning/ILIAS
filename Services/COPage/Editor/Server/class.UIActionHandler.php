<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

use ILIAS\DI\Exceptions\Exception;
use test\Mockery\Adapter\Phpunit\MockeryPHPUnitIntegrationTest;

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
        $o->pageHelp = $this->getPageHelp();
        $o->multiActions = $this->getMultiActions();
        return new Response($o);
    }

    /**
     * Get add commands
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

    /**
     * Get page help (drag drop explanation)
     * @param
     * @return
     */
    protected function getPageHelp()
    {
        // @todo remove legacy
        $lng = $this->lng;
        $lng->loadLanguageModule("content");
        $tpl = new \ilTemplate("tpl.editor_slate.html", true, true, "Services/COPage");
        $tpl->setCurrentBlock("help");
        $tpl->setVariable("TXT_ADD_EL", $lng->txt("cont_add_elements"));
        $tpl->setVariable("PLUS", \ilGlyphGUI::get(\ilGlyphGUI::ADD));
        $tpl->setVariable("DRAG_ARROW", \ilGlyphGUI::get(\ilGlyphGUI::DRAG));
        $tpl->setVariable("TXT_DRAG", $lng->txt("cont_drag_and_drop_elements"));
        $tpl->setVariable("TXT_SEL", $lng->txt("cont_double_click_to_delete"));
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }

    /**
     * Get multi actions
     * @return string
     */
    protected function getMultiActions()
    {
        $ui = $this->ui;
        $r = $ui->renderer();

        // @todo: what kind of ks elements are these? button groups?
        $tpl = new \ilTemplate("tpl.editor_multi_actions.html", true, true, "Services/COPage");

        $sections = [
            [
                "cut" => "cut",
                "copy" => "copy",
                "delete" => "delete"
            ],
            [
                "all" => "select_all",
                "none" => "cont_select_none",
            ],
            [
                "activate" => "cont_ed_enable",
                "characteristic" => "cont_assign_characteristic"
            ]
        ];

        foreach ($sections as $buttons) {
            foreach ($buttons as $action => $lng_key) {
                $tpl->setCurrentBlock("button");
                $tpl->setVariable("BUTTON", $r->renderAsync($this->getMultiActionButton($lng_key, $action)));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("section");
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Get multi button
     * @param string $lng_key
     * @param string $action
     * @return \ILIAS\UI\Component\Button\Standard
     */
    protected function getMultiActionButton($lng_key, $action)
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $lng = $this->lng;
        return $f->button()->standard($lng->txt($lng_key), "#")
          ->withOnLoadCode(
              function ($id) use ($action){
                  return "document.querySelector('#$id').setAttribute('data-copg-ed-type', 'multi');
                  console.log('multi button js');
                  document.querySelector('#$id').setAttribute('data-action', '$action');";
              }
          );
    }

}