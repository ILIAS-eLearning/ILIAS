<?php

// Access Control Functions

    /**
    * Checks access
    *
    * @param  int     $Aperm  The permission to be checked (1,2,4,8,16,32,64 or 128)
    *
    * @return boolean         True if access is granted
    */
    function grantAccess ($AObjId,$APerm)
    {
		return true;
	}

    function getRole ($ARoleId)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "SELECT * FROM object_data
                  WHERE type = 'role'
                  AND obj_id = '".$ARoleId."'";
        $db->query($query);

        if ($db->num_rows())
        {
            $db->next_record();

            $name = $db->f("title");
            
            return $name;
        }
        
        return false;
    }



    /**
    * get all Roles
    *
    * @param  none
    *
    * @return array/boolean  returns array of Roles or false if no Roles found
    */
    function getRoleList ()
    {
        global $ilias;
        $db = $ilias->db;
       
        $query = "SELECT * FROM object_data
                  WHERE type = 'role'
                  ORDER BY title ASC";
        $res = $db->query($query);
       
        if ($res->numRows())
        {
            while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
            {
                $arr[] = array(
                                    "obj_id"     	=> $row->obj_id,
                                    "title"  		=> $row->title,
                                    "description"   	=> $row->description
                                    );
            }
           
            return $arr;
        }

        return false;
    }
    /**
    * get all groups
    *
    * @param  none
    *
    * @return array/boolean  returns array of groups or false if no groups found
    */
    function getGroupList ()
    {
        global $ilias;
        $db = $ilias->db;

        if (!$typ_id = getModuleId("grp"))
        {
            return false;
        }

        $query = "SELECT * FROM obj_data
                  WHERE typ_id = $typ_id
                  ORDER BY title ASC";
        $db->query($query);

        if ($db->num_rows())
        {
            while ($db->next_record())
            {
                 $arr[] = array(
                                "id"           => $db->f("obj_id"),
                                "typ_id"       => $db->f("typ_id"),
                                "title"        => $db->f("title"),
                                "desc"         => $db->f("description"),
                                "owner"        => $db->f("owner"),
                                "create_date"  => $db->f("create_date"),
                                "last_update"  => $db->f("last_update")
                                );
            }

            return $arr;
        }

        return false;
    }

    /**
    * get user list
    *
    * @param  none
    *
    * @return array/boolean  returns array of ssers or false if no users found
    */
    function getUserList ($a_order = '',$a_direction = '')
    {
        global $ilias;
        $db = $ilias->db;

		if(!$a_order)
			$a_order = 'title';

        $query = "SELECT * FROM object_data
                  WHERE type = 'user'
                  ORDER BY ".$a_order." ".$a_direction;
        $res = $db->query($query);

        if (DB::isError($res))
        {
			die("<b>".$res->getMessage()."</b><br>Script: ".__FILE__."<br>Line: ".__LINE__);
		}			
		while($data = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = array(
				"obj_id"       => $data->obj_id,
				"title"        => $data->title,
				"desc"         => $data->description,
				"usr_id"       => $data->owner,
				"create_date"  => $data->create_date,
				"last_update"  => $data->last_update
				);
		}

        return $arr;
    }

    /**
    * get user list
    *
    * @param  none
    *
    * @return array/boolean  returns array of types or false if no types found
    */
    function getTypeList ($a_order = '',$a_direction = '')
    {
        global $ilias;
        $db = $ilias->db;

		if(!$a_order)
			$a_order = 'title';

        $query = "SELECT * FROM object_data ".
			"WHERE type = 'type' ".
			"ORDER BY ".$a_order." ".$a_direction;
        $res = $db->query($query);

		while($data = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = array(
				"obj_id"       => $data->obj_id,
				"type"         => $data->type,
				"title"        => $data->title,
				"desc"         => $data->description,
				"usr_id"       => $data->owner,
				"create_date"  => $data->create_date,
				"last_update"  => $data->last_update
				);
		}

        return $arr;
    }	
	
	
    /**
    * get object list
    *
    * @param  string  $AObjType
    *
    * @return array/boolean  returns array of objects or false if no objects found
    */
    function getObjectList ($AObjType = "",$AOffset = "",$ALimit = "")
    {
        global $ilias;
        $db = $ilias->db;

        if(!empty($ALimit))
        {
            $limit_clause = " LIMIT $AOffset,$ALimit";
        }


        if (empty($AObjType))
        {
            $query = "SELECT * FROM object_data
                      ORDER BY obj_id ASC".$limit_clause;
        }
        else
        {
            $query = "SELECT * FROM object_data
                      WHERE type = '$AObjType'
                      ORDER BY obj_id ASC".$limit_clause;
        }
        
		$res = $db->query($query);

		if (DB::isError($res)) {
			die("<b>".$res->getMessage()."</b><br>Script: ".__FILE__."<br>Line: ".__LINE__);
		}
        
		if ($res->numRows() > 0)
		{
            while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
            {
                 $arr[] = array (
                                "obj_id"       => $data["obj_id"],
                                "type"         => $data["type"],
                                "title"        => $data["title"],
                                "desc"         => $data["description"],
                                "usr_id"       => $data["owner"],
                                "create_date"  => $data["create_date"],
                                "last_update"  => $data["last_update"],
								"last_login"   => $data["last_login"]
				 				);

            }

            return $arr;
        }

        return false;
    }


    /**
    * creates a new object
    *
    * @param  string  $AObjType
    * @param  array   $AObjData
    *
    * @return int     returns object id
    */
    function createNewObject ($AObjType,$AObjData)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "INSERT INTO object_data
                  (type,title,description,owner,create_date,last_update)
                  VALUES
                  ('$AObjType','".$AObjData[title]."','".$AObjData[desc]."',
                  ".$ilias->account->Id.",now(),now())";
        $res = $db->query($query);
        
	$query = "SELECT LAST_INSERT_ID()";
        $res = $db->query($query);
		
	if (DB::isError($res)) {
	    die("<b>".$res->getMessage()."</b><br>Script: ".__FILE__."<br>Line: ".__LINE__);
	}		
		
	$data = $res->fetchRow();
				
        return $data[0];
    }

    /**
    * creates a new object
    *
    * @param  string  $AObjType
    * @param  array   $AObjData
    *
    * @return int     returns object id
    */
    function createNewOperation ($AOpsData)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "INSERT INTO operations ".
				 "(operation,description) ".
				 "VALUES ".
				 "('".$AOpsData[title]."','".$AOpsData[desc]."')";
        $db->query($query);
        
        return mysql_insert_id();
    }	
	
	
    /**
    * deletes an object
    *
    * @param  int     $AObjId
    *
    * @return boolean  returns true if successful otherwise false
    */
    function deleteObject ($AObjId)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "DELETE FROM object_data
                  WHERE obj_id = '".$AObjId."'";
        $db->query($query);
	$query = "DELETE FROM rbac_pa WHERE obj_id = '".$AObjId."'";
	$db->query($query);
        return true;
    }

    /**
    * updates an object
    *
    * @param  int     $AObjId
    * @param  string  $AObjType
    * @param  array   $AObjData
    *
    * @return boolean  returns true if successful otherwise false
    */
    function updateObject ($AObjId,$AObjType,$AObjData)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "UPDATE object_data
                  SET
                  title = '".$AObjData[title]."',
                  description = '".$AObjData[desc]."'
                  WHERE obj_id = $AObjId";
        $res = $db->query($query);

        return true;
    }

    /**
    * get an object
    *
    * @param  int     $AObjId
    *
    * @return array   returns the object
    */
    function getObject ($AObjId)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "SELECT * FROM object_data
                  WHERE obj_id = '".$AObjId."'";
        $res = $db->query($query);
        
        $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $obj    = array(
                        "obj_id"       => $data["obj_id"],
                        "type"         => $data["type"],
                        "title"        => $data["title"],
                        "desc"         => $data["description"],
                        "owner"        => $data["owner"],
                        "create_date"  => $data["create_date"],
                        "last_update"  => $data["last_update"]
                        );
        return $obj;
    }

    /**
    * get object list by sets there into
    *
    * @param  string  $AObjType
    *
    * @return array/boolean  returns array of objects or false if no objects found
    */
    function getObjectListBySet ($ASetId,$ARoleId)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "SELECT * FROM object_data
                  WHERE object_data.type IN (".$ilias->typedefinition[obj].")
                  ORDER BY object_data.obj_id ASC";
        $res = $db->query($query);

        if ($res->numRows())
        {
           while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
            {
                 $arr[] = array(
                                "obj_id"       => $data["obj_id"],
                                "type"         => $data["type"],
                                "title"        => $data["title"],
                                "desc"         => $data["description"],
                                "usr_id"       => $data["owner"],
                                "create_date"  => $data["create_date"],
                                "last_update"  => $data["last_update"]
                                );
            }

            return $arr;
        }

        return false;
    }

    /**
    * get operation list
    *
    * @return array/boolean  returns array of operations or false if no ops found
    */
    function getOperationList ($Aobj_type = "",$a_order= '',$a_direction = '')
    {
        global $ilias;
        $db = $ilias->db;

		if(!$a_order)
			$a_order = 'operation';

		if($Aobj_type)
		{
			$query = "SELECT * FROM rbac_operations ".
				"LEFT JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id ".
				"LEFT JOIN object_data ON rbac_ta.typ_id = object_data.obj_id ".
				"WHERE object_data.title='".$Aobj_type."' AND object_data.type='type' ".
				"ORDER BY rbac_operations.".$a_order." ".$a_direction; 
		}
		else
		{
			$query = "SELECT * FROM rbac_operations ".
				"ORDER BY ".$a_order." ".$a_direction;
		}
        
		$res = $db->query($query);

		if (DB::isError($res)) {
			die("<b>".$res->getMessage()."</b><br>Script: ".__FILE__."<br>Line: ".__LINE__);
		}
		
		while ($row = $res->fetchRow())
		{
			$arr[] = array(
				"ops_id"       => $row[0],
				"operation"    => $row[1],
				"desc"         => $row[2]
				);
		}
		
		return $arr;
    }	
	
	
    /**
    * assign role to object, parent_object and set permissions
    *
    * @param  int     $ARoleId
    * @param  int     $AObjId
    * @param  int     $ASetId
    * @param  string  $AObjType
    * @param  int     $APerms
    *
    * @return void
    */
    function assignObjectPerm ($ARoleId,$AObjId,$ASetId,$AObjType,$APerms)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "INSERT INTO role_object
                  (rol_id,obj_id,set_id,type,perms)
                  VALUES
                  ($ARoleId,$AObjId,$ASetId,'$AObjType',$APerms)";
        $db->query($query);
    }

    function assignClassPerm ($ARolId,$AObjType,$ASetId,$APerm)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "INSERT INTO role_class
                  (rol_id,type,set_id,perms)
                  VALUES
                  ($ARolId,'$AObjType',$ASetId,$APerm)";
        $db->query($query);
    }

    /**
    * check if user is logged in
    *
    * @param  void
    *
    * @return boolean  true if logged in
    */
    function isUserLoggedIn ()
    {
        global $ilias;

        if (empty($ilias->account->Id))
        {
            return false;
        }
        
        return true;
    }
    
    /**
    * check whether object type is a container or not
    *
    * @param  string    object type handle (frm,le,grp,...)
    *
    * @return boolean  true if is container
    */
    function isContainer ($AObjType)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "SELECT * FROM obj_types
                  WHERE type = '$AObjType'";
        $db->query($query);
        
        if ($db->num_rows() == 1)
        {
            $db->next_record();
            
            if ($db->f("class") == "y")
            {
                return true;
            }
            
            return false;
        }
        
        // an error occurred
        halt("Object-Type '".$AObjType."' doesn't exists!");
    }

    /**
    * fetch perm by object type (only set_id = -1)
    *
    * @param  int    set id
    *
    * @return array  role ids
    */
    function getClassPermByType ($ARoleId,$AObjType)
    {
        global $ilias;
        $db = $ilias->db;

        $query = "SELECT perms FROM role_class
                  WHERE set_id = -1
                  AND type = '$AObjType'
                  AND rol_id = $ARoleId";
        $db->query($query);

        if ($db->num_rows() == 1)
        {
            $db->next_record();

            return $db->f("perms");
        }
        
        return false;
    }

    // assigns class and object perms
    // function MUST be splitted up!!!
    function assignPermsToObject($AObjId,$ASetId,$AObjType)
    {
        global $ilias;
        $db = $ilias->db;

        // get class Perms from parent object depending of type to be created
        if (empty($ilias->typedefinition[$AObjType]))
        {
            // no container object
            $query = "SELECT * FROM role_class
                      WHERE set_id = $ASetId
                      AND type = '$AObjType'";
        }
        else
        {
            // is container object
            $query = "SELECT * FROM role_class
                      WHERE set_id = $ASetId
                      AND type IN (".$ilias->typedefinition[$AObjType].")";
        }
        $db->query($query);

        if ($db->num_rows())
        {
            while ($db->next_record())
            {
                $classperms[] = array(
                                       "rol_id" => $db->f("rol_id"),
                                       "type"   => $db->f("type"),
                                       "perms"  => $db->f("perms")
                                       );
            }
        }

        // now insert perms for the new object
        $check = array();   // for checking double role ids (see below)

        foreach($classperms as $perm)
        {
            if (!empty($ilias->typedefinition[$AObjType]))
            {
                // assign class permissions
                assignClassPerm($perm["rol_id"],$perm["type"],$AObjId,$perm["perms"]);
            }

            // assign object permissions
            if (!in_array($perm["rol_id"],$check))
            {
                assignObjectPerm($perm["rol_id"],$AObjId,$ASetId,$AObjType,$perm["perms"]);
                $check[] = $perm["rol_id"];
            }
        }
    }
?>