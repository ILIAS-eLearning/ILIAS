<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$data = array(
		array(
			'title'=>'row-1',
			'type'=>'webinar',
			'begin_date'=>'30.09.2017',
			'bookings_available'=>'3',
			'target_group'=>'Angestellter Aussendienst, Innenvertrieb',
			'goals'=>'Lorem Ipsum....',
			'topics'=>'<li>Warentransportversicherung</li><li>Europapolice</li>',
			'date' => '30.09.2017 - 02.10.2017',
			'location'=>'Bernried',
			'address' => 'Hauptstraße 123',
			'fee' => '380 €'
		),
		array(
			'title'=>'second',
			'type'=>'f2f',
			'begin_date'=>'12.12.2017',
			'bookings_available'=>'',
			'target_group'=>'',
			'goals'=>'',
			'topics'=>'',
			'date' => '',
			'location'=>'dfd',
			'address' => '',
			'fee' => ''
		),
		array(
			'title'=>'third',
			'type'=>'f2f',
			'begin_date'=>'',
			'bookings_available'=>'',
			'target_group'=>'',
			'goals'=>'',
			'topics'=>'',
			'date' => '',
			'location'=>'',
			'address' => '',
			'fee' => ''
		)
	);

	//configure row
	$prow = $f->table()->presentationRow('title')
	->withSubtitleField('type')
	->withImportantFields(
		array(
			'begin_date' => '',
			'location' => '',
			'bookings_available' => 'Freie Plätze: '
		)
	)
	->withDescriptionFields(
		array(
			'target_group' => 'Zielgruppe',
			'goals' => 'Ziele und Nutzen',
			'topics' => 'Inhalte'
		)
	)
	->withFurtherFields(
		array(
			'location' => 'Ort: ',
			'address' => '',
			'date' => 'Datum: ',
			'bookings_available' => 'Freie Plätze: ',
			'fee' => 'Gebühr: ',
		)
	);


	$rows = array();
	foreach ($data as $record) {
		$rows[] = $prow->withData($record)
			->withButtons(
				array(
					$f->button()->primary('launch course', '#'),
					$f->button()->standard('mark course', '#')
				)
			);
	}

	//build viewcontrols
	$actions = array (
		"All" => "#",
		"Upcoming events" => "#",
	);

	$aria_label = "change_the_currently_displayed_mode";
	$view_control = $f->viewControl()->mode($actions, $aria_label)->withActive("All");

	//build table
	$ptable = $f->table()->presentation(
		'Presentation Table',
		array(
			$view_control
		),
		$rows
	);

	return $renderer->render($ptable);
}
