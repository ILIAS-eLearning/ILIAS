<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Icon\Custom;

function custom_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $path = './src/UI/examples/Symbol/Icon/Custom/my_custom_icon.svg';
    $ico = $f->symbol()->icon()->custom($path, 'Example');

    $buffer[] = $renderer->render($ico)
        . ' Small Custom Icon';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Custom Icon';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Custom Icon';


    //Note that the svg needs to contain strictly valid xml to work with abbreviations.
    //Some exports e.g. form illustrator seem to be not properly formatted by default.
    $path = './templates/default/images/icon_fold.svg';
    $ico = $f->symbol()->icon()->custom($path, 'Example')
        ->withAbbreviation('FD');

    $buffer[] = $renderer->render($ico)
        . ' Small Custom Icon with Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Custom Icon with Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Custom Icon with Abbreviation';


    return implode('<br><br>', $buffer);
}
