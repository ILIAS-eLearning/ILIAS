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
* @author Alex Killing <alex.killing@gmx.de>
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
		global $ilias, $styleDefinition;

		if(defined("ILIAS_MODULE") and !defined("KEEP_IMAGE_PATH"))
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

		if (is_object($styleDefinition))
		{
			$st_image_dir = $styleDefinition->getImageDirectory($ilias->account->prefs["style"]);
			$user_skin_and_style = $base.$ilias->account->skin."/".
			$st_image_dir.
			"/images/".$img;
		}
		$user_skin = $base.$ilias->account->skin."/images/".$img;
		$default = $base."default/images/".$img;
		if (@file_exists($user_skin_and_style) && $st_image_dir != "")
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
	* @param	string/array	value to be selected
	* @param	string	variable name in formular
	* @param	array	array with $options (key = lang_key, value = long name)
	* @param	boolean
	* @param	boolean	if true, the option values are displayed directly, otherwise
	*					they are handled as language variable keys and the corresponding
	*					language variable is displayed
	*/
	function formSelect ($selected,$varname,$options,$multiple = false,$direct_text = false, $size = "0")
	{
		global $lng;

		if ($multiple == true)
		{
			$multiple = "multiple";
		}
		else
		{
			$multiple = "";
			$size = 0;
		}

		$str = "<select name=\"".$varname ."\"".$multiple." size=\"".$size."\">\n";

		foreach ($options as $key => $val)
		{
			if ($direct_text)
			{
				$str .= " <option value=\"".$key."\"";
			}
			else
			{
				$str .= " <option value=\"".$val."\"";
			}
			if (is_array($selected) )
			{
				if (in_array($key,$selected))
				{
					$str .= " selected=\"selected\"";
				}
			}
			else if ($selected == $key)
			{
				$str .= " selected=\"selected\"";
			}

			if ($direct_text)
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
	* @param	boolean	disabled checked checkboxes (default: false)
	* @return	string
	*/
	function formCheckbox ($checked,$varname,$value,$disabled = false)
	{
		$str = "<input type=\"checkbox\" name=\"".$varname."\"";

		if ($checked == 1)
		{
			$str .= " checked=\"checked\"";
		}

		if ($disabled)
		{
			$str .= " disabled=\"disabled\"";
		}

		$str .= " value=\"".$value."\" id=\"".$varname."\" />\n";

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

		$str .= " value=\"".$value."\"";
		
		$str .= " id=\"".$value."\" />\n";

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
	* Get all objects of a specific type where access is granted for the given list
	* of operations. This function does a checkAccess call for all objects
	* in the object hierarchy and return only the objects of the given type.
	* Please note if access is not granted to any object in the hierarchy
	* the function skips all objects under it.
	* Example:
	* You want a list of all Courses that are visible and readable for the user.
	* The function call would be:
	* $your_list = IlUtil::GetObjectsByOperations ("crs", "visible,read");
	* Lets say there is a course A where the user would have access to according to
	* his role assignments. Course A lies within a group object which is not readable
	* for the user. Therefore course A won't appear in the result list although
	* the queried operations 'visible' and 'read' would actually permit the user
	* to access course A.
	*
	* @access	public
	* @param	string	type or 'all' to get all objects
	* @param	string	permissions to check e.g. 'visible','read'
	* @param	integer	node_id from where to start search (optional)
	* @param	array	returned objects (internal use only for recursion)
	* @return	array	returned objects
	*/
	function getObjectsByOperations($a_type,$a_operation,$a_node_id = ROOT_FOLDER_ID, $objects = array())
	{
		global $tree, $rbacsystem;

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

					$objects = ilUtil::getObjectsByOperations($a_type,$a_operation,$child["child"],$objects);
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
	* die komplette LinkBar wird zur�ckgegeben
	* der Variablenname f�r den offset ist "offset"
	*
	* @author Sascha Hofmann <shofmann@databay.de>
	*
	* @access	public
	* @param	integer	Name der Skriptdatei (z.B. test.php)
	* @param	integer	Anzahl der Elemente insgesamt
	* @param	integer	Anzahl der Elemente pro Seite
	* @param	integer	Das aktuelle erste Element in der Liste
	* @param	array	Die zu �bergebenen Parameter in der Form $AParams["Varname"] = "Varwert" (optional)
	* @param	array	layout options (all optional)
	* 					link	=> css name for <a>-tag
	* 					prev	=> value for 'previous page' (default: '<<')
	* 					next	=> value for 'next page' (default: '>>')
	* @return	array	linkbar or false on error
	*/
	function Linkbar ($AScript,$AHits,$ALimit,$AOffset,$AParams = array(),$ALayout = array())
	{
		$LinkBar = "";

		$layout_link = "";
		$layout_prev = "&lt;&lt;";
		$layout_next = "&gt;&gt;";

		// layout options
		if (count($ALayout > 0))
		{
			if ($ALayout["link"])
			{
				$layout_link = " class=\"".$ALayout["link"]."\"";
			}

			if ($ALayout["prev"])
			{
				$layout_prev = $ALayout["prev"];
			}

			if ($ALayout["next"])
			{
				$layout_next = $ALayout["next"];
			}
		}

		// Wenn Hits gr�sser Limit, zeige Links an
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

			// �bergehe "zurck"-link, wenn offset 0 ist.
			if ($AOffset >= 1)
			{
				$prevoffset = $AOffset - $ALimit;
				$LinkBar .= "<a".$layout_link." href=\"".$link.$prevoffset."\">".$layout_prev."&nbsp;</a>";
			}

			// Ben�tigte Seitenzahl kalkulieren
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
					$LinkBar .= "<font color='Gray'>[<b>".$i."</b>]</font> ";
				}
				else
				{
					$LinkBar .= "[<a".$layout_link." href=\"".$link.$newoffset."\">$i</a>] ";
				}
			}
			//			}

			// Checken, ob letze Seite erreicht ist
			// Wenn nicht, gebe einen "Weiter"-Link aus
			if (! ( ($AOffset/$ALimit)==($pages-1) ) && ($pages!=1) )
			{
				$newoffset=$AOffset+$ALimit;
				$LinkBar .= "<a".$layout_link." href=\"".$link.$newoffset."\">&nbsp;".$layout_next."</a>";
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
	* Creates a combination of HTML selects for date inputs
	*
	* Creates a combination of HTML selects for date inputs
	* The select names are $prefix[y] for years, $prefix[m]
	* for months and $prefix[d] for days.
	*
	* @access	public
	* @param	string	$prefix Prefix of the select name
	* @param	integer	$year Default value for year select
	* @param	integer	$month Default value for month select
	* @param	integer	$day Default value for day select
	* @return	string	HTML select boxes
	* @author	Aresch Yavari <ay@databay.de>
	* @author Helmut Schottmüller <hschottm@tzi.de>
	*/
	function makeDateSelect($prefix, $year = "", $month = "", $day = "")
	{
		global $lng;

		if (!strlen("$year$month$day")) {
			$now = getdate();
			$year = $now["year"];
			$month = $now["mon"];
			$day = $now["mday"];
		} else {
			// delete leading zeros
			$year = (int)$year;
			$month = (int)$month;
			$day = (int)$day;
		}

		// build day select
		$sel_day .= "<select name=\"".$prefix."[d]\">\n";

		for ($i = 1; $i <= 31; $i++)
		{
			$sel_day .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
		}
		$sel_day .= "</select>\n";
		$sel_day = preg_replace("/(value\=\"$day\")/", "$1 selected=\"selected\"", $sel_day);

		// build month select
		$sel_month .= "<select name=\"".$prefix."[m]\">\n";

		for ($i = 1; $i <= 12; $i++)
		{
			$sel_month .= "<option value=\"$i\">" . $lng->txt("month_" . sprintf("%02d", $i) . "_long") . "</option>\n";
		}
		$sel_month .= "</select>\n";
		$sel_month = preg_replace("/(value\=\"$month\")/", "$1 selected=\"selected\"", $sel_month);

		// build year select
		$sel_year .= "<select name=\"".$prefix."[y]\">\n";

		for ($i = $year; $i <= $year + 3; $i++)
		{
			$sel_year .= "<option value=\"$i\">" . sprintf("%04d", $i) . "</option>\n";
		}
		$sel_year .= "</select>\n";
		$sel_year = preg_replace("/(value\=\"$year\")/", "$1 selected=\"selected\"", $sel_year);

		$dateformat = $lng->text["lang_dateformat"];
		$dateformat = strtolower(preg_replace("/\W/", "", $dateformat));
		$dateformat = strtolower(preg_replace("/(\w)/", "%%$1", $dateformat));
		$dateformat = preg_replace("/%%d/", $sel_day, $dateformat);
		$dateformat = preg_replace("/%%m/", $sel_month, $dateformat);
		$dateformat = preg_replace("/%%y/", $sel_year, $dateformat);
		return $dateformat;
	}

	/**
	* Creates a combination of HTML selects for time inputs
	*
	* Creates a combination of HTML selects for time inputs.
	* The select names are $prefix[h] for hours, $prefix[m]
	* for minutes and $prefix[s] for seconds.
	*
	* @access	public
	* @param	string	$prefix Prefix of the select name
	* @param  boolean $short Set TRUE for a short time input (only hours and minutes). Default is TRUE
	* @param	integer $hour Default hour value
	* @param	integer $minute Default minute value
	* @param	integer $second Default second value
	* @return	string	HTML select boxes
	* @author Helmut Schottmüller <hschottm@tzi.de>
	*/
	function makeTimeSelect($prefix, $short = true, $hour = "", $minute = "", $second = "")
	{
		global $lng;

		if (!strlen("$hour$minute$second")) {
			$now = localtime();
			$hour = $now[2];
			$minute = $now[1];
			$second = $now[0];
		} else {
			$hour = (int)$hour;
			$minute = (int)$minute;
			$second = (int)$second;
		}
		// build hour select
		$sel_hour .= "<select name=\"".$prefix."[h]\">\n";

		for ($i = 0; $i <= 23; $i++)
		{
			$sel_hour .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
		}
		$sel_hour .= "</select>\n";
		$sel_hour = preg_replace("/(value\=\"$hour\")/", "$1 selected=\"selected\"", $sel_hour);

		// build minutes select
		$sel_minute .= "<select name=\"".$prefix."[m]\">\n";

		for ($i = 0; $i <= 59; $i++)
		{
			$sel_minute .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
		}
		$sel_minute .= "</select>\n";
		$sel_minute = preg_replace("/(value\=\"$minute\")/", "$1 selected=\"selected\"", $sel_minute);

		if (!$short) {
			// build seconds select
			$sel_second .= "<select name=\"".$prefix."[s]\">\n";

			for ($i = 0; $i <= 59; $i++)
			{
				$sel_second .= "<option value=\"$i\">" . sprintf("%02d", $i) . "</option>\n";
			}
			$sel_second .= "</select>\n";
			$sel_second = preg_replace("/(value\=\"$second\")/", "$1 selected=\"selected\"", $sel_second);
		}
		$timeformat = $lng->text["lang_timeformat"];
		$timeformat = strtolower(preg_replace("/\W/", "", $timeformat));
		$timeformat = preg_replace("/(\w)/", "%%$1", $timeformat);
		$timeformat = preg_replace("/%%h/", $sel_hour, $timeformat);
		$timeformat = preg_replace("/%%i/", $sel_minute, $timeformat);
		if ($short) {
			$timeformat = preg_replace("/%%s/", "", $timeformat);
		} else {
			$timeformat = preg_replace("/%%s/", $sel_second, $timeformat);
		}
		return $timeformat;
	}

	/*
	* This preg-based function checks whether an e-mail address is formally valid.
	* It works with all top level domains including the new ones (.biz, .info, .museum etc.)
	* and the special ones (.arpa, .int etc.)
	* as well as with e-mail addresses based on IPs (e.g. webmaster@123.45.123.45)
	* @author	Unknown <mail@philipp-louis.de> (source: http://www.php.net/preg_match)
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
	*/
	function isPassword($a_passwd)
	{
		if (empty($a_passwd))
		{
			return false;
		}

		if (strlen($a_passwd) < 6)
		{
			return false;
		}

		if (!ereg("^[A-Za-z0-9_\.\+\-\*\@!\$\%\~]+$", $a_passwd))
		{
			return false;
		}

		return true;
	}

	/*
	* validates a login
	* @access	public
	* @param	string	login
	* @return	boolean	true if valid
	*/
	function isLogin($a_login)
	{
		if (empty($a_login))
		{
			return false;
		}

		if (strlen($a_login) < 4)
		{
			return false;
		}

		if (!ereg("^[A-Za-z0-9_\.\+\-\*\@!\$\%\~]+$", $a_login))
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

	/**
	* converts a string of format var1 = "val1" var2 = "val2" ... into an array
	*
	* @param	string		$a_str		string in format: var1 = "val1" var2 = "val2" ...
	*
	* @return	array		array of variable value pairs
	*/
	function attribsToArray($a_str)
	{
		$attribs = array();
		while (is_int(strpos($a_str, "=")))
		{
			$eq_pos = strpos($a_str, "=");
			$qu1_pos = strpos($a_str, "\"");
			$qu2_pos = strpos(substr($a_str, $qu1_pos + 1), "\"") + $qu1_pos + 1;
			if (is_int($eq_pos) && is_int($qu1_pos) && is_int($qu2_pos))
			{
				$var = trim(substr($a_str, 0, $eq_pos));
				$val = trim(substr($a_str, $qu1_pos + 1, ($qu2_pos - $qu1_pos) - 1));
				$attribs[$var] = $val;
				$a_str = substr($a_str, $qu2_pos + 1);
			}
			else
			{
				$a_str = "";
			}
		}
		return $attribs;
	}

	/**
	* Copies content of a directory $a_sdir recursively to a directory $a_tdir
	* @param	string	$a_sdir		source directory
	* @param	string	$a_tdir		target directory
	*
	* @return	boolean	TRUE for sucess, FALSE otherwise
	* @access	public
	*/
	function rCopy ($a_sdir, $a_tdir)
	{
		// check if arguments are directories
		if (!@is_dir($a_sdir) or
		!@is_dir($a_tdir))
		{
			return FALSE;
		}

		// read a_sdir, copy files and copy directories recursively
		$dir = opendir($a_sdir);

		while($file = readdir($dir))
		{
			if ($file != "." and
			$file != "..")
			{
				// directories
				if (@is_dir($a_sdir."/".$file))
				{
					if (!@is_dir($a_tdir."/".$file))
					{
						if (!ilUtil::makeDir($a_tdir."/".$file))
						return FALSE;

						//chmod($a_tdir."/".$file, 0775);
					}

					if (!ilUtil::rCopy($a_sdir."/".$file,$a_tdir."/".$file))
					{
						return FALSE;
					}
				}

				// files
				if (@is_file($a_sdir."/".$file))
				{
					if (!copy($a_sdir."/".$file,$a_tdir."/".$file))
					{
						return FALSE;
					}
				}
			}
		}
		return TRUE;
	}

	/**
	* get webspace directory
	*
	* @param	string		$mode		use "filesystem" for filesystem operations
	*									and "output" for output operations, e.g. images
	*/
	function getWebspaceDir($mode = "filesystem")
	{
		global $ilias;

		if ($mode == "filesystem")
		{
			return "./".ILIAS_WEB_DIR."/".$ilias->client_id;
		}
		else
		{
			if (defined("ILIAS_MODULE"))
			{
				return "../".ILIAS_WEB_DIR."/".$ilias->client_id;
			}
			else
			{
				return "./".ILIAS_WEB_DIR."/".$ilias->client_id;
			}
		}

		//return $ilias->ini->readVariable("server","webspace_dir");
	}

	/**
	* get data directory (outside webspace)
	*/
	function getDataDir()
	{
		return CLIENT_DATA_DIR;
		//global $ilias;

		//return $ilias->ini->readVariable("server", "data_dir");
	}

	/**
	* reads all active sessions from db and returns users that are online
	* OR returns only one active user if a user_id is given
	*
	* @param	integer	user_id (optional)
	* @return	array
	*/
	function getUsersOnline($a_user_id = 0)
	{
		global $ilias;

		if ($a_user_id == 0)
		{
			$where = "WHERE user_id != 0";
		}
		else
		{
			$where = "WHERE user_id = '".$a_user_id."'";
		}

		$q = "SELECT count(user_id) as num,user_id,data,firstname,lastname,title,login,last_login FROM usr_session ".
		"LEFT JOIN usr_data ON user_id=usr_id ".$where.
		" AND expires>UNIX_TIMESTAMP() GROUP BY user_id";
		$r = $ilias->db->query($q);

		while ($user = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$users[$user["user_id"]] = $user;
		}

		return $users ? $users : array();
	}


	/**
	* create directory
	*
	* deprecated use makeDir() instead!
	*/
	function createDirectory($a_dir, $a_mod = 0755)
	{
		ilUtil::makeDir($a_dir);
		//@mkdir($a_dir);
		//@chmod($a_dir, $a_mod);
	}


	/**
	* unzip file
	*
	* @param	string	$a_file		full path/filename
	*/
	function unzip($a_file)
	{
		//global $ilias;

		$pathinfo = pathinfo($a_file);
		$dir = $pathinfo["dirname"];
		$file = $pathinfo["basename"];

		// unzip
		$cdir = getcwd();
		chdir($dir);
		$unzip = PATH_TO_UNZIP;
		//$unzip = $ilias->getSetting("unzip_path");

		// workaround for unzip problem (unzip of subdirectories fails, so
		// we create the subdirectories ourselves first)
		// get list
		$unzipcmd = $unzip." -Z -1 ".ilUtil::escapeShellArg($file);
		exec($unzipcmd, $arr);
		$zdirs = array();

		foreach($arr as $line)
		{
			if(is_int(strpos($line, "/")))
			{
				$zdir = substr($line, 0, strrpos($line, "/"));
				$nr = substr_count($zdir, "/");
				//echo $zdir." ".$nr."<br>";
				while ($zdir != "")
				{
					$nr = substr_count($zdir, "/");
					$zdirs[$zdir] = $nr;				// collect directories
					//echo $dir." ".$nr."<br>";
					$zdir = substr($zdir, 0, strrpos($zdir, "/"));
				}
			}
		}

		asort($zdirs);

		foreach($zdirs as $zdir => $nr)				// create directories
		{
			ilUtil::createDirectory($zdir);
		}

		// real unzip
		$unzipcmd = $unzip." ".ilUtil::escapeShellArg($file);
		exec($unzipcmd);

		chdir($cdir);
	}

	/**
	*	zips given directory into given zip.file
	*/
	function zip($a_dir, $a_file)
	{
		//global $ilias;

		$cdir = getcwd();

		$pathinfo = pathinfo($a_file);
		$dir = $pathinfo["dirname"];
		$file = $pathinfo["basename"];

		// unzip
		$cdir = getcwd();
		chdir($dir);

		$zip = PATH_TO_ZIP;
		//$zip = $ilias->getSetting("zip_path");

		$name = basename($a_dir);

		$zipcmd = $zip." -r ".ilUtil::escapeShellArg($a_file)." ".ilUtil::escapeShellArg($name);
		exec($zipcmd);

		chdir($cdir);
	}

	/**
	* get convert command
	*/
	function getConvertCmd()
	{
		return PATH_TO_CONVERT;
		//global $ilias;

		//return $ilias->getSetting("convert_path");
	}

	/**
	*
	*
	* @param	string		$a_from				source file
	* @param	string		$a_to				target file
	* @param	string		$a_target_format	target image file format
	*/
	function convertImage($a_from, $a_to, $a_target_format = "", $a_geometry = "")
	{
		$format_str = ($a_target_format != "")
		? strtoupper($a_target_format).":"
		: "";
		$geometry = ($a_geometry != "")
		? " -geometry ".$a_geometry."x".$a_geometry." "
		: "";
		$convert_cmd = ilUtil::getConvertCmd()." ".
		ilUtil::escapeShellArg($a_from)." ".$geometry.ilUtil::escapeShellArg($format_str.$a_to);
		system($convert_cmd);
	}

	/**
	*	produce pdf out of html with htmldoc
	*   @param  html    String  HTML-Data given to create pdf-file
	*   @param  pdf_file    String  Filename to save pdf in
	*/
	function html2pdf($html, $pdf_file)
	{
		//global $ilias;

		$html_file = str_replace(".pdf",".html",$pdf_file);

		$fp = fopen( $html_file ,"wb");
		fwrite($fp, $html);
		fclose($fp);

		$htmldoc_path = PATH_TO_HTMLDOC;
		//$htmldoc_path = $ilias->getSetting("htmldoc_path");

		$htmldoc = $htmldoc_path." ";
		$htmldoc .= "--no-toc ";
		$htmldoc .= "--no-jpeg ";
		$htmldoc .= "--webpage ";
		$htmldoc .= "--outfile " . ilUtil::escapeShellArg($pdf_file) . " ";
		$htmldoc .= "--bodyfont Arial ";
		$htmldoc .= "--charset iso-8859-15 ";
		$htmldoc .= "--color ";
		$htmldoc .= "--size A4  ";      // --landscape
		$htmldoc .= "--format pdf ";
		$htmldoc .= "--footer ... ";
		$htmldoc .= "--header ... ";
		$htmldoc .= "--left 60 ";
		// $htmldoc .= "--right 200 ";
		$htmldoc .= $html_file;
		exec($htmldoc);

	}

	/**
	*   deliver data for download via browser.
	*/
	function deliverData($a_data, $a_filename, $mime = "application/octet-stream")
	{
		$disposition = "attachment"; // "inline" to view file in browser or "attachment" to download to hard disk
		//		$mime = "application/octet-stream"; // or whatever the mime type is

		if (isset($_SERVER["HTTPS"])) {
			/**
			* We need to set the following headers to make downloads work using IE in HTTPS mode.
			*/
			header("Pragma: ");
			header("Cache-Control: ");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
			header("Cache-Control: post-check=0, pre-check=0", false);
		}
		else if ($disposition == "attachment")
		{
			header("Cache-control: private");
		}
		else
		{
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
		}

		$ascii_filename = ilUtil::getASCIIFilename($a_filename);

		header("Content-Type: $mime");
		header("Content-Disposition:$disposition; filename=\"".$ascii_filename."\"");
		header("Content-Description: ".$ascii_filename);
		header("Content-Length: ".(string)(strlen($a_data)));
		header("Connection: close");

		echo $a_data;
		exit;
	}

	/**
	*   deliver file for download via browser.
	*/
	function deliverFile($a_file, $a_filename)
	{
		$disposition = "attachment"; // "inline" to view file in browser or "attachment" to download to hard disk
		$mime = "application/octet-stream"; // or whatever the mime type is
		if (isset($_SERVER["HTTPS"]))
		{
			/**
			* We need to set the following headers to make downloads work using IE in HTTPS mode.
			*/
			header("Pragma: ");
			header("Cache-Control: ");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
			header("Cache-Control: post-check=0, pre-check=0", false);
		}
		else if ($disposition == "attachment")
		{
			header("Cache-control: private");
		}
		else
		{
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
		}

		$ascii_filename = ilUtil::getASCIIFilename($a_filename);

		header("Content-Type: $mime");
		header("Content-Disposition:$disposition; filename=\"".$ascii_filename."\"");
		header("Content-Description: ".$ascii_filename);
		header("Content-Length: ".(string)(filesize($a_file)));
		header("Connection: close");
		readfile( $a_file );
		exit;
	}

	/**
	* convert utf8 to ascii filename
	*
	* @param	string		$a_filename		utf8 filename
	*/
	function getASCIIFilename($a_filename)
	{
		// The filename must be converted to ASCII, as of RFC 2183,
		// section 2.3.
		// Despite the RFC, Internet Explorer on Windows supports
		// ISO 8895-1 encoding for the file name. We use this fact, to
		// produce a better result, if the user uses IE.

		/// Implementation note:
		/// 	The proper way to convert charsets is mb_convert_encoding.
		/// 	Unfortunately Multibyte String functions are not an
		/// 	installation requirement for ILIAS 3.
		/// 	Codelines behind three slashes '///' show how we would do
		/// 	it using mb_convert_encoding.
		/// 	Note that mb_convert_encoding has the bad habit of
		/// 	substituting unconvertable characters with HTML
		/// 	entitities. Thats why we need a regular expression which
		/// 	replaces HTML entities with their first character.
		/// 	e.g. &auml; => a

		$user_agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
		if ((is_integer(strpos($user_agent, "msie"))) && is_integer(strpos($user_agent, "win")))
		{
			///$ascii_filename = mb_convert_encoding($a_filename, 'ISO_8859-1','UTF-8');
			///$ascii_filename = preg_replace('/\&(.)[^;]*;/","\\1', $ascii_filename);

			$ascii_filename = utf8_decode($a_filename);
		}
		else
		{
			///$ascii_filename = mb_convert_encoding($a_filename,'US-ASCII','UTF-8');
			///$ascii_filename = preg_replace('/\&(.)[^;]*;/','\\1', $ascii_filename);

			$ascii_filename = htmlentities($a_filename,ENT_NOQUOTES,'UTF-8');
			$ascii_filename = preg_replace('/\&(.)[^;]*;/','\\1', $ascii_filename);
			$ascii_filename = preg_replace('/[\x7f-\xff]/','_', $ascii_filename);
		}

		// Windows does not allow the following characters in filenames:
		// \/:*?"<>|
		if (is_integer(strpos($user_agent, "win")))
		{
			$ascii_filename = preg_replace('/[:\x5c\/\*\?\"<>\|]/','_', $ascii_filename);
		}

		return $ascii_filename;
	}

	/**
	* get full java path (dir + java command)
	*/
	function getJavaPath()
	{
		return PATH_TO_JAVA;
		//global $ilias;

		//return $ilias->getSetting("java_path");
	}

	/**
	* append URL parameter string ("par1=value1&par2=value2...")
	* to given URL string
	*/
	function appendUrlParameterString($a_url, $a_par)
	{
		$url = (is_int(strpos($a_url, "?")))
		? $a_url."&".$a_par
		: $a_url."?".$a_par;

		return $url;
	}

	/**
	* creates a new directory and inherits all filesystem permissions of the parent directory
	* You may pass only the name of your new directory or with the entire path or relative path information.
	*
	* examples:
	* a_dir = /tmp/test/your_dir
	* a_dir = ../test/your_dir
	* a_dir = your_dir (--> creates your_dir in current directory)
	*
	* @access	public
	* @param	string	[path] + directory name
	* @return	boolean
	*
	*/
	function makeDir($a_dir)
	{
		$a_dir = trim($a_dir);

		// remove trailing slash (bugfix for php 4.2.x)
		if (substr($a_dir,-1) == "/")
		{
			$a_dir = substr($a_dir,0,-1);
		}

		// check if a_dir comes with a path
		if (!($path = substr($a_dir,0, strrpos($a_dir,"/") - strlen($a_dir))))
		{
			$path = ".";
		}

		// create directory with file permissions of parent directory
		umask(0000);
		return @mkdir($a_dir,fileperms($path));
	}


	/**
	* Create a new directory and all parent directories
	*
	* Creates a new directory and inherits all filesystem permissions of the parent directory
	* If the parent directories doesn't exist, they will be created recursively.
	* The directory name NEEDS TO BE an absolute path, because it seems that relative paths
	* are not working with PHP's file_exists function.
	*
	* @author Helmut Schottmüller <hschottm@tzi.de>
	* @param string $a_dir The directory name to be created
	* @access public
	*/
	function makeDirParents($a_dir)
	{
		$dirs = array($a_dir);
		$a_dir = dirname($a_dir);
		$last_dirname = '';
		while($last_dirname != $a_dir)
		{
			array_unshift($dirs, $a_dir);
			$last_dirname = $a_dir;
			$a_dir = dirname($a_dir);
		}

		umask(0000);
		foreach ($dirs as $dir)
		{
			if (! file_exists($dir))
			{
				if (! mkdir($dir, $umask))
				{
					error_log("Can't make directory: $dir");
					return false;
				}
			}
			elseif (! is_dir($dir))
			{
				error_log("$dir is not a directory");
				return false;
			}
			else
			{
				// get umask of the last existing parent directory
				$umask = fileperms($dir);
			}
		}
		return true;
	}

	/**
	* removes a dir and all its content (subdirs and files) recursively
	*
	* @access	public
	* @param	string	dir to delete
	* @author	Unknown <flexer@cutephp.com> (source: http://www.php.net/rmdir)
	*/
	function delDir($a_dir)
	{
		if (!is_dir($a_dir))
		{
			return;
		}

		$current_dir = opendir($a_dir);

		while($entryname = readdir($current_dir))
		{
			if(is_dir($a_dir."/".$entryname) and ($entryname != "." and $entryname!=".."))
			{
				ilUtil::delDir(${a_dir}."/".${entryname});
			}
			elseif ($entryname != "." and $entryname!="..")
			{
				unlink(${a_dir}."/".${entryname});
			}
		}

		closedir($current_dir);
		rmdir(${a_dir});
	}


	/**
	* get directory
	*/
	function getDir($a_dir)
	{
		$current_dir = opendir($a_dir);

		$dirs = array();
		$files = array();
		while($entry = readdir($current_dir))
		{
			if(is_dir($a_dir."/".$entry))
			{
				$dirs[$entry] = array("type" => "dir", "entry" => $entry);
			}
			else
			{
				$size = filesize($a_dir."/".$entry);
				$files[$entry] = array("type" => "file", "entry" => $entry,
				"size" => $size);
			}
		}
		ksort($dirs);
		ksort($files);

		return array_merge($dirs, $files);
	}


	/**
	* get the tree_id of a group where an object with the passed ref_id belongs to.
	* DEPRECATED
	* @param	string	ref_id of an object that is in a group
	* @access	public
	* @return	integer	the ref_id of the group or boolean false if no group was found
	*/
	function getGroupId($a_parent_ref)
	{
		return false;

		global $ilias;

		$q = "SELECT DISTINCT tree FROM grp_tree WHERE child='".$a_parent_ref."'";
		$r = $ilias->db->query($q);
		$row = $r->fetchRow();

		return $row[0] ? $row[0] : false;
	}

	/**
	* strip slashes if magic qoutes is enabled
	*
	* @param	boolean		strip also html tags
	*/
	function stripSlashes($a_str, $a_strip_html = true)
	{
		if (ini_get("magic_quotes_gpc"))
		{
			$a_str = stripslashes($a_str);
		}
		
		if ($a_strip_html)
		{
			$a_str = ilUtil::stripScriptHTML($a_str);
		}
		
		return $a_str;
	}
	
	
	/**
	* strip script tags (has to be improved)
	*/
	function stripScriptHTML($a_str)
	{
		$a_str = strip_tags($a_str);
		
		return $a_str;
	}

	
	/**
	* add slashes if magic qoutes is disabled
	* don't use that for db inserts/updates! use prepareDBString
	* instead
	*/
	function addSlashes($a_str)
	{
		if (ini_get("magic_quotes_gpc"))
		{
			return $a_str;
		}
		else
		{
			return addslashes($a_str);
		}
	}

	/**
	* prepares string output for html forms
	* @access	public
	* @param	string
	* @param	boolean		true: strip slashes, if magic_quotes is enabled
	*						use this if $a_str comes from $_GET or $_POST var,
	*						use false, if $a_str comes from database
	* @return	string
	*/
	function prepareFormOutput($a_str, $a_strip = false)
	{
		if($a_strip)
		{
			$a_str = ilUtil::stripSlashes($a_str);
		}
		return htmlspecialchars($a_str);
	}


	/**
	* prepare a string for db writing (insert/update)
	*
	* @param	string		$a_str		string
	*
	* @return	string		escaped string
	*/
	function prepareDBString($a_str)
	{
		return addslashes($a_str);
	}


	/**
	* removes object from all user's desktops
	* @access	public
	* @param	integer	ref_id
	* @return	array	user_ids of all affected users
	*/
	function removeItemFromDesktops($a_id)
	{
		global $ilias;

		$q = "SELECT user_id FROM desktop_item WHERE item_id = '".$a_id."'";
		$r = $ilias->db->query($q);

		$users = array();

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$users[] = $row->user_id;
		} // while

		if (count($users) > 0)
		{
			$q = "DELETE FROM desktop_item WHERE item_id = '".$a_id."'";
			$ilias->db->query($q);
		}

		return $users;
	}


	/**
	* extracts parameter value pairs from a string into an array
	*
	* @param	string		$a_parstr		parameter string (format: par1="value1", par2="value2", ...)
	*
	* @return	array		array of parameter value pairs
	*/
	function extractParameterString($a_parstr)
	{
		// parse parameters in array
		$par = array();
		$ok=true;
		while(($spos=strpos($a_parstr,"=")) && $ok)
		{
			// extract parameter
			$cpar = substr($a_parstr,0,$spos);
			$a_parstr = substr($a_parstr,$spos,strlen($a_parstr)-$spos);
			while(substr($cpar,0,1)=="," ||substr($cpar,0,1)==" " || substr($cpar,0,1)==chr(13) || substr($cpar,0,1)==chr(10))
			$cpar = substr($cpar,1,strlen($cpar)-1);
			while(substr($cpar,strlen($cpar)-1,1)==" " || substr($cpar,strlen($cpar)-1,1)==chr(13) || substr($cpar,strlen($cpar)-1,1)==chr(10))
			$cpar = substr($cpar,0,strlen($cpar)-1);

			// extract value
			if($spos=strpos($a_parstr,"\""))
			{
				$a_parstr = substr($a_parstr,$spos+1,strlen($a_parstr)-$spos);
				$spos=strpos($a_parstr,"\"");
				if(is_int($spos))
				{
					$cval = substr($a_parstr,0,$spos);
					$par[$cpar]=$cval;
					$a_parstr = substr($a_parstr,$spos+1,strlen($a_parstr)-$spos-1);
				}
				else
				$ok=false;
			}
			else
			$ok=false;
		}

		if($ok) return $par; else return false;
	}

	function assembleParameterString($a_par_arr)
	{
		if (is_array($a_par_arr))
		{
			$target_arr = array();
			foreach ($a_par_arr as $par => $val)
			{
				$target_arr[] = "$par=\"$val\"";
			}
			$target_str = implode(", ", $target_arr);
		}

		return $target_str;
	}

	/**
	* dumps ord values of every character of string $a_str
	*/
	function dumpString($a_str)
	{
		$ret = $a_str.": ";
		for($i=0; $i<strlen($a_str); $i++)
		{
			$ret.= ord(substr($a_str,$i,1))." ";
		}
		return $ret;
	}


	/**
	* convert "y"/"n" to true/false
	*/
	function yn2tf($a_yn)
	{
		if(strtolower($a_yn) == "y")
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* convert true/false to "y"/"n"
	*/
	function tf2yn($a_tf)
	{
		if($a_tf)
		{
			return "y";
		}
		else
		{
			return "f";
		}
	}

	/**
	* sub-function to sort an array
	*
	* @param	array	$a
	* @param	array	$b
	*
	* @return	boolean	true on success / false on error
	*/
	function sort_func ($a, $b)
	{
		global $array_sortby,$array_sortorder;

		if ($array_sortorder == "asc")
		{
			return strcasecmp($a[$array_sortby], $b[$array_sortby]);
		}

		if ($array_sortorder == "desc")
		{
			return strcasecmp($b[$array_sortby], $a[$array_sortby]);
		}
	}

	/**
	* sub-function to sort an array
	*
	* @param	array	$a
	* @param	array	$b
	*
	* @return	boolean	true on success / false on error
	*/
	function sort_func_numeric ($a, $b)
	{
		global $array_sortby,$array_sortorder;

		if ($array_sortorder == "asc")
		{
			return $a["$array_sortby"] > $b["$array_sortby"];
		}

		if ($array_sortorder == "desc")
		{
			return $a["$array_sortby"] < $b["$array_sortby"];
		}
	}
	/**
	* sortArray
	*
	* @param	array	array to sort
	* @param	string	sort_column
	* @param	string	sort_order (ASC or DESC)
	* @param	bool	sort numeric?
	*
	* @return	array	sorted array
	*/
	function sortArray($array,$a_array_sortby,$a_array_sortorder = 0,$a_numeric = false)
	{
		global $array_sortby,$array_sortorder;

		$array_sortby = $a_array_sortby;

		if ($a_array_sortorder == "desc")
		{
			$array_sortorder = "desc";
		}
		else
		{
			$array_sortorder = "asc";
		}
		if($a_numeric)
		{
			usort($array, array("ilUtil", "sort_func_numeric"));
		}
		else
		{
			usort($array, array("ilUtil", "sort_func"));
		}
		//usort($array,"ilUtil::sort_func");

		return $array;
	}

	/**
	* Make a multi-dimensional array to have only DISTINCT values for a certain "column".
	* It's like using the DISTINCT parameter on a SELECT sql statement.
	*
	* @param	array	your multi-dimensional array
	* @param	string	'column' to filter
	* @return	array	filtered array
	* @author	Unknown <tru@ascribedata.com> (found in PHP annotated manual)
	*/
	function unique_multi_array($array, $sub_key)
	{
		$target = array();
		$existing_sub_key_values = array();

		foreach ($array as $key=>$sub_array)
		{
			if (!in_array($sub_array[$sub_key], $existing_sub_key_values))
			{
				$existing_sub_key_values[] = $sub_array[$sub_key];
				$target[$key] = $sub_array;
			}
		}

		return $target;
	}


	/**
	* returns the best supported image type by this PHP build
	*
	* @param	string	$desired_type	desired image type ("jpg" | "gif" | "png")
	*
	* @return	string					supported image type ("jpg" | "gif" | "png" | "")
	*/
	function getGDSupportedImageType($a_desired_type)
	{
		$a_desired_type = strtolower($a_desired_type);
		// get supported Image Types
		$im_types = ImageTypes();

		switch($a_desired_type)
		{
			case "jpg":
			if ($im_types & IMG_JPG) return "jpg";
			if ($im_types & IMG_GIF) return "gif";
			if ($im_types & IMG_PNG) return "png";
			break;

			case "gif":
			if ($im_types & IMG_GIF) return "gif";
			if ($im_types & IMG_JPG) return "jpg";
			if ($im_types & IMG_PNG) return "png";
			break;

			case "png":
			if ($im_types & IMG_PNG) return "png";
			if ($im_types & IMG_JPG) return "jpg";
			if ($im_types & IMG_GIF) return "gif";
			break;
		}

		return "";
	}

	/**
	* checks if mime type is provided by getimagesize()
	*
	* @param	string		$a_mime		mime format
	*
	* @return	boolean		returns true if size is deducible by getimagesize()
	*/
	function deducibleSize($a_mime)
	{
		if (($a_mime == "image/gif") || ($a_mime == "image/jpeg") ||
		($a_mime == "image/png") || ($a_mime == "application/x-shockwave-flash") ||
		($a_mime == "image/tiff") || ($a_mime == "image/x-ms-bmp") ||
		($a_mime == "image/psd") || ($a_mime == "image/iff"))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* http redirect to other script
	*
	* @param	string		$a_script		target script
	*/
	function redirect($a_script)
	{
		global $log;

		header("Location: ".$a_script);
		exit();
	}

	/**
	* inserts installation id into ILIAS id
	*
	* e.g. "il__pg_3" -> "il_43_pg_3"
	*/
	function insertInstIntoID($a_value)
	{
		if (substr($a_value, 0, 4) == "il__")
		{
			$a_value = "il_".IL_INST_ID."_".substr($a_value, 4, strlen($a_value) - 4);
		}

		return $a_value;
	}

	/**
	* checks if group name already exists. Groupnames must be unique for mailing purposes
	* static function
	* @access	public
	* @param	string	groupname
	* @param	integer	obj_id of group to exclude from the check.
	* @return	boolean	true if exists
	*/
	function groupNameExists($a_group_name,$a_id = 0)
	{
		global $ilDB,$ilErr;

		if (empty($a_group_name))
		{
			$message = get_class($this)."::_NameExists(): No groupname given!";
			$ilErr->raiseError($message,$ilErr->WARNING);
		}

		$clause = ($a_id) ? " AND obj_id != '".$a_id."'" : "";

		$q = "SELECT obj_id FROM object_data ".
		"WHERE title = '".addslashes($a_group_name)."' ".
		"AND type = 'grp'".
		$clause;
		$r = $ilDB->query($q);

		if ($r->numRows() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*
	* get the user_ids which correspond a search string
	* static function
	* @param	string search string
	* @access	public
	*/
	function searchGroups($a_search_str)
	{
		global $ilDB;

		$q = "SELECT * ".
		"FROM object_data ,object_reference ".
		"WHERE (object_data.title LIKE '%".$a_search_str."%' ".
		"OR object_data.description LIKE '%".$a_search_str."%') ".
		"AND object_data.type = 'grp' ".
		"AND object_data.obj_id = object_reference.obj_id";

		$res = $ilDB->query($q);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// STORE DATA IN ARRAY WITH KEY obj_id
			// SO DUPLICATE ENTRIES ( LINKED OBJECTS ) ARE UNIQUE
			$ids[$row->obj_id] = array(
			"ref_id"        => $row->ref_id,
			"title"         => $row->title,
			"description"   => $row->description);
		}

		return $ids ? $ids : array();
	}

	function getMemString()
	{
		$my_pid = getmypid();
		return ("MEMORY USAGE (% KB PID ): ".`ps -eo%mem,rss,pid | grep $my_pid`);
	}

	function isWindows()
	{
		if (strtolower(substr(php_uname(), 0, 3)) == "win")
		{
			return true;
		}
		return false;
	}

	function escapeShellArg($a_arg)
	{
		global $PHP_OS;

		if (ini_get("safe_mode") == 1 || ilUtil::isWindows())
		{
			return $a_arg;
		}
		else
		{
			return escapeshellarg($a_arg);
		}
	}

	/*
	* Calculates a Microsoft Excel date/time value
	*
	* Calculates a Microsoft Excel date/time value (nr of days after 1900/1/1 0:00) for
	* a given date and time. The function only accepts dates after 1970/1/1, because the
	* unix timestamp functions used in the function are starting with that date.
	* If you don't enter parameters the date/time value for the actual date/time
	* will be calculated.
	*
	* static function
	*
	* @param	integer $year Year
	* @param	integer $month Month
	* @param	integer $day Day
	* @param	integer $hour Hour
	* @param	integer $minute Minute
	* @param	integer $second Second
	* @return float The Microsoft Excel date/time value
	* @access	public
	*/
	function excelTime($year = "", $month = "", $day = "", $hour = "", $minute = "", $second = "")
	{
		$starting_time = mktime(0, 0, 0, 1, 1, 1970);
		if (strcmp("$year$month$day$hour$minute$second", "") == 0)
		{
			$target_time = time();
		}
		else
		{
			if ($year < 1970)
			{
				return 0;
			}
		}
		$target_time = mktime($hour, $minute, $second, $month, $day, $year);
		$difference = $target_time - $starting_time;
		$days = (($difference - ($difference % 86400)) / 86400);
		$difference = $difference - ($days * 86400) + 3600;
		return ($days + 25569 + ($difference / 86400));
	}

	/**
	* rename uploaded executables for security reasons
	*/
	function renameExecutables($a_dir)
	{
		ilUtil::rRenameSuffix($a_dir, "php", "sec");
		ilUtil::rRenameSuffix($a_dir, "php3", "sec");
		ilUtil::rRenameSuffix($a_dir, "php4", "sec");
		ilUtil::rRenameSuffix($a_dir, "inc", "sec");
		ilUtil::rRenameSuffix($a_dir, "lang", "sec");
		ilUtil::rRenameSuffix($a_dir, "phtml", "sec");
		ilUtil::rRenameSuffix($a_dir, "htaccess", "sec");
	}

	/**
	* Copies content of a directory $a_sdir recursively to a directory $a_tdir
	* @param	string	$a_sdir		source directory
	* @param	string	$a_tdir		target directory
	*
	* @return	boolean	TRUE for sucess, FALSE otherwise
	* @access	public
	*/
	function rRenameSuffix ($a_dir, $a_old_suffix, $a_new_suffix)
	{
		if ($a_dir == "/" || $a_dir == "" || is_int(strpos($a_dir, "..")))
		{
			return false;
		}

		// check if argument is directory
		if (!@is_dir($a_dir))
		{
			return false;
		}

		// read a_dir
		$dir = opendir($a_dir);

		while($file = readdir($dir))
		{
			if ($file != "." and
			$file != "..")
			{
				// directories
				if (@is_dir($a_dir."/".$file))
				{
					ilUtil::rRenameSuffix($a_dir."/".$file, $a_old_suffix, $a_new_suffix);
				}

				// files
				if (@is_file($a_dir."/".$file))
				{
					$path_info = pathinfo($a_dir."/".$file);
					if (strtolower($path_info["extension"]) ==
					strtolower($a_old_suffix))
					{
						$pos = strrpos($a_dir."/".$file, ".");
						$new_name = substr($a_dir."/".$file, 0, $pos).".".$a_new_suffix;
						rename($a_dir."/".$file, $new_name);
					}
				}
			}
		}
		return true;
	}

	function isAPICall () {
		return  strpos($_SERVER["SCRIPT_FILENAME"],"api") !== false ||
		strpos($_SERVER["SCRIPT_FILENAME"],"dummy") !== false;
	}

	function KT_replaceParam($qstring, $paramName, $paramValue) {
		if (preg_match("/&" . $paramName . "=/", $qstring)) {
			return preg_replace("/&" . $paramName . "=[^&]+/", "&" . $paramName . "=" . urlencode($paramValue), $qstring);
		} else {
			return $qstring . "&" . $paramName . "=" . urlencode($paramValue);
		}
	}

	function replaceUrlParameterString ($url, $parametersArray) {
		
		foreach ($parametersArray as $paramName => $paramValue ) {
			$url = ilUtil::KT_replaceParam($url, $paramName, $paramValue);
		}
		return $url;
	}

} // END class.ilUtil
?>
