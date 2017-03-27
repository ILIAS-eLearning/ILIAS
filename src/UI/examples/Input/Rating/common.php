<?php
function common() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$ri = $f->input()->rating('topic')
		->withCaptions(
			array(
				'opt1',
				'opt2',
				'opt3',
				'opt4',
				'opt5'
			))
		->withByline(
			'Set an explanationary byline/text for this input.'
			.'<br>Text <i>can</i> contain HTML, but usually <i>should not</i>.'
			);

	return $renderer->render($ri);
}