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

declare(strict_types=1);

use ILIAS\UI\Component\Input\Field\MarkdownRenderer;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Data\URI;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUIMarkdownPreviewGUI implements MarkdownRenderer, ilCtrlBaseClassInterface
{
    protected const CMD_RENDER_ASYNC = 'renderAsync';

    protected Refinery $refinery;
    protected ilCtrlInterface $ctrl;
    protected HTTPServices $http;

    public function __construct()
    {
        global $DIC;

        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
    }

    public function executeCommand(): void
    {
        if (!$this->ctrl->isAsynch() && self::CMD_RENDER_ASYNC !== $this->ctrl->getCmd()) {
            $this->http->saveResponse(
                $this->http
                    ->response()
                    ->withBody(Streams::ofString('Whoops, something went wrong!'))
                    ->withStatus(404)
            );

            $this->http->sendResponse();
            $this->http->close();
        }

        $this->renderAsync();
    }

    public function getAsyncUrl(): string
    {
        return $this->ctrl->getLinkTargetByClass(
            self::class,
            self::CMD_RENDER_ASYNC,
            null,
            true
        );
    }

    public function getParameterName(): string
    {
        return 'raw_markdown';
    }

    public function renderAsync(): void
    {
        $parameter_name = $this->getParameterName();

        if (!$this->http->wrapper()->post()->has($parameter_name)) {
            $this->sendResponse('');
        }

        $raw_markdown = $this->http->wrapper()->post()->retrieve(
            $parameter_name,
            $this->refinery->to()->string()
        );

        $this->sendResponse($this->render($raw_markdown));
    }

    public function render(string $markdown_text): string
    {
        return $this->refinery->string()->markdown()->toHTML()->transform($markdown_text);
    }

    protected function sendResponse(string $html): void
    {
        $this->http->saveResponse(
            $this->http->response()->withBody(Streams::ofString($html))
        );

        $this->http->sendResponse();
        $this->http->close();
    }
}
