<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Launcher\Inline;

use ILIAS\Data\URI;
use ILIAS\Data\Result;
use ILIAS\UI\Component\Launcher\Launcher;

/**
 * In this example, the Launcher is configured with inputs;
 * a Modal containing a Form will open upon clicking the launch-button.
 */
function with_fields()
{
    global $DIC;
    $ui_factory = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $data_factory = new \ILIAS\Data\Factory();
    $request = $DIC->http()->request();
    $ctrl = $DIC['ilCtrl'];
    $spacer = $ui_factory->divider()->horizontal();

    $url = $data_factory->uri($DIC->http()->request()->getUri()->__toString());
    $url = $url->withParameter('launcher_redirect', '');

    //A Launcher with a checkbox-form
    $description = '<p>Before you can join this group, you will have to accept the terms and conditions</p>';
    $instruction = $ui_factory->messageBox()->info('Accept the conditions.');
    $group = $ui_factory->input()->field()->group([
            $ui_factory->input()->field()->checkbox('Understood', 'ok')
    ]);
    $evaluation = function (Result $result, Launcher &$launcher) use ($ctrl, $ui_factory) {
        if ($result->isOK() && $result->value()[0]) {
            $ctrl->redirectToURL(
                (string)$launcher->getTarget()->getURL()->withParameter('launcher_redirect', 'terms accepted (' . $launcher->getButtonLabel() . ')')
            );
        }
        $launcher = $launcher->withStatusMessageBox($ui_factory->messageBox()->failure('You must accept the conditions.'));
    };

    $target = $data_factory->link('Join Group', $url->withParameter('launcher_id', 'l1'));

    $launcher = $ui_factory->launcher()
        ->inline($target)
        ->withDescription($description)
        ->withInputs($group, $evaluation, $instruction);

    if (array_key_exists('launcher_id', $request->getQueryParams()) && $request->getQueryParams()['launcher_id'] === 'l1') {
        $launcher = $launcher->withRequest($request);
    }

    //A Launcher with icon
    $icon = $ui_factory->symbol()->icon()->standard('auth', 'authentification needed', 'large');
    $description = '<p>Before you can take the survey, you have to agree to our terms and conditions.</p>';
    $target = $data_factory->link('Take Survey', $url->withParameter('launcher_id', 'l2'));
    $launcher2 = $ui_factory->launcher()
        ->inline($target)
        ->withStatusIcon($icon)
        ->withButtonLabel('Take Survey')
        ->withDescription($description)
        ->withInputs($group, $evaluation);

    if (array_key_exists('launcher_id', $request->getQueryParams()) && $request->getQueryParams()['launcher_id'] === 'l2') {
        $launcher2 = $launcher2->withRequest($request);
    }


    //A Launcher with password field
    $icon = $ui_factory->symbol()->icon()->standard('ps', 'authentification needed', 'large');
    $status_message = $ui_factory->messageBox()->info("You will be asked for your personal passcode when you start the test.");
    $instruction = $ui_factory->messageBox()->info('Fill the form; use password "ilias" to pass');
    $group = $ui_factory->input()->field()->group([
            $ui_factory->input()->field()->password('pwd', 'Password')
    ]);
    $evaluation = function (Result $result, Launcher &$launcher) use ($ctrl, $ui_factory) {
        if ($result->isOK() && $result->value()[0]->toString() === 'ilias') {
            $ctrl->redirectToURL(
                (string)$launcher->getTarget()->getURL()->withParameter('launcher_redirect', 'password protected')
            );
        }
        $launcher = $launcher->withStatusMessageBox($ui_factory->messageBox()->failure('nope. wrong pass.'));
    };

    $target = $data_factory->link('Begin Exam', $url->withParameter('launcher_id', 'l3'));
    $launcher3 = $ui_factory->launcher()
        ->inline($target)
        ->withDescription('')
        ->withInputs($group, $evaluation, $instruction)
        ->withStatusIcon($icon)
        ->withStatusMessageBox($status_message)
        ->withModalSubmitLabel('Begin Exam')
        ->withModalCancelLabel('Cancel')
    ;

    if (array_key_exists('launcher_id', $request->getQueryParams()) && $request->getQueryParams()['launcher_id'] === 'l3') {
        $launcher3 = $launcher3->withRequest($request);
    }


    $result = "not submitted or wrong pass";

    if (array_key_exists('launcher_redirect', $request->getQueryParams())
        && $v = $request->getQueryParams()['launcher_redirect']
    ) {
        $result = "<b>sucessfully redirected ($v)</b>";
    }

    return $result . "<hr/>" . $renderer->render([
        $launcher,
        $spacer,
        $launcher2,
        $spacer,
        $launcher3
    ]);
}
