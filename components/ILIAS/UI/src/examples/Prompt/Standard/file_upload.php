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

namespace ILIAS\UI\examples\Prompt\Standard;

use ILIAS\UI\Component\Prompt\IsPromptContent;
use ILIAS\UI\URLBuilder;
use Generator;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\UI\Component\Entity\EntityRetrieval;
use ilUIDemoFileUploadHandlerGUI;

/**
 * ---
 * description: >
 *   Example showing how to use the Input Field File inside a Prompt (State) component.
 *   It includes minimal data processing.
 *
 * expected output: >
 *   ILIAS initially shows a button. When clicked, a dialog opens with a form visible.
 *   Inside the form there is one input for uploading files. When operated, files can
 *   be chosen using the browsers dialog. After choosing a file, one of the buttons at
 *   the bottom of the first dialog, can be used to submit the form. When pressed, files
 *   start uploading. After a while the form is submitted and the initial dialog is
 *   updated, while the page is not refreshed. Inside this new dialog state, an element
 *   appears that contains the submitted data. In this data the identifier of the uploaded
 *   file is be visible as an arbitrary string.
 * ---
 */
function file_upload(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $http = $DIC->http();
    $server_request = $DIC->http()->request();
    $get_request = $DIC->http()->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();

    $here_url = $data_factory->uri((string) $DIC->http()->request()->getUri());
    $here_url_builder = new URLBuilder($here_url);

    // will simulate a GET request to this example asking for a prompt state
    [$form_get_url, $form_parameter] = $here_url_builder->acquireParameter([...explode(__NAMESPACE__, '\\'), __METHOD__], "get_form", "1");

    // will simulate a POST request to this example that processes the file upload
    [$form_post_url, $process_parameter] = $here_url_builder->acquireParameter([...explode(__NAMESPACE__, '\\'), __METHOD__], "process", "1");

    // will simulate a shared function to create a Form that is accessible in all endpoints
    $build_form = static fn() => $factory->input()->container()->form()->standard(
        (string) $form_post_url->buildURI(),
        [$factory->input()->field()->file(new ilUIDemoFileUploadHandlerGUI(), "Upload file")],
    )->withSubmitLabel('Upload');

    // simulates the POST endpoint for processing the file upload
    if ($get_request->has($process_parameter->getName())) {
        $form = $build_form()->withRequest($server_request);
        $data_message_box = $factory->messageBox()->info(
            "Files have been uploaded. You can verify the submitted data below:" .
            "<pre>" . print_r($form->getData(), true) . "</pre>"
        );
        $html = $renderer->renderAsync($factory->prompt()->state()->show($form, $data_message_box));
        $http->saveResponse(
            $http->response()
                 ->withHeader('Content-Type', 'text/html; charset=utf-8')
                 ->withBody(Streams::ofString($html))
        );
        $http->sendResponse();
        $http->close();
    }

    // simulates the GET endpoint for rendering the prompt state
    if ($get_request->has($form_parameter->getName())) {
        $html = $renderer->renderAsync($factory->prompt()->state()->show($build_form()));
        $http->saveResponse(
            $http->response()
                 ->withHeader('Content-Type', 'text/html; charset=utf-8')
                 ->withBody(Streams::ofString($html))
        );
        $http->sendResponse();
        $http->close();
    }

    // create a Standard Prompt that requests the simulated GET endpoint
    $prompt = $factory->prompt()->standard($form_get_url->buildURI())->withTitle('Upload file');

    // create a Button that triggers the Standard Prompt
    $button = $factory->button()->primary('Upload file', $prompt->getShowSignal());

    return $renderer->render([$prompt, $button]);
}
