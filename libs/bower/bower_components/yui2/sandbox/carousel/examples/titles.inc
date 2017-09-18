<?php
$examples = array(
	'selection.html'	=> 'A spotlight example',
	'circular.html'		=> 'A circular Carousel example',
	'reveal.html'		=> 'Revealing previous and next items example',
	'imagentext.html'	=> 'Using both images and text within an item example',
	'dynload2.html'		=> 'Using Ajax for deferred loading of items example'
);

$myself = basename(getenv('PHP_SELF'));

function writeTitle() {
	global $myself;

    echo "Example: {$examples[$myself]} (YUI Library)";
}
?>