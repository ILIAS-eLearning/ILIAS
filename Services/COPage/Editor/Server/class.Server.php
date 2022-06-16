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

namespace ILIAS\COPage\Editor\Server;

use Psr\Http\Message;
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
    protected \ilPageObjectGUI $page_gui;
    protected \ILIAS\DI\UIServices $ui;
    protected Message\ServerRequestInterface $request;
    protected \ilLogger $log;

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

    public function reply() : void
    {
        $this->log->debug("Start replying...");
        $query = $this->request->getQueryParams();
        $post = $this->request->getParsedBody();

        try {
            if (isset($post) && is_array($post) && count($post) > 0) {
                $body = $post;
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
            $this->log->error($e->getMessage() . "\n" . $e->getTraceAsString());
            $data->error = $e->getMessage();
            if (defined('DEVMODE') && DEVMODE) {
                $data->error .= "<br><br>" . nl2br($e->getTraceAsString());
            }
            $response = new Response($data);
        }

        $this->log->debug("... sending response");
        $response->send();
    }

    protected function getActionHandlerForQuery(
        array $query
    ) : QueryActionHandler {
        $handler = null;

        switch ($query["component"]) {
            case "Page":
                $handler = new Page\PageQueryActionHandler($this->page_gui);
                break;
        }

        if ($handler === null) {
            throw new Exception("Unknown Component " . ((string) $query["component"]));
        }
        return $handler;
    }

    protected function getActionHandlerForCommand(
        array $query,
        array $body
    ) : CommandActionHandler {
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
