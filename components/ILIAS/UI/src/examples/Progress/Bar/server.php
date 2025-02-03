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
 *   Standard Button is clicked, the Progress Bar value us increased by 10% ~every
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
    $endpoint_url = $uri . "&$endpoint_flag=1";
    $endpoint_url = $data_factory->uri($endpoint_url);

    $progress_bar = $factory->progress()->bar('waiting about 10 seconds', $endpoint_url);

    $trigger = $factory->button()->standard('start making progress', '#');
    $trigger = $trigger->withAdditionalOnLoadCode(
        static fn(string $id) => "
            document.getElementById('$id')?.addEventListener('click', (event) => {
                // always 'kick off' async progress bars with an indeterminate state.
                il.UI.Progress.Bar.indeterminate('{$progress_bar->getUpdateSignal()}', 'Estimating...');
                event.target.disabled = true;
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

    $state = match ($task_progress) {
        1 => $state = $factory->progress()->state()->bar()->determinate(10, 'Start processing...'),
        2 => $state = $factory->progress()->state()->bar()->determinate(20),
        3 => $state = $factory->progress()->state()->bar()->determinate(30),
        4 => $state = $factory->progress()->state()->bar()->determinate(40),
        5 => $state = $factory->progress()->state()->bar()->determinate(50, 'Still processing...'),
        6 => $state = $factory->progress()->state()->bar()->determinate(60),
        7 => $state = $factory->progress()->state()->bar()->determinate(70),
        8 => $state = $factory->progress()->state()->bar()->determinate(80),
        9 => $state = $factory->progress()->state()->bar()->determinate(90),
        10 => $state = $factory->progress()->state()->bar()->success("All done!"),
        default => $state = $factory->progress()->state()->bar()->failure("An error ocurred."),
    };

    if (10 > $task_progress) {
        incrementTaskProgress();
    } else {
        resetTask();
    }

    $html = $renderer->renderAsync($state);

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
