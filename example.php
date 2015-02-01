<html>
    <head>
    </head>
    <body>
<?php

require_once("formlets.php");

$br = text("<br />");

$int_input = text_input()
    ->satisfies(_fn("is_numeric"), "No integer!")
    ->map(_fn("intval", 1))
    ;

$all_inputs = 
    inject(collect())
    ->cmb(with_label("A text input: ", text_input()))
    ->cmb($br)
    ->cmb(with_label("A int input: ", with_errors($int_input)))
    ->cmb($br)
    ->cmb(with_label("A checkbox input...", checkbox()))
    ->cmb($br)
    ->cmb(with_label("A textarea input...", textarea()))
    ->cmb(inject(stop()))
    ;

$formlet =
    inject(collect())
    ->cmb($all_inputs)
    ->cmb($br)
    ->cmb($br)
    ->cmb(fieldset("In Fieldset: ", $all_inputs))
    ->cmb(submit("Absenden", array(), true))
    ->cmb(inject(stop()))
    ;

try {
    $form = form("example", "example.php", $formlet);
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
        echo "Error: ".$form->error()."<br />";
    }
}
catch (Exception $e) {
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
    </body>

</html>
