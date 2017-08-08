<?php

function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$data = array(
		array(
			'title'=>'row-1',
			'type'=>'webinar',
			'begin_date'=>'',
			'bookings_available'=>'',
			'target_group'=>'',
			'goals'=>'',
			'topics'=>'',
			'date' => '',
			'location'=>'',
			'address' => '',
			'fee' => ''
		),
		array(
			'title'=>'second',
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

	//build table
	$ptable = $f->table()->presentation(
		'Presentation Table',
		array(), //view controls
		$rows
	);

	return $renderer->render($ptable);
}
