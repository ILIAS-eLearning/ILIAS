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
* The function is called only in this way: uasort($languages,"cmp");
*
* @param	array	$a	expect $languages
* @param	string	$b	the function name itself ('cmp')
*
* @return	array	$languages	sorted array $languages
*/
function sortLanguagesByName ($a, $b)
{
		return strcmp($a["name"], $b["name"]);
}
?>