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

	$tooltip = $factory->tooltip()->standard([$factory->legacy('Hello World')]);
	$button = $factory->button()
		->standard('Hover Me!', '#')
		->withOnHover($tooltip->getShowSignal());

	return $renderer->render([$tooltip, $button]);
}