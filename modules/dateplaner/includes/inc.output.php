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
		global $actualIliasDir,$templatefolder, $actualtemplate ,$PAGETITLE, $CSCW_StyleFname, $CSCW_Style, $CSCW_language, $CSCW_JSscript;

		if($CSCW_JSscript != "1") {
			$jsscriptIn			= "<!-- ";
			$jsscriptOut		= " -->";
			$template	= str_replace("{jsscriptIn}","$jsscriptIn",$template);
			$template	= str_replace("{jsscriptOut}","$jsscriptOut",$template);
		}else {
			$template	= str_replace("{jsscriptIn}","",$template);
			$template	= str_replace("{jsscriptOut}","",$template);
		}

		$template	= str_replace("{PAGETITLE}","$PAGETITLE",$template);

		$css		= '<LINK href="'.$templatefolder."/".$actualtemplate."/".$CSCW_Style.'.css" type="text/css" rel="stylesheet" />';
        $template	= str_replace("{css}","$css",$template);

		$css_ilias	= '<LINK href="'.$actualIliasDir.$CSCW_StyleFname.'" type="text/css" rel="stylesheet" />';
        $template	= str_replace("{css_ilias}","$css_ilias",$template);

        echo $template;
	}
?>
