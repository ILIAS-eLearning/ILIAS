<?

/* permission system:
  Each right is represented by a certain bit that can be set to 0 or 1.
  
    1 - show:      Resource is visible to user but access to content is denied (or an abstract is given only)
    2 - read:      Read content of resource
    4 - write:     Edit resource (change Metadata/move resource)
    8 - delete:    Delete resource an all its content (including other resources)
   16 - new:       Create new resource in current position (container)
   32 - create:    Create new container
   64 - deleteCon: Delete container and all its content (including other container/resources)
  128 - rights:    Edit permissions of resources/containers

*/


/**
* Rechte-Klasse von ILIAS3                                      /
* Berechnet die Rechte und 喘erpr’t die Zugriffsrechte         /
* @version $Id$
* @package ilias-core
*/
class TRole
{
    var $ClassName = "TRole";

    var $Id;
    var $Right;             // Permissions in binary representation
	var $data = array();	// Role data
    var $Perms = array();	// Permissions in boolean representation
    var $groupList;         // id of groupList refered to user in db
    var $inList = array();  // contains grouplistIDs and groupIDs
	var $assignedUsers = array(); // contains uiser_ids of users who assigned to this role

    // temporв: var mit systemrole werten
    var $Pgrp;
    var $Pmod;
    var $Pfrm;

	var $db;				// db-handler


    // Constructor
    function TRole ($ARightId = "")
    {
		global $ilias;
		
		$this->db = $ilias->db;
		
		if (!empty($ARightId) and ($ARightId > 0))
		{
		    $this->Id = $ARightId;
		}
    }
    
    // get all Role-data from DB
    function getRole ()
    {
        if (empty($this->Id))
		{
			halt("rol_id nicht gesetzt");
		}
		
		$this->db->query("SELECT * FROM role_class WHERE rol_id='$this->Id'");

        $this->db->next_record();

        $this->Right = $this->db->f("perms_group");
		$this->data["GroupId"] = $this->db->f("grp_id");
		$this->data["Name"] = $this->db->f("rol_name");
		$this->data["Desc"] = $this->db->f("rol_desc");

        $this->processRight();
    }

	// sets Role-data without perms from Form-entry
    function setRole ($ARoledata)
    {
	    //$this->Id = $ARoledata["Id"];
		$this->data = $ARoledata;
    }

    // init permissions
    function initPerms ()
    {
		$this->Perms = array(
							 "Show"		 => 1,
							 "Read"		 => 1,
							 "Write"	 => 1,
							 "Delete"	 => 1,
							 "New"		 => 1,
							 "Create"	 => 1,
							 "DeleteCon" => 1,
							 "Rights"	 => 1
							 );
	}

	// sets permissions without role-data
    function setPerms ($APerms)
    {
        if ($APerms["Show"] == "1") $this->Perms["Show"] = 1; else $this->Perms["Show"] = 0;
        if ($APerms["Read"] == "1") $this->Perms["Read"] = 1; else $this->Perms["Read"] = 0;
        if ($APerms["Write"] == "1") $this->Perms["Write"] = 1; else $this->Perms["Write"] = 0;
        if ($APerms["Delete"] == "1") $this->Perms["Delete"] = 1; else $this->Perms["Delete"] = 0;
        if ($APerms["New"] == "1") $this->Perms["New"] = 1; else $this->Perms["New"] = 0;
        if ($APerms["Create"] == "1") $this->Perms["Create"] = 1; else $this->Perms["Create"] = 0;
        if ($APerms["DeleteCon"] == "1") $this->Perms["DeleteCon"] = 1; else $this->Perms["DeleteCon"] = 0;
        if ($APerms["Rights"] == "1") $this->Perms["Rights"] = 1; else $this->Perms["Rights"] = 0;
    }

    // private
	// Calculates Perms-array from given Right
    function processRight ()
    {
        if ($this->Right & 1) $this->Perms["Show"] = 1; else $this->Perms["Show"] = 0;
        if ($this->Right & 2) $this->Perms["Read"] = 1; else $this->Perms["Read"] = 0;
        if ($this->Right & 4) $this->Perms["Write"] = 1; else $this->Perms["Write"] = 0;
        if ($this->Right & 8) $this->Perms["Delete"] = 1; else $this->Perms["Delete"] = 0;
        if ($this->Right & 16) $this->Perms["New"] = 1; else $this->Perms["New"] = 0;
        if ($this->Right & 32) $this->Perms["Create"] = 1; else $this->Perms["Create"] = 0;
        if ($this->Right & 64) $this->Perms["DeleteCon"] = 1; else $this->Perms["DeleteCon"] = 0;
        if ($this->Right & 128) $this->Perms["Rights"] = 1; else $this->Perms["Rights"] = 0;
    }



// ************************************************************ //
// WICHTIG:
// Die beiden Methoden oben m《sen f〉 flexible R…kgabe angepasst werden.
// Man ｜ergibt einen Wert und zut…k kommt der entsprechende Array
// Oder: Man ｜ergibt einen Array und bekommt den Einzelwert zur…k.
// *********************************************************** //




        
    // Returns a combined binary representation of the current users permissions
    // for the record of all resources where the user have access to.
    // (see method: doesUserHaveAccess)
    /*
    function getPerms($resource_array)
    {
        if ($this->isAdmin())
        {
            return 255;
        }
        
        // Hier hin der Kram
        return $perms;
    }
    */
    
	// sets Right from permissions
    function setRight ()
    {
        $this->Right = 0;

        if ($this->Perms["Show"] == "1") $this->Right = $this->Right + 1;
        if ($this->Perms["Read"] == "1") $this->Right = $this->Right + 2;
        if ($this->Perms["Write"] == "1") $this->Right = $this->Right + 4;
        if ($this->Perms["Delete"] == "1") $this->Right = $this->Right + 8;
        if ($this->Perms["New"] == "1") $this->Right = $this->Right + 16;
        if ($this->Perms["Create"] == "1") $this->Right = $this->Right + 32;
        if ($this->Perms["DeleteCon"] == "1") $this->Right = $this->Right + 64;
        if ($this->Perms["Rights"] == "1") $this->Right = $this->Right + 128;
    }


    // saves only permissions to DB
	function savePerms ()
    {
		$query = "UPDATE role_class SET
					perms_group='$this->Right'
					WHERE rol_id='".$this->Id."'";
		
		$this->db->query($query);
    }


    // saves all role-data to DB
	function update ()
    {
		// TODO: move into db-wrapper-class
        $query = "UPDATE role_class SET
				  rol_name='".$this->data[Name]."',
				  rol_desc='".$this->data[Desc]."',
				  last_update=now()
				  WHERE rol_id=$this->Id";
               
        $this->db->query($query);
    }
	
    // Fills this role object with the data from a given system role
    // not longer used (maybe obsolete)
    function getSystemRole($ARoleId)
    {
        $this->db->query("SELECT * FROM role_system WHERE sys_id='$ARoleId'");

        $this->db->next_record();

        $this->data[Name] = $this->db->f("sys_name");
        $this->data[Desc] = $this->db->f("sys_desc");
        $this->Pgrp = $this->db->f("perms_group");
        $this->Pmod = $this->db->f("perms_modul");
        $this->Pfrm = $this->db->f("perms_forum");
    }



    // saves all role-data to DB as new entry
	function saveAsNew ()
    {
		$query = "INSERT INTO role_class
                  (grp_id,perms_group,rol_name,rol_desc,create_date,last_update)
				  VALUES
				  (".$this->data[GroupId].",'$this->Right','".$this->data[Name]."','".$this->data[Desc]."',now(),now())";
	    $this->db->query($query);
        $this->Id = mysql_insert_id();		
    }


	// delete a role, but check first if a user is assigned to this role
	function delete ()
	{
		
		// Diese private Methode muss wieder raus. Der check erfolgt auf Basis der Gruppen und ob die Rolle als Standardrolle definiert wurde
		if ($this->getUsers())
		{
			return false;
		}

		$this->db->query("DELETE FROM role_class WHERE rol_id=$this->Id");
		$this->db->query("DELETE FROM idx_usr_rol WHERE rol_id=$this->Id");
		return true;
	}

	// fetch all users assigned to role
	function getUsers ()
	{
		$query = "SELECT usr_id FROM idx_usr_rol WHERE rol_id=$this->Id";
		$this->db->query($query);
		
		if ($this->db->num_rows() > 0)
		{
			while ($this->db->next_record())
			{
				$this->assignedUsers[] = $this->db->f("usr_id");
			}

			return true;
		}

		return false;
	}

	
	// shortcut to determine if user has adminrights
    function isAdmin ($userGroup)
    {
        if ($userGroup == 1)
        {
            return true;
        }
        
        return false;
    }

// *********************** function moved to class.group *************************//
    // Returns true if the current user is a member of group $groupId
    // $groupId must be set. $this->groupList must contain groups
    function isMemberOfGroup ($groupId)
    {
		$groupId = intval($groupId);  // must be integer
		
		if ($this->groupList && $groupId)
        {
			return $this->inList($this->groupList, $groupId);
		}
	}

    // Checks if the permissions is granted based on a page-record ($row) and $perms (binary and'ed)
    // $row is the pagerow for which the permissions is checked
    // $perms is the binary representation of the permission we are going to check. Every bit in this number represents a permission that must be set
	function doesUserHaveAccess ($resource_array,$perms)
    {
		$userPerms = $this->getPerms($resource_array);
		return ($userPerms & $perms) == $perms;
	}

    // Check which module user has access to.
    function modAccess ($special_rights)
    {
        return ($modRights);
	}

    // Check which type of resources user has access to.
    function resAccess ($special_rights)
    {
        return ($resRights);
	}
} // END class.rights

?>