<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

use \Psr\Http\Message;
use ILIAS\DI\Exceptions\Exception;

/**
 * Page editor json server
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class Server
{
    /**
     * @var \ilPageObjectGUI
     */
    protected $page_gui;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;


    /**
     * @var Message\ServerRequestInterface
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(
        \ilPageObjectGUI $page_gui,
        \ILIAS\DI\UIServices $ui,
        Message\ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->ui = $ui;
        $this->page_gui = $page_gui;
    }

    /**
     * Reply
     */
    public function reply()
    {
        $query = $this->request->getQueryParams();
        $body = $this->request->getParsedBody();
        $action_handler = $this->getActionHandlerForQuery($query);
        $response = $action_handler->handle($query, $body);
        $response->send();
    }

    /**
     * Get action handler for query
     * @param
     * @return
     */
    protected function getActionHandlerForQuery($query)
    {
        $handler = null;
        if (isset($query["action"]) && is_int(strpos($query["action"], "."))) {
            $action_arr = explode(".", $query["action"]);

            switch ($action_arr[0]) {
                case "ui":
                    $handler = new UIActionHandler($this->page_gui);
                    break;
            }
        }

        if ($handler === null) {
            throw new Exception("Unknown Action ".((string) $query));
        }
        return $handler;
    }

}