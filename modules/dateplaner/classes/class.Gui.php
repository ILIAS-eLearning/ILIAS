<?

/**
* Gui Class
*
* this class should manage the Gui function
* 
* @author Frank Grümmert 
* 
* @version $Id: class.Gui.php,v 0.9 2003/06/11 
* @package application
* @access public
*
* Die Klasse ermöglicht es Code und Statisches HTML mittels Templates auszugeben. 
* Da die Ausgabe letztlich eine Ausgabe erzeugen soll wird eine ausführende Funktion benötigt. 
* Als Ausführende Datei nutzt darum diese Klasse die Funktion "inc.output.php" im Includes Ordner.   
*/

require('./config/conf.gui.php');

class Gui
{

	/**
	* constructor
	*/

	function Gui() {
 	}

	/**
	* function gettemplate($template,$endung="htm")
	* @description : get Inforamtion from a template File 
	* @param string template
	* @param string extension ( means the extension of the Template files, eg. .htm )
	* @global string templatefolder
	* @global string actualtemplate ( means the number of Template set eg. "default" )
	* @return Sting
	*/

	function getTemplate($template, $extension="htm") {
        global $templatefolder, $actualtemplate;

        if(!$templatefolder) $templatefolder = "templates";
        return str_replace("\"","\\\"",implode("",file($templatefolder."/".$actualtemplate."/".$template.".".$extension)));
	}

	/**
	* function setToolTip($starttime, $endtime, $shortext, $text, $id)
	* @description : set a mouse over tooltip to dates 
	* @param string starttime
	* @param string endtime 
	* @param string shortext
	* @param string text 
	* @param int id 
	* @return Sting float
	*/

	function setToolTip($starttime, $endtime, $shortext, $text, $id ) {
		$text = str_replace("\r\n","<br>" , $text);
		$headerText = $starttime.' -  '.$endtime.' ['.$shortext.']';
		$float= "<div id='$id' style='position:absolute; top:400px; left:0px; width:250; visibility:hidden; z-index:1; background-color:white'>";
		$float.= '
<table border="0" cellpadding="2" cellspacing="2" style="border-collapse: collapse"  width="100%" height="100%">
  <tr>
    <td width="100%" bgcolor="#000080"><b><font color="#FFFFFF">'.$headerText.'</font></b> </td>
  </tr>
  <tr>
    <td width="100%" height="100%" valign="top" bgcolor="#FFFFFF" ><font color="#000000">'.$text.'</font></td>
  </tr>
</table>
						</div>';
		Return $float;

	}


} // end class
?>