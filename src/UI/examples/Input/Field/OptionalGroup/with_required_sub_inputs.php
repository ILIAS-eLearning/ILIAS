<?php

/**
 * Example showing how an optional group (of inputs) which shows, that
 * the optional input will not be required even though it's sub inputs
 * are.
 */
function with_required_sub_inputs()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $optional_group = $factory->input()->field()->optionalGroup([
        $factory->input()->field()->text(
            'this input is required',
            'but only if the optional group is checked'
        )->withRequired(true)
    ], 'this input is not required');

    $form = $factory->input()->container()->form()->standard('#', [$optional_group]);

    if ("POST" === $request->getMethod()) {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    return "<pre>" . print_r($result, true) . "</pre>" . $renderer->render($form);
}
