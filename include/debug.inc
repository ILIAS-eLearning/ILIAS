<?php
/**
* debugging functions
* 
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-develop
*/

function vd($mixed)
{
	echo "<pre>";
	var_dump($mixed);
	echo "</pre>";
}
?>