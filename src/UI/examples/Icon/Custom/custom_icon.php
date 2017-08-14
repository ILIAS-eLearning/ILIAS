<?php
function custom_icon() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buffer = array();

	$path = './templates/default/images/icon_reps.svg';
	$ico = $f->icon()->custom($path, 'Example');

	$buffer[] = $renderer->render($ico)
		.' Small Example';

	$buffer[] = $renderer->render($ico->withSize('medium'))
		.' Medium Example';

	$buffer[] = $renderer->render($ico->withSize('large'))
		.' Large Example';


	$path = './templates/default/images/icon_fold.svg';
	$ico = $f->icon()->custom($path, 'Example')
		->withAbbreviation('FD');

	$buffer[] = $renderer->render($ico)
		.' Custom Icon with Abbreviation';

	$buffer[] = $renderer->render($ico->withSize('medium'))
		.' Custom Icon with Abbreviation';

	$buffer[] = $renderer->render($ico->withSize('large'))
		.' Custom Icon with Abbreviation';


	return implode('<br><br>', $buffer);
}
