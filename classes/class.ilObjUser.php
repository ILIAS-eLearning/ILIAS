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



require_once "classes/class.ilObject.php";

/**
* user class for ilias
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @author	Stefan Meyer <smeyer@databay.de>
* @author	Peter Gabriel <pgabriel@databay.de>
* @version	$Id$
* @package	ilias-core
*/
class ilObjUser extends ilObject
{
	/**
	* all user related data in single vars
	* @access	public
	*/
	// personal data

	var $login;		// username in system
	var $passwd;	// md5 hash of password
	var $gender;	// 'm' or 'f'
	var $utitle;	// user title (keep in mind, that we derive $title from object also!)
	var $firstname;
	var $lastname;
	var $fullname;	// title + firstname + lastname in one string
	//var $archive_dir = "./image";  // point to image file (should be flexible)
 	// address data
	var $institution;
	var $street;
	var $city;
	var $zipcode;
	var $country;
	var $phone;
	var $email;
	var $hobby;


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
	* default role
	* @var		string
	* @access	private
	*/
	var $default_role;

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
	function ilObjUser($a_user_id = 0, $a_call_by_reference = false)
	{
		global $ilias;

		// init variables
		$this->ilias =& $ilias;

		$this->type = "usr";
		$this->ilObject($a_user_id, $a_call_by_reference);

		// for gender selection. don't change this
		/*$this->gender = array(
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );*/

		if (!empty($a_user_id))
		{
			$this->setId($a_user_id);
			$this->read();
		}
		else
		{
			// TODO: all code in else-structure doesn't belongs in class user !!!
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
	function read ()
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
		parent::read();
	}

	/**
	* loads a record "user" from array
	* @access	public
	* @param	array		userdata
	*/
	function assignData($a_data)
	{
		// basic personal data
		$this->setLogin($a_data["login"]);
		$this->setPasswd($a_data["passwd"]);
		$this->setGender($a_data["gender"]);
		$this->setUTitle($a_data["title"]);
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
		$this->setHobby($a_data["hobby"]);

		// system data
		$this->setLastLogin($a_data["last_login"]);
		$this->setLastUpdate($a_data["last_update"]);
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
			 "email,hobby,institution,street,city,zipcode,country,".
			 "phone,last_login,last_update,create_date) ".
			 "VALUES ".
			 "('".$this->id."','".$this->login."','".md5($this->passwd)."', ".
			 "'".$this->firstname."','".$this->lastname."', ".
			 "'".$this->utitle."','".$this->gender."', ".
			 "'".$this->email."','".$this->hobby."', ".
			 "'".$this->institution."','".$this->street."', ".
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
			 "title='".$this->utitle."', ".
			 "firstname='".$this->firstname."', ".
			 "lastname='".$this->lastname."', ".
			 "email='".$this->email."', ".
			 "hobby='".$this->hobby."', ".
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

		parent::update();

		$this->read();

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
	* @param	string	old password
	* @param	string	new password1
	* @param	string	new password2
	* @return	boolean	true on success; otherwise false
	* @access	public
	*/
	function updatePassword($a_old, $a_new1, $a_new2)
	{
		if (func_num_args() != 3)
		{
			return false;
		}

		if (!isset($a_old) or !isset($a_new1) or !isset($a_new2))
		{
			return false;
		}

		if ($a_new1 != $a_new2)
		{
			return false;
		}

		// is catched by isset() ???
		if ($a_new1 == "" || $a_old == "")
		{
			return false;
		}

		//check old password
		if (md5($a_old) != $this->passwd)
		{
			return false;
		}

		//update password
		$this->passwd = md5($a_new1);

		$q = "UPDATE usr_data SET ".
			 "passwd='".$this->passwd."' ".
			 "WHERE usr_id='".$this->id."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* reset password
	* @param	string	new password1
	* @param	string	new password2
	* @return	boolean	true on success; otherwise false
	* @access	public
	*/
	function resetPassword($a_new1, $a_new2)
	{
		if (func_num_args() != 2)
		{
			return false;
		}

		if (!isset($a_new1) or !isset($a_new2))
		{
			return false;
		}

		if ($a_new1 != $a_new2)
		{
			return false;
		}

		//update password
		$this->passwd = md5($a_new1);

		$q = "UPDATE usr_data SET ".
			 "passwd='".$this->passwd."' ".
			 "WHERE usr_id='".$this->id."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* update login name
	* @param	string	new login
	* @return	boolean	true on success; otherwise false
	* @access	public
	*/
	function updateLogin($a_login)
	{
		if (func_num_args() != 1)
		{
			return false;
		}

		if (!isset($a_login))
		{
			return false;
		}

		//update login
		$this->login = $a_login;

		$q = "UPDATE usr_data SET ".
			 "login='".$this->login."' ".
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
/*
	function selectUserpref()
	{
		$q="SELECT FROM urs_pref ".
			"WHERE usr_id='".$this->id."'";
		this->ilias->db->query($q);
		echo "Hallo World";
	}
*/
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
	//	$q = "SELECT * FROM usr_pref WHERE value='"y"'";
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->prefs[$row["keyword"]] = $row["value"];
		} // while

		return $r->numRows();
	}

// Adding new function by
// ratanatyrupp@yahoo.com
// purpose: for unsing in usr_profile.php


// End of testing purpose
//
//
	/**
	* deletes a user
	* @access	public
	* @param	integer		user_id
	*/
	function delete ()
	{
		global $rbacadmin;

		// delete user_account
		$this->ilias->db->query("DELETE FROM usr_data WHERE usr_id='".$this->getId()."'");

		// delete user_prefs
		$this->ilias->db->query("DELETE FROM usr_pref WHERE usr_id='".$this->getId()."'");

		// remove user from rbac
		$rbacadmin->removeUser($this->getId());
		
		// remove mailbox
		include_once ("classes/class.ilMailbox.php");
		$mailbox = new IlMailbox($this->getId());
		$mailbox->delete();
		
		// remove bookmarks
		// TODO: move this to class.ilBookmarkFolder
		$q = "DELETE FROM bookmark_tree WHERE tree='".$this->getId()."'";
		$this->ilias->db->query($q);

		$q = "DELETE FROM bookmark_data WHERE user_id='".$this->getId()."'";
		$this->ilias->db->query($q);

		// delete object data
		parent::delete();
		return true;
	}

	/**
	* builds a string with title + firstname + lastname
	* @access	public
	*/
	function setFullname ()
	{
		$this->fullname = "";

		if ($this->utitle)
		{
			$this->fullname = $this->utitle." ";
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
					"datetime" => date("Y-m-d")
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
			"datetime" => date("Y-m-d")
			);
		return $courses;
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
		$this->passwd = $a_str;
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
	* set user title
	* (note: don't mix up this method with setTitle() that is derived from
	* ilObject and sets the user object's title)
	* @access	public
	* @param	string	title
	*/
	function setUTitle($a_str)
	{
		$this->utitle = $a_str;
	}

	/**
	* get user title
	* (note: don't mix up this method with getTitle() that is derived from
	* ilObject and gets the user object's title)
	* @access	public
	*/
	function getUTitle()
	{
		return $this->utitle;
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
	* set hobbie
	* @access	public
	* @param	string	hobbie
	*/
	function setHobby($a_str)
	{
		$this->hobby = $a_str;
	}

	/**
	* get hobbie
	* @access	public
	*/
	function getHobby()
	{
		return $this->hobby;
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
	* set user's last login
	* @access	public
	* @param	string	login date
	*/
	function setLastLogin($a_str)
	{
		$this->last_login = $a_str;
	}

	/**
	* returns last login date
	* @access	public
	* @return	string	date
	*/
	function getLastLogin ()
	{
		 return $this->last_login;
	}

	/**
	* set last update of user data set
	* @access	public
	* @param	string	date
	*/
	function setLastUpdate($a_str)
	{
		$this->last_update = $a_str;
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
	* check user id with login name
	* @param	integer	account id
	* @access	public
	*/
	function checkUserId($AccountId)
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

	/*
	 * STATIC METHOD
	 * get the user_id of a login name
	 * @param	string login name
	 * @return  integer id of user
	 * @static
	 * @access	public
	 */
	function getUserIdByLogin($a_login)
	{
		$query = "SELECT usr_id FROM usr_data ".
			"WHERE login = '".$a_login."'";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

		return $row->usr_id ? $row->usr_id : 0;
	}

	/**
	 * STATIC METHOD
	 * get the user_id of an email address
	 * @param	string email of user
	 * @return  integer id of user
	 * @static
	 * @access	public
	 */
	function getUserIdByEmail($a_email)
	{
		$query = "SELECT usr_id FROM usr_data ".
			"WHERE email = '".$a_email."'";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
		return $row->usr_id ? $row->usr_id : 0;
	}

	/**
	 * STATIC METHOD
	 * get the user_ids which correspond a search string
	 * @param	string search string
	 * @static
	 * @access	public
	 */
	function searchUsers($a_search_str)
	{
		$query = "SELECT usr_id,login,firstname,lastname,email FROM usr_data ".
			"WHERE login LIKE '%".$a_search_str."%' ".
			"OR firstname LIKE '%".$a_search_str."%' ".
			"OR lastname LIKE '%".$a_search_str."%' ".
			"OR email LIKE '%".$a_search_str."%'";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = array(
				"usr_id"     => $row->usr_id,
				"login"      => $row->login,
				"firstname"  => $row->firstname,
				"lastname"   => $row->lastname,
				"email"      => $row->email);
		}
		return $ids ? $ids : array();
	}
} // END class ilObjUser
?>
