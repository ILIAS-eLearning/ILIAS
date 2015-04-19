<html>
    <head>
    </head>
    <body>
<?php

require_once("formlets.php");

$br = text("<br />");

$int_input = text_input("10")
    ->satisfies(_fn("is_numeric"), "No integer!")
    ->map(_fn("intval", 1))
    ;

$all_inputs = formlet( 
    inject(collect()),
    with_label("A text input: ", text_input()), $br,
    with_label("A int input: ", with_errors($int_input)), $br,
    with_label("A checkbox input...", checkbox()), $br,
    with_label("A textarea input...", textarea()), $br,
    with_label("A button input...", button("button")), $br,
    with_label("An email input...", email()), $br,
    with_label("A hidden input...", hidden("hidden")), $br,
    with_label("A number input...", number(10, 0, 100, 5)), $br,
    with_label("A password input...", password()), $br,
    with_label("A reset input...", reset_button("RESET")), $br,
    with_label("A search input...", search("RESET")), $br,
    with_label("A url input...", url()), $br,
    with_label("A select input...", select(array("one", "two", "three"))), $br,
    with_label("A radio input...", radio(array("aa", "bb", "cc"))),
    inject(stop())
    );

$formlet = formlet(
    inject(collect()),
    $all_inputs, $br, $br,
    fieldset("In Fieldset: ", $all_inputs),
    submit("Absenden", array(), true),
    inject(stop())
    );

try {
    $form = form("example", "example.php", $formlet);
    $form->init();

    echo $form->html();

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
    echo $e;
    echo "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
    </body>

</html>
