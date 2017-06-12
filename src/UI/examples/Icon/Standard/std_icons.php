<?php
function std_icons() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$default_icons = array(
		'course',
		'category',
		'calendar',
		'plugin',
		'file',
		'certificate'
	);

	$buffer = array();

	foreach ($default_icons as $icon) {
		$i = $f->icon()->standard($icon, $icon, 'medium');
		$buffer[] = $renderer->render($i)
		.' '
		.$icon;
	}

	return implode('<br><br>', $buffer);
}
