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

use Psr\Http\Message;
use ILIAS\COPage\Editor\Server;

/**
 * Adapter for JSON frontend.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageEditorServerAdapterGUI
{
    protected ilPageObjectGUI $page_gui;
    protected \ILIAS\DI\UIServices $ui;
    protected ilCtrl $ctrl;
    protected Message\ServerRequestInterface $request;

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

    public function executeCommand(): void
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
    protected function invokeServer(): void
    {
        $server = new Server\Server($this->page_gui, $this->ui, $this->request);
        $server->reply();
    }
}
