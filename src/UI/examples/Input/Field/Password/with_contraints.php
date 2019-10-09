<?php
/**
 * Passwords (when setting) usually have some constraints.
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
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example',
        'password2'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, ['pwd'=>$pwd_input]);

    //Step 3: Define some data processing.
    $result = '';
    if ($request->getMethod() == "POST"
            && $request->getQueryParams()['example'] =='password2') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    }

    //Step 4: Render the form/result.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
