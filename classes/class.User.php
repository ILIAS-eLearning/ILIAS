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
	* all user related data in single vars
	* @access	public
	*/
	// personal data
	var $login;		// username in system
	var $passwd;	// md5 hash of password
	var $gender;	// 'm' or 'f'
	var $title;
	var $firstname;
	var $lastname;
	var $fullname;	// title + firstname + surname in one string
 	// address data
	var $institution;
	var $street;
	var $city;
	var $zipcode;
	var $country;
	var $phone;
	var $email;
	// system data
	var $id;		// internal obj_id
	var $last_login;
	var $last_update;
	var $create_date;

	/**
	* Contains variable Userdata (Prefs, Settings)
	* @var		array
	* @access	public
	*/
	var $prefs;
	
	/**
	* Contains template set
	* @var		string
	* @access	public
	*/
	var $skin;
	
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
		
		// init variables
		$this->ilias =& $ilias;

		if (!empty($a_user_id))
		{
			$this->setId($a_user_id);
			$this->getData();
		}
		else
		{
			//load default data
			$this->prefs = array();
			//language
			$this->prefs["language"] = $this->ilias->ini->readVariable("language","default");
			
			//skin and pda support
			if (strpos($_SERVER["HTTP_USER_AGENT"],"Windows CE") > 0)
			{
				$this->skin = "pda";
			}
			else
			{
			 	$this->skin = $this->ilias->ini->readVariable("layout","skin");
			}
			
			$this->prefs["skin"] = $this->skin;
			
			//style (css)
		 	$this->prefs["style"] = $this->ilias->ini->readVariable("layout","style");
		}
	}

	/**
	* loads a record "user" from database
	* @access private
	*/
	function getData ()
	{
		// TODO: fetching default role should be done in rbacadmin
		$q = "SELECT * FROM usr_data ".
			 "LEFT JOIN rbac_ua ON usr_data.usr_id=rbac_ua.usr_id ".
			 "WHERE usr_data.usr_id='".$this->id."'";	
		$r = $this->ilias->db->query($q);
		
		if ($r->numRows() > 0)
		{
			$data = $r->fetchRow(DB_FETCHMODE_ASSOC);
			
			// fill member vars in one shot
			$this->assignData($data);

			//get userpreferences from usr_pref table
			$this->readPrefs();

			//set language to default if not set
			if ($this->prefs["language"] == "")
			{
				$this->prefs["language"] = $this->oldPrefs["language"];
			}

			//check skin-setting
			if ($this->prefs["skin"] == "" || file_exists($this->ilias->tplPath."/".$this->prefs["skin"]) == false)
			{
				$this->prefs["skin"] = $this->oldPrefs["skin"];
			}

			//pda support
			if (strpos($_SERVER["HTTP_USER_AGENT"],"Windows CE") > 0)
			{
				$this->skin = "pda";
			}
			else
			{
				$this->skin = $this->prefs["skin"];
			}
			
			//check style-setting (skins could have more than one stylesheet
			if ($this->prefs["style"] == "" || file_exists($this->ilias->tplPath."/".$this->skin."/".$this->prefs["style"].".css") == false)
			{
				//load default (css)
		 		$this->prefs["style"] = $this->ilias->ini->readVariable("layout","style");
			}

		}
		else
		{
			 $this->ilias->raiseError("<b>Error: There is no dataset with id ".$this->id."!</b><br />class: ".get_class($this)."<br />Script: ".__FILE__."<br />Line: ".__LINE__, $this->ilias->FATAL);
		}
	}
	
	/**
	* set userdata
	* @access	public
	* @param	array		userdata
	*/
	function setData ($a_data)
	{
		$this->assignData($a_data);
	}

	/**
	* loads a record "user" from array
	* @access	private
	* @param	array		userdata
	*/
	function assignData($a_data)
	{
		// basic personal data
		$this->setLogin($a_data["login"]);
		$this->setPasswd($a_data["passwd"]);
		$this->setGender($a_data["gender"]);
		$this->setTitle($a_data["title"]);
		$this->setFirstname($a_data["firstname"]);
		$this->setLastname($a_data["lastname"]);
		$this->setFullname();

		// address data
		$this->setInstitution($a_data["institution"]);
		$this->setStreet($a_data["street"]);
		$this->setCity($a_data["city"]);
		$this->setZipcode($a_data["zipcode"]);
		$this->setCountry($a_data["country"]);
		$this->setPhone($a_data["phone"]);
		$this->setEmail($a_data["email"]);
		
		// system data
		$this->last_login	= $a_data["last_login"];
		$this->last_update	= $a_data["last_update"];
		$this->create_date	= $a_data["create_date"];
	}

	/**
	* TODO: drop fields last_update & create_date. redundant data in object_data!
	* saves a new record "user" to database
	* @access	public
	*/
	function saveAsNew ()
	{
		$q = "INSERT INTO usr_data ".
			 "(usr_id,login,passwd,firstname,lastname,title,gender,".
			 "email,institution,street,city,zipcode,country,".
			 "phone,last_login,last_update,create_date) ".
			 "VALUES ".
			 "('".$this->id."','".$this->login."','".$this->passwd."', ".
			 "'".$this->firstname."','".$this->lastname."', ".
			 "'".$this->title."','".$this->gender."', ".
			 "'".$this->email."','".$this->institution."','".$this->street."', ".
			 "'".$this->city."','".$this->zipcode."','".$this->country."', ".
			 "'".$this->phone."', 0, now(), now())";

		$this->ilias->db->query($q);
	}

	/**
	* updates a record "user" and write it into database
	* @access	public
	*/
	function update ()
	{
		//$this->id = $this->data["Id"];

		$q = "UPDATE usr_data SET ".
			 "gender='".$this->gender."', ".
			 "title='".$this->title."', ".
			 "firstname='".$this->firstname."', ".
			 "lastname='".$this->lastname."', ".
			 "email='".$this->email."', ".
			 "institution='".$this->institution."', ".
			 "street='".$this->street."', ".
			 "city='".$this->city."', ".
			 "zipcode='".$this->zipcode."', ".
			 "country='".$this->country."', ".
			 "phone='".$this->phone."', ".
			 "last_update=now() ".
			 "WHERE usr_id='".$this->id."'";
		$this->ilias->db->query($q);
		
		$this->writePrefs();
		
		// TODO: get rid of this call
		//$this->getUserData();

		return true;
	}
	
	/**
	* updates the login data of a "user"
	* // TODO set date with now() should be enough
	* @access	public
	*/
	function refreshLogin ()
	{
		$q = "UPDATE usr_data SET ".
			 "last_login = '".date("Y-m-d H:i:s")."' ".
			 "WHERE usr_id = '".$this->id."'";
	
		$this->ilias->db->query($q);	
	}
	
	/**
	* updates password
	* @param string
	* @param string
	* @param string
	* @access	public
	*/
	function updatePassword($old, $pw1, $pw2)
	{
		if ($pw1 != $pw2)
		{
			return false;
		}
		
		if ($pw1 == "" || $old == "")
		{
			return false;
		}
		
		//check old password
		if (md5($old) != $this->passwd)
		{
			return false;
		}
		
		//update password
		$this->passwd = md5($pw1);

		$q = "UPDATE usr_data SET ".
			 "passwd='".$this->passwd."' ".
			 "WHERE usr_id='".$this->id."'";
		$this->ilias->db->query($q);

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
		$q = "DELETE FROM usr_pref ".
			 "WHERE usr_id='".$this->id."' ".
			 "AND keyword='".$a_keyword."'";
		$this->ilias->db->query($q);

		//INSERT
		if ($a_value != "")
		{
			$q = "INSERT INTO usr_pref ".
				 "(usr_id, keyword, value) ".
				 "VALUES ".
				 "('".$this->id."', '".$a_keyword."', '".$a_value."')";
			$this->ilias->db->query($q);
		}
	}

	/**
	* write all userprefs
	* @access	private
	*/
	function writePrefs()
	{
		//DELETE
		$q = "DELETE FROM usr_pref ".
			 "WHERE usr_id='".$this->id."'";
		$this->ilias->db->query($q);

		foreach ($this->prefs as $keyword => $value)
		{
			//INSERT
			$q = "INSERT INTO usr_pref ".
				 "(usr_id, keyword, value) ".
				 "VALUES ".
				 "('".$this->id."', '".$keyword."', '".$value."')";
			$this->ilias->db->query($q);
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
		if (is_array($this->prefs))
		{
			$this->oldPrefs = $this->prefs;
		}
		
		$this->prefs = array();
		
		$q = "SELECT * FROM usr_pref WHERE usr_id='".$this->id."'";	
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->prefs[$row["keyword"]] = $row["value"];
		} // while	 

		return $r->numRows();
	}
	
	/**
	* TODO: method needs a revision! method call should be without any parameter OR with parameter, but not both!
	* deletes a user
	* @access	public
	* @param	integer		user_id
	*/
	function delete ($a_user_id = 0)
	{
		if (empty($a_user_id))
		{
			 $id = $this->id;
		}
		else
		{
			 $id = $a_user_id;
		}
		
		// delete user_account
		$this->ilias->db->query("DELETE FROM usr_data WHERE usr_id='".$id."'");
		
		// delete user-role relation
		$this->ilias->db->query("DELETE FROM rbac_ua WHERE usr_id='".$id."' AND rol_id='".$rol_id."'");
		
		// delete obj_data entry
		$this->ilias->db->query("DELETE FROM object_data WHERE obj_id='".$id."'");
	}
	
	/**
	* builds a string with title + firstname + surname
	* @access	public
	*/
	function setFullname ()
	{
		if ($this->title)
		{
			$this->fullname = $this->title." ";
		}
				
		if ($this->firstname)
		{
			$this->fullname .= $this->firstname." ";
		}
				
		$this->fullname .= $this->lastname;			
	}
	
	/**
	* get fullname
	* @access	public
	*/	
	function getFullname()
	{
		return $this->fullname;
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
		$q = "SELECT * FROM lessons ".
			 "WHERE user_fk='".$this->id."' ".
			 "AND read='1'";
				
			$lessons[] = array(
					"id" => 1,
					"title" => "Lesson 1",
					"content" => "This is Lesson One",
					"page" => "Contents",
					"pageid" => "1",
					"datetime" => Format::fmtDate(date("Y-m-d"),$lng->txt("lang_dateformat"))
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
			"datetime" => Format::fmtDate(date("Y-m-d"),$lng->txt("lang_dateformat"))
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
		$sql = "SELECT * FROM fav_data";

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
		$sql = "SELECT * FROM fav_data";

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
	* set user id
	* @param	integer
	* @access	public
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* get user id
	* @access	public
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* set login / username
	* @access	public
	* @param	string	username
	*/
	function setLogin($a_str)
	{
		$this->login = $a_str;
	}	
	
	/**
	* get login / username
	* @access	public
	*/
	function getLogin()
	{
		return $this->login;
	}

	/**
	* set password md5 encrypted
	* @access	public
	* @param	string	passwd
	*/
	function setPasswd($a_str)
	{
		$this->passwd = md5($a_str);
	}	
	
	/**
	* get password (md5 hash)
	* @access	public
	*/
	function getPasswd()
	{
		return $this->passwd;
	}

	/**
	* set gender
	* @access	public
	* @param	string	gender
	*/
	function setGender($a_str)
	{
		$this->gender = substr($a_str,-1);
	}
	
	/**
	* get gender
	* @access	public
	*/
	function getGender()
	{
		return $this->gender;
	}

	/**
	* set title
	* @access	public
	* @param	string	title
	*/
	function setTitle($a_str)
	{
		$this->title = $a_str;
	}

	/**
	* get title
	* @access	public
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* set firstname
	* @access	public
	* @param	string	firstname
	*/
	function setFirstname($a_str)
	{
		$this->firstname = $a_str;
	}

	/**
	* get firstname
	* @access	public
	*/
	function getFirstname()
	{
		return $this->firstname;
	}

	/**
	* set lastame
	* @access	public
	* @param	string	lastname
	*/
	function setLastname($a_str)
	{
		$this->lastname = $a_str;
	}
	
	/**
	* get lastname
	* @access	public
	*/
	function getLastname()
	{
		return $this->lastname;
	}
	
	/**
	* set institution
	* @access	public
	* @param	string	institution
	*/
	function setInstitution($a_str)
	{
		$this->institution = $a_str;
	}

	/**
	* get institution
	* @access	public
	*/
	function getInstitution()
	{
		return $this->institution;
	}

	/**
	* set street
	* @access	public
	* @param	string	street
	*/
	function setStreet($a_str)
	{
		$this->street = $a_str;
	}

	/**
	* get street
	* @access	public
	*/
	function getStreet()
	{
		return $this->street;
	}
	
	/**
	* set city
	* @access	public
	* @param	string	city
	*/
	function setCity($a_str)
	{
		$this->city = $a_str;
	}

	/**
	* get city
	* @access	public
	*/
	function getCity()
	{
		return $this->city;
	}

	/**
	* set zipcode
	* @access	public
	* @param	string	zipcode
	*/
	function setZipcode($a_str)
	{
		$this->zipcode = $a_str;
	}

	/**
	* get zipcode
	* @access	public
	*/
	function getZipcode()
	{
		return $this->zipcode;
	}

	/**
	* set country
	* @access	public
	* @param	string	country
	*/
	function setCountry($a_str)
	{
		$this->country = $a_str;
	}

	/**
	* get country
	* @access	public
	*/
	function getCountry()
	{
		return $this->country;
	}

	/**
	* set phone
	* @access	public
	* @param	string	phone
	*/
	function setPhone($a_str)
	{
		$this->phone = $a_str;
	}

	/**
	* get phone
	* @access	public
	*/
	function getPhone()
	{
		return $this->phone;
	}

	/**
	* set email
	* @access	public
	* @param	string	email address
	*/
	function setEmail($a_str)
	{
		$this->email = $a_str;
	}

	/**
	* get email address
	* @access	public
	*/
	function getEmail()
	{
		return $this->email;
	}

	/**
	* set user language
	* @access	public
	* @param	string	lang_key (i.e. de,en,fr,...)
	*/
	function setLanguage($a_str)
	{
		$this->prefs["language"] = $a_str;
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
	* set user skin (template set)
	* @access	public
	* @param	string	directory name of template set
	*/
	function setSkin($a_str)
	{	
		// TODO: exception handling (dir exists)
		$this->skin = $a_str;
	}
	
	/*
	* get user id by login name
	* @param	integer	account id (should be username)
	* @access	public
	*/
	function getUserId($AccountId)
	{
		$r = $this->ilias->db->query("SELECT usr_id FROM usr_data WHERE login='".$this->ilias->auth->getUsername()."'");
		//query has got a result
		if ($r->numRows() > 0)
		{
			$data = $r->fetchRow();
			$this->id = $data[0];
			return $this->id;
		}
		return false;
	}
	
} // END class.User
?>