<?php declare(strict_types=1);

namespace ILIAS\UI\Examples\Symbol\Icon\Standard;

function std_course_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $ico = $f->symbol()->icon()->standard('crs', 'Course', 'small');

    $buffer[] = $renderer->render($ico)
        . ' Small Course';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Course';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Course';


    return implode('<br><br>', $buffer);
}
