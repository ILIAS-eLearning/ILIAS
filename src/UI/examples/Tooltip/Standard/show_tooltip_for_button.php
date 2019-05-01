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
		->standard([$factory->legacy('Hello World!')]);
	$button = $factory->button()
		->standard('Hover Me!', '#')
		->withOnHover($tooltip->getShowSignal());

	$tooltip2 = $factory->tooltip()
		->standard([$factory->legacy(
			'Foo <i>bar</i> with some <b>HTML</b>!'
		)])
		->withPlacementRight();
	$button2 = $factory->button()
		->standard('Click Me!', '#')
		->withOnClick($tooltip2->getShowSignal());

	return $renderer->render([$tooltip, $button, new \ILIAS\UI\Implementation\Component\Legacy\Legacy('&nbsp;'), $tooltip2, $button2]);
}