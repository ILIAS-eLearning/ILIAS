<?php
function bylined() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$icon=$f->symbol()->icon()->standard("crs", 'Example');

	$node = $f->tree()->node()->bylined('label', 'byline');
	$node2 = $f->tree()->node()->bylined('label', 'byline', $icon);

	return $renderer->render([$node, $node2]);
}
