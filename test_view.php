<html>
    <head>
    </head>
    <body>
        <form action="test_view.php" method="post">
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

$repr = $formlet->instantiate(NameSource::instantiate());

try {
    if (in_array("Absenden", $_POST)) {
        $res = $repr["collector"]->collect($_POST);
        $dict = new RenderDict($_POST, $res);

        echo $repr["builder"]->buildWithDict($dict)->render();
        echo "<pre>";
        echo "<hr />";
        if (!$res->isError()) {
            echo "Results are:<br />";
            print_r($res->get());
        }
        else {
            echo "Error: ".$res->error()."<br />";
            print_r($dict);
        }
        echo "</pre>";
    }
    else {
        echo $repr["builder"]->build()->render();
    }
}
catch (Exception $e) {
    echo $e->getTraceAsString();
}
            ?>
        </form>
    </body>

</html>
