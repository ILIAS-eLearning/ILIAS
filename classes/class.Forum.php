<?php
/**
* Class Forum
* core functions for forum
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
class Forum
{
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	
	/**
    * database table name
    * @var string
    * @see setDbTable(), getDbTable()
    * @access private
    */
    var $dbTable;
	
	/**
    * class name
    * @var object
    * @access private
    */
	var $className="Forum";
	
	/**
    * database table field for sorting the results
    * @var string
    * @see setOrderField()
    * @access private
    */
    var $orderField;
	
	var $whereCondition = "1";

	/**
	* Constructor
	* @access	public
	*/
	function Forum()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}
	
	
	/**
    * set database field for sorting results
    * @param string $orderField database field for sorting
    * @see $orderField
    * @access private
    */
    function setOrderField($orderField) {
        if ($orderField == "") {
            die($this->className . "::setOrderField(): No orderField given.");			
        } else {
            $this->orderField = $orderField;
        }
    }
	
	/**
    * get name of orderField
    * @return string name of orderField
    * @see $orderField
    * @access public
    */
	function getOrderField()
	 {
		 return $this->orderField;
	 }
	
	/**
    * set database table
    * @param string $dbTable database table
    * @see $dbTable
    * @access public
    */
    function setDbTable($dbTable) {
        if ($dbTable == "") {
            die($this->className . "::setDbTable(): No database table given.");
        } else {
            $this->dbTable = $dbTable;
        }
    }

    /**
    * get name of database table
    * @return string name of database table
    * @see $dbTable
    * @access public
    */
    function getDbTable() {
        return $this->dbTable;
    }
	
	function setWhereCondition($whereCondition = "1") {
        $this->whereCondition = $whereCondition;
        return true;
    }

    function getWhereCondition() {
        return $this->whereCondition;
    }
	
	
	
	// *******************************************************************************
	
		
	
	/**
	* get topic-data by WhereCondition
	* @param	string	$AObjType
	* @return object $result result identifier for use with getNext()
	* @access public
	*/
	function getOneTopic()
	{	
		
		$query = "SELECT * FROM frm_data WHERE ( ".$this->whereCondition." )";
		
		$result = $this->ilias->db->getRow($query, DB_FETCHMODE_ASSOC);
     		
		$this->setWhereCondition("1");
		
		return $result;	
	}
	
	
	/**
	* generate new dataset in frm_posts
	* @param	int	$topic
	* @param	int	$thread
	* @param	int	$user
	* @param	string	$message	
	* @access public
	*/
	function generatePost($obj_id, $parent_id, $topic, $thread, $user, $message)
	{	
		global $tree;
		
		$pos_data = array(
            "pos_top_fk"   	=> $topic,
			"pos_thr_fk"   	=> $thread,
            "pos_usr_id" 	=> $user,
            "pos_message"   => strip_tags($message),
            "pos_date"   	=> date("Y-m-d H:i:s"),            
            "pos_update" 	=> date("Y-m-d H:i:s")
        );
		
		// insert new post into frm_posts
		$q = "INSERT INTO frm_posts ";
		$q .= "(pos_top_fk,pos_thr_fk,pos_usr_id,pos_message,pos_date,pos_update) ";
		$q .= "VALUES ";
		$q .= "('".$pos_data["pos_top_fk"]."','".$pos_data["pos_thr_fk"]."','".$pos_data["pos_usr_id"]."','".$pos_data["pos_message"]."','".$pos_data["pos_date"]."','".$pos_data["pos_update"]."')";
		$result = $this->ilias->db->query($q);
		
		// get last insert id and return it
		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($query);
		$lastInsert = $res->fetchRow();	
		
		$tree->tree_id = $obj_id;
		$tree->insertNode($lastInsert[0],$thread,$obj_id);		
				
		$lastPost = $topic."#".$thread."#".$lastInsert[0];
			
		// Thread aktualisieren		
		$q = "UPDATE frm_threads SET thr_num_posts = thr_num_posts + 1, ";
        $q .= "thr_last_post = '".$lastPost. "', ";        
		$q .= "thr_last_modified = '" . date("Y-m-d H:i:s") . "' ";
        $q .= "WHERE thr_pk = '" . $thread . "'";
        $result = $this->ilias->db->query($q);
		
		// Topic aktualisieren		
        $q = "UPDATE frm_data SET top_num_posts = top_num_posts + 1, ";
        $q .= "top_last_post = '" .$lastPost. "', ";
        $q .= "top_last_modified = '" . date("Y-m-d H:i:s") . "' ";
        $q .= "WHERE top_pk = '" . $topic . "'";
        $result = $this->ilias->db->query($q);
		
		// Moderatoren informieren
		$a_user_id = $this->ilias->account->data["usr_id"]; //eingelogter Session-User
		$userData = $this->getModerator($a_user_id);
		
		$this->setWhereCondition("top_pk = ".$topic);
		$topicData = $this->getOneTopic();	
		
		if ($topicData["top_mods"] != "")
		{
			$MODS = explode("#", $topicData["top_mods"]);
			for ($i = 0; $i < count($MODS); $i++)
			{
				$modData = $this->getModerator($MODS[$i]);	// Moderator-User
				
				if ($modData["email"] != "")
				{
					$m = "New Message from: ".$userData["email"]."\n";
					$m .= "Message-ID: ".$lastInsert[0]."\n";
					
					mail($modData["email"], "New Message in Forum", $m, "From:".$userData["email"]);
				}
			}
		}	
		
		return 	$lastInsert[0];
		
	}
	
	
	/**
	* generate new dataset in frm_threads
	* @param	int	$topic
	* @param	int	$user
	* @param	string	$subject
	* @return int $lastInsert ID
	* @access public
	*/
	function generateThread($obj_id, $parent_id, $topic, $user, $subject, $message)
	{	
		global $tree;
		
		$thr_data = array(
            "thr_top_fk"   	=> $topic,
			"thr_usr_id" 	=> $user,
            "thr_subject"   => $subject,
            "thr_date"   	=> date("Y-m-d H:i:s"),            
            "thr_update" 	=> date("Y-m-d H:i:s")
        );
		
		// insert new thread into frm_threads
		$q = "INSERT INTO frm_threads ";
		$q .= "(thr_top_fk,thr_usr_id,thr_subject,thr_date,thr_update) ";
		$q .= "VALUES ";
		$q .= "('".$thr_data["thr_top_fk"]."','".$thr_data["thr_usr_id"]."','".$thr_data["thr_subject"]."','".$thr_data["thr_date"]."','".$thr_data["thr_update"]."')";
		$result = $this->ilias->db->query($q);
		
		// get last insert id and return it
		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($query);
		$lastInsert = $res->fetchRow();		
		
		$tree->tree_id = $obj_id;
		$tree->insertNode($lastInsert[0],$obj_id,0);
		
		// Thread-Zähler in frm_data erhöhen
        $q = "UPDATE frm_data SET top_num_threads = top_num_threads + 1 ";
        $q .= "WHERE top_pk = '" . $topic . "'";
        $result = $this->ilias->db->query($q);
		
		$newPost = $this->generatePost($obj_id, $parent_id, $topic, $lastInsert[0], $user, $message);
		
	}
	
	
	function getThreadList($topic)
	{
		$q = "SELECT frm_threads.*, usr_data.surname FROM frm_threads, usr_data WHERE ";
		$q .= "thr_top_fk ='".$topic."' AND ";
		$q .= "thr_usr_id = usr_id ";
		$q .= "ORDER BY ".$this->orderField;	
		
		$res = $this->ilias->db->query($q);			
		
		
		return $res;
	}
	
	
	
	function getPostList($topic, $thread)
	{
		$q = "SELECT frm_posts.*, usr_data.surname FROM frm_posts, usr_data WHERE ";
		$q .= "pos_top_fk ='".$topic."' AND ";
		$q .= "pos_thr_fk ='".$thread."' AND ";
		$q .= "pos_usr_id = usr_id ";
		$q .= "ORDER BY ".$this->orderField;	
		
		$res = $this->ilias->db->query($q);			
		
		
		return $res;
	}
	
	
	
	
	
	function getLastPost($lastPost)
	{
		$LP = explode("#", $lastPost);		
		
		$q = "SELECT DISTINCT frm_posts.*, usr_data.surname FROM frm_posts, usr_data WHERE ";
		$q .= "pos_top_fk = '".$LP[0]."' AND ";
		$q .= "pos_thr_fk = '".$LP[1]."' AND ";
		$q .= "pos_pk = '".$LP[2]."' AND ";
		$q .= "pos_usr_id = usr_id";		

		$result = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);
		
		if (strlen($result["pos_message"]) > 40)
			$result["pos_message"] = substr($result["pos_message"], 0, 37)."...";
			
		$result["pos_date"] = $this->convertDate($result["pos_date"]);
				
		
		return $result;
	}
	
	
	function getOnePost($post)
	{				
		$q = "SELECT frm_posts.*, usr_data.surname FROM frm_posts, usr_data WHERE ";		
		$q .= "pos_pk = '".$post."' AND ";
		$q .= "pos_usr_id = usr_id";		

		$result = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);
					
		$result["pos_date"] = $this->convertDate($result["pos_date"]);
					
		return $result;
	}
	
	
	
	function getModerator($mod_user_id)
	{
		$moderator = new User($mod_user_id);
		
		return $moderator->data;
	}
	
	
	function countUserArticles($user)
	{
		$q = "SELECT * FROM frm_posts WHERE ";
		$q .= "pos_usr_id ='".$user."'";
				
		$res = $this->ilias->db->query($q);			
		
		return $res->numRows();
	}
	
	
	
	function getForumPath($obj_id, $parent_id, $topic=0, $thread=0)
	{
		global $tree;		
		
		$path = "";
		
		if ($topic == 0 && $thread == 0)
		{
			//var_dump("<pre>",$tmpPath,"obj ".$obj,"parent ".$parent,"</pre>");
			
			$tmpPath = $tree->getPathFull($obj_id, $parent_id);			
			for ($i = 0; $i < (count($tmpPath)-1); $i++)
			{
				if ($path != "") $path .= " > ";
				$path .= $tmpPath[$i]["title"];				
			}
		}
		
		if ($topic > 0)
		{
			$q = "SELECT * FROM frm_data WHERE ";
			$q .= "top_pk = '".$topic."'";			

			$res = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);
			
			if ($path != "") $path .= " > ";
			$path .= $res["top_name"];
		}
		
		if ($thread > 0)
		{
			$q2 = "SELECT * FROM frm_threads WHERE ";
			$q2 .= "thr_pk  = '".$thread."'";			
	
			$res2 = $this->ilias->db->getRow($q2, DB_FETCHMODE_ASSOC);
			
			if ($path != "") $path .= " > ";
			$path .= $res2["thr_subject"];
		}
		
		//$path .= ": ";
		
		return $path;
	}
	
	
	/**
    * Konvertiert ein Datum, das im Format "Y-m-d H:i:s" vorliegt in einen Timestamp-
    * @param string $date Umzuwandelndes Datum
    * @return int Timestamp
    * @access private
    */
    function convertDate($date)
    {
        global $lng;
			
		if ($date == "0000-00-00 00:00:00")
		{
			
			return "00.00.0000 00:00:00";
		}
		elseif ($date > date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d"), date("Y"))))
        {
            
			return  $lng->txt("today")." " . substr($date, 11);
        }
        else
        {
            
			return date("d.m.Y H:i:s", mktime(substr($date, 11, 2),
                                              substr($date, 14, 2),
                                              substr($date, 17, 2),
                                              substr($date, 5, 2),
                                              substr($date, 8, 2),
                                              substr($date, 0, 4)
                                              )
                        );
        }
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
} // END class.Forum

?>