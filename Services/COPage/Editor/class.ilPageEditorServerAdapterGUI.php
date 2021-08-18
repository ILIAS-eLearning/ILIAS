<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use \Psr\Http\Message;
use \ILIAS\COPage\Editor\Server;

/**
 * Adapter for JSON frontend.
 *
 * @author killing@leifos.de
 */
class ilPageEditorServerAdapterGUI
{
    /**
     * @var ilPageObjectGUI
     */
    protected $page_gui;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var Message\ServerRequestInterface
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(
        ilPageObjectGUI $page_gui,
        ilCtrl $ctrl,
        \ILIAS\DI\UIServices $ui,
        Message\ServerRequestInterface $request
    ) {
        $this->request = $request;
        $this->ctrl = $ctrl;
        $this->ui = $ui;
        $this->page_gui = $page_gui;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("invokeServer");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("invokeServer"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Invoke rpc server
     */
    protected function invokeServer()
    {
        $server = new Server\Server($this->page_gui, $this->ui, $this->request);
        $server->reply();
    }
}
