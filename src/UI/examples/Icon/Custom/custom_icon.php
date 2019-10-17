<?php
function custom_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $path = './src/UI/examples/Icon/Custom/my_custom_icon.svg';
    $ico = $f->icon()->custom($path, 'Example');

    $buffer[] = $renderer->render($ico)
        . ' Small Custom Icon';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Custom Icon';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Custom Icon';


    $path = './templates/default/images/icon_fold.svg';
    $ico = $f->icon()->custom($path, 'Example')
        ->withAbbreviation('FD');

    $buffer[] = $renderer->render($ico)
        . ' Small Custom Icon with Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Custom Icon with Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Custom Icon with Abbreviation';


    return implode('<br><br>', $buffer);
}
