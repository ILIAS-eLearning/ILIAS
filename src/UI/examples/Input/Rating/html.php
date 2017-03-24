<?php
function html() {
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
    	->withByline('Wählen Sie aus, wie zufrieden Sie mit diesem Input sind.');

    return $renderer->render($ri);
}