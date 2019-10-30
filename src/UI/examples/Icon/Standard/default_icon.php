<?php
function default_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $ico = $f->icon()->standard('someExample', 'Example');
    $ico = $ico->withAbbreviation('E');

    $buffer[] = $renderer->render($ico)
        . ' Small Example with Short Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Example with Short Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Example with Short Abbreviation';


    $ico = $f->icon()->standard('someOtherExample', 'Example');
    $ico = $ico->withAbbreviation('LA');

    $buffer[] = $renderer->render($ico->withSize('small'))
        . ' Small Example with Long Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Example with Long Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Example with Long Abbreviation';


    return implode('<br><br>', $buffer);
}
