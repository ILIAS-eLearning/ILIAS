<?php
/**
* util class
* various functions, usage as namespace
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
* @package ilias-core
*/
class TUtil
{
	/**
	* Fetch system_roles and return them in array(role_id => role_name)
	*/
	function getRoles ()
	{
		global $ilias;
		$db = $ilias->db;
		
		$res = $db->query("SELECT * FROM object_data
					WHERE type = 'role' ORDER BY title");
		
		if ($res->numRows() > 0)
		{
			while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$arr[$data["obj_id"]] = $data["title"];
			}
		}
		else
		{
			return false;
		}
		
		return $arr;
	}
	
	/**
	* Fetch loaded modules or possible modules in context
	* @param string
	*/
	function getModules ($a_objname)
	{
		global $ilias, $objDefinition;

		$rbacadmin = new RbacAdminH($ilias->db);
		$db = $ilias->db;
		
		$arr = array();
		
		$ATypeList = $objDefinition->getSubObjectsAsString($a_objname);
		
		if (empty($ATypeList))
		{
			$query = "SELECT * FROM object_data
					  WHERE type = 'typ'
					  ORDER BY type";
		}
		else
		{
			$query = "SELECT * FROM object_data
					  WHERE title IN ($ATypeList)
					  AND type='typ'";
		}

		$res = $db->query($query);
		
		$rolf_exist = false;
		
		if (count($rbacadmin->getRoleFolderOfObject($_GET["obj_id"])) > 0)
		{
			$rolf_exist = true;
		}
		
		if ($res->numRows() > 0)
		{
			while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (!$rolf_exist || ($data["title"] != "rolf"))
				{
					$arr[$data["title"]] = $data["description"];
				}
			}
		}
	   
		return $arr;
	}
	/**
	* Builds an html image tag
	* @param	string	object type
	* @param	string	tpl path
	* @param	string  html image tag
	*/
	function getImageTagByType($a_type,$a_path)
	{
		return "<img src=\"".$a_path."/images/"."icon_".$a_type.".gif\" border=\"0\">";
	}
	
	/**
	* Builds a select form field with options and shows the selected option first
	* @param	string	value to be selected
	* @param	string	variable name in formular
	* @param	array	array with $options (key = lang_key, value = long name)
	* @param	boolean
	*/
	function formSelect ($selected,$varname,$options,$multiple = false)
	{
		global $lng;
		
		$multiple ? $multiple = " multiple=\"multiple\"" : "";
		$str = "<select name=\"".$varname ."\"".$multiple.">\n";

		foreach ($options as $key => $val)
		{
		
			$str .= " <option value=\"".$val."\"";
			
			if ($selected == $key)
			{
				$str .= " selected=\"selected\"";
			}
			
			$str .= ">".$lng->txt($val)."</option>\n";
		}

		$str .= "</select>\n";
		
		return $str;
	}

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
	* @param string
	* @param string	 
	*/
	function getSelectName ($selected,$values)
	{
		return($values[$selected]);
	}

	/**
	* ???
	* @param string	 
	* @param string	 
	* @param string	 
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
	* @param string	 
	* @param string	 
	* @param string	 
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
		// Diese Funktion soll Formfeldeingaben berprfen (empty und required)
	}

	/**
	* ???
	* @param string	 
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
	* liefert den owner des objektes $Aobj_id als user_objekt zurück
	* @param string	 
	*/
	function getOwner ($Aobj_id)
	{
		global $ilias;
		$db = $ilias->db;

		$query = "SELECT owner FROM object_data
				  WHERE obj_id = '".$Aobj_id."'";

		$res = $db->query($query);
		
		if ($res->numRows() == 1)
		{
			$row = $res->fetchRow(DB_FETCHMODE_ORDERED);
			$owner_id = $row[0];
			
			if ($owner_id == -1)
			{
				//objekt hat keinen owner
				return false;
			}

			$owner = new User($owner_id);

			return $owner;
		}
		else
		{
			// select liefert falsch row-anzahl oder nix
			return false;
		}	
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
	* @param integer column to highlight
	* @param array array with templatereplacements
	*/
	function showTabs($a_hl, $a_o)
	{
		global $lng;
		
		$tpltab = new Template("tpl.tabs.html", true, true);
		
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
	* Get all objejects of a specific type and check access
    * recursive method
	* @param string type
	* @param string permissions to check e.g. 'visible','read'
	*/
	function getObjectsByOperations($a_type,$a_operation,$a_node = 0)
	{
		global $tree, $rbacsystem;
		static $objects = array();

		if($childs = $tree->getChilds($a_node))
		{
			foreach($childs as $child)
			{
				if($rbacsystem->checkAccess($a_operation,$child["obj_id"],$child["parent"],$a_type))
				{
					if($child["type"] == $a_type)
					{
						$objects[] = $child;
					}
					TUtil::getObjectsByOperations($a_type,$a_operation,$child["obj_id"]);
				}
			}
		}
		return $objects;
	}

	
	
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
	* @param	integer		Name der Skriptdatei (z.B. test.php)
	* @param	integer		Anzahl der Elemente insgesamt
	* @param	integer		Anzahl der Elemente pro Seite
	* @param	integer		Das aktuelle erste Element in der Liste
	* @param	array		Die zu übergebenen Parameter in der Form $AParams["Varname"] = "Varwert" (optional)
	* @return	array		linkbar or false on error
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

			// übergehe "zurck"-link, wenn offset 0 ist.
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
	* @param	string		$text: Der Text	
	*/
	function makeClickable($text)
	{
		// URL mit ://-Angabe
		$ret = eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])", "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\1://\\2\\3</a>", $text);
		
		// www-URL ohne ://-Angabe
		$ret = eregi_replace("([[:space:]]+)(www\.)([[:alnum:]#?/&=\.]+)", "\\1<a href=\"http://\\2\\3\" target=\"_blank\">\\2\\3</a>", $ret);
		
		// ftp-URL ohne ://-Angabe
		$ret = eregi_replace("([[:space:]]+)(ftp\.)([[:alnum:]#?/&=\.]+)", "\\1<a href=\"ftp://\\2\\3\" target=\"_blank\">\\2\\3</a>", $ret);
	    
		// E-Mail
		$ret = eregi_replace("(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",  "<a  href=\"mailto:\\1\">\\1</a>", $ret);
	    
		return($ret);
	}
	
	

} // END class.util
?>