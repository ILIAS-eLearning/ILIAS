<?php
/**
* user class for ilias
* 
* @author Sascha Hofmann <shofmann@databay.de>
* @author Stefan Meyer <smeyer@databay.de>
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package ilias-core
*/
class User
{
	/**
	* user_id
	* @var		integer
	* @access	public
	*/
	var $Id;					

	/**
	* Contains fixed Userdata
	* @var		array
	* @access	public
	*/
	var $data;

	/**
	* Contains variable Userdata (Prefs, Settings)
	* @var		array
	* @access	public
	*/
	var $prefs;
	
	/**
	* ilias object
	* @var object ilias
	* @access private
	*/	
	var $ilias;
	
	/**
	* Constructor
	* @access	public
	* @param	integer		user_id
	*/
	function User($a_user_id = 0)
	{
		global $ilias;

		// Initiate variables
		$this->ilias =& $ilias;
		$this->data = array();

		if (!empty($a_user_id))
		{
		    $this->Id = $a_user_id;
		    $this->getUserdata();
		}
	}

	/**
	* loads a record "user" from database
	* @access private
	*/
	function getUserdata ()
	{
		$query = "SELECT * FROM user_data ".
				 "LEFT JOIN rbac_ua ON user_data.usr_id=rbac_ua.usr_id ".
				 "WHERE user_data.usr_id='".$this->Id."'";	
		$res = $this->ilias->db->query($query);
		
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
								);

			//get userpreferences from user_pref table
			$this->readPrefs();
			//set language to default if not set
			if ($this->prefs["language"] == "")
			{
				$this->prefs["language"] = $this->ilias->ini->readVariable("language","default");
			}
			
			//check skin-setting
			if ($this->prefs["skin"] != "" && 
			    file_exists($this->ilias->tplPath."/".$this->prefs["skin"]) == false)
			{
				$this->prefs["skin"] == "";
				$this->writePref("skin", "");
			}
			//set template to default if not set
			if ($this->prefs["skin"] == "")
			{
				//TODO: read it from default system settings
			 	$this->prefs["skin"] = $this->ilias->ini->readVariable("layout","defaultskin");
			}
		}
		else
		{
			 $this->ilias->raiseError("<b>Error: There is no dataset with id ".$this->Id."!</b><br>class: ".get_class($this)."<br>Script: ".__FILE__."<br>Line: ".__LINE__, $this->ilias->FATAL);
		}
	}
	
	/**
	* loads a record "user" from array
	* @access	private
	* @param	array		userdata
	*/
	function setUserdata ($a_userdata)
	{
		$this->data = $a_userdata;
	}
	
	/**
	* returns a 2char-language-string
	* @access	public
	* @return	string	language
	*/
	function getLanguage ()
	{
		 return $this->data["language"];
	}

	/**
	* saves a new record "user" to database
	* @access	public
	*/
	function saveAsNew ()
	{
		// fill user_data
		$query = "INSERT INTO user_data
				 (usr_id,login,passwd,
				 firstname,surname,
				 title,gender,
				 email,
				 last_login,last_update,create_date)
				 VALUES
				 ('".$this->data["Id"]."','".$this->data["Login"]."','".md5($this->data["Passwd"])."',
				  '".$this->data["FirstName"]."','".$this->data["SurName"]."',
				  '".$this->data["Title"]."','".$this->data["Gender"]."',
				  '".$this->data["Email"]."',
				  ',0,now(),now())";
		$res = $this->ilias->db->query($query);

		$this->Id = $this->data["Id"];
	}

	/**
	* updates a record "user" and write it into database
	* @access	public
	*/
	function update ()
	{
		$this->Id = $this->data["Id"];

		$query = "UPDATE user_data SET
				 gender='".$this->data[Gender]."',
				 title='".$this->data[Title]."',
				 firstname='".$this->data[FirstName]."',
				 surname='".$this->data[SurName]."',
				 email='".$this->data[Email]."'
				 WHERE usr_id='".$this->Id."'";
		$this->ilias->db->query($query);
		
		$this->writePrefs();
		
		$this->getUserData();

		return true;
	}

	/**
	* write userpref to user table
	* @access	private
	* @param	string	keyword
	* @param	string		value
	*/
	function writePref($a_keyword, $a_value)
	{
		//DELETE
		$sql = "DELETE FROM user_pref 
				WHERE usr_id='".$this->Id."'
				AND keyword='".$a_keyword."'";
		$r = $this->ilias->db->query($sql);

		//INSERT
		if ($a_value != "")
		{
			$sql = "INSERT INTO user_pref 
				(usr_id, keyword, value)
				VALUES
				('".$this->Id."', '".$a_keyword."', '".$a_value."')";
			$r = $this->ilias->db->query($sql);
		}
	}

	/**
	* write all userprefs
	* @access	private
	*/
	function writePrefs()
	{
		//DELETE
		$sql = "DELETE FROM user_pref 
			WHERE usr_id='".$this->Id."'";
		$r = $this->ilias->db->query($sql);

		foreach ($this->prefs as $keyword => $value)
		{
			//INSERT
			$sql = "INSERT INTO user_pref 
				(usr_id, keyword, value)
				VALUES
				('".$this->Id."', '".$keyword."', '".$value."')";
			$r = $this->ilias->db->query($sql);
		}
	}

	/**
	* set a user preference
	* @param	string	name of parameter
	* @param	string	value
	* @access	public
	*/
	function setPref($a_keyword, $a_value)
	{
		if ($a_keyword != "")
		{
			$this->prefs[$a_keyword] = $a_value;
		}
	}
	
	/**
	* get a user preference
	* @param	string	name of parameter
	* @param	string	value
	* @access	public
	*/
	function getPref($a_keyword)
	{
		return $this->prefs[$a_keyword];
	}	
	
	/**
	* get all user preferences
	* @access	private
	* @return	integer		number of preferences
	*/
	function readPrefs()
	{
		$this->prefs = array();
		
		$query = "SELECT * FROM user_pref WHERE usr_id='".$this->Id."'";	
		$res = $this->ilias->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->prefs[$row["keyword"]] = $row["value"];
		} // while	 

		return $res->numRows();
	}
	
	/**
	* deletes a user
	* @access	public
	* @param	integer		user_id
	*/
	function delete ($a_user_id = 0)
	{
		if (empty($a_user_id))
		{
			 $id = $this->Id;
		}
		else
		{
			 $id = $a_user_id;
		}
		
		// delete user_account
		$this->ilias->db->query("DELETE FROM user_data WHERE usr_id='$id'");
		
		// delete user-role relation
		$this->ilias->db->query("DELETE FROM rbac_ua WHERE usr_id='$id' AND rol_id='$rol_id'");
		
		// delete obj_data entry
		$this->ilias->db->query("DELETE FROM object_data WHERE obj_id='$id'");
	}
	
	/**
	* builds a string with Title + Firstname + Surname
	* 
	* @access	public
	* @param	string	title
	* @param	string	firstname
	* @param	string	surname
	* @return	string	fullname
	*/
	function buildFullName ($a_title = "",$a_firstname = "",$a_surname = "")
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
				if ($a_title)
				{
					$FullName = $a_title." ";
				}
				
				if ($a_firstname)
				{
					$FullName .= $a_firstname." ";
				}
				
				$FullName .= $a_surname;			
				break;
				
			default:
				// Falsche Parameterzahl
				break;		
		}
		
		return $FullName;
	}

	/**
	* get last read lessons
	* @access	public
	* @return	array	lessons
	* // TODO: query wird nicht abgeschickt!!!
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
			"datetime" => $lng->fmtDate(date("Y-m-d"))
			);
		return $lessons;
	}

	/**
	* get all lessons
	* @access	public
	* @return	array	lessons
	* // TODO: query wird nicht abgeschickt!!!
	*/
	function getLessons()
	{
		//initialize array
		$lessons = array();

		//query
		$sql = "SELECT * FROM lessons
				WHERE user_fk='".$this->id."'
				AND read=1";

/*		$lessons[] = array(
			"id" => 1,
			"title" => "Lesson 1",
			"content" => "This is Lesson One",
			"page" => "Contents",
			"pageid" => "1",
			"datetime" => $lng->fmtDate(date("Y-m-d"))
			);
*/
		return $lessons;
	}

	/**
	* get courses the user has access to
	* @access	public
	* @return	array	lessons
	* // TODO: query wird nicht abgeschickt!!!
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
			"datetime" => $lng->fmtDate(date("Y-m-d"))
			);
		return $courses;
	}

	/**
	* get own bookmarks
	* @access	public
	* @return	array	bookmarks
	* // TODO: query wird nicht abgeschickt!!!
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
	* get own bookmarks
	* @access	public
	* @return	array	bookmarks
	* // TODO: query wird nicht abgeschickt!!!
	*/
	function getBookmarkFolder()
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

	/**
	* get literature bookmarks
	* @access	public
	* @return	array	lessons
	* // TODO: query wird nicht abgeschickt!!!
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
	* @access	public
	* @param	string	str
	*/
	function setFirstName($a_str)
	{
		$this->data["FirstName"] = $a_str;
	}

	/**
	* set last name
	* @access	public
	* @param	string	str
	*/
	function setLastName($a_str)
	{
		$this->data["LastName"] = $a_str;
	}

	/**
	* set gender
	* @access	public
	* @param	string	str
	*/
	function setGender($a_str)
	{
		$this->data["Gender"] = $a_str;
	 }

	/**
	* set title
	* @access	public
	* @param	string	str
	*/
	function setTitle($a_str)
	{
		$this->data["Title"] = $a_str;
	}

	/**
	* set email
	* @access	public
	* @param	string	str
	*/
	function setEmail($a_str)
	{
		$this->data["Email"] = $a_str;
	}

	/**
	* set language
	* @access	public
	* @param	string	str
	*/
	function setLanguage($a_str)
	{
		$this->prefs["language"] = $a_str;
	}
} // END class.User
?>
