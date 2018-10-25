<?php
function disabled_icon()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buffer = array();

	$ico = $f->icon()->standard('grp', 'Course', 'large', false)->withDisabled(true);

	$buffer[] = $renderer->render($ico) .' Large Group Disabled';

    $buffer[] = $renderer->render($ico->withIsOutlined(true)).' Large Group Disabled Outlined';

	return implode('<br><br>', $buffer);
}