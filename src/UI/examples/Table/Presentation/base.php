<?php
function base() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	//build viewcontrols
	$actions = array ("All" => "#",	"Upcoming events" => "#");
	$aria_label = "filter entries";
	$view_controls = array(
		$f->viewControl()->mode($actions, $aria_label)->withActive("All")
	);

	//build table
	$ptable = $f->table()->presentation(
		'Presentation Table', //title
		array($view_controls),
		function ($row, $record, $ui_factory, $environment) { //mapping-closure
			return $row
				->withTitle($record['title'])
				->withSubTitle($record['type'])
				->withImportantFields(
					array(
						$record['begin_date'] => '',
						$record['location'] => '',
						$record['bookings_available'] => 'Available Slots: '
					)
				)

				->withContent(
					$ui_factory->listing()->descriptive(
						array(
							'Targetgroup' => $record['target_group'],
							'Goals' => $record['goals'],
							'Topics' => $record['topics']
						)
					)
				)

				->withFurtherFieldsHeadline('Detailed Information')
				->withFurtherFields(
					array(
						$record['location'] => 'Location: ',
						$record['address'] => '',
						$record['date'] => 'Date: ',
						$record['bookings_available'] => 'Available Slots: ',
						$record['fee'] => 'Fee: '
					)
				)
				->withButtons(
					array(
						$ui_factory->button()->standard('book course', '#')
					)
				);
		}
	);

	//example data as from an assoc-query, list of arrays (see below)
	require('included_data.php');
	$data = included_data();

	//apply data to table and render
	return $renderer->render($ptable->withData($data));
}