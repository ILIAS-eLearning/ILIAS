<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\RoundTrip;

function show_form_in_modal()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $post_wrapper = $DIC->http()->wrapper()->post();

    // Build the form
    $form = new \ilPropertyFormGUI();
    $form->setTitle("");
    $form->setTarget("");
    $form->setId(uniqid('form'));
    $item = new \ilTextInputGUI('Firstname', 'firstname');
    $item->setRequired(true);
    $form->addItem($item);
    $item = new \ilTextInputGUI('Lastname', 'lastname');
    $item->setRequired(true);
    $form->addItem($item);
    $form->setFormAction("");
    $item = new \ilHiddenInputGUI('cmd');
    $item->setValue('submit');
    $form->addItem($item);

    // Build a submit button (action button) for the modal footer
    $form_id = 'form_' . $form->getId();
    $submit = $factory->button()->primary('Submit', '#')
        ->withOnLoadCode(function ($id) use ($form_id) {
            return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
        });

    // Check if the form was submitted, if validation fails, show it again in a modal
    $out = '';
    $valid = true;
    if ($post_wrapper->has('cmd') && $post_wrapper->retrieve('cmd', $refinery->kindlyTo()->string()) == 'submit') {
        if ($form->checkInput()) {
            // TODO PHP8: check superglobal
            $panel = $factory->panel()->standard('Form validation successful', $factory->legacy(print_r($_POST, true)));
            $out = $renderer->render($panel);
        } else {
            $form->setValuesByPost();
            $valid = false;
        }
    }

    $modal = $factory->modal()->roundtrip('User Details', $factory->legacy($form->getHTML()))
        ->withActionButtons([$submit]);

    // The modal triggers its show signal on load if validation failed
    if (!$valid) {
        $modal = $modal->withOnLoad($modal->getShowSignal());
    }
    $button1 = $factory->button()->standard('Show Form', '#')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button1, $modal]) . $out;
}
