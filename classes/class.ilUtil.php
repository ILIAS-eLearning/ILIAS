<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* util class
* various functions, usage as namespace
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
* @package ilias-core
*/
class ilUtil
{
	/**
	* Builds an html image tag
	* TODO: function still in use, but in future use getImagePath and move HTML-Code to your template file
	* @access	public
	* @param	string	object type
	* @param	string	tpl path
	*/
	function getImageTagByType($a_type,$a_path)
	{
		global $lng;

		return "<img src=\"".$a_path."/images/"."icon_".$a_type."_b.gif\" alt=\"".$lng->txt("obj_".$a_type)."\" title=\"".$lng->txt("obj_".$a_type)."\" border=\"0\" vspace=\"0\"/>";
	}

	/**
	* get image path (for images located in a template directory)
	*
	* @access	public
	* @param	string		full image filename (e.g. myimage.gif)
	* @param	boolean		should be set to true, if the image is within a module
	*						template directory (e.g. content/templates/default/images/test.gif)
	*/
	function getImagePath($img, $in_module = false)
	{
		global $ilias;

		if(defined("ILIAS_MODULE"))
		{
			$dir = ".";
		}
		else
		{
			$dir = "";
		}
		$base = "./";
		if ($in_module)
		{
			$base.= ILIAS_MODULE."/";
		}
		$base .= "templates/";
		$user_skin_and_style = $base.$ilias->account->skin."/".
			$ilias->account->prefs["style"]."/images/".$img;
		$user_skin = $base.$ilias->account->skin."/images/".$img;
		$default = $base."default/images/".$img;
//echo ":".$user_skin_and_style.":<br>";
		if (file_exists($user_skin_and_style))
		{
			return $dir.$user_skin_and_style;
		}
		else if (file_exists($user_skin))
		{
			return $dir.$user_skin;
		}
		return $dir.$default;
	}

	function getJSPath($a_js)
	{
		global $ilias;
		
		if(defined("ILIAS_MODULE"))
		{
			$dir = ".";
		}
		else
		{
			$dir = "";
		}
		$in_style = "./templates/".$ilias->account->skin."/".$ilias->account->prefs["style"]."/".$a_js;
		$default = "./templates/".$ilias->account->skin."/".$a_js;
		if(@is_file($in_style))
		{
			return $dir.$in_style;
		}
		else
		{
			return $dir.$default;
		}
	}

	/**
	* get full style sheet file name (path inclusive) of current user
	*
	* @access	public
	*/
	function getStyleSheetLocation()
	{
		global $ilias;

		if(defined("ILIAS_MODULE"))
		{
			$base = "../";
		}
		else
		{
			$base = "./";
		}
		return $base."templates/".$ilias->account->skin."/".$ilias->account->prefs["style"].".css";
	}

	/**
	* Builds a select form field with options and shows the selected option first
	*
	* @access	public
	* @param	string	value to be selected
	* @param	string	variable name in formular
	* @param	array	array with $options (key = lang_key, value = long name)
	* @param	boolean
	* @param	boolean	if true, the option values are displayed directly, otherwise
	*					they are handled as language variable keys and the corresponding
	*					language variable is displayed
	*/
	function formSelect ($selected,$varname,$options,$multiple = false,$direct_text = false)
	{
		global $lng;

		$multiple ? $multiple = " multiple=\"multiple\"" : "";
		$str = "<select name=\"".$varname ."\"".$multiple.">\n";

		foreach ($options as $key => $val)
		{
			if($direct_text)
			{
				$str .= " <option value=\"".$key."\"";
			}
			else
			{
				$str .= " <option value=\"".$val."\"";
			}

			if ($selected == $key)
			{
				$str .= " selected=\"selected\"";
			}

			if($direct_text)
			{
				$str .= ">".$val."</option>\n";
			}
			else
			{
				$str .= ">".$lng->txt($val)."</option>\n";
			}
		}

		$str .= "</select>\n";

		return $str;
	}

	/**
	* ???
	* @access	public
	* @param	string
	* @param	string
	* @param	array
	* @param	boolean
	* @return	string 
	*/
	function formSelectWoTranslation ($selected,$varname,$options,$multiple = false)
	{
		$multiple ? $multiple = " multiple=\"multiple\"" : "";
		$str = "<select name=\"".$varname ."\"".$multiple.">\n";

		foreach ($options as $key => $val)
		{
		
			$str .= " <option value=\"".$key."\"";
			
			if ($selected == $key)
			{
				$str .= " selected=\"selected\"";
			}
			
			$str .= ">".$val."</option>\n";
		}

		$str .= "</select>\n";
		
		return $str;
	}

	/**
	* ???
	*
	* @access	public
	* @param string
	* @param string	 
	*/
	function getSelectName ($selected,$values)
	{
		return($values[$selected]);
	}

	/**
	* ???
	* @access	public
	* @param	string
	* @param	string
	* @param	string
	* @return	string
	*/
	function formCheckbox ($checked,$varname,$value)
	{
		$str = "<input type=\"checkbox\" name=\"".$varname."\"";
		
		if ($checked == 1)
		{
			$str .= " checked=\"checked\"";
		}
		
		$str .= " value=\"".$value."\" />\n";
		
		return $str;
	}

	/**
	* ???
	* @access	public
	* @param	string
	* @param	string
	* @param	string
	* @return	string
	*/
	function formRadioButton($checked,$varname,$value)
	{
	$str = "<input type=\"radio\" name=\"".$varname."\"";
		if ($checked == 1)
		{
			$str .= " checked=\"checked\"";
		}

		$str .= " value=\"".$value."\" />\n";
		
		return $str;
	}

	/**
	* ???
	* @param string	 
	*/
	function checkInput ($vars)
	{
		// TO DO:
		// Diese Funktion soll Formfeldeingaben berprfen (empty und required)
	}

	/**
	* ???
	* @access	public
	* @param	string
	*/
	function setPathStr ($a_path)
	{
		if ("" != $a_path && "/" != substr($a_path, -1))
		{
			$a_path .= "/";
			//$a_path = substr($a_path,1);
		}
	
		//return getcwd().$a_path;
		return $a_path;
	}

	/**
	* switches style sheets for each even $a_num
	* (used for changing colors of different result rows)
	* 
	* @access	public
	* @param	integer	$a_num	the counter
	* @param	string	$a_css1	name of stylesheet 1
	* @param	string	$a_css2	name of stylesheet 2
	* @return	string	$a_css1 or $a_css2
	*/
	function switchColor ($a_num,$a_css1,$a_css2)
	{
		if (!($a_num % 2))
		{
			return $a_css1;	
		}
		else
		{
			return $a_css2;
		}
	}
	
	/**
	* show the tabs in admin section
	* 
	* @access	public
	* @param	integer	column to highlight
	* @param	array	array with templatereplacements
	*/
	function showTabs($a_hl, $a_o)
	{
		global $lng;
		
		$tpltab = new ilTemplate("tpl.tabs.html", true, true);
		
		for ($i=1; $i<=4; $i++)
		{
			$tpltab->setCurrentBlock("tab");
			if ($a_hl == $i)
			{
				$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}
				
			switch ($i)
			{
				case 1: 
					$txt = $lng->txt("view_content");
					break;
				case 2: 
					$txt = $lng->txt("edit_properties");
					break;
				case 3: 
					$txt = $lng->txt("perm_settings");
					break;
				case 4: 
					$txt = $lng->txt("show_owner");
					break;
			} // switch
			$tpltab->setVariable("CONTENT", $txt);
			$tpltab->setVariable("TABTYPE", $tabtype);
			$tpltab->setVariable("TAB", $tab);
			$tpltab->setVariable("LINK", $a_o["LINK".$i]);
			$tpltab->parseCurrentBlock();
		}

		return $tpltab->get();
	}

	/**
	* Get all objects of a specific type and check access
	* recursive method
	* 
	* @access	public
	* @param	string	type or 'all' to get all objects
	* @param	string	permissions to check e.g. 'visible','read' 
	*/
	function getObjectsByOperations($a_type,$a_operation,$a_node_id = ROOT_FOLDER_ID)
	{
		global $tree, $rbacsystem;
		static $objects = array();

		$all = $a_type == 'all' ? true : false;

		$childs = $tree->getChilds($a_node_id);

		if (count($childs) > 0)
		{
			foreach ($childs as $child)
			{
				// CHECK IF CONTAINER OBJECT IS VISIBLE
				if ($rbacsystem->checkAccess('visible',$child["child"],$a_type))
				{
					if ($all or $child["type"] == $a_type)
					{
						// NOW CHECK FOR ASKED OPERATION
						if ($rbacsystem->checkAccess($a_operation,$child["child"],$a_type))
						{
							$objects[] = $child;
						}
					}

					ilUtil::getObjectsByOperations($a_type,$a_operation,$child["child"]);
				}
			}
		}

		return $objects;
	}
	
	/**
	* ??? 
	* @access	public
	* @param	array
	* @return	string
	*/
	function checkFormEmpty ($emptyFields)
	{		
		
		$feedback = "";		

		foreach ($emptyFields as $key => $val)
		{				
			if ($val == "") {
				if ($feedback != "") $feedback .= ", ";
				$feedback .= $key;					
			}			
		}		
	
		return $feedback;
	}

	/**
	* Linkbar
	* Diese Funktion erzeugt einen typischen Navigationsbalken mit
	* "Previous"- und "Next"-Links und den entsprechenden Seitenzahlen
	*
	* die komplette LinkBar wird zurückgegeben
	* der Variablenname für den offset ist "offset"
	* 
	* @author Sascha Hofmann <shofmann@databay.de>
	*
	* @access	public
	* @param	integer	Name der Skriptdatei (z.B. test.php)
	* @param	integer	Anzahl der Elemente insgesamt
	* @param	integer	Anzahl der Elemente pro Seite
	* @param	integer	Das aktuelle erste Element in der Liste
	* @param	array	Die zu übergebenen Parameter in der Form $AParams["Varname"] = "Varwert" (optional)
	* @return	array	linkbar or false on error
	*/
	function Linkbar ($AScript,$AHits,$ALimit,$AOffset,$AParams = array())
	{
		$LinkBar = "";

		// Wenn Hits grösser Limit, zeige Links an
		if ($AHits > $ALimit)
		{
			if (!empty($AParams))
			{
				foreach ($AParams as $key => $value)
				{
					$params.= $key."=".$value."&";
				}
			}
			// if ($params) $params = substr($params,0,-1);
			$link = $AScript."?".$params."offset=";

			// übergehe "zurck"-link, wenn offset 0 ist.
			if ($AOffset >= 1)
			{
				$prevoffset = $AOffset - $ALimit;
				$LinkBar .= "<a class=\"inlist\" href=\"".$link.$prevoffset."\">&lt;&lt;&nbsp;</a>";
			}

			// Benötigte Seitenzahl kalkulieren
			$pages=intval($AHits/$ALimit);

			// Wenn ein Rest bleibt, addiere eine Seite
			if (($AHits % $ALimit))
				$pages++;

// Bei Offset = 0 keine Seitenzahlen anzeigen : DEAKTIVIERT
//			if ($AOffset != 0) {

				// ansonsten zeige Links zu den anderen Seiten an
				for ($i = 1 ;$i <= $pages ; $i++)
				{
					$newoffset=$ALimit*($i-1);

					if ($newoffset == $AOffset)
					{
						$LinkBar .= "<font color='Gray'>[<b>".$i."</b>]</font>";
					}
					else
					{
						$LinkBar .= "[<a class=\"inlist\" href=\"".$link.$newoffset."\">$i</a>]";
					}
				}
//			}

			// Checken, ob letze Seite erreicht ist
			// Wenn nicht, gebe einen "Weiter"-Link aus
			if (! ( ($AOffset/$ALimit)==($pages-1) ) && ($pages!=1) )
			{
				$newoffset=$AOffset+$ALimit;
				$LinkBar .= "<a class=\"inlist\" href=\"".$link.$newoffset."\">&nbsp;&gt;&gt;</a>";
			}

			return $LinkBar;
		}
		else
		{
			return false;
		}
	}

	/**
	* makeClickable
	* In Texten enthaltene URLs und Mail-Adressen klickbar machen
	*
	* @access	public
	* @param	string	$text: Der Text
	* @return	string	clickable link
	*/
	function makeClickable($a_text)
	{
		// URL mit ://-Angabe
		$ret = eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=-])",
							 "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</a>", $a_text);

		// www-URL ohne ://-Angabe
		$ret = eregi_replace("([[:space:]]+)(www\.)([[:alnum:]#?/&=\.-]+)",
							 "\\1<a href=\"http://\\2\\3\" target=\"_blank\">\\2\\3</a>", $ret);

		// ftp-URL ohne ://-Angabe
		$ret = eregi_replace("([[:space:]]+)(ftp\.)([[:alnum:]#?/&=\.-]+)",
							 "\\1<a href=\"ftp://\\2\\3\" target=\"_blank\">\\2\\3</a>", $ret);

		// E-Mail
		$ret = eregi_replace("(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
							 "<a  href=\"mailto:\\1\">\\1</a>", $ret);

		return($ret);
	}

	/**
	* StopWatch
	* benchmark scriptcode
	*
	* Usage:
	* $t1 = StopWatch(); // starts the StopWatch
	* // your code you want to benchmark
	* $diff = StopWatch($t1); // stops the StopWatch
	*
	* $diff contains the time elapsed so far from the point where you set the marker $t1
	* in microseconds
	*
	* @access	public
	* @param	float	starttime in microseconds
	* @return	float	time in microseconds
	*/
	function StopWatch($begin = -1)
	{
		$m = explode(" ",microtime());
		$m = $m[0] + $m[1];

		if ($begin != -1)
		{
			$m = $m - $begin;
		}
	
		return($m);
	}

	/**
	*  erstellt dateselect-boxen mit voreingestelltem datum
	* @access	public
	* @param	string	var name in formular
	* @param	string	date
	* @return	string	HTML select boxes
	* @author	Aresch Yavari <ay@databay.de>
	*/
	function makeDateSelect($prefix,$date="current")
	{
		if ($date=="current")
		{
			$date = date("Y-m-d");
		}

		$time = explode("-",$date);
		$ret .= "<select name=\"".$prefix."[d]\">\n";

		for ($i=1;$i<=31;$i++)
		{
			if ($time[2]==$i) { $sel = " selected"; } else { $sel = "";}
			$ret .= "<option".$sel.">".$i."\n";
		}
		
		$ret .= "</select>\n";

		$months = array(1 => 'Januar', 'Februar', 'März', 'April', 'Mai','Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

		$ret .= "<select name=\"".$prefix."[m]\">\n";

		for ($i=1;$i<=12;$i++)
		{
			if ($time[1]==$i)
			{
				$sel = " selected";
			}
			else
			{
				$sel = "";
			}

			$ret .= "<option value=\"".$i."\" ".$sel.">".$months[$i]."\n";
		}

		$ret .= "</select>\n";

		$ret .= "<select name=\"".$prefix."[y]\">\n";

		for ($i=0;$i<=3;$i++)
		{
			if ($time[0]==(date("Y")+$i))
			{
				$sel = " selected";
			}
			else
			{
				$sel = "";
			}

			$ret .= "<option".$sel.">".(date("Y")+$i)."\n";
		}

		$ret .= "</select>\n";

		return $ret;
	}
	
	/*
	* This preg-based function checks whether an e-mail address is formally valid.
	* It works with all top level domains including the new ones (.biz, .info, .museum etc.)
	* and the special ones (.arpa, .int etc.)
	* as well as with e-mail addresses based on IPs (e.g. webmaster@123.45.123.45)
	* @access	public
	* @param	string	email address
	* @return	boolean	true if valid
	*/
	function is_email($a_email)
	{
		return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i",$a_email));
	}

	/*
	* validates a password
 	* @access	public
	* @param	string	password
	* @return	boolean	true if valid
	* TODO: populate function with restrictions for passwords :-)
	*/
	function is_password($a_passwd)
	{
		if (empty($a_passwd))
		{
			return false;
		}

		return true;
	}

	/**
	* shorten a string to given length.
	* Adds 3 dots at the end of string (optional)
	* TODO: do not cut within words (->wordwrap function)
	* @access	public
	* @param	string	string to be shortened
	* @param	integer	string length in chars
	* @param	boolean	adding 3 dots (true) or not (false, default)
	* @return	string 	shortended string
	*/
	function shortenText ($a_str, $a_len, $a_dots = "false")
	{
		if (strlen($a_str) > $a_len)
		{

			$a_str = substr($a_str,0,$a_len);

			if ($a_dots)
			{
				$a_str .= "...";
			}
		}

		return $a_str;
	}

} // END class.util
?>
