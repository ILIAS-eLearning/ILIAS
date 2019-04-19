<?php
/**
 * @return string
 * @author Niels Theen <ntheen@databay.de>
 * @author Colin Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
function show_tooltip_for_button() {
	global $DIC;

	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$tooltip = $factory->tooltip()
		->standard([$factory->legacy('Hello World')]);
	$button = $factory->button()
		->standard('Hover Me!', '#')
		->withOnHover($tooltip->getShowSignal());

	$tooltip2 = $factory->tooltip()
		->standard([$factory->legacy(
			'Foo bar with some <span style="color:red; font-weight: bold">HTML!</span>' .
			'<ul><li>a</li><li>b</li><li>c</li></ul>'
		)])
		->withPlacementRight();
	$button2 = $factory->button()
		->standard('Click Me!', '#')
		->withOnClick($tooltip2->getShowSignal());

	return $renderer->render([$tooltip, $button, $tooltip2, $button2]);
}