<?
/*
+---------------------------------------------------------------+
/ classes/class.group.php                                       /
/                                                               /
/ Gruppen-Klasse von ILIAS3                                     /
/ 	                                    						/
/                                                               /
+---------------------------------------------------------------+
*/

class TGroup 
{
    // Properties
    var $ClassName = "TGroup";

    var $Id;					// (int) GroupId

    var $data = array();		// Contains all Groupdata
	
	var $noMembers = false;
	
	var $Members = array();	    // Contains member data
	
	var $db;					// database-handler

    // Constructor
    function TGroup ($AGroupId = "")
    {
	    global $ilias;
		$this->db = $ilias->db;			// TODO: Optimizing: Testen, obs auch per Referenz klappt (-> save memory)
		
		// User cannot instantiate a TGroup object while not logged in
		if (empty($ilias->account->Id))
		{
			halt("Bitte erst einloggen, um Gruppen zu bearbeiten");
		}
		
		if (!empty($AGroupId) and ($AGroupId > 0))
		{
		    $this->Id = $AGroupId;
			$this->getGroup();
		}
    }
    

    function getGroup ()
    {
		// TODO: move into db-wrapper-class
		$this->db->query("SELECT * FROM group_data WHERE grp_id='$this->Id'");
        
        if ($this->db->next_record())
        {
			$this->data = array(
								"Id"	     => $this->Id,
								"Name"	     => $this->db->f("grp_name"),
            					"Desc"       => $this->db->f("grp_desc"),
                                "Owner"      => $this->db->f("grp_owner"),
                                "CreateDate" => $this->db->f("create_date"),
                                "LastUpdate" => $this->db->f("last_update")
								);
        }
        else
		{
            halt(get_class($this).": There is no dataset with id ".$this->Id);
		}
    }

    function setGroup ($AGroupdata)
    {
	    $this->data = $AGroupdata;
    }

    // saves a new group
    function saveAsNew ()
    {
        global $ilias;  // wegen $account->Id und $account->data[Role]

        //echo var_dump($ilias);
        //exit;


        // TODO:
		// - move into db-wrapper-class
        // - lock tables
		
		// insert group
		$query = "INSERT INTO group_data
                  (grp_name,grp_desc,grp_owner,create_date,last_update)
                  VALUES
                  ('".$this->data[Name]."','".$this->data[Desc]."',
                   '".$ilias->account->data[Id]."',now(),now())";

		$this->db->query($query);
        $this->Id = mysql_insert_id();

        // just to keep data consistent
        $this->data["Id"] = $this->Id;
		
		// create all default roles according to existing system roles and set permissions
        $this->createDefaultRoles();
    }

    // Private method
    // inserts all default roles when a new group is created
    function createDefaultRoles()
    {
		global $ilias;  // wegen $accound->Id und $account->data[Role]
  
        $this->db->query("SELECT * FROM role_system");
        
        // fetch all system roles to an array temporarily
        while ($this->db->next_record())
        {
            $sys_roles[] = array(
                                    "Id"   => $this->db->f("sys_id"),
                                    "Name" => $this->db->f("sys_name"),
                                    "Desc" => $this->db->f("sys_desc"),
                                    "PUSR" => $this->db->f("perms_user"),
                                    "PGRP" => $this->db->f("perms_group"),
                                    "PMOD" => $this->db->f("perms_modul"),
                                    "PFRM" => $this->db->f("perms_forum")
                                    );
        }
        
        // insert the roles for this group
        foreach ($sys_roles as $role)
        {
            $query = "INSERT INTO role_class
                      (rol_name,rol_desc,grp_id,sys_id,perms_group,perms_modul,
                       perms_forum,create_date,last_update)
                      VALUES
                      ('".$role[Name]."','".$role[Desc]."','$this->Id','".$role[Id]."',
                       '".$role[PGRP]."','".$role[PMOD]."','".$role[PFRM]."',
                       now(),now())";
            $this->db->query($query);
            
            $role_id = mysql_insert_id();
            
            // the creator of the group is automatically joined to his group
            if ($role["Id"] == $ilias->account->data["Role"])
            {
                $this->joinGroup($ilias->account->Id,$role_id);
            }
        }
    }


    function update ()
    {
        $this->Id = $this->data["Id"];
		
		// TODO: move into db-wrapper-class
		$query = "UPDATE group_data SET
                  grp_name='".$this->data[Name]."',
                  grp_desc='".$this->data[Desc]."'
				  WHERE grp_id='$this->Id'";
                  
        $this->db->query($query);
    }


    function delete ($AGroupId = "")
    {
        if (empty($AGroupId))
        {
            $id = $this->Id;
        }
        else
        {
            $id = $AGroupId;
        }
        
        // TODO:
		// - move into db-wrapper-class
		// - check if other users have the same role
		// - if not, delete role and role-user relation
		$this->db->query("DELETE FROM group_data WHERE grp_id='$id'");
    }


    // gets all groupmembers plus their roles and perms
    function getMembers ()
    {
        $query = "SELECT * FROM user_data
                  LEFT JOIN idx_usr_rol ON user_data.usr_id=idx_usr_rol.usr_id
                  LEFT JOIN role_class ON role_class.rol_id=idx_usr_rol.rol_id
                  WHERE role_class.grp_id='$this->Id'";

        $this->db->query($query);

        if ($this->db->num_rows())
        {
            while ($this->db->next_record())
            {
                //echo var_dump($db->Record);
                //echo "<hr>";

                $this->Members[] = array(
                                        "User"   => array(
                                                          "Id"        => $this->db->f("usr_id"),
                                                          "Title"     => $this->db->f("usr_title"),
                                                          "FirstName" => $this->db->f("usr_firstname"),
                                                          "SurName"   => $this->db->f("usr_surname"),
                                                          "FullName"  => User::buildFullName($this->db->f("usr_title"),$this->db->f("usr_firstname"),$this->db->f("usr_surname"))
                                                          ),
                                        "Role"   => array(
                                                          "Id"         => $this->db->f("rol_id"),
                                                          "Name"       => $this->db->f("rol_name")
                                                          ),
                                        "Perms"  => array(
                                                          "Group" => $this->db->f("perms_group"),
                                                          "Modul" => $this->db->f("perms_modul"),
                                                          "Forum" => $this->db->f("perms_forum")
                                                          )
                                        );
            }
        }
        else
        {
            $this->noMembers = true;
        }
    }
    
	// shortcut to check if user is member of recent group
	// returns true (is member) or false (is not) 
	function isMemberOfGroup ($AUserId)
	{
		foreach ($this->Members as $member)
		{
			if ($member["User"]["Id"] == $AUserId)
			{
				return true;
			}
		}
		
		return false;
	}

    // puts a user into group
    // if RoleId is not set, welookup for the correct role id according to
    // the system role of the user
    function joinGroup ($AUserId,$ARoleId="")
    {
        global $ilias;

        // get RoleId if not set
        if (empty($ARoleId))
        {
            $query = "SELECT rol_id FROM role_class
                      WHERE grp_id='$this->Id'
                      AND sys_id='".$ilias->account->data[Role]."'";
            $this->db->query($query);
            $this->db->next_record();
            
            $ARoleId = $this->db->f("rol_id");
        }

        // join group
        $query = "INSERT INTO idx_usr_rol (usr_id,rol_id) VALUES ($AUserId,$ARoleId)";
		$this->db->query($query);
    }
    
    function leaveGroup ($AUserId,$ARoleId)
    {
		$this->db->query("DELETE FROM idx_usr_rol WHERE usr_id=".$AUserId." AND rol_id=".$ARoleId);
    }

	function isOwner ($AUserId)
	{
		return false;
	}

	function isLastMember ($AUserid)
	{
		return true;
	}

	function haveRessources ()
	{
		return true;
	}

	function doesUserHasStandardRole ($AUserId)
	{
		return true;
	}


    // returns the RoleId of a groupmember
    function getRoleId ($AUserId)
    {
		foreach ($this->Members as $member)
		{
			if ($member["User"]["Id"] == $AUserId)
			{
				return $member["Role"]["Id"];
			}
		}
    }

    function getResources ()
    {
        // leer
    }
    
    function removeResource ()
    {
        // leer
    }

} // END class.group

?>
