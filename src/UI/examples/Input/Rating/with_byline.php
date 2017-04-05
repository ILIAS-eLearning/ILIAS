<?php
function with_byline() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$captions = array(
		'opt1',
		'opt2',
		'opt3',
		'opt4',
		'opt5'
	);

	$byline = 'This is a short explanation of the'
		.' topic to be rated.'
		.' <br>The byline <i>MAY</i> contain HTML,'
		.' but usually <i>SHOULD</i> not.';


	$ri = $f->input()->rating('topic')
		->withCaptions($captions)
		->withByline($byline);

	return $renderer->render($ri);
}