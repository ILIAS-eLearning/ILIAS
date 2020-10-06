<?php
/**
 * This examples modifies a standard form:
 * - add a cancel-button
 * - hide the buttons above the form
 * - customize the submit-label
 */
function modifications()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $text_input = $ui->input()->field()->text("Basic Input", "Just some basic input");
    $section1 = $ui->input()->field()->section([$text_input], "Section 1", "Description of Section 1");

    //Define the form and attach the section.
    $form = $ui->input()->container()->form()->standard("", [$section1]);

    //Enable a cancel-button:
    $data_factory = new \ILIAS\Data\Factory();
    $url = $data_factory->uri('http://www.ilias.de');
    $form = $form->withCancelURL($url);

    //Hide/remove top-buttons for short forms:
    $form = $form->withBottomButtonsOnly(true);

    //Re-label the save-button (do not regularly do that!)
    //The string will still be translated.
    $form = $form->withSubmitLabel('ok');

    //Render the form
    return $renderer->render($form);
}
