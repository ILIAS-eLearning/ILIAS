<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\RoundTrip;

use ILIAS\UI\Implementation\Component\ReplaceSignal;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @return void
 */
function with_async_content(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $http = $DIC->http();

    $async_token = 'isAsync';

    if ($http->wrapper()->query()->has($async_token)) {
        $async_content = $factory->messageBox()->success("successfully loaded content!");

        $http->saveResponse(
            $http->response()
                 ->withBody(Streams::ofString($renderer->render($async_content)))
                 ->withHeader('Content-type', 'text/html; charset=UTF-8')
        );

        $http->sendResponse();
        $http->close();
    }

    $sync_content = $factory->legacy("<p>use the button below to load the content.</p><br />");

    $modal = $factory->modal()->roundtrip('some round', $sync_content);

    $load_content = $modal->getReplaceSignal()->withAsyncRenderUrl(
        $request->getUri()->__toString() . "&$async_token=1"
    );

    $modal = $modal->withActionButtons([
        $factory->button()->primary('load async content', $load_content),
    ]);

    $trigger = $factory->button()->standard('open modal', $modal->getShowSignal());

    return $renderer->render([$modal, $trigger]);
}
