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
	* Properties
	*/
	var $ClassName = "TUtil";	// Eigentlich unnötig
	
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
	* Fetch system_roles and return them in array(role_id => role_name)
	*/
	function getGroups ()
	{
		global $ilias;
		
		$db = $ilias->db;
		
		$db->query("SELECT grp_id,grp_name FROM group_data ORDER BY grp_id");
		
		if ($db->num_rows())
		{
			while ($db->next_record())
			{
				$arr[$db->f("grp_id")] = $db->f("grp_name");
			}
		}
		else
		{
			return false;
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
		$multiple ? $multiple = "multiple" : "";
		$str = "<select name=\"".$varname ."\" ".$multiple.">\n";

		foreach ($options as $key => $val)
		{
			$str .= " <option value=\"".$key."\"";
			
			if ($selected == $key)
			{
				$str .= " selected";
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
			$str .= " checked";
		}
		
		$str .= " value=\"".$value."\">\n";
		
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
			$str .= " checked";
		}
		
		$str .= " value=\"".$value."\">\n";
		
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
		
		//in the template COL1-4, COL1-4BG, COL1-4BASE are defined
		$tpltab = new Template("tpl.adm_tabs.html", false, false);
		for ($i=1; $i<=4; $i++)
		{
			$tpltab->setVariable("COL".$i, "#efefef");
			$tpltab->setVariable("COL".$i."BG", "#c0c0c0");
			$tpltab->setVariable("COL".$i."BASE", "#000000");
		}
		$tpltab->setVariable("COL".$a_hl, "#ffffff");
		$tpltab->setVariable("COL".$a_hl."BG", "#ffffff");
		
		//and the options
		foreach ($a_o as $key => $val)
		{
			$tpltab->setVariable($key, $val);
		}

		//last the language-replacements
		$tpltab->setVariable("TXT_VIEW_CONTENT", $lng->txt("view_content"));
		$tpltab->setVariable("TXT_EDIT_PROPERTIES", $lng->txt("edit_properties"));
		$tpltab->setVariable("TXT_PERM_SETTINGS", $lng->txt("perm_settings"));
		$tpltab->setVariable("TXT_SHOW_OWNER", $lng->txt("show_owner"));
		return $tpltab->get();
	}
	
} // END class.util
?>