<?
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
* 	@param string CSCW_UId     ( actual User ID )
* 	@return Array Keywords [][]
*/
function getKeywords($CSCW_UId)
{

	$DB					= new Database();
	$Keywords			= $DB->getKeywords ($CSCW_UId);
	return $Keywords;
	
} // end func 


/**
* 	function showKeywords()
* 	@description : the Main function of the keyword function
* 	@description : called from the executed file
* 	@param  Array S_Keywords    ( control variable )
* 	@global string CSCW_UId     ( actual User ID )
* 	@global Array CSCW_language ( include Languageproperties )
* 	@global Array CSCW_Keywords ( active Keywords )
* 	@return string keywords_float    ( contains the output )
*/
function showKeywords($S_Keywords)
{
	global $CSCW_UId, $CSCW_Keywords , $CSCW_language;
	
	$Keywords = getKeywords($CSCW_UId);
	$keywords_float = "<br>";

	if ($S_Keywords) 
	{
		$CSCW_Keywords =  $S_Keywords;
		session_register("CSCW_Keywords");
	}

	$keywords_float = $keywords_float.'
		<form name="Keywords" action="" method="post">
		<select multiple size="6" name="S_Keywords[]">';

	if ($CSCW_Keywords[0] ==  "*" or !isset($CSCW_Keywords)) 
	{
		$CSCW_Keywords = array ("*");
		$keywords_float = $keywords_float.'<option value="*" selected >'.$CSCW_language[k_alldates].'</option>';
	}
	else 
	{
		$keywords_float = $keywords_float.'<option value="*">'.$CSCW_language[k_alldates].'</option>';
	}

	for ($i=0;$i<count($Keywords);$i++) 
	{
		$j = $i+1;
		if (@in_array ( $Keywords[$i][0] , $CSCW_Keywords)) 
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