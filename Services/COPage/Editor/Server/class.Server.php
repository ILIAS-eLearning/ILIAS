<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Server;

use \Psr\Http\Message;
use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Components\Page;
use ILIAS\COPage\Editor\Components\Paragraph;
use ILIAS\COPage\Editor\Components\Grid;
use ILIAS\COPage\Editor\Components\Section;
use ILIAS\COPage\Editor\Components\MediaObject;
use ILIAS\COPage\Editor\Components\Table;

/**
 * Page editor json server
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class Server
{
    /**
     * @var ilLogger
     */
    protected $log;
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
        Message\ServerRequestInterface $request
    ) {
        $this->request = $request;
        $this->ui = $ui;
        $this->page_gui = $page_gui;
        $this->log = \ilLoggerFactory::getLogger('copg');
    }

    /**
     * Reply
     */
    public function reply()
    {
        $this->log->debug("Start replying...");
        $query = $this->request->getQueryParams();

        try {
            if (is_array($_POST) && count($_POST) > 0) {
                $body = $this->request->getParsedBody();
            } else {
                $body = json_decode($this->request->getBody()->getContents(), true);
            }
            if (isset($query["component"])) {
                $action_handler = $this->getActionHandlerForQuery($query);
                $response = $action_handler->handle($query);
            } else {
                //sleep(5);
                $action_handler = $this->getActionHandlerForCommand($query, $body);
                $response = $action_handler->handle($query, $body);
            }
        } catch (Exception $e) {
            $data = new \stdClass();
            $this->log->error($e->getMessage()."\n".$e->getTraceAsString());
            $data->error = $e->getMessage();
            if (defined('DEVMODE') && DEVMODE) {
                $data->error.= "<br><br>".nl2br($e->getTraceAsString());
            }
            $response = new Response($data);
        }

        $this->log->debug("... sending response");
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

        switch ($query["component"]) {
            case "Page":
                $handler = new Page\PageQueryActionHandler($this->page_gui);
                break;
        }

        if ($handler === null) {
            throw new Exception("Unknown Action " . ((string) $query));
        }
        return $handler;
    }

    /**
     * Get action handler for query
     * @param
     * @return
     */
    protected function getActionHandlerForCommand($query, $body)
    {
        $handler = null;

        switch ($body["component"]) {
            case "Paragraph":
                $handler = new Paragraph\ParagraphCommandActionHandler($this->page_gui);
                break;
            case "Page":
                $handler = new Page\PageCommandActionHandler($this->page_gui);
                break;
            case "Grid":
                $handler = new Grid\GridCommandActionHandler($this->page_gui);
                break;
            case "Section":
                $handler = new Section\SectionCommandActionHandler($this->page_gui);
                break;
            case "MediaObject":
                $handler = new MediaObject\MediaObjectCommandActionHandler($this->page_gui);
                break;
            case "Table":
                $handler = new Table\TableCommandActionHandler($this->page_gui);
                break;
        }

        if ($handler === null) {
            throw new Exception("Unknown component " . ((string) $body["component"]));
        }
        return $handler;
    }
}
