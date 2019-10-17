<?php
function std_icons()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $lng = $DIC->language();

    $i = $f->icon()->standard('NONE', 'DummyIcon');
    $default_icons = $i->getAllStandardHandles();
    $buffer = array();

    foreach ($default_icons as $icon) {
        $i = $f->icon()->standard($icon, $icon, 'medium');
        $buffer[] = $renderer->render($i)
        . ' '
        . $icon
        . ' - '
        . $lng->txt("obj_$icon");
    }

    return implode('<br><br>', $buffer);
}
