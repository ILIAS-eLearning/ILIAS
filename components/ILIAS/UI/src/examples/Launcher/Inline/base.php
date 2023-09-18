<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Launcher\Inline;

function base()
{
    global $DIC;
    $ui_factory = $DIC->ui()->factory();
    $data_factory = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();
    $spacer = $ui_factory->divider()->horizontal();

    $url = $data_factory->uri($DIC->http()->request()->getUri()->__toString());

    $target = $data_factory->link('Start Test', $url);
    $launcher = $ui_factory->launcher()
        ->inline($target)
        ->withDescription('<p>You will have <strong>90 minutes to answer all questions.</strong></p><p>Please make sure that you have the time to complete the test and that you will be undisturbed. There is no way for you to pause or re-take this test.</p>');

    $icon = $ui_factory->symbol()->icon()->standard('ps', '', 'large');

    $status_message =  $ui_factory->messageBox()->failure("You have to complete all preconditions first.")
        ->withLinks([
            $ui_factory->link()->standard("Do this first", "#"),
            $ui_factory->link()->standard("And this is mandatory, too", "#")
        ]);

    $launcher2 = $launcher
        ->withDescription('<p>This course goes into advanced knowledge for students preparing for the final exam. You should have a firm understanding of the basics before enrolling.</p>')
        ->withButtonLabel('Course is locked', false)
        ->withStatusIcon($icon)
        ->withStatusMessageBox($status_message);

    $target = $data_factory->link('Continue Survey', $url);
    $progressmeter = $ui_factory->chart()->progressMeter()->mini(100, 50, 100);
    $status_message = $ui_factory->messageBox()->info("There are still 8 questions left in the survey.");
    $launcher3 = $ui_factory->launcher()
        ->inline($target)
        ->withStatusIcon($progressmeter)
        ->withStatusMessageBox($status_message)
        ->withDescription('');

    $target = $data_factory->link('Repeat Test', $url);
    $status_message = $ui_factory->messageBox()->success("You have completed this test already. If you want you can try it again.");
    $icon = $ui_factory->symbol()->icon()->standard('task', '', 'large');
    $launcher4 = $ui_factory->launcher()
        ->inline($target)
        ->withStatusIcon($icon)
        ->withStatusMessageBox($status_message);

    return $renderer->render([
            $launcher,
            $spacer,
            $launcher2,
            $spacer,
            $launcher3,
            $spacer,
            $launcher4
    ]);
}
