<?php
/**
* colleczion of predefind sort functions
* should only be included where you need it
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @package ilias-core
*/

/**
* sub-function to sort $languages-array by their long names
*
* Long names of languages depends on the chosen language setting by the current user
* The function is called only in this way: uasort($languages,"sortLanguagesByName");
*
* @param	array	$a	expect $languages
* @param	string	$b	the function name itself ('sortLanguagesByName')
*
* @return	boolean	true on success / false on error
*/
function sortLanguagesByName ($a, $b)
{
		return strcmp($a["name"], $b["name"]);
}

/**
* sub-function to sort $object_data arrays by title
*
* @param	array	$a	expect $obj_data
* @param	string	$b	the function name itself ('sortObjectsByTitle')
*
* @return	boolean	true on success / false on error
*/
function sortObjectsByTitle ($a, $b)
{
		return strcasecmp($a["title"], $b["title"]);
}
?>