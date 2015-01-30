<html>
    <head>
    </head>
    <body>
<?php

require_once("formlets.php");

$br = _text("<br />");

$int_input = _text_input()
    ->satisfies(_fn("is_numeric"), "No integer!")
    ->map(_fn("intval", 1))
    ;

$all_inputs = 
    _pure(_collect())
    ->cmb(_with_label("A text input: ", _text_input()))
    ->cmb($br)
    ->cmb(_with_label("A int input: ", _with_errors($int_input)))
    ->cmb($br)
    ->cmb(_with_label("A checkbox input...", _checkbox()))
    ->cmb($br)
    ->cmb(_with_label("A textarea input...", _textarea()))
    ->cmb(_pure(stop()))
    ;

$formlet =
    _pure(_collect())
    ->cmb($all_inputs)
    ->cmb($br)
    ->cmb($br)
    ->cmb(_fieldset("In Fieldset: ", $all_inputs))
    ->cmb(_submit("Absenden", array(), true))
    ->cmb(_pure(stop()))
    ;

try {
    $form = form("test", "test_view.php", $formlet);
    $form->init();

    echo $form->display();

    if ($form->wasSuccessfull()) {
        echo "<hr />";
        echo "Results are:<br />";
        echo "<pre>";
        print_r($form->result());
        echo "</pre>";
    }
    else if ($form->_result() !== null) {
        $dict = new RenderDict($_POST, $form->_result());
        echo "Error: ".$form->error()."<br />";
        echo "<pre>";
        print_r($dict);
        echo "</pre>";
    }
}
catch (Exception $e) {
    echo $e->getTraceAsString();
}
?>
    </body>

</html>
