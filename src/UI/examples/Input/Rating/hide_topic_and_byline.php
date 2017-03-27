<?php
function hide_topic_and_byline() {
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
		->withHiddenTopic()
		;

	return $renderer->render($ri);
}