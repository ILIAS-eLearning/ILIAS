<?php
function disabled_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $ico = $f->icon()->standard('grp', 'Group', 'large', false);

    $buffer[] = $renderer->render($ico) . ' Large Group Enabled';
    $buffer[] = $renderer->render($ico->withDisabled(true)) . ' Large Group Disabled';
    $buffer[] = $renderer->render($ico->withIsOutlined(true)) . ' Large Group Enabled Outlined';
    $buffer[] = $renderer->render($ico->withDisabled(true)->withIsOutlined(true)) . ' Large Group Disabled Outlined';

    return implode('<br><br>', $buffer);
}
