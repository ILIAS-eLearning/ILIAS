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
	function getModules ($ATypeList = "")
	{
		global $ilias;

		$rbacadmin = new RbacAdminH($ilias->db);
		$db = $ilias->db;
		
		$arr = array();

		if (empty($ATypeList))
		{
			$query = "SELECT * FROM object_data
					  WHERE type = 'type'
					  ORDER BY type";
		}
		else
		{
			$query = "SELECT * FROM object_data
					  WHERE title IN ($ATypeList)
					  AND type='type'";
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
	* Builds aa select form field with options and shows the selected option first
	* @param string value to be selected
	* @param string variable name in formular
	* @param array array with $options
	* @param boolean
	*/
	function formSelect ($selected,$varname,$options,$multiple = false)
	{
		$multiple ? $multiple = "multiple=\"multiple\" " : "";
		$str = "<select name=\"".$varname ."\" ".$multiple.">\n";

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
			$a_path .= "/";
			
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
		
		$tpltab = new Template("tpl.adm_tabs.html", true, true);
		
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
	
} // END class.util
?>