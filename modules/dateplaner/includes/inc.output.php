<?php
/**
* Include file Output
*
* this file gerates an output
* 
* @author Frank Grümmert 
* 
* @version $Id: inc.output.php,v 0.9 2003/06/11 
* @package application
* @access public
*
*/


/**
*	void function dooutput($template)
*	@description : retuns Output
*	@param int template
*	
*/

function doOutput($template) {
		global $templatefolder, $actualtemplate ,$PAGETITLE, $DP_StyleFname, $DP_Style, $DP_language, $_SESSION;

		if($_SESSION[DP_JSscript] != "1" ) {
			$jsscriptIn			= "<!-- ";
			$jsscriptOut		= " -->";
			$template	= str_replace("{jsscriptIn}","$jsscriptIn",$template);
			$template	= str_replace("{jsscriptOut}","$jsscriptOut",$template);
		}else {
			$template	= str_replace("{jsscriptIn}","",$template);
			$template	= str_replace("{jsscriptOut}","",$template);
		}

		$template	= str_replace("{TITLE}","$DP_language[title]",$template);
		$template	= str_replace("{PAGETITLE}","$PAGETITLE",$template);
		
		$template	= str_replace("{DATEPLANER_ROOT_DIR}",DATEPLANER_ROOT_DIR,$template);
		$template	= str_replace("{ILIAS_HTTP_PATH}",ILIAS_HTTP_PATH,$template);

		$css_ilias	= '<LINK href="'.$DP_StyleFname.'" type="text/css" rel="stylesheet" />';
        $template	= str_replace("{css_ilias}","$css_ilias",$template);

		$css		= '<LINK href=".'.DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate."/".$DP_Style.'.css" type="text/css" rel="stylesheet" />';
        $template	= str_replace("{css}","$css",$template);
        echo $template;
	}
?>
