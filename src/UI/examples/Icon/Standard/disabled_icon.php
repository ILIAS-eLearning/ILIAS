<?php
function disabled_icon()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buffer = array();

	$ico = $f->icon()->standard('crs', 'Course', 'small', false);

	$buffer[] = $renderer->render($ico->withDisabled(true))
		.' Small Course';

	$buffer[] = $renderer->render($ico->withSize('medium')->withDisabled(true))
		.' Medium Course';

	$buffer[] = $renderer->render($ico->withSize('large')->withDisabled(true))
		.' Large Course';


	return implode('<br><br>', $buffer);
}