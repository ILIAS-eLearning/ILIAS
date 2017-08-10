<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//example data as from an assoc-query, list of arrays (see below)
	require('included_data.php');
	$data = included_data();

	//configure row
	$prow = $f->table()->presentationRow('title')
	->withSubtitleField('type')
	->withImportantFields(
		array(
			'begin_date' => '',
			'location' => '',
			'bookings_available' => 'Available Slots: '
		)
	)
	->withDescriptionFields(
		array(
			'target_group' => 'Targetgroup',
			'goals' => 'Goals',
			'topics' => 'Topics'
		)
	)
	->withFurtherFieldsHeadline('Detailed Information')
	->withFurtherFields(
		array(
			'location' => 'Location: ',
			'address' => '',
			'date' => 'Date: ',
			'bookings_available' => 'Available Slots: ',
			'fee' => 'Fee: ',
		)
	);

	//apply data to rows and add button
	$rows = array();
	foreach ($data as $record) {
		$rows[] = $prow->withData($record)
			->withButtons(
				array(
					$f->button()->standard('book course', '#')
				)
			);
	}

	//build viewcontrols
	$actions = array (
		"All" => "#",
		"Upcoming events" => "#",
	);
	$aria_label = "filter entries";
	$view_control = $f->viewControl()->mode($actions, $aria_label)->withActive("All");

	//build table
	$ptable = $f->table()->presentation(
		'Presentation Table',
		array($view_control),
		$rows
	);

	return $renderer->render($ptable);
}
