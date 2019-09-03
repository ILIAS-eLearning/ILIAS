<?php
function simpleWithLink() {

	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$icon = $factory->symbol()
		->icon()
		->standard("crs", 'Example');

	$node = $factory->tree()
		->node()
		->simple('label');


	$uri = new \ILIAS\Data\URI('https://ilias.de');

	$node2 = $factory->tree()
		->node()
		->simple('label', $icon, $uri);

	return $renderer->render([$node, $node2]);
}
