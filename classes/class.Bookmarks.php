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
		 $query = "SELECT * FROM bookmarks
			LEFT JOIN rbac_ua
			ON user_data.usr_id=rbac_ua.usr_id
			WHERE user_data.usr_id='".$this->Id."'";	
		
		$res = $this->db->query($query);
		
		if (DB::isError($res)) {
			$this->raiseError($res->getMessage()."FILE, Line: ".__FILE__.",".__LINE__, $this->FATAL);
		 }
		 
		 if ($res->numRows() > 0)
		 {
			 $data = $res->FetchRow(DB_FETCHMODE_ASSOC);

			 $this->data = array(
				 "Id"		 => $this->Id,
				 "login"      => $data["login"],
				 "passwd"     => $data["passwd"],
				 "Gender"	 => $data["gender"],
				 "Title"      => $data["title"],
				 "FirstName"  => $data["firstname"],
				 "SurName"    => $data["surname"],
				 "Email"      => $data["email"],
				 "Role"       => $data["rol_id"],
				 "LastLogin"  => $data["last_login"],
				 "language" => $data["language"]
				 );
			 if ($this->data["language"] == "")
				 $this->data["language"] = "en";
		 }
		 else
		 {
			 die("<b>Error: There is no dataset with id ".$this->Id."!</b><br>class: ".get_class($this)."<br>Script: ".__FILE__."<br>Line: ".__LINE__);
		 }
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
                  ('".$this->data["Id"]."','".$this->data["Login"]."','".md5($this->data["Passwd"])."',
				   '".$this->data["FirstName"]."','".$this->data["SurName"]."',
			   '".$this->data["language"]."',0,now(),now())";

		$res = $this->db->query($query);

		if(DB::isError($res))
		{
			$this->raiseError($res->getMessage(), $this->FATAL);
		}
		$this->Id = $this->data["Id"];
	 }

	 /**
	  * updates a record "user" and write it into database
	  *
	  * public method
	  *
	  */
	function update ()
	{
		$this->Id = $this->data["Id"];
		// TODO: move into db-wrapper-class
		 $query = "UPDATE bookmarks SET
                  gender='".$this->data[Gender]."',
                  title='".$this->data[Title]."',
                  firstname='".$this->data[FirstName]."',
                  surname='".$this->data[SurName]."',
                  email='".$this->data[Email]."',
                  language='".$this->data[language]."'
                  WHERE usr_id='".$this->Id."'";
		 $this->db->query($query);
		 
		 $this->getUserData();
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
	*
	* @return array bookmarks
	* @access public
	*/
	function getBookmarkList()
	{
		 //initialize array
		 $bookmarks = array();
		 //query
		 $sql = "SELECT * FROM bookmarks";

		 $r = $this->db->query($sql);
	 
		 $bookmarks[] = array(
			 "id" => 1,
			 "url" => "http://www.gutenberg.de",
			 "desc" => "project gutenberg",
			 );
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
		$bookmarks = array();
		//query
		$sql = "SELECT * FROM bookmarks";

		$bookmarks[] = array(
			 "id" => 1,
			 "name" => "sonstiges",
			 );
		return $bookmarks;
	}

} // END class user
?>
