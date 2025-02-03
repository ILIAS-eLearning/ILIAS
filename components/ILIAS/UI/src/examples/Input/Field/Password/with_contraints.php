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

namespace ILIAS\UI\examples\Input\Field\Password;

/**
 * ---
 * description: >
 *   Passwords (when setting) usually have some constraints.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Password". An inserted text won't be displayed but exchanged with dots.
 *   ILIAS will apply your password if the following requirements are fullfilled:
 *   - At least eight letters
 *   - At least one number
 *   - At least one capital letter
 *   - At least one lowercase letter
 *   - At least one special character
 *   Else ILIAS will display an error message above the input field.
 *   Clicking "Save" will reload the page and show your input in the following format in the box above:
 *
 *   Array
 *   (
 *      [pwd] => ILIAS\Data\Password Object
 *      (
 *          [pass:ILIAS\Data\Password:private] => Passwort-1
 *      )
 *   )
 * ---
 */
function with_contraints()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $lng = $DIC->language();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $data = new \ILIAS\Data\Factory();
    $refinery = new \ILIAS\Refinery\Factory($data, $lng);
    $pw_validation = $refinery->password();

    //Step 1: Define the input field
    //and add some constraints.
    $pwd_input = $ui->input()->field()->password("Password", "constraints in place.")
        ->withAdditionalTransformation(
            $refinery->logical()->parallel([
                $pw_validation->hasMinLength(8),
                $pw_validation->hasLowerChars(),
                $pw_validation->hasUpperChars(),
                $pw_validation->hasNumbers(),
                $pw_validation->hasSpecialChars()
            ])
        );

    //Step 2: Define the form and attach the field.
    $form = $ui->input()->container()->form()->standard('#', ['pwd' => $pwd_input]);

    //Step 3: Define some data processing.
    $result = '';
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    }

    //Step 4: Render the form/result.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
