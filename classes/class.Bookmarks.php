<?php
/**
* Class Bookmarks
* Bookmark management
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package application
*/
class Bookmarks
{
	/**
	* User Id
	* @var integer
	* @access public
	*/
	var $user_Id;

	/**
	* ilias object
	* @var object ilias
	* @access public
	*/	
	var $ilias;
	
	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function Bookmarks($a_user_id = 0)
	{
		global $ilias;
		
		// Initiate variables
		$this->ilias =& $ilias;
		$this->userId = $a_user_id;
	}

	/**
	* loads a bookmark
	* @access public
	*/
	function getBookmark ()
	{
		//query
		// TODO: ist wohl noch nicht ganz fertig, oder?
		if ($folder != "")
		{
			$w = "folder='".$folder."' AND ";
		}
			
		$sql = "SELECT * FROM bookmarks 
				WHERE ".$w." usr_fk='".$this->userId."'
				ORDER BY folder, pos";

		$r = $this->ilias->db->query($sql);

		if ($r->numRows()>0)
		{
		 	$row = $r->fetchRow(DB_FETCHMODE_ASSOC);

			$bookmark = array(
							"id"		=> $row["id"],
							"url"		=> "http://".$row["url"],
							"name"		=> $row["name"],
							"folder"	=> $row["folder"],
							"pos"		=> $row["pos"]
							);
			
			return $bookmark;
		}

		return false;
	}

	/**
	* saves a bookmark
	* @access	public
	*/
	function insert()
	{
		// fill user_data
		$query = "INSERT INTO bookmark ".
				 "(usr_fk, pos, url, name) ".
				 "VALUES ".
				 "('".$this->userId."','0','".$this->url."','".$this->name."')";
		$res = $this->ilias->db->query($query);
	}

	/**
	* updates a record "user" and write it into database
	* @access	public
	*/
	function update ()
	{
		if (empty($this->id))
		{
			return false;
		}

		$query = "UPDATE bookmarks SET ".
				 "name='".$this->name."', ".
				 "url='".$this->url."' ".
				 "WHERE usr_fk='".$this->userId."' ".
				 "AND id='".$this->id."'";

		$this->ilias->db->query($query);
		
		return true;
	}

	/**
	* @access	public
	* @param	integer		bookmark_id
	*/
	function delete ($a_id)
	{
		// delete bookmark
		$sql = "DELETE FROM bookmarks WHERE id='".$a_id."'";
		$this->ilias->db->query($sql);
	}

	/**
	* DESCRIPTION MISSING
	* get own bookmarks
	* @access	public
	* @param	string		folder
	* @return	array		bookmarks
	*/
	function getBookmarkList($a_folder = "")
	{
		//initialize array
		$bookmarks = array();
		//query
		if ($folder!="")
		{
			$w = "folder='".$a_folder."' AND ";
		}
			
		$sql = "SELECT * FROM bookmarks 
				WHERE ".$w." usr_fk='".$this->userId."'
				ORDER BY folder, pos";
		$r = $this->ilias->db->query($sql);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$bookmarks[] = array(
							"id" => $row["id"],
							"url" => "http://".$row["url"],
							"name" => $row["name"],
							"folder" => $row["folder"],
							"pos" => $row["pos"]
								);
		}

		return $bookmarks;
	}

	/**
	* get bookmarkfolders
	* @access	public
	* @return	array	bookmarks
	*/
	function getFolders()
	{
		//initialize array
		$folders = array();
		//query
		$sql = "SELECT folder FROM bookmarks 
				WHERE usr_fk='".$this->userId."'
				GROUP BY folder";
		$r = $this->ilias->db->query($sql);
		
		while ($row = $r->fetchRow())
		{
			if ($row[0] != "top")
			{
				$folders[] = array(
								"name" => $row[0]
								);
			}
		}

		return $folders;
	}

	/**
	* set id
	* @access	public
	* @param	integer
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
		return true;
	}

	/**
	* set description
	* @access	public
 	* @param	string
	*/
	function setName($a_str)
	{
		$this->name = $a_str;
		return true;
	}

	/**
	* set url
	* @access	public
	* @param	string
	* TODO: heir fehlt noch ein url-check inklusive "http://"-check
	*/
	function setURL($a_url)
	{
		$this->url = $a_url;
		return true;
	}
} // END class.Bookmarks
?>
