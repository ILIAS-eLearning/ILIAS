<?php
/**
* Include file Keyword
*
* this file should manage the keyword functions
* 
* @author Frank Grümmert 
* 
* @version $Id: inc.keywords.php,v 0.9 2003/06/11 
* @package application
* @access public
*
*/

/**
* 	function getKeywords()
* 	@description : get Content for the Week View from the sortdates functions 
* 	@param  string DP_UId     ( actual User ID )
*	@param  DB $DB		( Object of class db )
* 	@return Array Keywords [][]
*/
function getKeywords($DP_UId, $DB)
{

	$Keywords			= $DB->getKeywords ($DP_UId);
	return $Keywords;
	
} // end func 


/**
* 	function showKeywords()
* 	@description : the Main function of the keyword function
* 	@description : called from the executed file
* 	@param  Array S_Keywords    ( control variable )
*	@param  DB $DB		( Object of class db )
* 	@global string DP_UId     ( actual User ID )
* 	@global Array DP_language ( include Languageproperties )
* 	@global Array $_SESSION  ( DP_Keywords = active Keywords )
* 	@return string keywords_float    ( contains the output )
*/
function showKeywords($S_Keywords, $DB)
{
	global $DP_UId, $_SESSION , $DP_language;
	
	$Keywords = getKeywords($DP_UId, $DB);
	$keywords_float = "<br>";

	$DP_Keywords = $_SESSION[DP_Keywords];

	$keywords_float = $keywords_float.'
		<form name="Keywords" action="" method="post">
		<select multiple size="6" name="S_Keywords[]">';

	if ($DP_Keywords[0] ==  "*" or !isset($DP_Keywords)) 
	{
		$DP_Keywords = array ("*");
		$keywords_float = $keywords_float.'<option value="*" selected >'.$DP_language[k_alldates].'</option>';
	}
	else 
	{
		$keywords_float = $keywords_float.'<option value="*">'.$DP_language[k_alldates].'</option>';
	}

	for ($i=0;$i<count($Keywords);$i++) 
	{
		$j = $i+1;
		if (@in_array ( $Keywords[$i][0] , $DP_Keywords)) 
		{
			$keywords_float = $keywords_float.'<option value="'.$Keywords[$i][0].'" selected>'.$Keywords[$i][1].'</option>';
		}
		else 
		{
			$keywords_float = $keywords_float.'<option value="'.$Keywords[$i][0].'">'.$Keywords[$i][1].'</option>';
		}
	}
	$keywords_float = $keywords_float.'</select>';
	$keywords_float = $keywords_float.'<input type="submit" value="OK">';
	$keywords_float = $keywords_float.'</form>';
	
	return $keywords_float;

} // end func
?>
