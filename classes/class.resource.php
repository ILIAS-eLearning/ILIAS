<?php

/**
* Objekt-Klasse von ILIAS3
* @version $Id$
* @package ilias-core
*/
class TResource
{
    // Properties
    var $ClassName = "TResource";

    var $Id;					// (int) ResId

    var $data = array();		// Contains all Resource data
	
	var $db;					// database-handler
 
    var $GroupId;               // The group id
    

    /**
    * Constructor
    */
    function TResource ($AResId = "")
    {
	    global $ilias;
		$this->db = $ilias->db;			// TODO: Optimizing: Testen, obs auch per Referenz klappt (-> save memory)
		
		if (empty($ilias->account->Id))
		{
			halt("Bitte erst einloggen, um Objekte zu bearbeiten");
		}

		if (!empty($AResId) and ($AResId > 0))
		{
		    $this->Id = $AResId;
			$this->getResource();
		}
    }
	

    function getResource ()
    {
    
		// TODO: move into db-wrapper-class
		
		
		// Fetch the object data and group informations
		// What if the object is referenced with more than one group??????
        $query = ("SELECT obj_data.obj_id,obj_name,obj_desc,obj_type,group_data.grp_id,grp_name
                  FROM obj_data
                  LEFT JOIN idx_obj_grp ON obj_data.obj_id=idx_obj_grp.obj_id
		          LEFT JOIN group_data ON idx_obj_grp.grp_id=group_data.grp_id
				  WHERE obj_data.obj_id = '$this->Id'");


		
		$this->db->query($query);
        
        if ($this->db->next_record())
        {
			$this->data = array(
								"Id"	 => $this->Id,
								"Name"	 => $this->db->f("obj_name"),
								"Desc"   => $this->db->f("obj_desc"),
            					"Type"   => $this->db->f("obj_type"),
            					"Group"  => array(
												  "Id"	 => $this->db->f("grp_id"),
												  "Name" => $this->db->f("grp_name")
												  )
    							);
        }
        else
		{
            halt(get_class($this).": There is no dataset with id ".$this->Id);
		}
    }


    function setResource ($AResdata)
    {
	    $this->data = $AResdata;
    }


    function saveAsNew ()
    {
        global $ilias;

        //echo var_dump($this->data);
        //exit;

        // TODO: move into db-wrapper-class
		
		// Insert object data
        $query = "INSERT INTO obj_data
                  (obj_name,obj_desc,obj_type,obj_owner,create_date,last_update)
                  VALUES
                  ('".$this->data[Name]."','".$this->data[Desc]."',
                   '".$this->data[Type]."','".$ilias->account->Id."',now(),now())";

		$this->db->query($query);
        $this->Id = mysql_insert_id();
		
        // just to keep data consistent
		$this->data["Id"] = $this->Id;
  
        // create link to group
        $query = "INSERT INTO idx_obj_grp (obj_id,grp_id)
                  VALUES
                  ('$this->Id','".$this->data[GroupId]."')";
        $this->db->query($query);
		
		// sets permissions per role
       
		// First we have to fetch all role ids of that group
		$query = "SELECT rol_id,perms_group,perms_modul,perms_forum FROM role_class WHERE grp_id='".$this->data[GroupId]."'";
        $this->db->query($query);
		
		// Write role ids in a temporary array
		while ($this->db->next_record())
		{
			// Depending on the object type we set the permissions
			switch ($this->data["Type"])
			{
				case "grp":
					$perms = $this->db->f("perms_group");
				break;

				case "mod":
					$perms = $this->db->f("perms_modul");
				break; 

				case "frm":
					$perms = $this->db->f("perms_forum");
				break;
			}

			$role[] = array (
							"Id" 	=> $this->db->f("rol_id"),
							"Perms" => $perms
							);
		} 				
		
		// now insert all objectroles
		foreach ($role as $val)
		{
			$query = "INSERT INTO role_object (rol_id,obj_id,grp_id,perms)
            VALUES
            ('".$val[Id]."','$this->Id','".$this->data[GroupId]."','".$val[Perms]."')";
        	$this->db->query($query);
		}
		
		// increment the object counter in group_data
		$query = "UPDATE group_data SET
				  grp_object_num=grp_object_num+1
				  WHERE grp_id='".$this->data[GroupId]."'";
		$this->db->query($query);
		
    }
    

    function update ()
    {
        $this->Id = $this->data["Id"];
		
		// TODO: move into db-wrapper-class
		
		// set table obj_data
		$query = "UPDATE obj_data SET
				  obj_name='".$this->data[Name]."',
                  obj_desc='".$this->data[Desc]."',
				  obj_type='".$this->data[Type]."'
				  WHERE obj_id='$this->Id'";
		$this->db->query($query);

		// set group only if changed
		
		// Hier muss auch die Gruppe aktualisiert werden (grp_object_num)
		if ($this->data["GroupId"] != $this->data["GroupIdOld"])
		{
			$query = "UPDATE idx_obj_grp SET
					  grp_id='".$this->data[GroupId]."'
					  WHERE obj_id='$this->Id' AND grp_id='".$this->data[GroupIdOld]."'";
			$this->db->query($query);
		
			$query = "UPDATE role_object SET
					  grp_id='".$this->data[GroupId]."'
					  WHERE obj_id='$this->Id' AND grp_id='".$this->data[GroupIdOld]."'";
			$this->db->query($query);
		}		
    }
    

    function delete ($AResId = "")
    {
       
        if (empty($AResId))
        {
            $id = $this->Id;
        }
        else
        {
            $id = $AResId;
        }
        
        // TODO: move into db-wrapper-class
		$this->db->query("DELETE FROM resources WHERE oid='$id'");
    }
	
} // END class.resource

?>