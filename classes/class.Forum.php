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
	
	/**
    * set content of WHERE condition
    * @param string $whereCondition 
    * @see $whereCondition
    * @access public
    */
	function setWhereCondition($whereCondition = "1") {
        $this->whereCondition = $whereCondition;
        return true;
    }
	
	/**
    * get content of whereCondition
    * @return string 
    * @see $whereCondition
    * @access public
    */
    function getWhereCondition() {
        return $this->whereCondition;
    }
	
	/**
    * set number of max. visible datasets
    * @param int $pageHits 
    * @see $pageHits
    * @access public
    */
	function setPageHits($pageHits) {
         if ($pageHits < 1) {
            die($this->className . "::setPageHits(): No int pageHits given.");
        } else {
            $this->pageHits = $pageHits;
			return true;
        }
    }
	
	/**
    * get number of max. visible datasets
    * @return int $pageHits 
    * @see $pageHits
    * @access public
    */
    function getPageHits() {
        return $this->pageHits;
    }
	
	
	
	// *******************************************************************************
	
	
	/**
	* get one dataset from set Table and set WhereCondition
	* @return array $res dataset 
	* @access public
	*/
	function getOneDataset()
	{	
		
		$q = "SELECT * FROM ".$this->dbTable." WHERE ( ".$this->whereCondition." )";		
		
		if ($this->orderField != "")
			$q .= " ORDER BY ".$this->orderField;				
			
		$res = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);
     		
		$this->setWhereCondition("1");
		
		return $res;	
	}
	
	
	/**
	* get one topic-dataset by WhereCondition
	* @return array $result dataset of the topic
	* @access public
	*/
	function getOneTopic()
	{	
		
		$query = "SELECT * FROM frm_data WHERE ( ".$this->whereCondition." )";
		
		$result = $this->ilias->db->getRow($query, DB_FETCHMODE_ASSOC);
     		
		$this->setWhereCondition("1");
		
		$result["top_name"] = trim(stripslashes($result["top_name"]));
		$result["top_description"] = nl2br(stripslashes($result["top_description"]));
		
		return $result;	
	}
	
	
	/**
	* get one thread-dataset by WhereCondition
	* @return array $result dataset of the thread
	* @access public
	*/
	function getOneThread()
	{	
		
		$query = "SELECT * FROM frm_threads WHERE ( ".$this->whereCondition." )";
		
		$result = $this->ilias->db->getRow($query, DB_FETCHMODE_ASSOC);
     		
		$this->setWhereCondition("1");
		
		$result["thr_subject"] = trim(stripslashes($result["thr_subject"]));
				
		return $result;	
	}
	
	
	/**
	* get one post-dataset 
	* @return array $result dataset of the post
	* @access public
	*/
	function getOnePost($post)
	{				
		$q = "SELECT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data WHERE ";		
		$q .= "pos_pk = '".$post."' AND ";
		$q .= "pos_usr_id = usr_id";		

		$result = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);
					
		$result["pos_date"] = $this->convertDate($result["pos_date"]);		
		$result["pos_message"] = nl2br(stripslashes($result["pos_message"]));
					
		return $result;
	}
	
	
	/**
	* generate new dataset in frm_posts
	* @param	int	$topic
	* @param	int	$thread
	* @param	int	$user
	* @param	string	$message	
	* @param	int	$parent_pos	
	* @return int $lastInsert: new post ID
	* @access public
	*/
	function generatePost($topic, $thread, $user, $message, $parent_pos=0)
	{		
				
		$pos_data = array(
            "pos_top_fk"   	=> $topic,
			"pos_thr_fk"   	=> $thread,
            "pos_usr_id" 	=> $user,
            "pos_message"   => strip_tags(addslashes($message)),
            "pos_date"   	=> date("Y-m-d H:i:s")            
        );
		
		// insert new post into frm_posts
		$q = "INSERT INTO frm_posts ";
		$q .= "(pos_top_fk,pos_thr_fk,pos_usr_id,pos_message,pos_date) ";
		$q .= "VALUES ";
		$q .= "('".$pos_data["pos_top_fk"]."','".$pos_data["pos_thr_fk"]."','".$pos_data["pos_usr_id"]."','".$pos_data["pos_message"]."','".$pos_data["pos_date"]."')";
		$result = $this->ilias->db->query($q);
		
		// get last insert id and return it
		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($query);
		$lastInsert = $res->fetchRow();					
		
		// entry in tree-table
		if ($parent_pos == 0) $this->addPostTree($thread, $lastInsert[0]);		
		else $this->insertPostNode($lastInsert[0],$parent_pos,$thread);
		
		// string last post
		$lastPost = $topic."#".$thread."#".$lastInsert[0];
			
		// update thread
		$q = "UPDATE frm_threads SET thr_num_posts = thr_num_posts + 1, ";
        $q .= "thr_last_post = '".$lastPost. "' ";        
		$q .= "WHERE thr_pk = '" . $thread . "'";
        $result = $this->ilias->db->query($q);
		
		// update topic
        $q = "UPDATE frm_data SET top_num_posts = top_num_posts + 1, ";
        $q .= "top_last_post = '" .$lastPost. "' ";
        $q .= "WHERE top_pk = '" . $topic . "'";
        $result = $this->ilias->db->query($q);
				
		
		return 	$lastInsert[0];
		
	}
	
	
	/**
	* generate new dataset in frm_threads
	* @param	int	$topic
	* @param	int	$user
	* @param	string	$subject
	* @param	string	$message
	* @return int: new post ID
	* @access public
	*/
	function generateThread($topic, $user, $subject, $message)
	{			
		
		$thr_data = array(
            "thr_top_fk"   	=> $topic,
			"thr_usr_id" 	=> $user,
            "thr_subject"   => addslashes($subject),
            "thr_date"   	=> date("Y-m-d H:i:s")            
        );
		
		// insert new thread into frm_threads
		$q = "INSERT INTO frm_threads ";
		$q .= "(thr_top_fk,thr_usr_id,thr_subject,thr_date) ";
		$q .= "VALUES ";
		$q .= "('".$thr_data["thr_top_fk"]."','".$thr_data["thr_usr_id"]."','".$thr_data["thr_subject"]."','".$thr_data["thr_date"]."')";
		$result = $this->ilias->db->query($q);
		
		// get last insert id and return it
		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($query);
		$lastInsert = $res->fetchRow();				
				
		// update topic
        $q = "UPDATE frm_data SET top_num_threads = top_num_threads + 1 ";
        $q .= "WHERE top_pk = '" . $topic . "'";
        $result = $this->ilias->db->query($q);
		
		return $this->generatePost($topic, $lastInsert[0], $user, $message);
		
	}
	
	
	/**
	* update dataset in frm_posts
	* @param	int	$pos_pk	
	* @param	string	$message	
	* @return	boolean
	* @access public
	*/
	function updatePost($message, $pos_pk)
	{		
		$query = "UPDATE frm_posts ".
				 "SET ".
				 "pos_message = '".addslashes($message)."',".
				 "pos_update = '".date("Y-m-d H:i:s")."',".
				 "update_user = '".$_SESSION["AccountId"]."' ".				 
				 "WHERE pos_pk = '".$pos_pk."'";
		$res = $this->ilias->db->query($query);
	
		return true;		
		
	}
	
	
	/**
	* delete post and sub-posts
	* @param	int	$post: ID	
	* @access public
	* @return int: 0 or thread-ID
	*/
	function deletePost($post)
	{		
		// delete tree and get id's of all posts to delete
		$p_node = $this->getPostNode($post);	
		$del_id = $this->deletePostTree($p_node);
		
		$dead_pos = count($del_id);
		$dead_thr = 0;
		
		// if deletePost is thread opener ...
		if ($p_node["parent"] == 0)
		{
			// delete thread
			$dead_thr = $p_node["tree"];
			$query = "DELETE FROM frm_threads ".
					 "WHERE thr_pk = '".$p_node["tree"]."'";					 
			$this->ilias->db->query($query);
			
			// update num_threads
			$query2 = "UPDATE frm_data ".
					 "SET ".
					 "top_num_threads = top_num_threads - 1 ".					
					 "WHERE top_frm_fk = '".$_GET["obj_id"]."'";
			$this->ilias->db->query($query2);
			
			// delete all posts of this thread
			$query3 = "DELETE FROM frm_posts ".
					 "WHERE pos_thr_fk = '".$p_node["tree"]."'";					 
			$this->ilias->db->query($query3);				
			
		}
		else
		{
			// delete this post and its sub-posts
			for ($i = 0; $i < $dead_pos; $i++)
			{
				$query = "DELETE FROM frm_posts ".
						 "WHERE pos_pk = '".$del_id[$i]."'";					 
				$this->ilias->db->query($query);
			}
			
			// update num_posts in frm_threads
			$query2 = "UPDATE frm_threads ".
					 "SET ".
					 "thr_num_posts = thr_num_posts - $dead_pos ".					
					 "WHERE thr_pk = '".$p_node["tree"]."'";
			$this->ilias->db->query($query2);
			
			// get latest post of thread and update last_post
			$q = "SELECT * FROM frm_posts WHERE ";
			$q .= "pos_thr_fk = '".$p_node["tree"]."' ";
			$q .= "ORDER BY pos_date DESC";
			
			$res1 = $this->ilias->db->query($q);
			
			if ($res1->numRows() == 0) $lastPost_thr = "";
			else
			{
				$z = 0;
				while ($selData = $res1->fetchRow(DB_FETCHMODE_ASSOC))
				{
					if ($z > 0) break;
					$lastPost_thr = $selData["pos_top_fk"]."#".$selData["pos_thr_fk"]."#".$selData["pos_pk"];
					$z ++;
				}
			}
			
			$query4 = "UPDATE frm_threads ".
					 "SET ".
					 "thr_last_post = '".$lastPost_thr."' ".					
					 "WHERE thr_pk = '".$p_node["tree"]."'";
			$this->ilias->db->query($query4);	
			
		}
		
		// update num_posts in frm_data
		$qu = "UPDATE frm_data ".
			"SET ".
			"top_num_posts = top_num_posts - $dead_pos ".					
			"WHERE top_frm_fk = '".$_GET["obj_id"]."'";
		$this->ilias->db->query($qu);
		
		// get latest post of forum and update last_post
		$q = "SELECT * FROM frm_posts, frm_data WHERE ";
		$q .= "pos_top_fk = top_pk AND ";
		$q .= "top_frm_fk ='".$_GET["obj_id"]."' ";
		$q .= "ORDER BY pos_date DESC";
		
		$res2 = $this->ilias->db->query($q);
		
		if ($res2->numRows() == 0) $lastPost_top = "";
		else
		{
			$z = 0;
			while ($selData = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if ($z > 0) break;
				$lastPost_top = $selData["pos_top_fk"]."#".$selData["pos_thr_fk"]."#".$selData["pos_pk"];
				$z ++;
			}
		}
		
		$query5 = "UPDATE frm_data ".
				 "SET ".
				 "top_last_post = '".$lastPost_top."' ".					
				 "WHERE top_frm_fk = '".$_GET["obj_id"]."'";
		$this->ilias->db->query($query5);		
		
	
		return $dead_thr;		
		
	}
	
	
	/**
   	* get all threads of given forum
	*
	* @param int $topic: forum-ID
	* @return object $res result identifier for use with fetchRow
	* @access public
   	*/
	function getThreadList($topic)
	{
		$q = "SELECT frm_threads.*, usr_data.lastname FROM frm_threads, usr_data WHERE ";
		$q .= "thr_top_fk ='".$topic."' AND ";
		$q .= "thr_usr_id = usr_id";
		if ($this->orderField != "")
			$q .= " ORDER BY ".$this->orderField;	
		
		$res = $this->ilias->db->query($q);			
		
		
		return $res;
	}
	
	
	/**
   	* get all posts of given thread
	*
	* @param int $topic: forum-ID
	* @param int $thread: thread-ID
	* @return object $res result identifier for use with fetchRow
	* @access public
   	*/
	function getPostList($topic, $thread)
	{
		$q = "SELECT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data WHERE ";
		$q .= "pos_top_fk ='".$topic."' AND ";
		$q .= "pos_thr_fk ='".$thread."' AND ";
		$q .= "pos_usr_id = usr_id";
		if ($this->orderField != "")
			$q .= " ORDER BY ".$this->orderField;
		
		$res = $this->ilias->db->query($q);			
		
		
		return $res;
	}
	
	
	/**
   	* get content of given ID's
	*
	* @param string $lastPost: ID's, separated with #
	* @return array $result 
	* @access public
   	*/
	function getLastPost($lastPost)
	{
		$LP = explode("#", $lastPost);		
		
		$q = "SELECT DISTINCT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data WHERE ";
		$q .= "pos_top_fk = '".$LP[0]."' AND ";
		$q .= "pos_thr_fk = '".$LP[1]."' AND ";
		$q .= "pos_pk = '".$LP[2]."' AND ";
		$q .= "pos_usr_id = usr_id";		

		$result = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);		
		
		// limit the message-size
		$result["pos_message"] = $this->prepareText($result["pos_message"],2);
		
		if (strpos($result["pos_message"], $this->txtQuote2) > 0)
		{			
			$viewPos = strrpos($result["pos_message"], $this->txtQuote2) + strlen($this->txtQuote2);
			$result["pos_message"] = substr($result["pos_message"], $viewPos);				
		}			
		if (strlen($result["pos_message"]) > 40)
			$result["pos_message"] = substr($result["pos_message"], 0, 37)."...";
		
		$result["pos_message"] = stripslashes($result["pos_message"]);
				
		// convert date
		$result["pos_date"] = $this->convertDate($result["pos_date"]);
				
		
		return $result;
	}	
	
	
	/**
   	* get content of given user-ID
	*
	* @param int $mod_user_id: user-ID
	* @return array 
	* @access public
   	*/
	function getModerator($mod_user_id)
	{
		$moderator = new User($mod_user_id);
		
		return $moderator->data;
	}
	
	
	/**
   	* checks edit-right for given post-ID
	*
	* @param int $post_id: post-ID
	* @return	boolean
	* @access public
   	*/
	function checkEditRight($post_id)
	{
		global $rbacsystem;		
		
		// is online-user the author of the post?	
		$q = "SELECT * FROM frm_posts WHERE ";
		$q .= "pos_usr_id ='".$_SESSION["AccountId"]."' ";
		$q .= "AND pos_pk ='".$post_id."'";
				
		$res = $this->ilias->db->query($q);			
		
		// if not, is he authorised to edit?
		if ($res->numRows() > 0) 
			return true;
		elseif ($rbacsystem->checkAccess("edit post", $_GET["obj_id"], $_GET["parent"]))
			return true;		
		else
			return false;
	}
	
	
	/**
   	* get number of articles from given user-ID
	*
	* @param int $user: user-ID
	* @return int
	* @access public
   	*/
	function countUserArticles($user)
	{
		$q = "SELECT * FROM frm_posts WHERE ";
		$q .= "pos_usr_id ='".$user."'";
				
		$res = $this->ilias->db->query($q);			
		
		return $res->numRows();
	}
	
	
	/**
   	* builds a string to show the forum-context
	*
	* @param int $obj_id
	* @param int $parent_id
	* @return string
	* @access public
   	*/
	function getForumPath($obj_id, $parent_id)
	{
		global $tree;		
		
		$path = "";		
					
		$tmpPath = $tree->getPathFull($obj_id, $parent_id);		
		// count -1, to exclude the forum itself
		for ($i = 0; $i < (count($tmpPath)-1); $i++)
		{
			if ($path != "") $path .= " > ";
			$path .= $tmpPath[$i]["title"];						
		}						
		
		return $path;
	}
	
	
	/**
    * converts the date format
    * @param string $date 
    * @return Timestamp
    * @access public
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
	* @param	integer		a_tree_id: id where tree belongs to
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
	* @param	integer		tree_id
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
	* get data of given node from frm_posts_tree and frm_posts
	* @access	public
	* @param	integer		post_id	
	* @return	object		db result object
	*/
	function getPostNode($post_id)
	{
		$query = "SELECT * FROM frm_posts, frm_posts_tree ".
				 "WHERE pos_pk = pos_fk ".				 
				 "AND pos_pk = '".$post_id."'";
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
					"update_user"	=> $a_row->update_user,
					"tree"			=> $a_row->thr_fk,					
					"parent"		=> $a_row->parent_pos,
					"lft"			=> $a_row->lft,
					"rgt"			=> $a_row->rgt,
					"depth"			=> $a_row->depth,
					"id"			=> $a_row->fpt_pk		
					);
		
		$data["message"] = stripslashes($data["message"]);
		
		return $data ? $data : array();
	}
	
	
	
	/**
	* delete node and the whole subtree under this node
	* @access	public
	* @param	array		node_data of a node
	* @return array: ID's of deleted posts
	*/
	function deletePostTree($a_node)
	{
		// GET LEFT AND RIGHT VALUES
		$query = "SELECT * FROM frm_posts_tree ".
			"WHERE thr_fk = '".$a_node["tree"]."' ".
			"AND pos_fk = '".$a_node["pos_pk"]."' ".
			"AND parent_pos = '".$a_node["parent"]."'";
		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$a_node["lft"] = $row->lft;
			$a_node["rgt"] = $row->rgt;
		}

		$diff = $a_node["rgt"] - $a_node["lft"] + 1;		
		
		// get data of posts
		$query = "SELECT * FROM frm_posts_tree ".
				 "WHERE lft BETWEEN '".$a_node["lft"]."' AND '".$a_node["rgt"]." '".
				 "AND thr_fk = '".$a_node["tree"]."'";
		$result = $this->ilias->db->query($query);
		
		$del_id = array();
		
		while ($treeData = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$del_id[] = $treeData["pos_fk"];
		}
		
		// delete subtree
		$query = "DELETE FROM frm_posts_tree ".
				 "WHERE lft BETWEEN '".$a_node["lft"]."' AND '".$a_node["rgt"]." '".
				 "AND thr_fk = '".$a_node["tree"]."'";
		$this->ilias->db->query($query);		

		// close gaps
		$query = "UPDATE frm_posts_tree SET ".
				 "lft = CASE ".
				 "WHEN lft > '".$a_node["lft"]." '".
				 "THEN lft - '".$diff." '".
				 "ELSE lft ".
				 "END, ".
				 "rgt = CASE ".
				 "WHEN rgt > '".$a_node["lft"]." '".
				 "THEN rgt - '".$diff." '".
				 "ELSE rgt ".
				 "END ".
				 "WHERE thr_fk = '".$a_node["tree"]."'";
		$this->ilias->db->query($query);
		
		return $del_id;
	}
	
	
	/**
	* update page hits of given forum- or thread-ID
	* @access	public
	* @param	int	
	*/
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
	
	
	/**
	* prepares given string
	* @access	public
	* @param	string	
	* @param	int	
	* @return	string	
	*/
	function prepareText($text,$edit=0)
	{		
		if ($edit == 1)
		{
			$text = str_replace($this->txtQuote1, "", $text);		
			$text = str_replace($this->txtQuote2, "", $text);		
			$text = $this->txtQuote1.$text.$this->txtQuote2;
		}
		else
		{		
			// check for quotation		
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
				
				// only one txtQuote can exist...
				if ($startZ > 1)
				{
					$start_firstPos = strpos($text, $this->txtQuote1);				
					$text_s2 = str_replace($this->txtQuote1, "", substr($text, ($start_firstPos+strlen($this->txtQuote1))));
					$text_s1 = substr($text, 0, ($start_firstPos+strlen($this->txtQuote1)));
					$text = $text_s1.$text_s2;				
				}
				if ($endZ > 1)
				{				
					$end_firstPos = strrpos($text, $this->txtQuote2);				
					$text_e1 = str_replace($this->txtQuote2, "", substr($text, 0, $end_firstPos));
					$text_e2 = substr($text, $end_firstPos);
					$text = $text_e1.$text_e2;					
				}			
				
				if ($edit == 0)
				{
					$text = str_replace($this->txtQuote1, $this->replQuote1, $text);		
					$text = str_replace($this->txtQuote2, $this->replQuote2, $text);					
				}
			
			}		
		}
		
		$text = stripslashes($text);
		
		return $text;
	}
	
	
	
} // END class.Forum

?>