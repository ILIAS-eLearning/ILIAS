<?php
/**
 * user class for ilias
 * @author Sascha Hofmann <shofmann@databay.de>
 * @author Stefan Meyer <smeyer@databay.de>
 * @author Peter Gabriel <pgabriel@databay.de>
 * 
 * @package ilias-core
 * @version $Id$
 */

class User extends PEAR
{
    /**
     * User Id
     *
	 * @var integer
     */
    var $Id;					

	/**
     * Contains all Userdata
     *
     * @var array
     */
    var $data;
	
    /**
     * database handler
     *
     * @var object
     */	
    var $db;

    /**
     * Constructor
     *
     * setup an user object
     *
     * @param object		database handler
     * @param integer	user ID
     * @return void
     */

    function User(&$dbhandle, $AUsrId = "")
    {

		// Initiate variables
		$this->db =& $dbhandle;
		$this->data = array();

		if (!empty($AUsrId) and ($AUsrId > 0))
		{
		    $this->Id = $AUsrId;
		    $this->getUserdata();
		}
    }

	/**
     * loads a record "user" from database
     *
     * @access private
     * @param void
     * @return void
     */

     function getUserdata ()
	 {
		 $query = "SELECT * FROM user_data
                  LEFT JOIN rbac_ua
                  ON user_data.usr_id=rbac_ua.usr_id
                  WHERE user_data.usr_id='".$this->Id."'";	
		 
		 $res = $this->db->query($query);
		 
		 if (DB::isError($res)) {
			 die("<b>".$res->getMessage()."</b><br>Class: ".get_class($this)."<br>Script: ".__FILE__."<br>Line: ".__LINE__);
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
	  * loads a record "user" from array
	  *
	  * @access private
	  * @param array		userdata
	  * @return void	
	  */
	 function setUserdata ($AUserdata)
	 {
		 $this->data = $AUserdata;
	 }
	 
	 /**
	  * returns a 2char-language-string
	  *
	  * @access public
	  * @param void
	  * @return string language
	  */
	 function getLanguage ()
	 {
		 if ($this->data["language"] == "")
			 return "en";
		 else
			 return $this->data["language"];
	 }

	 /**
	  * saves a new record "user" to database
	  *
	  * public method
	  *
	  * @param     void
	  */
	 function saveAsNew ()
	 {
		 // fill user_data
		 $query = "INSERT INTO user_data
                 (usr_id,login,passwd,firstname,surname,title,gender,email,language,
                   last_login,last_update,create_date)
                  VALUES
                  ('".$this->data["Id"]."','".$this->data["Login"]."',
                   '".md5($this->data["Passwd"])."','".$this->data["FirstName"]."',
                   '".$this->data["SurName"]."','".$this->data["Title"]."',
                   '".$this->data["Gender"]."','".$this->data["Email"]."','".$this->data["language"]."',0,now(),now())";

		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			die ($res->getMessage());
		}
		$this->Id = $this->data["Id"];
	 }

	 /**
	  * updates a record "user" and write it into database
	  *
	  * public method
	  *
	  * @param     void
	  */
	function update ()
	{
		$this->Id = $this->data["Id"];
		// TODO: move into db-wrapper-class
		 $query = "UPDATE user_data SET
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

	 function delete ($AUsrId = "")
	 {
		 if (empty($AUsrId))
		 {
			 $id = $this->Id;
		 }
		 else
		 {
			 $id = $AUsrId;
		 }
		 
		 // delete user_account
		 $this->db->query("DELETE FROM user_data WHERE usr_id='$id'");
		 
		 // delete user-role relation
		 $this->db->query("DELETE FROM rbac_ua WHERE usr_id='$id' AND rol_id='$rol_id'");
		 
		 // delete obj_data entry
		 $this->db->query("DELETE FROM object_data WHERE obj_id='$id'");
	 }
	 
	 /**
	  * builds a string with Title + Firstname + Surname
	  *
	  * @param     string    title
	  * @param     string    firstname
	  * @param     string    surname
	  * @return    string    fullname
	  */
	 function buildFullName ($ATitle="",$AFirstName="",$ASurName="")
	 {
		 $num_args = func_num_args();
		 
		 switch ($num_args)
		 {
			 case 0:
				 if ($this->data["Title"])
				 {
					 $FullName = $this->data["Title"]." ";
				 }
				 if ($this->data["FirstName"])
				 {
					 $FullName .= $this->data["FirstName"]." ";
				 }
				 
				 $FullName .= $this->data["SurName"];				
				 break;
				 
			 case 3:
				 if ($ATitle)
				 {
					 $FullName = $ATitle." ";
				 }
				 
				 if ($AFirstName)
				 {
					 $FullName .= $AFirstName." ";
				 }
				 
				 $FullName .= $ASurName;			
				 break;
				 
			 default:
				 // Falsche Parameterzahl
				 break;		
		 }
		 
		 return $FullName;
	 }
	 
	 /**
	  * get unread mail
	  *
	  * @param void
	  * @return array mails
	  * @access public
	  */
	 function getUnreadMail()
	 {
		 global $lng;

		 //initialize array
		 $mails = array();
		 //query
		 $sql = "SELECT * FROM mails 
                 WHERE user_fk='".$this->id."'
                 AND read=0";
		 $mails[] = array(
			 "id" => 1,
			 "from" => "Peter Gabriel",
			 "email" => "pgabriel@databay.de",
			 "subject" => "Hello",
			 "body" => "This is a test mail",
			 "datetime" => $lng->formatDate(date("Y-m-d"))
			 );
		 return $mails;
	 }
	  
	 /**
	  * get last read lessons
	  *
	  * @param void
	  * @return array lessons
	  * @access public
	  */
	 function getLastVisitedLessons()
	 {
		 global $lng;

		 //initialize array
		 $lessons = array();
		 //query
		 $sql = "SELECT * FROM lessons
                 WHERE user_fk='".$this->id."'
                 AND read=1";
		 $lessons[] = array(
			 "id" => 1,
			 "title" => "Lesson 1",
			 "content" => "This is Lesson One",
			 "page" => "Contents",
			 "pageid" => "1",
			 "datetime" => $lng->formatDate(date("Y-m-d"))
			 );
		 return $lessons;
	 }

	 /**
	  * get all lessons
	  *
	  * @param void
	  * @return array lessons
	  * @access public
	  */
	 function getLessons()
	 {
		 //initialize array
		 $lessons = array();

		 
//$nodes = array();
//$tree = new Tree(0,1,1);
//$Tree = $tree->buildTree($nodes);
//$data = $tree->display($Tree,$id,0,$open);
//var_dump($Tree); 
		 //query
		 $sql = "SELECT * FROM lessons
                 WHERE user_fk='".$this->id."'
                 AND read=1";

/*		 $lessons[] = array(
			 "id" => 1,
			 "title" => "Lesson 1",
			 "content" => "This is Lesson One",
			 "page" => "Contents",
			 "pageid" => "1",
			 "datetime" => $lng->formatDate(date("Y-m-d"))
			 );
*/
		 return $lessons;
	 }

	 /**
	  * get courses the user has access to
	  *
	  * @param void
	  * @return array lessons
	  * @access public
	  */
	 function getCourses()
	 {
		 global $lng;

		 //initialize array
		 $courses = array();
		 //query
		 $sql = "SELECT * FROM courses
                 WHERE user_fk='".$this->id."'
                 AND read=1";
		 $courses[] = array(
			 "id" => 1,
			 "title" => "Course 1",
			 "desc" => "description of course one",
			 "content" => "This is Course One",
			 "datetime" => $lng->formatDate(date("Y-m-d"))
			 );
		 return $courses;
	 }

	 /**
	  * get own bookmarks
	  *
	  * @param void
	  * @return array bookmarks
	  * @access public
	  */
	 function getBookmarks()
	 {
		 //initialize array
		 $bookmarks = array();
		 //query
		 $sql = "SELECT * FROM bookmarks";

		 $bookmarks[] = array(
			 "id" => 1,
			 "url" => "http://www.gutenberg.de",
			 "desc" => "project gutenberg",
			 );
		 return $bookmarks;
	 }

	 /**
	  * get literature bookmarks
	  *
	  * @param void
	  * @return array lessons
	  * @access public
	  */
	 function getLiterature()
	 {
		 //initialize array
		 $literature = array();
		 //query
		 $sql = "SELECT * FROM literature";

		 $literature[] = array(
			 "id" => 1,
			 "url" => "http://www.gutenberg.de",
			 "desc" => "project gutenberg",
			 );
		 return $literature;
	 }

	 /**
	  * set first name
	  * @access public
	  * @param string str
	  * @return void
	  */
	 function setFirstName($str)
	 {
		 $this->data["FirstName"] = $str;
	 }

	 /**
	  * set last name
	  * @access public
	  * @param string str
	  * @return void
	  */
	 function setLastName($str)
	 {
		 $this->data["LastName"] = $str;
	 }
	 	 
	 /**
	  * set gender

  * @access public
	  * @param string str
	  * @return void
	  */
	 function setGender($str)
	 {
		 $this->data["Gender"] = $str;
	 }
	 
	 /**
	  * set title
	  * @access public
	  * @param string str
	  * @return void
	  */
	 function setTitle($str)
	 {
		 $this->data["Title"] = $str;
	 }
	 
	 /**
	  * set email
	  * @access public
	  * @param string str
	  * @return void
	  */
	 function setEmail($str)
	 {
		 $this->data["Email"] = $str;
	 }
	 
	 /**
	  * set language
	  * @access public
	  * @param string str
	  * @return void
	  */
	 function setLanguage($str)
	 {
		 $this->data["language"] = $str;
	 }
	 
} // END class user
?>