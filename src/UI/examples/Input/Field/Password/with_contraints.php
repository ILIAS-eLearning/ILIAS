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
    $validation = new \ILIAS\Validation\Factory($data, $lng);
    $pw_validation = $validation->password();

    //Step 1: Define the input field
    //and add some constraints.
    $pwd_input = $ui->input()->field()->password("Password", "constraints in place.")
        ->withAdditionalConstraint(
            $validation->parallel([
                $pw_validation->hasMinLength(8),
                $pw_validation->hasLowerChars(),
                $pw_validation->hasUpperChars(),
                $pw_validation->hasNumbers(),
                $pw_validation->hasSpecialChars()
            ])
        );
    //the above can be shortcut into:
    $pwd_input2 = $ui->input()->field()->password("Password", "constraints in place.")
        ->withStandardConstraints(8, true, true, true, true);

    //Step 2: Define the form and attach the field.
    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example',
        'password'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, ['pwd' => $pwd_input, 'pwd2' => $pwd_input2]);

    //Step 3: Define some data processing.
    $result = '';
    if ($request->getMethod() == "POST"
            && $request->getQueryParams()['example'] == 'password') {
        $form = $form->withRequest($request);
        $result = $form->getData();
    }

    //Step 4: Render the form.
    return
        $renderer->render($form);
}
