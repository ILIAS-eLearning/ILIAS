<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ForumExport
* core export functions for forum
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForumExport
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
	* @var string class name
	* @access private
	*/
	var $className="ilForumExport";
	
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
	var $replQuote1 = "<blockquote class=\"quote\"><hr size=\"1\" color=\"#000000\">"; 
	var $replQuote2 = "<hr size=\"1\" color=\"#000000\"/></blockquote>"; 
	
	// max. datasets per page
	var $pageHits = 20;
	
	// object id
	var $id;

	/**
	* Constructor
	* @access	public
	*/
	function ilForumExport()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}
	
	
	
	/**
	* set database field for sorting results
	* @param	string	$orderField database field for sorting
	* @see				$orderField
	* @access	private
	*/
	function setOrderField($orderField)
	{
		if ($orderField == "")
		{
			die($this->className . "::setOrderField(): No orderField given.");			
		}
		else
		{
			$this->orderField = $orderField;
		}
	}
	
	/**
	* get name of orderField
	* @return	string	name of orderField
	* @see				$orderField
	* @access	public
	*/
	function getOrderField()
	{
		return $this->orderField;
	}
	
	/**
	* set database table
	* @param	string	$dbTable database table
	* @see				$dbTable
	* @access	public
	*/
	function setDbTable($dbTable)
	{
		if ($dbTable == "")
		{
			die($this->className . "::setDbTable(): No database table given.");
		}
		else
		{
			$this->dbTable = $dbTable;
		}
	}

	/**
	* get name of database table
	* @return	string	name of database table
	* @see				$dbTable
	* @access	public
	*/
	function getDbTable()
	{
		return $this->dbTable;
	}
	
	/**
	* set content of WHERE condition
	* @param	string	$whereCondition 
	* @see				$whereCondition
	* @access	public
	*/
	function setWhereCondition($whereCondition = "1")
	{
		$this->whereCondition = $whereCondition;
		return true;
	}
	
	/**
	* get content of whereCondition
	* @return	string 
	* @see				$whereCondition
	* @access	public
	*/
	function getWhereCondition()
	{
		return $this->whereCondition;
	}
	
	
	/**
	* get one topic-dataset by WhereCondition
	* @return	array	$result dataset of the topic
	* @access	public
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
	* @return	array	$result dataset of the thread
	* @access	public
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
	* get data of the first node from frm_posts_tree and frm_posts
	* @access	public
	* @param	integer		tree id	
	* @return	object		db result object
	*/
	function getFirstPostNode($tree_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM frm_posts, frm_posts_tree ".
				 "WHERE pos_pk = pos_fk ".				 
				 "AND parent_pos = 0 ".
				 "AND thr_fk = ".$ilDB->quote($tree_id)."";
		$res = $this->ilias->db->query($query);
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		return $this->fetchPostNodeData($row);
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
		global $ilDB;
		
	    $subtree = array();
	
		$query = "SELECT * FROM frm_posts_tree ".
				 "LEFT JOIN frm_posts ON frm_posts.pos_pk = frm_posts_tree.pos_fk ".
				 "WHERE frm_posts_tree.lft BETWEEN ".$ilDB->quote($a_node["lft"])." AND ".$ilDB->quote($a_node["rgt"])." ".
				 "AND thr_fk = ".$ilDB->quote($a_node["tree"])."";
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
					"alias"			=> $a_row->pos_usr_alias,
					"message"		=> $a_row->pos_message,
					"subject"		=> $a_row->pos_subject,
					"pos_cens_com"	=> $a_row->pos_cens_com,
					"pos_cens"		=> $a_row->pos_cens,
					"date"			=> $a_row->date,
					"create_date"	=> $a_row->pos_date,
					"update"		=> $a_row->pos_update,					
					"update_user"	=> $a_row->update_user,
					"tree"			=> $a_row->thr_fk,					
					"parent"		=> $a_row->parent_pos,
					"lft"			=> $a_row->lft,
					"rgt"			=> $a_row->rgt,
					"depth"			=> $a_row->depth,
					"id"			=> $a_row->fpt_pk,
					"import_name"   => $a_row->import_name
					);
		
		$data["message"] = stripslashes($data["message"]);
		
		return $data ? $data : array();
	}
	
	
	
	/**
	* get content of given user-ID
	*
	* @param	integer $a_user_id: user-ID
	* @return	object	user object 
	* @access	public
   	*/
	function getUser($a_user_id)
	{
		$userObj = new ilObjUser($a_user_id);

		return $userObj;
	}
	
	
	
	
	/**
    * converts the date format
    * @param	string	$date 
    * @return	string	formatted datetime
    * @access	public
    */
    function convertDate($date)
    {
        global $lng;
		
#		if ($date > date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d"), date("Y"))))
#        {
#			return  $lng->txt("today").", ".ilFormat::formatDate($date,"time");
#		}
		
		return ilFormat::formatDate($date);
    }
	
	
	
	/**
   	* get number of articles from given user-ID
	*
	* @param	integer	$user: user-ID
	* @return	integer
	* @access	public
   	*/
	function countUserArticles($user)
	{
		global $ilDB;
		
		$q = "SELECT * FROM frm_posts WHERE ";
		$q .= "pos_usr_id = ".$ilDB->quote($user)."";
				
		$res = $this->ilias->db->query($q);			
		
		return $res->numRows();
	}
	
	function isAnonymized($a_obj_id)
	{
		global $ilDB;
		
		$q = "SELECT anonymized FROM frm_settings WHERE ";
		$q .= "obj_id = ".$ilDB->quote($a_obj_id)."";
		return $this->ilias->db->getOne($q);
	}
	
	
	/**
	* prepares given string
	* @access	public
	* @param	string	
	* @param	integer	
	* @return	string	
	*/
	function prepareText($text,$edit=0)
	{		
		global $lng;

		if ($edit == 1)
		{
			$text = str_replace($this->txtQuote1, "", $text);		
			$text = str_replace($this->txtQuote2, "", $text);		
			$text = $this->txtQuote1.$text.$this->txtQuote2;
		}
		else
		{		
			// check for quotation		
			$startZ = substr_count ($text, "[quote");
			$endZ = substr_count ($text, "[/quote]");
			
			
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
				/*if ($startZ > 1)
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
				}*/			
				
				if ($edit == 0)
				{
					$ws= "[ \t\r\f\v\n]*";
					
					$text = eregi_replace("\[(quote$ws=$ws\"([^\"]*)\"$ws)\]",
						$this->replQuote1.'<div class="ilForumQuoteHead">'.$lng->txt("quote")." (\\2)".'</div>', $text);

					$text = str_replace("[quote]",
						$this->replQuote1.'<div class="ilForumQuoteHead">'.$lng->txt("quote").'</div>', $text);
					$text = str_replace("[/quote]", $this->replQuote2, $text);
				}
			}		
		}
		$text = stripslashes($text);
		return $text;
	}
	
	
	
	/**
	* get one post-dataset 
	* @param    integer post id 
	* @return	array result dataset of the post
	* @access	public
	*/
	function getOnePost($post)
	{
		global $lng, $ilDB;
				
		$q = "SELECT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data WHERE ";		
		$q .= "pos_pk = ".$ilDB->quote($post)." AND ";
		$q .= "pos_usr_id = usr_id";		

		$q = "SELECT frm_posts.*  FROM frm_posts WHERE ";		
		$q .= "pos_pk = ".$ilDB->quote($post)." ";

		$result = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);
		
		$ROW = $this->fetchPostNodeData($result);
				
		$result["create_date"] = $result["pos_date"];		
		$result["message"] = nl2br(stripslashes($result["pos_message"]));
		$result["author"] = nl2br(stripslashes($result["pos_usr_id"]));
		$result["alias"] = nl2br(stripslashes($result["pos_usr_alias"]));
		$result["date"] = date;
		$result["update"] = $result["pos_update"];		
			
				
		return $result;
	}
	
	
	/**
	* get all threads of given forum
	* @param	integer	topic: forum-ID
	* @return	object	res result identifier for use with fetchRow
	* @access	public
	*/
	function getThreadList($topic)
	{
		global $ilDB;
		
		$q = "SELECT frm_threads.*, usr_data.lastname FROM frm_threads, usr_data WHERE ";
		$q .= "thr_top_fk = ".$ilDB->quote($topic)." AND ";
		$q .= "thr_usr_id = usr_id";

		$q = "SELECT frm_threads.* FROM frm_threads WHERE ";
		$q .= "thr_top_fk = ".$ilDB->quote($topic)." ";

		if ($this->orderField != "")
		{
			$q .= " ORDER BY ".$this->orderField;
		}
	
		$res = $this->ilias->db->query($q);			

		return $res;
	}
	
	function getUserData($id,$a_import_name = 0)
	{
		global $lng, $ilDB;

		if($id && ilObject::_exists($id) && ilObjectFactory::getInstanceByObjId($id,false))
		{
			$query = "SELECT * FROM usr_data WHERE usr_id = ".$ilDB->quote($id)."";
			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$tmp_array["usr_id"] = $row->usr_id;
				$tmp_array["login"]  = $row->login;
				$tmp_array["create_date"] = $row->create_date;
				$tmp_array["id"] = $row->usr_id;
				$tmp_array["public_profile"] = ilObjUser::_lookupPref($id, "public_profile");

			}
			return $tmp_array ? $tmp_array : array();
		}
		else
		{
			$login = $a_import_name ? $a_import_name." (".$lng->txt("imported").")" : $lng->txt("unknown");

			return array("usr_id" => 0,"login" => $login);
		}
	}
} // END class.ForumExport
