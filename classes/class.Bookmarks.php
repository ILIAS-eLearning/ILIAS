<?php
/**
 * bookmarks
*
*  * @author Peter Gabriel <pgabriel@databay.de>
 * 
 * @package ilias-core
 * @version $Id$
 */
class Bookmarks extends PEAR
{
	/**
	* User Id
	*
	* @var integer
	*/
	var $userId;

	/**
	* database handler
	*
	* @var object DB
	*/	
	var $db;
	
	/**
	* error handling
	* @var object error
	*/
	var $error_class;

    /**
     * Constructor
     *
     * @param object database handler
     * @param string UserID
     */
    function Bookmarks(&$dbhandle, $AUsrId = "")
    {
		$this->PEAR();
		$this->error_class = new ErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK,array($this->error_class,'errorHandler'));

		// Initiate variables
		$this->db =& $dbhandle;
		$this->userId = $AUsrId;
	}

	/**
     * loads a bookmark
     *
     * @access public
     */
     function getBookmark ()
	 {
		 //query
		 if ($folder!="")
		 	$w = "folder='".$folder."' AND ";
			
		 $sql = "SELECT * FROM bookmarks 
		 		WHERE ".$w." usr_fk='".$this->userId."'
				ORDER BY folder, pos";

		 $r = $this->db->query($sql);
	 	 if ($r->numRows()>0)
		 {
		 	 $row = $r->fetchRow(DB_FETCHMODE_ASSOC);
			 $bookmark = array(
				 "id" => $row["id"],
				 "url" => "http://".$row["url"],
				 "name" => $row["name"],
				 "folder" => $row["folder"],
				 "pos" => $row["pos"]
			 );
			 return $bookmark;
		 }
		 return false;
	 }

	 /**
	  * saves a bookmark
	  *
	  * public method
	  *
	  */
	 function insert()
	 {
		 // fill user_data
		 $query = "INSERT INTO bookmark
                 (usr_fk, pos, url, name)
                  VALUES
                  ('".$this->userId."','0','".$this->url."','".$this->name."')";

		$res = $this->db->query($query);

		if(DB::isError($res))
		{
			$this->raiseError($res->getMessage(), $this->FATAL);
		}
	 }

	 /**
	  * updates a record "user" and write it into database
	  *
	  * public method
	  *
	  */
	function update ()
	{
		if ($this->id == "")
			return false;

		 $query = "UPDATE bookmarks SET
                  name='".$this->name."',
                  url='".$this->url."'
                  WHERE usr_fk='".$this->userId."'
				  AND id='".$this->id."'";
		 $this->db->query($query);
		 
		 return true;
	 }

	/**
	* @param string
	*/
	 function delete ($id)
	 {
		 // delete bookmark
		$sql = "DELETE FROM bookmarks WHERE id='$id'";
		$this->db->query($sql);
	}

	/**
	* get own bookmarks
	* @param string
	* @return array bookmarks
	* @access public
	*/
	function getBookmarkList($folder="")
	{
		 //initialize array
		 $bookmarks = array();
		 //query
		 if ($folder!="")
		 	$w = "folder='".$folder."' AND ";
			
		 $sql = "SELECT * FROM bookmarks 
		 		WHERE ".$w." usr_fk='".$this->userId."'
				ORDER BY folder, pos";

		 $r = $this->db->query($sql);
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
	*
	* @return array bookmarks
	* @access public
	*/
	function getFolders()
	{
		//initialize array
		$folders = array();
		//query
		$sql = "SELECT folder FROM bookmarks 
				WHERE usr_fk='".$this->userId."'
				GROUP BY folder";

		$r = $this->db->query($sql);
		
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
	* @param int
	* @access public
	*/
	function setId($id)
	{
		$this->id = $id;
		return true;
	}

	/**
	* set description
	* @param int
	* @access public
	*/
	function setName($str)
	{
		$this->name = $str;
		return true;
	}

		/**
	* set id
	* @param string
	* @access public
	*/
	function setURL($str)
	{
		$this->url = $str;
		return true;
	}


} // END class user
?>
