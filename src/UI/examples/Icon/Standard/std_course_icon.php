<?php
function std_course_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $ico = $f->icon()->standard('crs', 'Course', 'small');

    $buffer[] = $renderer->render($ico)
        . ' Small Course';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Course';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Course';


    return implode('<br><br>', $buffer);
}
