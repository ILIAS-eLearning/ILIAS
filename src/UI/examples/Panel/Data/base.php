<?php
/**
 * Only serving as Example
 */
function base() {
	
	global $DIC;
	
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	
	$dataPanel = $factory->panel()->data('Any Label/Value Statistic');
	
	$dataPanel->withAdditionalEntry(
		$factory->legacy('First Label'),
		$factory->legacy('Good Value')
	);
	
	$dataPanel->withAdditionalEntry(
		$factory->legacy('Second Label'),
		$factory->legacy('More Well Value')
	);
	
	$dataPanel->withAdditionalEntry(
		$factory->legacy('A Last Label'),
		$factory->legacy('Any Last Value')
	);
	
	$html = $renderer->render($dataPanel);
	
	return $html;
}