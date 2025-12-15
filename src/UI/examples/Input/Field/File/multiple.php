<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\File;

use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   The example shows the behaviour of multiple File Field's inside the same Form Container.
 *
 * expected output: >
 *   ILIAS shows a Standard Form with two File Field's. The Form is always submittable, regardless
 *   of whether files are selected or which File Field is used for this. After submission, a
 *   generated file ID is displayed above the Form for each File Field that had a file selected.
 * ---
 */
function multiple(): string
{
    global $DIC;

    $http = $DIC->http();
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $get_request = $http->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();

    $example_uri = $data_factory->uri((string) $http->request()->getUri());
    $url_builder = new URLBuilder($example_uri);
    [$process_form_url_builder, $process_form_parameter] = $url_builder->acquireParameter(explode('\\', __NAMESPACE__), "process");

    $file_input_one = $factory->input()->field()->file(new \ilUIDemoFileUploadHandlerGUI(), "Upload File");
    $file_input_two = $factory->input()->field()->file(new \ilUIDemoFileUploadHandlerGUI(), "Upload More");

    $form = $factory->input()->container()->form()->standard(
        (string) $process_form_url_builder->withParameter($process_form_parameter, '1')->buildURI(),
        [$file_input_one, $file_input_two]
    );

    // simulates a form processing endpoint:
    if ($get_request->has($process_form_parameter->getName())) {
        $form = $form->withRequest($http->request());
        $data = $form->getData();
    } else {
        $data = 'No submitted data yet.';
    }

    return '<pre>' . print_r($data, true) . '</pre>' . $renderer->render($form);
}
