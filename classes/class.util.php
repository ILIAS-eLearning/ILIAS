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
    var $ClassName = "TUtil";    // Eigentlich unn÷tig

    
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

/*
	// get Roles of a group
    function getRoles ($AGroupId)
    {
        global $ilias;
        
        $db = $ilias->db;
        
        $db->query("SELECT rid,role_name FROM perm_groups WHERE gid=$AGroupId ORDER BY rid");
        
        if ($db->num_rows())
        {
            while ($db->next_record())
            {
                $arr[$db->f("rid")] = $db->f("role_name");
            }
        }
        else
        {
            return false;
        }
        
        return $arr;
    }
    */

    /**
	 * builds an array for access output
	 * @param string
	 * @param string
	 */
    function setAccessString ($ARights,$AVar)
    {
        $granted = "<font face=\"courier\" color=\"green\">o</font>";
        $denied  = "<font face=\"courier\" color=\"red\">x</font>";

        if ($ARights & 1) $arr[$AVar."1"] = $granted; else $arr[$AVar."1"] = $denied;
        if ($ARights & 2) $arr[$AVar."2"] .= $granted; else $arr[$AVar."2"] .= $denied;
        if ($ARights & 4) $arr[$AVar."3"] .= $granted; else $arr[$AVar."3"] .= $denied;
        if ($ARights & 8) $arr[$AVar."4"] .= $granted; else $arr[$AVar."4"] .= $denied;
        if ($ARights & 16) $arr[$AVar."5"] .= $granted; else $arr[$AVar."5"] .= $denied;
        if ($ARights & 32) $arr[$AVar."6"] .= $granted; else $arr[$AVar."6"] .= $denied;
        if ($ARights & 64) $arr[$AVar."7"] .= $granted; else $arr[$AVar."7"] .= $denied;
        if ($ARights & 128) $arr[$AVar."8"] .= $granted; else $arr[$AVar."8"] .= $denied;

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
    function setPathStr ($Apath)
	{
        if ("" != $Apath && "/" != substr($Apath, -1))
            $Apath .= "/";
			
		return $Apath;
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

			$owner = new User($db,$owner_id);
			return $owner;
        }
        else
        {
			// select liefert falsch row-anzahl oder nix
			return false;
        }	
	}
	
} // END class.util

?>