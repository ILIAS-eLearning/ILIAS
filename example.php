<html>
    <head>
    </head>
    <body>
<?php

require_once("tests/autoloader.php");

use Lechimp\Formlets\Formlets as F;

$br = F::text("<br />");

$int_input = F::text_input("10")
    ->satisfies(F::fun("is_numeric"), "No integer!")
    ->map(F::fun("intval", 1))
    ;

$all_inputs = F::formlet( 
    F::inject(F::collect()),
    F::with_label("A text input: ", F::text_input()), $br,
    F::with_label("A int input: ", F::with_errors($int_input)), $br,
    F::with_label("A checkbox input...", F::checkbox()), $br,
    F::with_label("A textarea input...", F::textarea()), $br,
    F::with_label("A button input...", F::button("button")), $br,
    F::with_label("An email input...", F::email()), $br,
    F::with_label("A hidden input...", F::hidden("hidden")), $br,
    F::with_label("A number input...", F::number(10, 0, 100, 5)), $br,
    F::with_label("A password input...", F::password()), $br,
    F::with_label("A reset input...", F::reset("RESET")), $br,
    F::with_label("A search input...", F::search("RESET")), $br,
    F::with_label("A url input...", F::url()), $br,
    F::with_label("A select input...", F::select(array("one", "two", "three"))), $br,
    F::with_label("A radio input...", F::radio(array("aa", "bb", "cc"))),
    F::inject(F::stop())
    );

$formlet = F::formlet(
    F::inject(F::collect()),
    $all_inputs, $br, $br,
    F::fieldset("In Fieldset: ", $all_inputs),
    F::submit("Absenden", array(), true),
    F::inject(F::stop())
    );

try {
    $form = F::form("example", "example.php", $formlet);
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
