<?php
function default_icon() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$buffer = array();

	$ico = $f->icon('someobject', 'Object', 'small', 'E');

	$buffer[] = $renderer->render($ico)
		.' Small Example';

	$buffer[] = $renderer->render($ico->withSize('medium'))
		.' Medium Example';

	$buffer[] = $renderer->render($ico->withSize('large'))
		.' Large Example';


	$ico = $f->icon('someobject', 'Object', 'small', 'OB');

	$buffer[] = $renderer->render($ico)
		.' Small Object';

	$buffer[] = $renderer->render($ico->withSize('medium'))
		.' Medium Object';

	$buffer[] = $renderer->render($ico->withSize('large'))
		.' Large Object';


	return implode('<br><br>', $buffer);
}
