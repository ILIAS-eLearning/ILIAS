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
	
	var $txtQuote1 = "[quote]"; 
	var $txtQuote2 = "[/quote]"; 
	var $replQuote1 = "<blockquote><i>"; 
	var $replQuote2 = "</blockquote></i>"; 
	
	// max. datasets per page
	var $pageHits = 20;

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
	
	function setPageHits($pageHits) {
         if ($pageHits < 1) {
            die($this->className . "::setPageHits(): No int pageHits given.");
        } else {
            $this->pageHits = $pageHits;
			return true;
        }
    }

    function getPageHits() {
        return $this->pageHits;
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
	* get thread-data by WhereCondition
	* @param	string	$AObjType
	* @return object $result result identifier for use with getNext()
	* @access public
	*/
	function getOneThread()
	{	
		
		$query = "SELECT * FROM frm_threads WHERE ( ".$this->whereCondition." )";
		
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
	function generatePost($obj_id, $parent_id, $topic, $thread, $user, $message, $parent_pos=0, $firstPos="no")
	{		
		
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
		
		//$tree->tree_id = $obj_id;
		//$tree->insertNode($lastInsert[0],$thread,$obj_id);		
		
		// Eintrag in tree-table
		if ($firstPos == "yes") $this->addPostTree($thread, $lastInsert[0]);		
		else $this->insertPostNode($lastInsert[0],$parent_pos,$thread);
			
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
				
		// Thread-Zähler in frm_data erhöhen
        $q = "UPDATE frm_data SET top_num_threads = top_num_threads + 1 ";
        $q .= "WHERE top_pk = '" . $topic . "'";
        $result = $this->ilias->db->query($q);
		
		$newPost = $this->generatePost($obj_id, $parent_id, $topic, $lastInsert[0], $user, $message, 0, "yes");
		
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
		
		// Message-Länge begrenzen
		$QU = 0;			
		if (strpos($result["pos_message"], $this->txtQuote1) > 0 || strpos($result["pos_message"], $this->txtQuote2) > 0)
		{			
			// falls [quote] enthalten sind...
			$C1 = substr_count($result["pos_message"], $this->txtQuote1);
			$C2 = substr_count($result["pos_message"], $this->txtQuote2);
			
			$N1 = $C1 * strlen($this->txtQuote1);
			$N2 = $C2 * strlen($this->txtQuote2);
			
			$QU = $N1 + $N2;
		}			
		if (strlen($result["pos_message"]) > (40+$QU))
			$result["pos_message"] = substr($result["pos_message"], 0, (40+$QU-3))."...";
				
		// Datum konvertieren
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
			$tmpPath = $tree->getPathFull($obj_id, $parent_id);		
			//var_dump("<pre>",$tmpPath,"obj= ".$obj_id,"parent ".$parent_id,"</pre>");
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
	
	
	/**
	* create a new post-tree
	* @param	integer		a_tree_id: obj_id of object where tree belongs to
	* @param	integer		a_node_id: root node of tree (optional; default is tree_id itself)
	* @return	boolean		true on success
	* @access	public
	*/
	function addPostTree($a_tree_id,$a_node_id = -1)
	{
		if ($a_node_id <= 0)
		{
			$a_node_id = $a_tree_id;
		}
		
		$query = "INSERT INTO frm_posts_tree (thr_fk, pos_fk, parent_pos, lft, rgt, depth, date) ".
				 "VALUES ".
				 "('".$a_tree_id."','".$a_node_id."', 0, 1, 2, 1, '".date("Y-m-d H:i:s")."')";
		$this->ilias->db->query($query);
		
		return true;
	}
	
	
	/**
	* insert node under parent node
	* @access	public
	* @param	integer		node_id
	* @param	integer		parent_id (optional)
	*/
	function insertPostNode($a_node_id,$a_parent_id,$tree_id)
	{		
		// get left value
	    $query = "SELECT * FROM frm_posts_tree ".
		   "WHERE pos_fk = '".$a_parent_id."' ".		   
		   "AND thr_fk = '".$tree_id."'";

	    $res = $this->ilias->db->getRow($query);
		
		$left = $res->lft;

		$lft = $left + 1;
		$rgt = $left + 2;
//		var_dump("<pre>","child = ".$a_parent_id,"parent = ".$a_parent_parent_id,"left = ".$left,"lft = ".$lft,"rgt = ".$rgt,"</pre");

		// spread tree
		$query = "UPDATE frm_posts_tree SET ".
				 "lft = CASE ".
				 "WHEN lft > ".$left." ".
				 "THEN lft + 2 ".
				 "ELSE lft ".
				 "END, ".
				 "rgt = CASE ".
				 "WHEN rgt > ".$left." ".
				 "THEN rgt + 2 ".
				 "ELSE rgt ".
				 "END ".
				 "WHERE thr_fk = '".$tree_id."'";

		$this->ilias->db->query($query);
		
		$depth = $this->getPostDepth($a_parent_id, $tree_id) + 1;
	
		// insert node
		$query = "INSERT INTO frm_posts_tree (thr_fk,pos_fk,parent_pos,lft,rgt,depth,date) ".
				 "VALUES ".
				 "('".$tree_id."','".$a_node_id."','".$a_parent_id."','".$lft."','".$rgt."','".$depth."','".date("Y-m-d H:i:s")."')";
		$this->ilias->db->query($query);
	}
	
	/**
	* Return depth of an object
	* @access	private
	* @param	integer		node_id of parent's node_id
	* @param	integer		node_id of parent's node parent_id
	* @return	integer		depth of node
	*/
	function getPostDepth($a_node_id, $tree_id)
	{
		if ($tree_id)
		{
			$query = "SELECT depth FROM frm_posts_tree ".
					 "WHERE pos_fk = '".$a_node_id."' ".					 
					 "AND thr_fk = '".$tree_id."'";
	
			$res = $this->ilias->db->getRow($query);
	
			return $res->depth;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	* get all nodes in the subtree under specified node
	* 
	* @access	public
	* @param	array		node_data
	* @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
	*/
	
	function getPostTree($a_node)
	{
	    $subtree = array();
	
		$query = "SELECT * FROM frm_posts_tree ".
				 "LEFT JOIN frm_posts ON frm_posts.pos_pk = frm_posts_tree.pos_fk ".
				 "WHERE frm_posts_tree.lft BETWEEN '".$a_node["lft"]."' AND '".$a_node["rgt"]."' ".
				 "AND thr_fk = '".$a_node["tree"]."'";
		if ($this->orderField != "")
			$query .= " ORDER BY ".$this->orderField." DESC";		 
		
		$res = $this->ilias->db->query($query);
		
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$subtree[] = $this->fetchPostNodeData($row);
		}
					
		return $subtree;
	}
	
	/**
	* get all information of the first node.
	* get data of the first node from frm_posts_tree and frm_posts
	* @access	public
	* @param	integer		tree id	
	* @return	object		db result object
	*/
	function getFirstPostNode($tree_id)
	{
		$query = "SELECT * FROM frm_posts, frm_posts_tree ".
				 "WHERE pos_pk = pos_fk ".				 
				 "AND parent_pos = 0 ".
				 "AND thr_fk = '".$tree_id."'";
		$res = $this->ilias->db->query($query);
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		return $this->fetchPostNodeData($row);
	}
	
	/**
	* get data of parent node from frm_posts_tree and frm_posts
	* @access	private
 	* @param	object	db	db result object containing node_data
	* @return	array		2-dim (int/str) node_data
	*/
	function fetchPostNodeData($a_row)
	{
		$data = array(
					"pos_pk"		=> $a_row->pos_pk,
					"author"		=> $a_row->pos_usr_id,
					"message"		=> $a_row->pos_message,
					"date"			=> $a_row->date,
					"create_date"	=> $a_row->pos_date,
					"update"		=> $a_row->pos_update,					
					"tree"			=> $a_row->thr_fk,					
					"parent"		=> $a_row->parent_pos,
					"lft"			=> $a_row->lft,
					"rgt"			=> $a_row->rgt,
					"depth"			=> $a_row->depth,
					"id"			=> $a_row->fpt_pk		
					);
		return $data ? $data : array();
	}
	
	
	function updateVisits($ID)
	{
		$checkTime = time() - (60*60);
		
		if ($_SESSION["frm_visit_".$this->dbTable."_".$ID] < $checkTime)
		{
			$_SESSION["frm_visit_".$this->dbTable."_".$ID] = time();		
		
			$q = "UPDATE ".$this->dbTable." SET ";
			$q .= "visits = visits + 1 ";
			$q .= "WHERE ( ".$this->whereCondition." )";			
			
			$this->ilias->db->query($q);
		}
		
		$this->setWhereCondition("1");
	}
	
	function prepareText($text)
	{		
		// Zitate		
		$startZ = substr_count ($text, $this->txtQuote1);
		$endZ = substr_count ($text, $this->txtQuote2);
		if ($startZ > 0 || $endZ > 0)
		{
			if ($startZ > $endZ) 
			{
				$diff = $startZ - $endZ;
				for ($i = 0; $i < $diff; $i++)
				{
					$text .= $this->txtQuote2;
				}
			}
			elseif ($startZ < $endZ)
			{
				$diff = $endZ - $startZ;
				for ($i = 0; $i < $diff; $i++)
				{				
					$text = $this->txtQuote1.$text;
				}
			}
			
			$text = str_replace($this->txtQuote1, $this->replQuote1, $text);		
			$text = str_replace($this->txtQuote2, $this->replQuote2, $text);
		
		}		
		
		
		return $text;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
} // END class.Forum

?>