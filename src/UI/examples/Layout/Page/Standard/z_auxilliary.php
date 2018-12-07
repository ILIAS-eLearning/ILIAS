<?php
function z_auxilliary()
{
	return 'These are helpers for the construction of demo-content.';
}

function _initIliasForPreview()
{
	chdir('../../../../../../');
	require_once("Services/Init/classes/class.ilInitialisation.php");
	require_once('src/UI/examples/MainControls/MetaBar/metabar.php');
	require_once('src/UI/examples/MainControls/MainBar/mainbar.php');
	require_once('src/UI/examples/Layout/Page/Standard/ui.php');
	ilInitialisation::initILIAS();
}


function pagedemoContent($f)
{
	return array (
		$f->panel()->standard('Demo Content',
			$f->legacy("some content<br>some content<br>some content<br>x.")),
		$f->panel()->standard('Demo Content 2',
			$f->legacy("some content<br>some content<br>some content<br>x.")),
		$f->panel()->standard('Demo Content 3',
			$f->legacy("some content<br>some content<br>some content<br>x.")),
		$f->panel()->standard('Demo Content 4',
			$f->legacy("some content<br>some content<br>some content<br>x."))
	);
}

