<?php
/*
* Gruppenmitglieder-Klasse von ILIAS3                           /
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
* @package ilias-core
*/

class TGroupmember
{
    // Properties
    var $ClassName = "TGroupmember";

    var $UserId;
    var $Groups = array();
    var $Roles = array();
    var $noGroup = false;

    // Constructor
    function TGroupmember ($AUserId)
    {
        $this->UserId = $AUserId;
    }
    
    // Get Groups and their roles of $this->UserId
    function getMemberships ()
    {
        global $ilias;

        $db = $ilias->db;

        // Der COUNT muss auf basis von obj_data erfolgen, wegen Mehrfachzuordungen
		
		$query = "SELECT group_data.grp_id,grp_name,role_class.rol_id,rol_name,
                  perms_group,perms_modul,perms_forum,COUNT(idx_obj_grp.obj_id) as obj_num
                  FROM group_data
                  LEFT JOIN idx_obj_grp ON group_data.grp_id=idx_obj_grp.grp_id
                  LEFT JOIN role_class ON group_data.grp_id=role_class.grp_id
                  LEFT JOIN idx_usr_rol ON role_class.rol_id=idx_usr_rol.rol_id
                  WHERE idx_usr_rol.usr_id='$this->UserId'
                  GROUP BY group_data.grp_id";

        $db->query($query);

        if ($db->num_rows())
        {
            while ($db->next_record())
            {
                //echo var_dump($db->Record);
                //echo "<hr>";

                $this->Groups[] = array(
                                        "Group"  => array(
                                                          "Id"   => $db->f("grp_id"),
                                                          "Name" => $db->f("grp_name")
                                                          ),
                                        "Role"   => array(
                                                          "Id"   => $db->f("rol_id"),
                                                          "Name" => $db->f("rol_name")
                                                          ),
                                        "Right"   => array(
                                                          "Group" => $db->f("perms_group"),
                                                          "Modul" => $db->f("perms_modul"),
                                                          "Forum" => $db->f("perms_forum")
                                                          ),
                                        "Object"  => array(
                                                          "Count" => $db->f("obj_num")
                                                          )
                                        );
            }
        }
        else
        {
            $this->noGroup = true;
        }
    }

    function setAccessString ($ARights)
    {
        $granted = "<font face=\"courier\" color=\"green\">o</font>";
        $denied  = "<font face=\"courier\" color=\"red\">x</font>";
        
        if ($ARights & 1) $str = $granted; else $str = $denied;
        if ($ARights & 2) $str .= $granted; else $str .= $denied;
        if ($ARights & 4) $str .= $granted; else $str .= $denied;
        if ($ARights & 8) $str .= $granted; else $str .= $denied;
        if ($ARights & 16) $str .= $granted; else $str .= $denied;
        if ($ARights & 32) $str .= $granted; else $str .= $denied;
        if ($ARights & 64) $str .= $granted; else $str .= $denied;
        if ($ARights & 128) $str .= $granted; else $str .= $denied;

        //$str = $ARights;

        return $str;
    }

	function joinGroup ()
    {
        // leer
    }
    
    function leaveGroup ()
    {
        // leer
    }
    
    function inviteUser ($AUserId)
    {
        // leer
    }
    
    function kickUser ($AUserId)
    {
        // leer
    }
    
    function changeRole ($ARoleId)
    {
        // leer
    }
} // END class.groupmember

?>