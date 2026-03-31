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

namespace ILIAS\Questions\Presentation\Layout;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\UI\Component\Prompt\State\State;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Renderer as UIRenderer;

class Async
{
    public function __construct(
        private readonly HttpService $http,
        private readonly InterruptiveModal|RoundTripModal|MessageBox|State|array|string $content
    ) {
    }

    public function render(
        UIRenderer $ui_renderer
    ): void {
        $rendered_content = is_string($this->content)
            ? $this->content
            : $ui_renderer->renderAsync($this->content);

        $this->http->saveResponse(
            $this->http->response()->withBody(
                Streams::ofString(
                    $rendered_content
                )
            )
        );
        $this->http->sendResponse();
        $this->http->close();
    }


}
