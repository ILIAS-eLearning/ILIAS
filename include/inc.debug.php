<?php
/**
* debugging functions
* 
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-develop
*/


/**
* shortcut for formatted var_dump output
* 
* @access	public
* @param	mixed
*/
function vd($mixed)
{
	echo "<pre>";
	var_dump($mixed);
	echo "</pre>";
}
?>