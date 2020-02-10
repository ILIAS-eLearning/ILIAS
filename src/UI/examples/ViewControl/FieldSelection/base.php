<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $options = array(
        "option1" => "Option 1",
        "option2" => "Option 2",
        "option3" => "Option 3"
    );

    $selection = $f->viewControl()->fieldSelection(
        $options,
        'pick some',
        'apply'
    );

    $html = $renderer->render($selection);
    $result = $selection->getValue();

    return $html . '<hr>'  .print_r($result, 1);
}
