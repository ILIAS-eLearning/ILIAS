<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Progress\Bar;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI;

/**
 * ---
 * description: >
 *   This example shows how a Progress Bar can be rendered and updated by the server.
 *   The artificial endpoint uses Progres Bar Instructions to order the clientside
 *   Progress Bar to perform a desired update. A Standard Button can be used to start
 *   this process.
 *
 * expected output: >
 *   ILIAS shows the rendered Progress Bar and Standard Button. The Progress Bar is
 *   initially empty (no progress), and cannot be operated in any way. When the
 *   Stadnard Button is clicked, the Progress Bar value us increased by 10% ~every
 *   second. After the ~10 seconds, the Progress Bar will be finished showing a
 *   successful state.
 * ---
 */
function server(): string
{
    global $DIC;
    $http = $DIC->http();
    $uri = $http->request()->getUri();
    $request = $http->wrapper()->query();
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $data_factory = new \ILIAS\Data\Factory();

    $endpoint_flag = 'progress_bar_example_endpoint';
    $endpoint_url = $uri . "$endpoint_flag=1";
    $endpoint_url = $data_factory->uri($endpoint_url);

    $progress_bar = $factory->progress()->bar('waiting about 10 seconds', $endpoint_url);

    $trigger = $factory->button()->standard('start making progress', '#');
    $trigger = $trigger->withAdditionalOnLoadCode(
        static fn(string $id) => "
            document.getElementById('$id')?.addEventListener('click', ({ target }) => {
                il.UI.Progress.Bar.startAsync('{$progress_bar->getUpdateSignal()->getId()}', 'Estimating');
            });
        "
    );

    if ($request->has($endpoint_flag)) {
        callArtificialTaskEndpoint($http, $factory, $renderer);
    }

    return $renderer->render([$progress_bar, $trigger]);
}

function callArtificialTaskEndpoint(GlobalHttpState $http, UI\Factory $factory, UI\Renderer $renderer): void
{
    initialiseArtificialTaskOnce();

    $task_progress = getTaskProgress();

    $instruction = match ($task_progress) {
        1 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.10),
        2 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.20),
        3 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.30),
        4 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.40),
        5 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.50, 'Still processing.'),
        6 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.60),
        7 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.70),
        8 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.80),
        9 => $instruction = $factory->progress()->instruction()->bar()->determinate(0.90),
        10 => $instruction = $factory->progress()->instruction()->bar()->success("All done!"),
        default => $instruction = $factory->progress()->instruction()->bar()->failure("An error ocurred."),
    };

    if (10 > $task_progress) {
        incrementTaskProgress();
    } else {
        resetTask();
    }

    $html = $renderer->renderAsync($instruction);

    $http->saveResponse(
        $http->response()
             ->withHeader('Content-Type', 'text/html; charset=utf-8')
             ->withBody(Streams::ofString($html))
    );
    $http->sendResponse();
    $http->close();
}

function initialiseArtificialTaskOnce(): void
{
    if (!\ilSession::has(__NAMESPACE__ . '_example_task_progress')) {
        \ilSession::set(__NAMESPACE__ . '_example_task_progress', 1);
    }
}

function incrementTaskProgress(): void
{
    $previous_value = \ilSession::get(__NAMESPACE__ . '_example_task_progress');
    \ilSession::set(__NAMESPACE__ . '_example_task_progress', (int) $previous_value + 1);
}

function getTaskProgress(): int
{
    return \ilSession::get(__NAMESPACE__ . '_example_task_progress') ?? 1;
}

function resetTask(): void
{
    \ilSession::clear(__NAMESPACE__ . '_example_task_progress');
}
