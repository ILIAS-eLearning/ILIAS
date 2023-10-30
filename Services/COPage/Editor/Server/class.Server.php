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
use ILIAS\COPage\PC\Paragraph\ParagraphCommandActionHandler;
use ILIAS\COPage\PC\Grid\GridCommandActionHandler;
use ILIAS\COPage\PC\Section\SectionCommandActionHandler;
use ILIAS\COPage\PC\MediaObject\MediaObjectCommandActionHandler;
use ILIAS\COPage\PC\Table\TableCommandActionHandler;
use ILIAS\COPage\PC\Tabs\TabsCommandActionHandler;
use ILIAS\COPage\PC\Resources\ResourcesCommandActionHandler;
use ILIAS\COPage\PC\SourceCode\SourceCodeCommandActionHandler;
use ILIAS\COPage\PC\InteractiveImage\InteractiveImageCommandActionHandler;
use ILIAS\COPage\PC\LayoutTemplate\LayoutTemplateCommandActionHandler;
use ILIAS\COPage\PC\PlaceHolder\PlaceHolderCommandActionHandler;
use ILIAS\COPage\Page\PageCommandActionHandler;

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

    public function reply(): void
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
    ): QueryActionHandler {
        $handler = null;

        switch ($query["component"]) {
            case "Page":
                $handler = new \ILIAS\COPage\Page\PageQueryActionHandler($this->page_gui, $query["pc_id"] ?? "");
                break;
            case "InteractiveImage":
                $handler = new \ILIAS\COPage\PC\InteractiveImage\InteractiveImageQueryActionHandler($this->page_gui, $query["pc_id"] ?? "");
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
    ): CommandActionHandler {
        $handler = null;

        switch ($body["component"]) {
            case "Paragraph":
                $handler = new ParagraphCommandActionHandler($this->page_gui);
                break;
            case "Page":
                $handler = new PageCommandActionHandler($this->page_gui);
                break;
            case "Grid":
                $handler = new GridCommandActionHandler($this->page_gui);
                break;
            case "Tabs":
                $handler = new TabsCommandActionHandler($this->page_gui);
                break;
            case "Section":
                $handler = new SectionCommandActionHandler($this->page_gui);
                break;
            case "MediaObject":
                $handler = new MediaObjectCommandActionHandler($this->page_gui);
                break;
            case "Table":
            case "DataTable":
                $handler = new TableCommandActionHandler($this->page_gui);
                break;
            case "Resources":
                $handler = new ResourcesCommandActionHandler($this->page_gui);
                break;
            case "SourceCode":
                $handler = new SourceCodeCommandActionHandler($this->page_gui);
                break;
            case "InteractiveImage":
                $handler = new InteractiveImageCommandActionHandler($this->page_gui);
                break;
            case "LayoutTemplate":
                $handler = new LayoutTemplateCommandActionHandler($this->page_gui);
                break;
            case "PlaceHolder":
                $handler = new PlaceHolderCommandActionHandler($this->page_gui);
                break;
        }

        if ($handler === null) {
            throw new Exception("Unknown component " . ((string) $body["component"]));
        }
        return $handler;
    }
}
