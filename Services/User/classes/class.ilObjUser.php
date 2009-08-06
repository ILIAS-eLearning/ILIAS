<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define ("IL_PASSWD_PLAIN", "plain");
define ("IL_PASSWD_MD5", "md5");			// ILIAS 3 Password
define ("IL_PASSWD_CRYPT", "crypt");		// ILIAS 2 Password


require_once "classes/class.ilObject.php";
require_once 'Services/User/exceptions/class.ilUserException.php'; 

/**
* @defgroup ServicesUser Services/User
*
* User application class
*
* @author	Sascha Hofmann <saschahofmann@gmx.de>
* @author	Stefan Meyer <smeyer@databay.de>
* @author	Peter Gabriel <pgabriel@databay.de>
* @version	$Id$
*
* @ingroup ServicesUser
*/
class ilObjUser extends ilObject
{
	/**
	* all user related data in single vars
	* @access	public
	*/
	// personal data

	var $login;		// username in system

	var $passwd;	// password encoded in the format specified by $passwd_type
	var $passwd_type;
					// specifies the password format.
					// value: IL_PASSWD_PLAIN, IL_PASSWD_MD5 or IL_PASSWD_CRYPT.

					// Differences between password format in class ilObjUser and
					// in table usr_data:
					// Class ilObjUser supports three different password types
					// (plain, MD5 and CRYPT) and it uses the variables $passwd
					// and $passwd_type to store them.
					// Table usr_data supports only two different password types
					// (MD5 and CRYPT) and it uses the columns "passwd" and
					// "il2passwd" to store them.
					// The conversion between these two storage layouts is done
					// in the methods that perform SQL statements. All other
					// methods work exclusively with the $passwd and $passwd_type
					// variables.

	var $gender;	// 'm' or 'f'
	var $utitle;	// user title (keep in mind, that we derive $title from object also!)
	var $firstname;
	var $lastname;
	protected $birthday;
	var $fullname;	// title + firstname + lastname in one string
	//var $archive_dir = "./image";  // point to image file (should be flexible)
 	// address data
	var $institution;
	var $department;
	var $street;
	var $city;
	var $zipcode;
	var $country;
	var $phone_office;
	var $phone_home;
	var $phone_mobile;
	var $fax;
	var $email;
	var $hobby;
	var $matriculation;
	var $referral_comment;
	var $approve_date = null;
	var $agree_date = null;
	var $active;
	//var $ilinc_id; // unique Id for netucate ilinc service
	var $client_ip; // client ip to check before login
	var $auth_mode; // authentication mode

	var $im_icq;
	var $im_yahoo;
	var $im_msn;
	var $im_aim;
	var $im_skype;
	var $im_jabber;
	var $im_voip;

	var $delicious;
	var $latitude;
	var $longitude;
	var $loc_zoom;

	var $last_password_change_ts;
	var $login_attempts;

	var $user_defined_data = array();

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
		global $ilias,$ilDB;

		// init variables
		$this->ilias =& $ilias;
		$this->db =& $ilDB;

		$this->type = "usr";
		$this->ilObject($a_user_id, $a_call_by_reference);
		$this->auth_mode = "default";
		$this->passwd_type = IL_PASSWD_PLAIN;

		// for gender selection. don't change this
		/*$this->gender = array(
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );*/
		if ($a_user_id > 0)
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
			$this->skin = $this->ilias->ini->readVariable("layout","skin");

			$this->prefs["skin"] = $this->skin;
			$this->prefs["show_users_online"] = "y";

			//style (css)
		 	$this->prefs["style"] = $this->ilias->ini->readVariable("layout","style");
		}
	}

	/**
	* loads a record "user" from database
	* @access private
	*/
	function read()
	{
		global $ilErr, $ilDB;

		// Alex: I have removed the JOIN to rbac_ua, since there seems to be no
		// use (3.11.0 alpha)
		/*$q = "SELECT * FROM usr_data ".
			 "LEFT JOIN rbac_ua ON usr_data.usr_id=rbac_ua.usr_id ".
			 "WHERE usr_data.usr_id= ".$ilDB->quote($this->id); */
		$r = $ilDB->queryF("SELECT * FROM usr_data ".
			 "WHERE usr_id= %s", array("integer"), array($this->id));

		if ($data = $ilDB->fetchAssoc($r))
		{
			// convert password storage layout used by table usr_data into
			// storage layout used by class ilObjUser
			if ($data["passwd"] == "" && $data["i2passwd"] != "")
			{
				$data["passwd_type"] = IL_PASSWD_CRYPT;
				$data["passwd"] = $data["i2passwd"];
			}
			else
			{
				$data["passwd_type"] = IL_PASSWD_MD5;
				//$data["passwd"] = $data["passwd"]; (implicit)
			}
			unset($data["i2passw"]);

			// this assign must not be set via $this->assignData($data)
			// because this method will be called on profile updates and
			// would set this values to 0, because they arent posted from form
			$this->setLastPasswordChangeTS( $data['last_password_change'] );
			$this->setLoginAttempts( $data['login_attempts'] );


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
			include_once("./Services/Style/classes/class.ilStyleDefinition.php");
			if ($this->prefs["skin"] == "" ||
				!ilStyleDefinition::skinExists($this->prefs["skin"]))
			{
				$this->prefs["skin"] = $this->oldPrefs["skin"];
			}

			$this->skin = $this->prefs["skin"];

			//check style-setting (skins could have more than one stylesheet
			if ($this->prefs["style"] == "" ||
				!ilStyleDefinition::skinExists($this->skin, $this->prefs["style"]))
			{
				//load default (css)
		 		$this->prefs["style"] = $this->ilias->ini->readVariable("layout","style");
			}

			if (empty($this->prefs["hits_per_page"]))
			{
				$this->prefs["hits_per_page"] = 10;
			}

		}
		else
		{
			$ilErr->raiseError("<b>Error: There is no dataset with id ".
							   $this->id."!</b><br />class: ".get_class($this)."<br />Script: ".__FILE__.
							   "<br />Line: ".__LINE__, $ilErr->FATAL);
		}

		$this->readUserDefinedFields();

		parent::read();
	}

	/**
	* loads a record "user" from array
	* @access	public
	* @param	array		userdata
	*/
	function assignData($a_data)
	{
		global $ilErr, $ilDB, $lng;
		
		// basic personal data
		$this->setLogin($a_data["login"]);
		if (! $a_data["passwd_type"])
		{
			 $ilErr->raiseError("<b>Error: passwd_type missing in function assignData(). ".
								$this->id."!</b><br />class: ".get_class($this)."<br />Script: "
								.__FILE__."<br />Line: ".__LINE__, $ilErr->FATAL);
		}
		if ($a_data["passwd"] != "********" and strlen($a_data['passwd']))
		{
			$this->setPasswd($a_data["passwd"], $a_data["passwd_type"]);
		}

		$this->setGender($a_data["gender"]);
		$this->setUTitle($a_data["title"]);
		$this->setFirstname($a_data["firstname"]);
		$this->setLastname($a_data["lastname"]);
		$this->setFullname();
		if (!is_array($a_data['birthday']))
		{
			$this->setBirthday($a_data['birthday']);
		}
		else
		{
			$this->setBirthday(null);
		}
		
		// address data
		$this->setInstitution($a_data["institution"]);
		$this->setDepartment($a_data["department"]);
		$this->setStreet($a_data["street"]);
		$this->setCity($a_data["city"]);
		$this->setZipcode($a_data["zipcode"]);
		$this->setCountry($a_data["country"]);
		$this->setPhoneOffice($a_data["phone_office"]);
		$this->setPhoneHome($a_data["phone_home"]);
		$this->setPhoneMobile($a_data["phone_mobile"]);
		$this->setFax($a_data["fax"]);
		$this->setMatriculation($a_data["matriculation"]);
		$this->setEmail($a_data["email"]);
		$this->setHobby($a_data["hobby"]);
		$this->setClientIP($a_data["client_ip"]);

		// instant messenger data
		$this->setInstantMessengerId('icq',$a_data["im_icq"]);
		$this->setInstantMessengerId('yahoo',$a_data["im_yahoo"]);
		$this->setInstantMessengerId('msn',$a_data["im_msn"]);
		$this->setInstantMessengerId('aim',$a_data["im_aim"]);
		$this->setInstantMessengerId('skype',$a_data["im_skype"]);
		$this->setInstantMessengerId('jabber',$a_data["im_jabber"]);
		$this->setInstantMessengerId('voip',$a_data["im_voip"]);

		// other data
		$this->setDelicious($a_data["delicious"]);
		$this->setLatitude($a_data["latitude"]);
		$this->setLongitude($a_data["longitude"]);
		$this->setLocationZoom($a_data["loc_zoom"]);

		// system data
		$this->setLastLogin($a_data["last_login"]);
		$this->setLastUpdate($a_data["last_update"]);
		$this->create_date	= $a_data["create_date"];
        $this->setComment($a_data["referral_comment"]);
        $this->approve_date = $a_data["approve_date"];
        $this->active = $a_data["active"];
		$this->agree_date = $a_data["agree_date"];

        // time limitation
        $this->setTimeLimitOwner($a_data["time_limit_owner"]);
        $this->setTimeLimitUnlimited($a_data["time_limit_unlimited"]);
        $this->setTimeLimitFrom($a_data["time_limit_from"]);
        $this->setTimeLimitUntil($a_data["time_limit_until"]);
		$this->setTimeLimitMessage($a_data['time_limit_message']);

		// user profile incomplete?
		$this->setProfileIncomplete($a_data["profile_incomplete"]);

		//iLinc
		//$this->setiLincData($a_data['ilinc_id'],$a_data['ilinc_login'],$a_data['ilinc_passwd']);

		//authentication
		$this->setAuthMode($a_data['auth_mode']);
		$this->setExternalAccount($a_data['ext_account']);
	}

	/**
	* TODO: drop fields last_update & create_date. redundant data in object_data!
	* saves a new record "user" to database
	* @access	public
	* @param	boolean	user data from formular (addSlashes) or not (prepareDBString)
	*/
	function saveAsNew($a_from_formular = true)
	{ 
		global $ilErr, $ilDB, $ilSetting, $ilUser;

		switch ($this->passwd_type)
		{
			case IL_PASSWD_PLAIN:
				$pw_field = "passwd";
				if(strlen($this->passwd))
				{
					$pw_value = md5($this->passwd);	
				}
				else
				{
					$pw_value = $this->passwd;
				}
				break;

			case IL_PASSWD_MD5:
				$pw_field = "passwd";
				$pw_value = $this->passwd;
				break;

			case IL_PASSWD_CRYPT:
				$pw_field = "i2passwd";
				$pw_value = $this->passwd;
				break;

			default :
				 $ilErr->raiseError("<b>Error: passwd_type missing in function saveAsNew. ".
									$this->id."!</b><br />class: ".get_class($this)."<br />Script: ".__FILE__.
									"<br />Line: ".__LINE__, $ilErr->FATAL);
		}

		$insert_array = array(
			"usr_id" => array("integer", $this->id),
			"login" => array("text", $this->login),
			$pw_field => array("text", $pw_value),
			"firstname" => array("text", $this->firstname),
			"lastname" => array("text", $this->lastname),
			"title" => array("text", $this->utitle),
			"gender" => array("text", $this->gender),
			"email" => array("text", $this->email),
			"hobby" => array("text", (string) $this->hobby),
			"institution" => array("text", $this->institution),
			"department" => array("text", $this->department),
			"street" => array("text", $this->street),
			"city" => array("text", $this->city),
			"zipcode" => array("text", $this->zipcode),
			"country" => array("text", $this->country),
			"phone_office" => array("text", $this->phone_office),
			"phone_home" => array("text", $this->phone_home),
			"phone_mobile" => array("text", $this->phone_mobile),
			"fax" => array("text", $this->fax),
			"birthday" => array('date', $this->getBirthday()),
			"last_login" => array("timestamp", null),
			"last_update" => array("timestamp", ilUtil::now()),
			"create_date" => array("timestamp", ilUtil::now()),
			"referral_comment" => array("text", $this->referral_comment),
			"matriculation" => array("text", $this->matriculation),
			"client_ip" => array("text", $this->client_ip),
			"approve_date" => array("timestamp", $this->approve_date),
			"agree_date" => array("timestamp", $this->agree_date),
			"active" => array("integer", (int) $this->active),
			"time_limit_unlimited" => array("integer", $this->getTimeLimitUnlimited()),
			"time_limit_until" => array("integer", $this->getTimeLimitUntil()),
			"time_limit_from" => array("integer", $this->getTimeLimitFrom()),
			"time_limit_owner" => array("integer", $this->getTimeLimitOwner()),
			"auth_mode" => array("text", $this->getAuthMode()),
			"ext_account" => array("text", $this->getExternalAccount()),
			"profile_incomplete" => array("integer", $this->getProfileIncomplete()),
			"im_icq" => array("text", $this->im_icq),
			"im_yahoo" => array("text", $this->im_yahoo),
			"im_msn" => array("text", $this->im_msn),
			"im_aim" => array("text", $this->im_aim),
			"im_skype" => array("text", $this->im_skype),
			"delicious" => array("text", $this->delicious),
			"latitude" => array("text", $this->latitude),
			"longitude" => array("text", $this->longitude),
			"loc_zoom" => array("integer", (int) $this->loc_zoom),
			"last_password_change" => array("integer", (int) $this->last_password_change_ts),
			"im_jabber" => array("text", $this->im_jabber),
			"im_voip" => array("text", $this->im_voip)
			);
		$ilDB->insert("usr_data", $insert_array);

		// add new entry in usr_defined_data
		$this->addUserDefinedFieldEntry();
		// ... and update
		$this->updateUserDefinedFields();

		// CREATE ENTRIES FOR MAIL BOX
		include_once ("Services/Mail/classes/class.ilMailbox.php");
		$mbox = new ilMailbox($this->id);
		$mbox->createDefaultFolder();

		include_once "Services/Mail/classes/class.ilMailOptions.php";
		$mail_options = new ilMailOptions($this->id);
		$mail_options->createMailOptionsEntry();

		// create personal bookmark folder tree
		include_once "./Services/PersonalDesktop/classes/class.ilBookmarkFolder.php";
		$bmf = new ilBookmarkFolder(0, $this->id);
		$bmf->createNewBookmarkTree();

	}

	/**
	* updates a record "user" and write it into database
	* @access	public
	*/
	function update()
	{
		global $ilErr, $ilDB;

        $this->syncActive();

		$update_array = array(
			"gender" => array("text", $this->gender),
			"title" => array("text", $this->utitle),
			"firstname" => array("text", $this->firstname),
			"lastname" => array("text", $this->lastname),
			"email" => array("text", $this->email),
			"birthday" => array('date', $this->getBirthday()),
			"hobby" => array("text", $this->hobby),
			"institution" => array("text", $this->institution),
			"department" => array("text", $this->department),
			"street" => array("text", $this->street),
			"city" => array("text", $this->city),
			"zipcode" => array("text", $this->zipcode),
			"country" => array("text", $this->country),
			"phone_office" => array("text", $this->phone_office),
			"phone_home" => array("text", $this->phone_home),
			"phone_mobile" => array("text", $this->phone_mobile),
			"fax" => array("text", $this->fax),
			"referral_comment" => array("text", $this->referral_comment),
			"matriculation" => array("text", $this->matriculation),
			"client_ip" => array("text", $this->client_ip),
			"approve_date" => array("timestamp", $this->approve_date),
			"active" => array("integer", $this->active),
			"time_limit_unlimited" => array("integer", $this->getTimeLimitUnlimited()),
			"time_limit_until" => array("integer", $this->getTimeLimitUntil()),
			"time_limit_from" => array("integer", $this->getTimeLimitFrom()),
			"time_limit_owner" => array("integer", $this->getTimeLimitOwner()),
			"time_limit_message" => array("integer", $this->getTimeLimitMessage()),
			"profile_incomplete" => array("integer", $this->getProfileIncomplete()),
			"auth_mode" => array("text", $this->getAuthMode()),
			"ext_account" => array("text", $this->getExternalAccount()),
			"im_icq" => array("text", $this->im_icq),
			"im_yahoo" => array("text", $this->im_yahoo),
			"im_msn" => array("text", $this->im_msn),
			"im_aim" => array("text", $this->im_aim),
			"im_skype" => array("text", $this->im_skype),
			"delicious" => array("text", $this->delicious),
			"latitude" => array("text", $this->latitude),
			"longitude" => array("text", $this->longitude),
			"loc_zoom" => array("integer", (int) $this->loc_zoom),
			"last_password_change" => array("integer", $this->last_password_change_ts),
			"im_jabber" => array("text", $this->im_jabber),
			"im_voip" => array("text", $this->im_voip),
			"last_update" => array("timestamp", ilUtil::now())
			);
			
        if (isset($this->agree_date) && (strtotime($this->agree_date) !== false || $this->agree_date == null))
        {
            $update_array["agree_date"] = array("timestamp", $this->agree_date);
		}
		switch ($this->passwd_type)
		{
			case IL_PASSWD_PLAIN:
				if(strlen($this->passwd))
				{
					$update_array["i2passwd"] = array("text", (string) "");
					$update_array["passwd"] = array("text", (string) md5($this->passwd));
				}
				else
				{
					$update_array["i2passwd"] = array("text", (string) "");
					$update_array["passwd"] = array("text", (string) $this->passwd);
				}
				break;

			case IL_PASSWD_MD5:
				$update_array["i2passwd"] = array("text", (string) "");
				$update_array["passwd"] = array("text", (string) $this->passwd);
				break;

			case IL_PASSWD_CRYPT:
				$update_array["i2passwd"] = array("text", (string) $this->passwd);
				$update_array["passwd"] = array("text", (string) "");
				break;

			default :
				$ilErr->raiseError("<b>Error: passwd_type missing in function update()".$this->id."!</b><br />class: ".
								   get_class($this)."<br />Script: ".__FILE__."<br />Line: ".__LINE__, $ilErr->FATAL);
		}

		$ilDB->update("usr_data", $update_array, array("usr_id" => array("integer", $this->id)));

		$this->writePrefs();

		// update user defined fields
		$this->updateUserDefinedFields();

		parent::update();
        parent::updateOwner();

		$this->read();

		return true;
	}

	/**
	* write accept date of user agreement to db
	*/
	function writeAccepted()
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE usr_data SET agree_date = ".$ilDB->now().
			 " WHERE usr_id = %s", array("integer"), array($this->getId()));
	}

	/**
	* Private function for lookup methods
	*/
	private function _lookup($a_user_id, $a_field)
	{
		global $ilDB;
		
		$res = $ilDB->queryF("SELECT ".$a_field." FROM usr_data WHERE usr_id = %s",
			array("integer"), array($a_user_id));

		while($set = $ilDB->fetchAssoc($res))
		{
			return $set[$a_field];
		}
		return false;
	}
	
	/**
	* Lookup Full Name
	*/
	function _lookupFullname($a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->queryF("SELECT title, firstname, lastname FROM usr_data WHERE usr_id = %s",
			array("integer"), array($a_user_id));

		if ($rec = $ilDB->fetchAssoc($set))
		{
			if ($rec["title"])
			{
				$fullname = $rec["title"]." ";
			}
			if ($rec["firstname"])
			{
				$fullname .= $rec["firstname"]." ";
			}
			if ($rec["lastname"])
			{
				$fullname .= $rec["lastname"];
			}
		}
		return $fullname;
	}
	
	/**
	* Lookup IM
	*/
	function _lookupIm($a_user, $a_type)
	{
		return ilObjUser::_lookup($a_user_id, "im_".$a_type);
	}
	
	
	/**
	* Lookup email
	*/
	function _lookupEmail($a_user_id)
	{
		return ilObjUser::_lookup($a_user_id, "email");
	}

	/**
	* Lookup gender
	*/
	function _lookupGender($a_user_id)
	{
		return ilObjUser::_lookup($a_user_id, "gender");
	}

	/**
	* Lookup client ip
	*
	* @param	int		user id
	* @return	string	client ip
	*/
	function _lookupClientIP($a_user_id)
	{
		return ilObjUser::_lookup($a_user_id, "client_ip");
	}


	/**
	* lookup user name
	*/
	function _lookupName($a_user_id)
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT firstname, lastname, title, login FROM usr_data WHERE usr_id = %s",
			array("integer"), array($a_user_id));
		$user_rec = $ilDB->fetchAssoc($res);
		return array("user_id" => $a_user_id,
			"firstname" => $user_rec["firstname"],
			"lastname" => $user_rec["lastname"],
			"title" => $user_rec["title"],
			"login" => $user_rec["login"]);
	}

	/**
	* lookup fields (deprecated; use more specific methods instead)
	*/
	function _lookupFields($a_user_id)
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT * FROM usr_data WHERE usr_id = %s",
			array("integer"), array($a_user_id));
		$user_rec = $ilDB->fetchAssoc($res);
		return $user_rec;
	}

	/**
	* lookup login
	*/
	function _lookupLogin($a_user_id)
	{
		return ilObjUser::_lookup($a_user_id, "login");
	}

	/**
	* lookup external account for login and authmethod
	*/
	function _lookupExternalAccount($a_user_id)
	{
		return ilObjUser::_lookup($a_user_id, "ext_account");
	}

	/**
	* lookup id by login
	*/
	public static function _lookupId($a_user_str)
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT usr_id FROM usr_data WHERE login = %s",
			array("text"), array($a_user_str));
		$user_rec = $ilDB->fetchAssoc($res);
		return $user_rec["usr_id"];
	}

	/**
	* lookup last login
	*/
	function _lookupLastLogin($a_user_id)
	{
		return ilObjUser::_lookup($a_user_id, "last_login");
	}


	/**
	* updates the login data of a "user"
	* // TODO set date with now() should be enough
	* @access	public
	*/
	function refreshLogin()
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE usr_data SET ".
			 "last_login = ".$ilDB->now().
			 " WHERE usr_id = %s",
			 array("integer"), array($this->id));
	}

	/**
	* replaces password with new md5 hash
	* @param	string	new password as md5
	* @return	boolean	true on success; otherwise false
	* @access	public
	*/
	function replacePassword($new_md5)
	{
		global $ilDB;

		$this->passwd_type = IL_PASSWD_MD5;
		$this->passwd = $new_md5;

		$ilDB->manipulateF("UPDATE usr_data SET ".
			 "passwd = %s ".
			 "WHERE usr_id = %s",
			 array("text", "integer"), array($this->passwd, $this->id));

		return true;
	}

	/**
	* updates password
	* @param	string	old password as plaintext
	* @param	string	new password1 as plaintext
	* @param	string	new password2 as plaintext
	* @return	boolean	true on success; otherwise false
	* @access	public
	*/
	function updatePassword($a_old, $a_new1, $a_new2)
	{
		global $ilDB;

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
		switch ($this->passwd_type)
		{
			case IL_PASSWD_PLAIN:
				if ($a_old != $this->passwd)
				{
					return false;
				}
				break;

			case IL_PASSWD_MD5:
				if (md5($a_old) != $this->passwd)
				{
					return false;
				}
				break;

			case IL_PASSWD_CRYPT:
				if (_makeIlias2Password($a_old) != $this->passwd)
				{
					return false;
				}
				break;
		}

		//update password
		$this->passwd = md5($a_new1);
		$this->passwd_type = IL_PASSWD_MD5;

		$ilDB->manipulateF("UPDATE usr_data SET ".
			 "passwd = %s ".
			 "WHERE usr_id = %s",
			 array("text", "integer"), array($this->passwd, $this->id));

		return true;
	}

	/**
	* reset password
	* @param	string	new password1 as plaintext
	* @param	string	new password2 as plaintext
	* @return	boolean	true on success; otherwise false
	* @access	public
	*/
	function resetPassword($a_new1, $a_new2)
	{
		global $ilDB;

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
		$this->passwd_type = IL_PASSWD_MD5;

		$ilDB->manipulateF("UPDATE usr_data SET ".
			 "passwd = %s ".
			 "WHERE usr_id = %s",
			 array("text", "integer"),
			 array($this->passwd, $this->id));

		return true;
	}

	/**
	* get encrypted Ilias 2 password (needed for imported ilias 2 users)
	*/
	function _makeIlias2Password($a_passwd)
	{
		return (crypt($a_passwd,substr($a_passwd,0,2)));
	}

	/**
	* check if user has ilias 2 password (imported user)
	*/
	function _lookupHasIlias2Password($a_user_login)
	{
		global $ilias, $ilDB;

		$user_set = $ilDB->queryF("SELECT i2passwd FROM usr_data ".
			 "WHERE login = %s", array("text"), array($a_user_login));
		if ($user_rec = $ilDB->fetchAssoc($user_set))
		{
			if ($user_rec["i2passwd"] != "")
			{
				return true;
			}
		}

		return false;
	}

	/**
	* check if user has ilias 2 password (imported user)
	*/
	function _switchToIlias3Password($a_user, $a_pw)
	{
		global $ilias, $ilDB;

		$user_set = $ilDB->queryF("SELECT i2passwd FROM usr_data ".
			 "WHERE login = %s", array("text"), array($a_user_login));
		if ($user_rec = $ilDB->fetchAssoc($user_set))
		{
			if ($user_rec["i2passwd"] == ilObjUser::_makeIlias2Password($a_pw))
			{
				$ilDB->manipulateF("UPDATE usr_data SET passwd = %s, i2passwd = %s".
					"WHERE login = %s",
					array("text", "text", "text"),
					array(md5($a_pw), "", $a_user));
				return true;
			}
		}

		return false;
	}
	
	/**
	* Get login history
	*/
	function getLoginHistory($a_login)
	{
		global $ilDB;
			
//		return false; // Temporarily disabled (missing oracle support) 

		$result = $ilDB->queryF('
			SELECT * FROM loginname_history
			WHERE login = %s',
			array('text'), array($a_login));
		
		return $ilDB->fetchAssoc($result) ? true : false;
	}	
	
	/**
	* update login name
	* @param	string	new login
	* @return	boolean	true on success; otherwise false
	* @access	public
	* @throws ilUserException
	*/
	function updateLogin($a_login)
	{
		global $ilDB, $ilSetting;

		if(func_num_args() != 1)
		{
			return false;
		}

		if(!isset($a_login))
		{
			return false;
		}

		// Update not necessary
		if($a_login == self::_lookupLogin($this->getId()))
		{
			return false;
		}		
	
		//check if loginname exists in history
		$login_exists_in_history = $this->getLoginHistory($a_login);		

		if($ilSetting->get('create_history_loginname')== 1 &&
		   $ilSetting->get('allow_history_loginname_again') == 0 &&
		   $login_exists_in_history == 1)
		{
			throw new ilUserException($this->lng->txt('loginname_already_exists'));
		}
		else 			
		{			
			if($ilSetting->get('create_history_loginname') == 1)
			{
				ilObjUser::_writeHistory($this->getId(), self::_lookupLogin($this->getId()));	
			}

			//update login
			$this->login = $a_login;

			$res = $ilDB->manipulateF('
				UPDATE usr_data
				SET login = %s
				WHERE usr_id = %s',
				array('text', 'integer'), array($this->getLogin(), $this->getId()));
			
		}
		
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
		ilObjUser::_writePref($this->id, $a_keyword, $a_value);
		$this->setPref($a_keyword, $a_value);
	}


	/**
	* Deletes a userpref value of the user from the database
	* @access	public
	* @param	string	keyword
	*/
	function deletePref($a_keyword)
	{
		ilObjUser::_deletePref($this->getId(), $a_keyword);
	}

	/**
	* Deletes a userpref value of the user from the database
	* @access	public
	* @param	string	keyword
	*/
	function _deletePref($a_user_id, $a_keyword)
	{
		global $ilDB;

		$ilDB->manipulateF("DELETE FROM usr_pref WHERE usr_id = %s AND keyword = %s",
			array("integer", "text"), array($a_user_id, $a_keyword));
	}

	/**
	* Deletes a userpref value of the user from the database
	* @access	public
	* @param	string	keyword
	*/
	function _deleteAllPref($a_user_id)
	{
		global $ilDB;

		$ilDB->manipulateF("DELETE FROM usr_pref WHERE usr_id = %s",
			array("integer"), array($a_user_id));
	}

	/**
	* Write preference
	*/
	function _writePref($a_usr_id, $a_keyword, $a_value)
	{
		global $ilDB;

		ilObjUser::_deletePref($a_usr_id, $a_keyword);
		if (strlen($a_value))
		{
			$ilDB->manipulateF("INSERT INTO usr_pref (usr_id, keyword, value) VALUES (%s,%s,%s)",
				array("integer", "text", "text"), array($a_usr_id, $a_keyword, $a_value));
		}
	}

	/**
	* write all userprefs
	* @access	private
	*/
	function writePrefs()
	{
		global $ilDB;

		ilObjUser::_deleteAllPref($this->id);
		foreach ($this->prefs as $keyword => $value)
		{
			ilObjUser::_writePref($this->id, $keyword, $value);
		}
	}

	/**
	 * get timezone of user
	 *
	 * @access public
	 *
	 */
	public function getTimeZone()
	{
	 	if($tz = $this->getPref('user_tz'))
	 	{
	 		return $tz;
	 	}
	 	else
	 	{
	 		include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
	 		$settings = ilCalendarSettings::_getInstance();
	 		return $settings->getDefaultTimeZone();
	 	}
	}

	/**
	 * get time format
	 *
	 * @access public
	 * @return
	 */
	public function getTimeFormat()
	{
	 	if($format = $this->getPref('time_format'))
	 	{
	 		return $format;
	 	}
	 	else
	 	{
	 		include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
	 		$settings = ilCalendarSettings::_getInstance();
	 		return $settings->getDefaultTimeFormat();
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
		if (array_key_exists($a_keyword, $this->prefs))
		{
			return $this->prefs[$a_keyword];
		}
		else
		{
			return FALSE;
		}
	}

	function _lookupPref($a_usr_id,$a_keyword)
	{
		global $ilDB;

		$query = "SELECT * FROM usr_pref WHERE usr_id = ".$ilDB->quote($a_usr_id, "integer")." ".
			"AND keyword = ".$ilDB->quote($a_keyword, "text");
		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->value;
		}
		return false;
	}

	/**
	* get all user preferences
	* @access	private
	* @return	integer		number of preferences
	*/
	function readPrefs()
	{
		global $ilDB;

		if (is_array($this->prefs))
		{
			$this->oldPrefs = $this->prefs;
		}

		$this->prefs = ilObjUser::_getPreferences($this->id);
		return count($prefs);
	}

	/**
	* deletes a user
	* @access	public
	* @param	integer		user_id
	*/
	function delete()
	{
		global $rbacadmin, $ilDB;

		// deassign from ldap groups
		include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
		$mapping = ilLDAPRoleGroupMapping::_getInstance();
		$mapping->deleteUser($this->getId());

		// remove mailbox / update sent mails
		include_once ("Services/Mail/classes/class.ilMailbox.php");
		$mailbox = new ilMailbox($this->getId());
		$mailbox->delete();
		$mailbox->updateMailsOfDeletedUser();

		// delete feed blocks on personal desktop
		include_once("./Services/Block/classes/class.ilCustomBlock.php");
		$costum_block = new ilCustomBlock();
		$costum_block->setContextObjId($this->getId());
		$costum_block->setContextObjType("user");
		$c_blocks = $costum_block->queryBlocksForContext();
		include_once("./Services/Feeds/classes/class.ilPDExternalFeedBlock.php");
		foreach($c_blocks as $c_block)
		{
			if ($c_block["type"] == "pdfeed")
			{
				$fb = new ilPDExternalFeedBlock($c_block["id"]);
				$fb->delete();
			}
		}


		// delete block settings
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		ilBlockSetting::_deleteSettingsOfUser($this->getId());

		// delete user_account
		$ilDB->manipulateF("DELETE FROM usr_data WHERE usr_id = %s",
			array("integer"), array($this->getId()));

		// delete user_prefs
		ilObjUser::_deleteAllPref($this->getId());

		// delete user_session
		include_once("./Services/Authentication/classes/class.ilSession.php");
		ilSession::_destroyByUserId($this->getId());

		// remove user from rbac
		$rbacadmin->removeUser($this->getId());

		// remove bookmarks
		// TODO: move this to class.ilBookmarkFolder
		$q = "DELETE FROM bookmark_tree WHERE tree = ".
			$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($q);

		$q = "DELETE FROM bookmark_data WHERE user_id = ".
			$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($q);

		// DELETE FORUM ENTRIES (not complete in the moment)
		include_once './Modules/Forum/classes/class.ilObjForum.php';
		ilObjForum::_deleteUser($this->getId());

		// Delete link check notify entries
		include_once './classes/class.ilLinkCheckNotify.php';
		ilLinkCheckNotify::_deleteUser($this->getId());

		// Delete crs entries
		include_once './Modules/Course/classes/class.ilObjCourse.php';
		ilObjCourse::_deleteUser($this->getId());

		// Delete user tracking
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		ilObjUserTracking::_deleteUser($this->getId());

		include_once 'Modules/Session/classes/class.ilEventParticipants.php';
		ilEventParticipants::_deleteByUser($this->getId());

		// Delete user defined field entries
		$this->deleteUserDefinedFieldEntries();
		
		// Delete clipboard entries
		$this->clipboardDeleteAll();

		// delete object data
		parent::delete();
		return true;
	}

	/**
	* builds a string with title + firstname + lastname
	* method is used to build fullname in member variable $this->fullname. But you
	* may use the function in static manner.
	* @access	static
	* @param	string	title (opt.)
	* @param	string	firstname (opt.)
	* @param	string	lastname (opt.)
	*/
	function setFullname($a_title = "",$a_firstname = "",$a_lastname = "")
	{
		$this->fullname = "";

		if ($a_title)
		{
			$fullname = $a_title." ";
		}
		elseif ($this->utitle)
		{
			$this->fullname = $this->utitle." ";
		}

		if ($a_firstname)
		{
			$fullname .= $a_firstname." ";
		}
		elseif ($this->firstname)
		{
			$this->fullname .= $this->firstname." ";
		}

		if ($a_lastname)
		{
			return $fullname.$a_lastname;
		}

		$this->fullname .= $this->lastname;
	}

	/**
	* get fullname
	* @access	public
	* @param	integer	max. string length to return (optional)
	* 			if string length of fullname is greater than given a_max_strlen
	* 			the name is shortened in the following way:
	* 			1. abreviate firstname (-> Dr. J. Smith)
	* 			if fullname is still too long
	* 			2. drop title (-> John Smith)
	* 			if fullname is still too long
	* 			3. drop title and abreviate first name (J. Smith)
	* 			if fullname is still too long
	* 			4. drop title and firstname and shorten lastname to max length (--> Smith)
	*/
	function getFullname($a_max_strlen = 0)
	{
		if (!$a_max_strlen)
		{
			return ilUtil::stripSlashes($this->fullname);
		}

		if (strlen($this->fullname) <= $a_max_strlen)
		{
			return ilUtil::stripSlashes($this->fullname);
		}

		if ((strlen($this->utitle) + strlen($this->lastname) + 4) <= $a_max_strlen)
		{
			return ilUtil::stripSlashes($this->utitle." ".substr($this->firstname,0,1).". ".$this->lastname);
		}

		if ((strlen($this->firstname) + strlen($this->lastname) + 1) <= $a_max_strlen)
		{
			return ilUtil::stripSlashes($this->firstname." ".$this->lastname);
		}

		if ((strlen($this->lastname) + 3) <= $a_max_strlen)
		{
			return ilUtil::stripSlashes(substr($this->firstname,0,1).". ".$this->lastname);
		}

		return ilUtil::stripSlashes(substr($this->lastname,0,$a_max_strlen));
	}

// ### AA 03.09.01 updated page access logger ###
	/**
	* get read lessons, ordered by timestamp
	* @access	public
	* @return	array	lessons
	*/
	function getLastVisitedLessons()
	{
		global $ilDB;

		//query
		$q = "SELECT * FROM lo_access ".
			"WHERE usr_id= ".$ilDB->quote((int) $this->id, "integer")." ".
			"ORDER BY timestamp DESC";
		$rst = $ilDB->query($q);

		// fill array
		$result = array();
		while($record = $ilDB->fetchObject($rst))
		{
			$result[] = array(
			"timestamp"	=>	$record->timestamp,
			"usr_id"		=>	$record->usr_id,
			"lm_id"		=>	$record->lm_id,
			"obj_id"		=>	$record->obj_id,
			"lm_title"	=>	$record->lm_title);
		}
		return $result;
	}

// ### AA 03.09.01 updated page access logger ###
	/**
	* get all lessons, unordered
	* @access	public
	* @return	array	lessons
	*/
	function getLessons()
	{
		global $ilDB;

		//query
		$q = "SELECT * FROM lo_access ".
			"WHERE usr_id= ".$ilDB->quote((int) $this->id, "integer")." ";
		$rst = $ilDB->query($q);

		// fill array
		$result = array();
		while($record = $rst->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result[] = array(
			"timestamp"	=>	$record->timestamp,
			"usr_id"		=>	$record->usr_id,
			"lm_id"		=>	$record->lm_id,
			"obj_id"		=>	$record->obj_id,
			"lm_title"	=>	$record->lm_title);
		}
		return $result;
	}

	/**
	 * Check if user has accepted the agreement
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _hasAcceptedAgreement($a_username)
	{
		global $ilDB;

		if($a_username == 'root')
		{
			return true;
		}

		$res = $ilDB->queryF("SELECT usr_id FROM usr_data ".
			"WHERE login = %s AND NOT agree_date IS NULL",
			array("text"), array($a_username));
		return $ilDB->fetchAssoc($res) ? true : false;
	}


	/**
	* check wether user has accepted user agreement
	*/
	function hasAcceptedUserAgreement()
	{
		if ($this->agree_date != null || $this->login == "root")
		{
			return true;
		}
		return false;
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
	* set password
	* @access	public
	* @param	string	passwd
	*/
	function setPasswd($a_str, $a_type = IL_PASSWD_PLAIN)
	{
		$this->passwd = $a_str;
		$this->passwd_type = $a_type;
	}

	/**
	* get password
	* @return password. The password is encoded depending on the current
    *                   password type.
	* @access	public
	* @see getPasswdType
	*/
	function getPasswd()
	{
		return $this->passwd;
	}
	/**
	* get password type
	* @return password type (IL_PASSWD_PLAIN, IL_PASSWD_MD5 or IL_PASSWD_CRYPT).
	* @access	public
	* @see getPasswd
	*/
	function getPasswdType()
	{
		return $this->passwd_type;
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
	* set department
	* @access	public
	* @param	string	department
	*/
	function setDepartment($a_str)
	{
		$this->department = $a_str;
	}

	/**
	* get department
	* @access	public
	*/
	function getDepartment()
	{
		return $this->department;
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
	* set office phone
	* @access	public
	* @param	string	office phone
	*/
	function setPhoneOffice($a_str)
	{
		$this->phone_office = $a_str;
	}

	/**
	* get office phone
	* @access	public
	*/
	function getPhoneOffice()
	{
		return $this->phone_office;
	}

	/**
	* set home phone
	* @access	public
	* @param	string	home phone
	*/
	function setPhoneHome($a_str)
	{
		$this->phone_home = $a_str;
	}

	/**
	* get home phone
	* @access	public
	*/
	function getPhoneHome()
	{
		return $this->phone_home;
	}

	/**
	* set mobile phone
	* @access	public
	* @param	string	mobile phone
	*/
	function setPhoneMobile($a_str)
	{
		$this->phone_mobile = $a_str;
	}

	/**
	* get mobile phone
	* @access	public
	*/
	function getPhoneMobile()
	{
		return $this->phone_mobile;
	}

	/**
	* set fax
	* @access	public
	* @param	string	fax
	*/
	function setFax($a_str)
	{
		$this->fax = $a_str;
	}

	/**
	* get fax
	* @access	public
	*/
	function getFax()
	{
		return $this->fax;
	}

	/**
	* set client ip number
	* @access	public
	* @param	string	client ip
	*/
	function setClientIP($a_str)
	{
		$this->client_ip = $a_str;
	}

	/**
	* get client ip number
	* @access	public
	*/
	function getClientIP()
	{
		return $this->client_ip;
	}

	/**
	* set matriculation number
	* @access	public
	* @param	string	matriculation number
	*/
	function setMatriculation($a_str)
	{
		$this->matriculation = $a_str;
	}

	/**
	* get matriculation number
	* @access	public
	*/
	function getMatriculation()
	{
		return $this->matriculation;
	}

	/**
	 * Lookup matriculation
	 * @return string matricualtion
	 * @param int $a_usr_id
	 * @access public
	 */
	public static function lookupMatriculation($a_usr_id)
	{
		global $ilDB;
		
		$query = "SELECT matriculation FROM usr_data ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id);
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->matricultation ? $row->matriculation : '';
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
	* set hobby
	* @access	public
    * @param    string  hobby
	*/
	function setHobby($a_str)
	{
		$this->hobby = $a_str;
	}

	/**
    * get hobby
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
		$this->setPref("language",$a_str);
		unset($_SESSION['lang']);
	}

	/**
	* returns a 2char-language-string
	* @access	public
	* @return	string	language
	*/
	function getLanguage()
	{
		 return $this->prefs["language"];
	}

	/**
	* Sets the minimal disk quota imposed by this user account.
    *
    * The minimal disk quota is specified in bytes.
	 *
	* @access	public
	* @param	integer
	*/
	function setDiskQuota($a_disk_quota)
	{
		$this->setPref("disk_quota",$a_disk_quota);
	}

	/**
	* Returns the minimal disk quota imposed by this user account.
    *
    * The minimal disk quota is specified in bytes.
	* The default value is 0.
    *
	* @access	public
	* @return	integer
	*/
	function getDiskQuota()
	{
		 return $this->prefs["disk_quota"] ? $this->prefs["disk_quota"] : 0;
	}

	public function setLastPasswordChangeTS($a_last_password_change_ts)
	{
		$this->last_password_change_ts = $a_last_password_change_ts;
	}

	public function getLastPasswordChangeTS()
	{
		return $this->last_password_change_ts;
	}


	function _lookupLanguage($a_usr_id)
	{
		global $ilDB;

		$q = "SELECT value FROM usr_pref WHERE usr_id= ".
			$ilDB->quote($a_usr_id, "integer")." AND keyword = ".
			$ilDB->quote('language', "text");
		$r = $ilDB->query($q);

		while($row = $ilDB->fetchAssoc($r))
		{
			return $row['value'];
		}
		return 'en';
	}


	function _checkPassword($a_usr_id, $a_pw)
	{
		global $ilDB;

		$pw = ilObjUser::_lookup($a_usr_id, "passwd");
		if ($pw == md5($a_pw))
		{
			return true;
		}
		return false;
	}

	function _writeExternalAccount($a_usr_id, $a_ext_id)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE usr_data ".
			" SET ext_account = %s WHERE usr_id = %s",
			array("text", "integer"),
			array($a_ext_id, $a_usr_id));
	}

	function _writeAuthMode($a_usr_id, $a_auth_mode)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE usr_data ".
			" SET auth_mode = %s WHERE usr_id = %s",
			array("text", "integer"),
			array($a_auth_mode, $a_usr_id));
	}

	/**
	 * returns the current language (may differ from user's pref setting!)
	 *
	 */
	function getCurrentLanguage()
	{
		return $_SESSION['lang'];
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
	function getLastLogin()
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
	function getLastUpdate()
	{
		return $this->last_update;
	}

    /**
    * set referral comment
    * @access   public
    * @param    string  hobby
    */
    function setComment($a_str)
    {
        $this->referral_comment = $a_str;
    }

    /**
    * get referral comment
    * @access   public
    */
    function getComment()
    {
        return $this->referral_comment;
    }

    /**
    * set date the user account was activated
    * null indicates that the user has not yet been activated
    * @access   public
    * @return   void
    */
    function setApproveDate($a_str)
    {
        $this->approve_date = $a_str;
    }

    /**
    * get the date when the user account was approved
    * @access   public
    * @return   string      approve date
    */
    function getApproveDate()
    {
        return $this->approve_date;
    }

	// BEGIN DiskQuota: show when user accepted user agreement
    /**
    * get the date when the user accepted the user agreement
    * @access   public
    * @return   string      date of last update
    */
    function getAgreeDate()
    {
        return $this->agree_date;
    }
    /**
    * set date the user account was accepted by the user
    * nullindicates that the user has not accepted his account
    * @access   public
    * @return   void
    */
    function setAgreeDate($a_str)
    {
        $this->agree_date = $a_str;
    }
	// END DiskQuota: show when user accepted user agreement

    /**
    * set user active state and updates system fields appropriately
    * @access   public
    * @param    string  $a_active the active state of the user account
    * @param    string  $a_owner the id of the person who approved the account, defaults to 6 (root)
    */
    function setActive($a_active, $a_owner = 6)
    {
        if (empty($a_owner))
        {
            $a_owner = 0;
        }

        if ($a_active)
        {
            $this->active = 1;
            $this->setApproveDate(date('Y-m-d H:i:s'));
            $this->setOwner($a_owner);
        }
        else
        {
            $this->active = 0;
            $this->setApproveDate(null);
            $this->setOwner(0);
        }
    }

    /**
    * get user active state
    * @access   public
    */
    function getActive()
    {
        return $this->active;
    }

    /**
    * synchronizes current and stored user active values
    * for the owner value to be set correctly, this function should only be called when an admin is approving a user account
    * @access  public
    */
    function syncActive()
    {
        global $ilAuth;

        $storedActive   = 0;
        if ($this->getStoredActive($this->id))
        {
            $storedActive   = 1;
        }

        $currentActive  = 0;
        if ($this->active)
        {
            $currentActive  = 1;
        }

        if ((!empty($storedActive) && empty($currentActive)) ||
                (empty($storedActive) && !empty($currentActive)))
        {
            $this->setActive($currentActive, $this->getUserIdByLogin(ilObjUser::getLoginFromAuth()));
        }
    }

    /**
    * get user active state
    * @param   integer $a_id user id
    * @access  public
    * @return  true if active, otherwise false
    */
    function getStoredActive($a_id)
    {
		$active = ilObjUser::_lookup($a_id, "active");
        return $active ? true : false;
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

    function setTimeLimitOwner($a_owner)
    {
        $this->time_limit_owner = $a_owner;
    }
    function getTimeLimitOwner()
    {
        return $this->time_limit_owner ? $this->time_limit_owner : 7;
    }
    function setTimeLimitFrom($a_from)
    {
        $this->time_limit_from = $a_from;
    }
    function getTimeLimitFrom()
    {
        return $this->time_limit_from ? $this->time_limit_from : time();
    }
    function setTimeLimitUntil($a_until)
    {
        $this->time_limit_until = $a_until;
    }
    function getTimeLimitUntil()
    {
        return $this->time_limit_until ? $this->time_limit_until : time();
    }
    function setTimeLimitUnlimited($a_unlimited)
    {
        $this->time_limit_unlimited = $a_unlimited;
    }
    function getTimeLimitUnlimited()
    {
        return $this->time_limit_unlimited;
    }
	function setTimeLimitMessage($a_time_limit_message)
	{
		return $this->time_limit_message = $a_time_limit_message;
	}
	function getTimeLimitMessage()
	{
		return $this->time_limit_message;
	}

	public function setLoginAttempts($a_login_attempts)
	{
		$this->login_attempts = $a_login_attempts;
	}

	public function getLoginAttempts()
	{
		return $this->login_attempts;
	}


	function checkTimeLimit()
	{
		if($this->getTimeLimitUnlimited())
		{
			return true;
		}
		if($this->getTimeLimitFrom() < time() and $this->getTimeLimitUntil() > time())
		{
			return true;
		}
		return false;
	}
    function setProfileIncomplete($a_prof_inc)
    {
        $this->profile_incomplete = (boolean) $a_prof_inc;
    }
    function getProfileIncomplete()
    {
        return $this->profile_incomplete;
    }

    public function isPasswordChangeDemanded()
    {
		//error_reporting(E_ALL);
		if( $this->id == ANONYMOUS_USER_ID || $this->id == SYSTEM_USER_ID )
			return false;

    	require_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
    	$security = ilSecuritySettings::_getInstance();
    	if( $security->isPasswordChangeOnFirstLoginEnabled() &&
    		$this->getLastPasswordChangeTS() == 0 )
    	{
			return true;
     	}
    	return false;
    }

    public function isPasswordExpired()
    {
		//error_reporting(E_ALL);
		if($this->id == ANONYMOUS_USER_ID) return false;

    	require_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
    	$security = ilSecuritySettings::_getInstance();
    	if( $security->getAccountSecurityMode() == ilSecuritySettings::ACCOUNT_SECURITY_MODE_CUSTOMIZED &&
    		$this->getLastPasswordChangeTS() > 0 )
    	{
    		$max_pass_age = $security->getPasswordMaxAge();
    		if( $max_pass_age > 0 )
    		{
	    		$max_pass_age_ts = ( $max_pass_age * 86400 );
				$pass_change_ts = $this->getLastPasswordChangeTS();
		   		$current_ts = time();

				if( ($current_ts - $pass_change_ts) > $max_pass_age_ts )
					return true;
    		}
     	}
    	return false;
    }

    public function getPasswordAge()
    {
    	$current_ts = time();
    	$pass_change_ts = $this->getLastPasswordChangeTS();
    	$password_age = (int) ( ($current_ts - $pass_change_ts) / 86400 );
    	return $password_age;
    }

    public function setLastPasswordChangeToNow()
    {
		global $ilDB;
		
    	$this->setLastPasswordChangeTS( time() );

    	$query = "UPDATE usr_data SET usr_data.last_password_change = %s " .
    			"WHERE usr_data.usr_id = %s";
    	$affected = $ilDB->manipulateF($query,
		 	array('integer','integer'),
			array($this->getLastPasswordChangeTS(),$this->id));
    	if($affected) return true;
    	else return false;
    }

    public function resetLastPasswordChange()
    {
		global $ilDB;
		
		$query = "UPDATE usr_data SET usr_data.last_password_change = 0 " .
				"WHERE usr_data.usr_id = %s";
		$affected = $ilDB->manipulateF( $query, array('integer'),
    		array($this->getId()) );
    	if($affected) return true;
    	else return false;
    }

	/**
	* Set Latitude.
	*
	* @param	string	$a_latitude	Latitude
	*/
	function setLatitude($a_latitude)
	{
		$this->latitude = $a_latitude;
	}

	/**
	* Get Latitude.
	*
	* @return	string	Latitude
	*/
	function getLatitude()
	{
		return $this->latitude;
	}

	/**
	* Set Longitude.
	*
	* @param	string	$a_longitude	Longitude
	*/
	function setLongitude($a_longitude)
	{
		$this->longitude = $a_longitude;
	}

	/**
	* Get Longitude.
	*
	* @return	string	Longitude
	*/
	function getLongitude()
	{
		return $this->longitude;
	}

	/**
	* Set Location Zoom.
	*
	* @param	int	$a_locationzoom	Location Zoom
	*/
	function setLocationZoom($a_locationzoom)
	{
		$this->loc_zoom = $a_locationzoom;
	}

	/**
	* Get Location Zoom.
	*
	* @return	int	Location Zoom
	*/
	function getLocationZoom()
	{
		return $this->loc_zoom;
	}

	function &getAppliedUsers()
	{
		$this->applied_users = array();
		$this->__readAppliedUsers($this->getId());

		return $this->applied_users ? $this->applied_users : array();
	}

	function isChild($a_usr_id)
	{
		if($a_usr_id == $this->getId())
		{
			return true;
		}

		$this->applied_users = array();
		$this->__readAppliedUsers($this->getId());

		return in_array($a_usr_id,$this->applied_users);
	}

	function __readAppliedUsers($a_parent_id)
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT usr_id FROM usr_data ".
			"WHERE time_limit_owner = %s",
			array("integer"),
			array($a_parent_id));
		while ($row = $ilDB->fetchObject($res))
		{
			$this->applied_users[] = $row->usr_id;

			// recursion
			$this->__readAppliedUsers($row->usr_id);
		}
		return true;
	}

	/*
     * check user id with login name
     * @access  public
     */
	function checkUserId()
	{
		global $ilDB,$ilAuth, $ilSetting;

		$login = ilObjUser::getLoginFromAuth();
		$id = ilObjUser::_lookupId($login);
		if ($id > 0)
		{
			// check for simultaneous logins 
			if((int)$ilSetting->get('ps_prevent_simultaneous_logins') == 1)
			{
				$res = $ilDB->queryf('
					SELECT * FROM usr_session WHERE user_id = %s AND expires > %s',
					array('integer', 'integer'),
					array($id, time()));						
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$ilAuth->logout();
					@session_destroy();
					ilUtil::redirect('login.php?simultaneous_login=true');
					exit();
				}
			}
			
			return $id;
		}
		return false;
	}

	/**
	 * Gets the username from $ilAuth, and converts it into an ILIAS login name.
	 */
	private static function getLoginFromAuth() {
		global $ilAuth;
                
		// BEGIN WebDAV: Strip Microsoft Domain Names from logins
		require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
		if (ilDAVActivationChecker::_isActive())
		{
			require_once ('Services/WebDAV/classes/class.ilDAVServer.php');
			require_once ('Services/Database/classes/class.ilAuthContainerMDB2.php');
			$login = ilAuthContainerMDB2::toUsernameWithoutDomain($ilAuth->getUsername());
		}
		else
		{
			$login =$ilAuth->getUsername();
		}
                
		return $login;
        }

    /*
     * check to see if current user has been made active
     * @access  public
     * @return  true if active, otherwise false
     */
    function isCurrentUserActive()
    {
		global $ilDB,$ilAuth;

		$login = ilObjUser::getLoginFromAuth();
		$set = $ilDB->queryF("SELECT active FROM usr_data WHERE login= %s",
			array("text"),
			array($login));
        //query has got a result
		if ($rec = $ilDB->fetchAssoc($set))
		{
			if ($rec["active"])
			{
				return true;
			}
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
		return (int) ilObjUser::_lookupId($a_login);
	}

	/**
	 * STATIC METHOD
	 * get all user_ids of an email address
	 * @param	string email of user
	 * @return  integer id of user
	 * @static
	 * @access	public
	 */
	function _getUserIdsByEmail($a_email)
	{
		global $ilias, $ilDB;

		$res = $ilDB->queryF("SELECT login FROM usr_data ".
			"WHERE email = %s and active = 1",
			array("text"),
			array($a_email));
 		$ids = array ();
        while($row = $ilDB->fetchObject($res))
        {
            $ids[] = $row->login;
        }

		return $ids;
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
		global $ilDB;

		$res = $ilDB->queryF("SELECT usr_id FROM usr_data ".
			"WHERE email = %s", array("text"), array($a_email));

		$row = $ilDB->fetchObject($res);
		return $row->usr_id ? $row->usr_id : 0;
	}

    /*
     * STATIC METHOD
     * get the login name of a user_id
     * @param   integer id of user
     * @return  string login name; false if not found
     * @static
     * @access  public
     */
    function getLoginByUserId($a_userid)
    {
        $login = ilObjUser::_lookupLogin($a_userid);
        return $login ? $login : false;
    }

	/**
	* STATIC METHOD
	* get the user_ids which correspond a search string
	* @param	string search string
	* @param boolean $active Search only for active users
	* @param boolean $a_return_ids_only Return only an array of user id's instead of id, login, name, active status
	* @param mixed $filter_settings Filter settings of the user administration view
	* @static
	* @access	public
	*/
	static function searchUsers($a_search_str, $active = 1, $a_return_ids_only = false, $filter_settings = FALSE)
	{
		global $ilias, $ilDB, $ilLog;

		
		$query = "SELECT usr_data.usr_id, usr_data.login, usr_data.firstname, usr_data.lastname, usr_data.email, usr_data.active FROM usr_data ";
		
		$without_anonymous_users = true;

		// determine join filter
		$join_filter = " WHERE ";
		if ($filter_settings !== FALSE && strlen($filter_settings))
		{
			switch ($filter_settings)
			{
				case 3:
					// show only users without courses
					$join_filter = " LEFT JOIN crs_members ON usr_data.usr_id = crs_members.usr_id WHERE crs_members.usr_id IS NULL AND ";
					break;
				case 5:
					// show only users with a certain course membership
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$join_filter = " LEFT JOIN crs_members ON usr_data.usr_id = crs_members.usr_id WHERE crs_members.obj_id = ".
							"(SELECT obj_id FROM object_reference WHERE ref_id = ".$ilDB->quote($ref_id, "integer").") AND ";
					}
					break;
				case 6:
					global $rbacreview;
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$rolf = $rbacreview->getRoleFolderOfObject($ref_id);
						$local_roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
						if (is_array($local_roles) && count($local_roles))
						{
							$join_filter = " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE ".
								$ilDB->in("rbac_ua.rol_id", $local_roles, false, $local_roles)." AND ";
						}
					}
					break;
				case 7:
					global $rbacreview;
					$rol_id = $_SESSION["user_filter_data"];
					if ($rol_id)
					{
						$join_filter = " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id = ".
							$ilDB->quote($rol_id, "integer")." AND ";
						$without_anonymous_users = false;
					}
					break;
			}
		}
		// This is a temporary hack to search users by their role
		// See Mantis #338. This is a hack due to Mantis #337.
		if (strtolower(substr($a_search_str, 0, 5)) == "role:")
		{
			$query = "SELECT DISTINCT usr_data.usr_id,usr_data.login,usr_data.firstname,usr_data.lastname,usr_data.email ".
				"FROM object_data,rbac_ua,usr_data ".
				"WHERE ".$ilDB->like("object_data.title", "text", "%".substr($a_search_str,5)."%").
				" AND object_data.type = 'role' ".
				"AND rbac_ua.rol_id = object_data.obj_id ".
				"AND usr_data.usr_id = rbac_ua.usr_id ".
				"AND rbac_ua.usr_id != ".$illDB->quote(ANONYMOUS_USER_ID, "integer");
		}
		else
		{
			$query.= $join_filter.
				"(".$ilDB->like("usr_data.login", "text", "%".$a_search_str."%")." ".
				"OR ".$ilDB->like("usr_data.firstname", "text", "%".$a_search_str."%")." ".
				"OR ".$ilDB->like("usr_data.lastname", "text", "%".$a_search_str."%")." ".
				"OR ".$ilDB->like("usr_data.email", "text", "%".$a_search_str."%").") ";

			if ($filter_settings !== FALSE && strlen($filter_settings))
			{
				switch ($filter_settings)
				{
					case 0:
						$query.= " AND usr_data.active = ".$ilDB->quote(0, "integer")." ";
						break;
					case 1:
						$query.= " AND usr_data.active = ".$ilDB->quote(1, "integer")." ";
						break;
					case 2:
						$query.= " AND usr_data.time_limit_unlimited = ".$ilDB->quote(0, "integer")." ";
						break;
					case 4:
						$date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"], $_SESSION["user_filter_data"]["y"]));
						$query.= " AND last_login < ".$ilDB->quote($date, "timestamp")." ";
						break;
				}
			}
				
			if ($without_anonymous_users)
			{
				$query.= "AND usr_data.usr_id != ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");
			}

			if (is_numeric($active) && $active > -1 && $filter_settings === FALSE)
			{
				$query.= " AND active = ".$ilDB->quote($active, "integer")." ";
			}

		}
		$ilLog->write($query);
		$res = $ilDB->query($query);
		while ($row = $ilDB->fetchObject($res))
		{
			$users[] = array(
				"usr_id"    => $row->usr_id,
				"login"     => $row->login,
				"firstname" => $row->firstname,
				"lastname"  => $row->lastname,
				"email"     => $row->email,
				"active"    => $row->active);
			$ids[] = $row->usr_id;
		}
		if ($a_return_ids_only)
			return $ids ? $ids : array();
		else
			return $users ? $users : array();
	}

	/**
	* STATIC METHOD
	* get all user logins
	* @param	ilias object
	* @static
	* @return	array of logins
	* @access	public
	*/
	function _getAllUserLogins(&$ilias)
	{
		$res = $ilDB->query("SELECT login FROM usr_data");
		while($row = $ilDB->fetchObject($res))
		{
			$logins[] = $row->login;
		}
		return $logins ? $logins : array();
	}

	/**
     * STATIC METHOD
     * get user data of selected users
     * @param	array desired user ids
     * @return	array of user data
     * @static
     * @access	public
     */
	public static function _readUsersProfileData($a_user_ids)
	{
		global $ilDB;
		$res = $ilDB->query("SELECT * FROM usr_data WHERE ".
			$ilDB->in("usr_id", $a_user_ids, false, "integer"));
		while ($row = $ilDB->fetchAssoc($res))
		{
			$user_data["$row[usr_id]"] = $row;
		}
		return $user_data ? $user_data : array();
	}

	/**
     * STATIC METHOD
     * get all user data
     * @param	array desired columns
     * @static
     * @return	array of user data
     * @access	public
     */
	function _getAllUserData($a_fields = NULL, $active =-1)
	{
		global $ilDB;

		$result_arr = array();
		$types = array();
		$values = array();

		if ($a_fields !== NULL and is_array($a_fields))
		{
			if (count($a_fields) == 0)
			{
				$select = "*";
			}
			else
			{
			if (($usr_id_field = array_search("usr_id",$a_fields)) !== false)
				unset($a_fields[$usr_id_field]);

				$select = implode(",",$a_fields).",usr_data.usr_id";
				// online time
				if(in_array('online_time',$a_fields))
				{
					$select .= ",ut_online.online_time ";
				}
			}

			$q = "SELECT ".$select." FROM usr_data ";

			// Add online_time if desired
			// Need left join here to show users that never logged in
			if(in_array('online_time',$a_fields))
			{
				$q .= "LEFT JOIN ut_online ON usr_data.usr_id = ut_online.usr_id ";
			}

			switch ($active)
			{
				case 0:
				case 1:
					$q .= "WHERE active = ".$ilDB->quote($active, "integer");
					break;
				case 2:
					$q .= "WHERE time_limit_unlimited= ".$ilDB->quote(0, "integer");;
					break;
				case 3:
					$qtemp = $q . ", rbac_ua, object_data WHERE rbac_ua.rol_id = object_data.obj_id AND ".
						$ilDB->like("object_data.title", "text", "%crs%")." AND usr_data.usr_id = rbac_ua.usr_id";
					$r = $ilDB->query($qtemp);
					$course_users = array();
					while ($row = $ilDB->fetchAssoc($r))
					{
						array_push($course_users, $row["usr_id"]);
					}
					if (count($course_users))
					{
						$q .= " WHERE ".$ilDB->in("usr_data.usr_id", $course_users, true, "integer")." ";
					}
					else
					{
						return $result_arr;
					}
					break;
				case 4:
					$date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"], $_SESSION["user_filter_data"]["y"]));
					$q.= " AND last_login < ".$ilDB->quote($date, "timestamp");
					break;
				case 5:
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$q .= " LEFT JOIN crs_members ON usr_data.usr_id = crs_members.usr_id ".
							"WHERE crs_members.obj_id = (SELECT obj_id FROM object_reference ".
							"WHERE ref_id = ".$ilDB->quote($ref_id, "integer").") ";
					}
					break;
				case 6:
					global $rbacreview;
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$rolf = $rbacreview->getRoleFolderOfObject($ref_id);
						$local_roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
						if (is_array($local_roles) && count($local_roles))
					{
							$q.= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE ".
								$ilDB->in("rbac_ua.rol_id", $local_roles, false, "integer")." ";
						}
					}
					break;
				case 7:
					$rol_id = $_SESSION["user_filter_data"];
					if ($rol_id)
					{
						$q .= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id = ".
							$ilDB->quote($rol_id, "integer");
					}
					break;
			}

			$r = $ilDB->query($q);

			while ($row = $ilDB->fetchAssoc($r))
			{
				$result_arr[] = $row;
			}
		}

		return $result_arr;
	}

	/**
	* skins and styles
	*/
	function _getNumberOfUsersForStyle($a_skin, $a_style)
	{
		global $ilDB;

		$q = "SELECT count(*) as cnt FROM usr_pref up1, usr_pref up2 ".
			" WHERE up1.keyword= ".$ilDB->quote("style", "text").
			" AND up1.value= ".$ilDB->quote($a_style, "text").
			" AND up2.keyword= ".$ilDB->quote("skin", "text").
			" AND up2.value= ".$ilDB->quote($a_skin, "text").
			" AND up1.usr_id = up2.usr_id ";

		$cnt_set = $ilDB->query($q);

		$cnt_rec = $ilDB->fetchAssoc($cnt_set);

		return $cnt_rec["cnt"];
	}

	/**
	* skins and styles
	*/
	function _getAllUserAssignedStyles()
	{
		global $ilDB;

		$q = "SELECT DISTINCT up1.value style, up2.value skin FROM usr_pref up1, usr_pref up2 ".
			" WHERE up1.keyword = ".$ilDB->quote("style", "text").
			" AND up2.keyword = ".$ilDB->quote("skin", "text").
			" AND up1.usr_id = up2.usr_id";
			
		$sty_set = $ilDB->query($q);

		$styles = array();
		while($sty_rec = $ilDB->fetchAssoc($sty_set))
		{
			$styles[] = $sty_rec["skin"].":".$sty_rec["style"];
		}

		return $styles;
	}

	/**
	* skins and styles
	*/
	function _moveUsersToStyle($a_from_skin, $a_from_style, $a_to_skin, $a_to_style)
	{
		global $ilDB;

		$q = "SELECT up1.usr_id usr_id FROM usr_pref up1, usr_pref up2 ".
			" WHERE up1.keyword= ".$ilDB->quote("style", "text").
			" AND up1.value= ".$ilDB->quote($a_from_style, "text").
			" AND up2.keyword= ".$ilDB->quote("skin", "text").
			" AND up2.value= ".$ilDB->quote($a_from_skin, "text").
			" AND up1.usr_id = up2.usr_id ";

		$usr_set = $ilDB->query($q);

		while ($usr_rec = $ilDB->fetchAssoc($usr_set))
		{
			ilObjUser::_writePref($usr_rec["usr_id"], "skin", $a_to_skin);
			ilObjUser::_writePref($usr_rec["usr_id"], "style", $a_to_style);
		}
	}


	/**
	* add an item to user's personal desktop
	*
	* @param 	int		$a_usr_id		id of user object
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	* @static
	*/
	public static function _addDesktopItem($a_usr_id, $a_item_id, $a_type, $a_par = "")
	{
		global $ilDB;

		$item_set = $ilDB->queryF("SELECT * FROM desktop_item WHERE ".
			"item_id = %s AND type = %s AND user_id = %s",
			array("integer", "text", "integer"),
			array($a_item_id, $a_type, $a_usr_id));

		// only insert if item is not already on desktop
		if (!$ilDB->fetchAssoc($item_set))
		{
			$ilDB->manipulateF("INSERT INTO desktop_item (item_id, type, user_id, parameters) VALUES ".
				" (%s,%s,%s,%s)", array("integer", "text", "integer", "text"),
				array($a_item_id,$a_type,$a_usr_id,$a_par));
		}
	}

	/**
	* add an item to user's personal desktop
	*
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	*/
	function addDesktopItem($a_item_id, $a_type, $a_par = "")
	{
		ilObjUser::_addDesktopItem($this->getId(), $a_item_id, $a_type, $a_par);
	}

	/**
	* set parameters of a desktop item entry
	*
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	* @param	string	$a_par			parameters
	*/
	function setDesktopItemParameters($a_item_id, $a_type, $a_par)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE desktop_item SET parameters = %s ".
			" WHERE item_id = %s AND type = %s AND user_id = %s",
			array("text", "integer", "text", "integer"),
			array($a_par, $a_item_id, $a_type, $this->getId()));
	}


	/**
	* drop an item from user's personal desktop
	*
	* @param 	int		$a_usr_id		id of user object
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	* @static
	*/
	public static function _dropDesktopItem($a_usr_id, $a_item_id, $a_type)
	{
		global $ilDB;

		$ilDB->manipulateF("DELETE FROM desktop_item WHERE ".
			" item_id = %s AND type = %s  AND user_id = %s",
			array("integer", "text", "integer"),
			array($a_item_id, $a_type, $a_usr_id));
	}

	/**
	* drop an item from user's personal desktop
	*
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	*/
	function dropDesktopItem($a_item_id, $a_type)
	{
		ilObjUser::_dropDesktopItem($this->getId(), $a_item_id, $a_type);
	}

	/**
	* removes object from all user's desktops
	* @access	public
	* @param	integer	ref_id
	* @return	array	user_ids of all affected users
	*/
	static function _removeItemFromDesktops($a_id)
	{
		global $ilDB;
		
		$r = $ilDB->queryF("SELECT user_id FROM desktop_item WHERE item_id = %s",
			array("integer"), array($a_id));

		$users = array();

		while ($row = $ilDB->fetchObject($r))
		{
			$users[] = $row->user_id;
		} // while

		if (count($users) > 0)
		{
			$ilDB->manipulateF("DELETE FROM desktop_item WHERE item_id = %s",
				array("integer"), array($a_id));
		}

		return $users;
	}

	/**
	* check wether an item is on the users desktop or not
	*
	* @param 	int		$a_usr_id		id of user object
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	* @static
	*/
	public static function _isDesktopItem($a_usr_id, $a_item_id, $a_type)
	{
		global $ilDB;

		$item_set = $ilDB->queryF("SELECT * FROM desktop_item WHERE ".
			"item_id = %s AND type = %s AND user_id = %s",
			array("integer", "text", "integer"),
			array($a_item_id, $a_type, $a_usr_id));

		if ($ilDB->fetchAssoc($item_set))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* check wether an item is on the users desktop or not
	*
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	*/
	function isDesktopItem($a_item_id, $a_type)
	{
		return ilObjUser::_isDesktopItem($this->getId(), $a_item_id, $a_type);
	}

	function getDesktopItems($a_types = "")
	{
		return $this->_lookupDesktopItems($this->getId(), $a_types);
	}

	/**
	* get all desktop items of user and specified type
	*
	* note: the implementation of this method is not good style (directly
	* reading tables object_data and object_reference), must be revised someday...
	*/
	static function _lookupDesktopItems($user_id, $a_types = "")
	{
		global $ilUser, $rbacsystem, $tree, $ilDB;

		if ($a_types == "")
		{
			$item_set = $ilDB->queryF("SELECT obj.obj_id, obj.description, oref.ref_id, obj.title, obj.type ".
				" FROM desktop_item it, object_reference oref ".
					", object_data obj".
				" WHERE ".
				"it.item_id = oref.ref_id AND ".
				"oref.obj_id = obj.obj_id AND ".
				"it.user_id = %s", array("integer"), array($user_id));
			$items = array();
			while ($item_rec = $ilDB->fetchAssoc($item_set))
			{
				if ($tree->isInTree($item_rec["ref_id"])
					&& $item_rec["type"] != "rolf")
				{
					$parent_ref = $tree->getParentId($item_rec["ref_id"]);
					$par_left = $tree->getLeftValue($parent_ref);
					$par_left = sprintf("%010d", $par_left);


					$title = ilObject::_lookupTitle($item_rec["obj_id"]);
					$desc = ilObject::_lookupDescription($item_rec["obj_id"]);
					$items[$par_left.$title.$item_rec["ref_id"]] =
						array("ref_id" => $item_rec["ref_id"],
							"obj_id" => $item_rec["obj_id"],
							"type" => $item_rec["type"],
							"title" => $title,
							"description" => $desc,
							"parent_ref" => $parent_ref);
				}
			}
			ksort($items);
		}
		else
		{
			if (!is_array($a_types))
			{
				$a_types = array($a_types);
			}
			$items = array();
			$foundsurveys = array();
			foreach($a_types as $a_type)
			{
				$item_set = $ilDB->queryF("SELECT obj.obj_id, obj.description, oref.ref_id, obj.title FROM desktop_item it, object_reference oref ".
					", object_data obj WHERE ".
					"it.item_id = oref.ref_id AND ".
					"oref.obj_id = obj.obj_id AND ".
					"it.type = %s AND ".
					"it.user_id = %s ".
					"ORDER BY title",
					array("text", "integer"),
					array($a_type, $user_id));
				
				while ($item_rec = $ilDB->fetchAssoc($item_set))
				{
					$title = ilObject::_lookupTitle($item_rec["obj_id"]);
					$desc = ilObject::_lookupDescription($item_rec["obj_id"]);
					$items[$title.$a_type.$item_rec["ref_id"]] =
						array("ref_id" => $item_rec["ref_id"],
						"obj_id" => $item_rec["obj_id"], "type" => $a_type,
						"title" => $title, "description" => $desc);
				}

			}
			ksort($items);
		}
		return $items;
	}

	/**
	* add an item to user's personal clipboard
	*
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	*/
	function addObjectToClipboard($a_item_id, $a_type, $a_title,
		$a_parent = 0, $a_time = 0, $a_order_nr = 0)
	{
		global $ilDB;

		if ($a_time == 0)
		{
			$a_time = date("Y-m-d H:i:s", time());
		}

		$item_set = $ilDB->queryF("SELECT * FROM personal_clipboard WHERE ".
			"parent = %s AND item_id = %s AND type = %s AND user_id = %s",
			array("integer", "integer", "text", "integer"),
			array(0, $a_item_id, $a_type, $this->getId()));

		// only insert if item is not already in clipboard
		if (!$d = $item_set->fetchRow())
		{
			$ilDB->manipulateF("INSERT INTO personal_clipboard ".
				"(item_id, type, user_id, title, parent, insert_time, order_nr) VALUES ".
				" (%s,%s,%s,%s,%s,%s,%s)",
				array("integer", "text", "integer", "text", "integer", "timestamp", "integer"),
				array($a_item_id, $a_type, $this->getId(), $a_title, (int) $a_parent, $a_time, (int) $a_order_nr));
		}
		else
		{
			$ilDB->manipulateF("UPDATE personal_clipboard SET insert_time = %s ".
				"WHERE user_id = %s AND item_id = %s AND type = %s AND parent = 0",
				array("timestamp", "integer", "integer", "text"),
				array($a_time, $this->getId(), $a_item_id, $a_type));
		}
	}

	/**
	* Add a page content item to PC clipboard (should go to another class)
	*/
	function addToPCClipboard($a_content, $a_time, $a_nr)
	{
		global $ilDB;
		if ($a_time == 0)
		{
			$a_time = date("Y-m-d H:i:s", time());
		}
		$ilDB->insert("personal_pc_clipboard", array(
			"user_id" => array("integer", $this->getId()),
			"content" => array("clob", $a_content),
			"insert_time" => array("timestamp", $a_time),
			"order_nr" => array("integer", $a_nr)
			));
	}

	/**
	* Add a page content item to PC clipboard (should go to another class)
	*/
	function getPCClipboardContent()
	{
		global $ilDB;

		$set = $ilDB->queryF("SELECT MAX(insert_time) mtime FROM personal_pc_clipboard ".
			" WHERE user_id = %s", array("integer"), array($this->getId()));
		$row = $ilDB->fetchAssoc($set);
		
		$set = $ilDB->queryF("SELECT * FROM personal_pc_clipboard ".
			" WHERE user_id = %s AND insert_time = %s ORDER BY order_nr ASC",
			array("integer", "timestamp"),
			array($this->getId(), $row["mtime"]));
		$content = array();
		while ($row = $ilDB->fetchAssoc($set))
		{
			$content[] = $row["content"];
		}

		return $content;
	}

	/**
	* Check whether clipboard has objects of a certain type
	*/
	function clipboardHasObjectsOfType($a_type)
	{
		global $ilDB;

		$set = $ilDB->queryF("SELECT * FROM personal_clipboard WHERE ".
			"parent = %s AND type = %s AND user_id = %s",
			array("integer", "text", "integer"),
			array(0, $a_type, $this->getId()));
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}

		return false;
	}

	/**
	* Delete objects of type for user
	*/
	function clipboardDeleteObjectsOfType($a_type)
	{
		global $ilDB;

		$ilDB->manipulateF("DELETE FROM personal_clipboard WHERE ".
			"type = %s AND user_id = %s",
			array("text", "integer"),
			array($a_type, $this->getId()));
	}

	/**
	* Delete objects of type for user
	*/
	function clipboardDeleteAll()
	{
		global $ilDB;

		$ilDB->manipulateF("DELETE FROM personal_clipboard WHERE ".
			"user_id = %s", array("integer"), array($this->getId()));
	}

	/**
	* get all clipboard objects of user and specified type
	*/
	function getClipboardObjects($a_type = "", $a_top_nodes_only = false)
	{
		global $ilDB;

		$par = "";
		if ($a_top_nodes_only)
		{
			$par = " AND parent = ".$ilDB->quote(0, "integer")." ";
		}
		
		$type_str = ($a_type != "")
			? " AND type = ".$ilDB->quote($a_type, "text")." "
			: "";
		$q = "SELECT * FROM personal_clipboard WHERE ".
			"user_id = ".$ilDB->quote($this->getId(), "integer")." ".
			$type_str.$par.
			" ORDER BY order_nr";
		$objs = $ilDB->query($q);
		$objects = array();
		while ($obj = $ilDB->fetchAssoc($objs))
		{
			if ($obj["type"] == "mob")
			{
				$obj["title"] = ilObject::_lookupTitle($obj["item_id"]);
			}
			$objects[] = array ("id" => $obj["item_id"],
				"type" => $obj["type"], "title" => $obj["title"],
				"insert_time" => $obj["insert_time"]);
		}
		return $objects;
	}

	/**
	* Get childs of an item
	*/
	function getClipboardChilds($a_parent, $a_insert_time)
	{
		global $ilDB, $ilUser;

		$objs = $ilDB->queryF("SELECT * FROM personal_clipboard WHERE ".
			"user_id = %s AND parent = %s AND insert_time = %s ".
			" ORDER BY order_nr",
			array("integer", "integer", "timestamp"),
			array($ilUser->getId(), (int) $a_parent, $a_insert_time));
		$objects = array();
		while ($obj = $ilDB->fetchAssoc($objs))
		{
			if ($obj["type"] == "mob")
			{
				$obj["title"] = ilObject::_lookupTitle($obj["item_id"]);
			}
			$objects[] = array ("id" => $obj["item_id"],
				"type" => $obj["type"], "title" => $obj["title"]);
		}
		return $objects;
	}

	/**
	* get all users, that have a certain object within their clipboard
	*
	* @param	string		$a_type		object type
	* @param	string		$a_type		object type
	*
	* @return	array		array of user IDs
	*/
	function _getUsersForClipboadObject($a_type, $a_id)
	{
		global $ilDB;

		$q = "SELECT DISTINCT user_id FROM personal_clipboard WHERE ".
			"item_id = ".$ilDB->quote($a_id, "integer")." AND ".
			"type = ".$ilDB->quote($a_type, "text");
		$user_set = $ilDB->query($q);
		$users = array();
		while ($user_rec = $ilDB->fetchAssoc($user_set))
		{
			$users[] = $user_rec["user_id"];
		}

		return $users;
	}

	/**
	* remove object from user's personal clipboard
	*
	* @param	int		$a_item_id		ref_id for objects, that are in the main tree
	*									(learning modules, forums) obj_id for others
	* @param	string	$a_type			object type
	*/
	function removeObjectFromClipboard($a_item_id, $a_type)
	{
		global $ilDB;

		$q = "DELETE FROM personal_clipboard WHERE ".
			"item_id = ".$ilDB->quote($a_item_id, "integer").
			" AND type = ".$ilDB->quote($a_type, "text")." ".
			" AND user_id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($q);
	}

	function _getImportedUserId($i2_id)
	{
		global $ilDB;

		$query = "SELECT obj_id FROM object_data WHERE import_id = ".
			$ilDB->quote($i2_id, "text");

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$id = $row->obj_id;
		}
		return $id ? $id : 0;
	}

/*

	function setiLincData($a_id,$a_login,$a_passwd)
	{
		$this->ilinc_id = $a_id;
		$this->ilinc_login = $a_login;
		$this->ilinc_passwd = $a_passwd;
	}

*/

/*

	function getiLincData()
	{
		return array ("id" => $this->ilinc_id, "login" => $this->ilinc_login, "passwd" => $this->ilinc_passwd);
	}
*/
	/**
    * set auth mode
	* @access	public
	*/
	function setAuthMode($a_str)
	{
		$this->auth_mode = $a_str;
	}

	/**
    * get auth mode
	* @access	public
	*/
	function getAuthMode($a_auth_key = false)
	{
		if (!$a_auth_key)
		{
			return $this->auth_mode;
		}

		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		return ilAuthUtils::_getAuthMode($this->auth_mode);
	}

	/**
    * set external account
	*
	* note: 3.7.0 uses this field only for cas and soap authentication.
	*
	* @access	public
	*/
	function setExternalAccount($a_str)
	{
		$this->ext_account = $a_str;
	}

	/**
    * get external account
	*
	* note: 3.7.0 uses this field only for cas and soap authentication.
	*
	* @access	public
	*/
	function getExternalAccount()
	{
		return $this->ext_account;
	}

	/**
	 * Get list of external account by authentication method
	 * Note: If login == ext_account for two user with auth_mode 'default' and auth_mode 'ldap'
	 * 	The ldap auth mode chosen
	 *
	 * @access public
	 * @param string auth_mode
	 * @param bool also get users with authentication method 'default'
	 * @return array of external account names
	 *
	 */
	public static function _getExternalAccountsByAuthMode($a_auth_mode,$a_read_auth_default = false)
	{
	 	global $ilDB,$ilSetting;

	 	include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
	 	$q = "SELECT login,usr_id,ext_account,auth_mode FROM usr_data ".
	 		"WHERE auth_mode = %s";
		$types[] = "text";
		$values[] = $a_auth_mode;
	 	if($a_read_auth_default and ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode',AUTH_LOCAL)) == $a_auth_mode)
	 	{
	 		$q.= " OR auth_mode = %s ";
			$types[] = "text";
			$values[] = 'default';
	 	}

		$res = $ilDB->queryF($q, $types, $values);
		while ($row = $ilDB->fetchObject($res))
		{
			if($row->auth_mode == 'default')
			{
				$accounts[$row->usr_id] = $row->login;
			}
			else
			{
				$accounts[$row->usr_id] = $row->ext_account;
			}
		}
		return $accounts ? $accounts : array();
	}

	/**
	 * Toggle active status of users
	 *
	 * @access public
	 * @param
	 *
	 */
	public static function _toggleActiveStatusOfUsers($a_usr_ids,$a_status)
	{
	 	global $ilDB;

	 	if(!is_array($a_usr_ids))
	 	{
	 		return false;
	 	}
	 	$q = "UPDATE usr_data SET active = %s WHERE ".
			$ilDB->in("usr_id", $a_usr_ids, false, "integer");
	 	$ilDB->manipulateF($q, array("integer"), array(($a_status ? 1 : 0)));

		return true;
	}


	/**
	 * lookup auth mode
	 *
	 * @access public
	 * @static
	 *
	 * @param int usr_id
	 */
	public static function _lookupAuthMode($a_usr_id)
	{
		return (string) ilObjUser::_lookup($a_usr_id, "auth_mode");
	}

	/**
	* check whether external account and authentication method
	* matches with a user
	*
	* @static
	*/
	public static function _checkExternalAuthAccount($a_auth, $a_account)
	{
		global $ilDB,$ilSetting;

		// Check directly with auth_mode
		$r = $ilDB->queryF("SELECT * FROM usr_data WHERE ".
			" ext_account = %s AND auth_mode = %s",
			array("text", "text"),
			array($a_account, $a_auth));
		if ($usr = $ilDB->fetchAssoc($r))
		{
			return $usr["login"];
		}

		// For compatibility, check for login (no ext_account entry given)
		$res = $ilDB->queryF("SELECT login FROM usr_data ".
			"WHERE login = %s AND auth_mode = %s",
			array("text", "text"),
			array($a_account, $a_auth));
		if($usr = $ilDB->fetchAssoc($res))
		{
			return $usr['login'];
		}

		// If auth_default == $a_auth => check for login
		if(ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode')) == $a_auth)
		{
			$res = $ilDB->queryF("SELECT login FROM usr_data WHERE ".
				" ext_account = %s AND auth_mode = %s",
				array("text", "text"),
				array($a_account, "default"));
			if ($usr = $ilDB->fetchAssoc($res))
			{
				return $usr["login"];
			}

			// Search for login (no ext_account given)
			$res = $ilDB->queryF("SELECT login FROM usr_data ".
				"WHERE login = %s AND ext_account = %s AND auth_mode = %s",
				array("text", "text", "text"),
				array($a_account, "", "default"));
			if($usr = $ilDB->fetchAssoc($res))
			{
				return $usr["login"];
			}
		}
		return false;
	}

	/**
	* get number of users per auth mode
	*/
	function _getNumberOfUsersPerAuthMode()
	{
		global $ilDB;

		$r = $ilDB->query("SELECT count(*) AS cnt, auth_mode FROM usr_data ".
			"GROUP BY auth_mode");
		$cnt_arr = array();
		while($cnt = $ilDB->fetchAssoc($r))
		{
			$cnt_arr[$cnt["auth_mode"]] = $cnt["cnt"];
		}

		return $cnt_arr;
	}

	/**
	* check whether external account and authentication method
	* matches with a user
	*
	*/
	function _getLocalAccountsForEmail($a_email)
	{
		global $ilDB, $ilSetting;

		// default set to local (1)?

		$q = "SELECT * FROM usr_data WHERE ".
			" email = %s AND (auth_mode = %s ";
		$types = array("text", "text");
		$values = array($a_email, "local");

		if ($ilSetting->get("auth_mode") == 1)
		{
			$q.=" OR auth_mode = %s";
			$types[] = "text";
			$values[] = "default";
		}
		
		$q.= ")";

		$users = array();
		$usr_set = $ilDB->queryF($q, $types, $values);
		while ($usr_rec = $ilDB->fetchAssoc($usr_set))
		{
			$users[$usr_rec["usr_id"]] = $usr_rec["login"];
		}

		return $users;
	}


	/**
	* Create a personal picture image file from a temporary image file
	*
	* @param	string $tmp_file Complete path to the temporary image file
	* @param	int	$obj_id The object id of the related user account
	* @return returns TRUE on success, otherwise FALSE
	*/
	function _uploadPersonalPicture($tmp_file, $obj_id)
	{
		$webspace_dir = ilUtil::getWebspaceDir();
		$image_dir = $webspace_dir."/usr_images";
		$store_file = "usr_".$obj_id."."."jpg";
		$target_file = $image_dir."/$store_file";

		chmod($tmp_file, 0770);

		// take quality 100 to avoid jpeg artefacts when uploading jpeg files
		// taking only frame [0] to avoid problems with animated gifs
		$show_file  = "$image_dir/usr_".$obj_id.".jpg";
		$thumb_file = "$image_dir/usr_".$obj_id."_small.jpg";
		$xthumb_file = "$image_dir/usr_".$obj_id."_xsmall.jpg";
		$xxthumb_file = "$image_dir/usr_".$obj_id."_xxsmall.jpg";

		system(ilUtil::getConvertCmd()." $tmp_file" . "[0] -geometry 200x200 -quality 100 JPEG:$show_file");
		system(ilUtil::getConvertCmd()." $tmp_file" . "[0] -geometry 100x100 -quality 100 JPEG:$thumb_file");
		system(ilUtil::getConvertCmd()." $tmp_file" . "[0] -geometry 75x75 -quality 100 JPEG:$xthumb_file");
		system(ilUtil::getConvertCmd()." $tmp_file" . "[0] -geometry 30x30 -quality 100 JPEG:$xxthumb_file");

		// store filename
		ilObjUser::_writePref($obj_id, "profile_image", $store_file);

		return TRUE;
	}

	/**
	* get path to personal picture
	*
	* @param	string		size		"small", "xsmall" or "xxsmall"
	*/
	function getPersonalPicturePath($a_size = "small", $a_force_pic = false)
	{
		return ilObjUser::_getPersonalPicturePath($this->getId(),$a_size,$a_force_pic);
	}

	/**
	* get path to personal picture
	*
	* @param	string		size		"small", "xsmall" or "xxsmall"
	* STATIC
	*/
	function _getPersonalPicturePath($a_usr_id,$a_size = "small", $a_force_pic = false,
		$a_prevent_no_photo_image = false)
	{
		global $ilDB;

		// BEGIN DiskQuota: Fetch all user preferences in a single query
		$res = $ilDB->queryF("SELECT * FROM usr_pref WHERE ".
			"keyword IN (%s,%s) ".
			"AND usr_id = %s",
			array("text", "text", "integer"),
			array('public_upload', 'public_profile', $a_usr_id));
		while ($row = $ilDB->fetchAssoc($res))
		{
			switch ($row['keyword'])
			{
				case 'public_upload' :
					$upload = $row['value'] == 'y';
					break;
				case 'public_profile' :
					$profile = ($row['value'] == 'y' ||
						$row['value'] == 'g');
					break;
			}
		}

		// END DiskQuota: Fetch all user preferences in a single query

		if(defined('ILIAS_MODULE'))
		{
			$webspace_dir = ('.'.$webspace_dir);
		}
		$webspace_dir .= ('./'.ilUtil::getWebspaceDir());

		$image_dir = $webspace_dir."/usr_images";
		// BEGIN DiskQuota: Support 'big' user images
		if ($a_size == 'big')
		{
				$thumb_file = $image_dir."/usr_".$a_usr_id.".jpg";
		}
		else
		{
				$thumb_file = $image_dir."/usr_".$a_usr_id."_".$a_size.".jpg";
		}
		// END DiskQuota: Support 'big' user images

		if((($upload && $profile) || $a_force_pic)
			&& @is_file($thumb_file))
		{
			$file = $thumb_file."?t=".rand(1, 99999);
		}
		else
		{
			if (!$a_prevent_no_photo_image)
			{
				$file = ilUtil::getImagePath("no_photo_".$a_size.".jpg");
			}
		}

		return $file;
	}

	/**
	* Remove user picture.
	*/
	function removeUserPicture()
	{
		$webspace_dir = ilUtil::getWebspaceDir();
		$image_dir = $webspace_dir."/usr_images";
		$file = $image_dir."/usr_".$this->getID()."."."jpg";
		$thumb_file = $image_dir."/usr_".$this->getID()."_small.jpg";
		$xthumb_file = $image_dir."/usr_".$this->getID()."_xsmall.jpg";
		$xxthumb_file = $image_dir."/usr_".$this->getID()."_xxsmall.jpg";
		$upload_file = $image_dir."/upload_".$this->getID();

		// remove user pref file name
		$this->setPref("profile_image", "");
		$this->update();

		if (@is_file($file))
		{
			unlink($file);
		}
		if (@is_file($thumb_file))
		{
			unlink($thumb_file);
		}
		if (@is_file($xthumb_file))
		{
			unlink($xthumb_file);
		}
		if (@is_file($xxthumb_file))
		{
			unlink($xxthumb_file);
		}
		if (@is_file($upload_file))
		{
			unlink($upload_file);
		}
	}
	
	
	function setUserDefinedData($a_data)
	{
		if(!is_array($a_data))
		{
			return false;
		}
		foreach($a_data as $field => $data)
		{
			#$new_data[$field] = ilUtil::stripSlashes($data);
			// Assign it directly to avoid update problems of unchangable fields
			$this->user_defined_data['f_'.$field] = $data;
		}
		#$this->user_defined_data = $new_data;

		return true;
	}

	function getUserDefinedData()
	{
		return $this->user_defined_data ? $this->user_defined_data : array();
	}

	function updateUserDefinedFields()
	{
		global $ilDB;

		$fields = '';

		$field_def = array();
		
		include_once("./Services/User/classes/class.ilUserDefinedData.php");
		$udata = new ilUserDefinedData($this->getId());

		foreach($this->user_defined_data as $field => $value)
		{
			if($field != 'usr_id')
			{
//				$field_def[$field] = array('text',$value);
				$udata->set($field, $value);
			}
		}
		$udata->update();

/*		if(!$field_def)
		{
			return true;
		}
		
		$query = "SELECT usr_id FROM udf_data WHERE usr_id = ".$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->query($query);
		
		
		if($res->numRows())
		{
			// Update
			$ilDB->update('udf_data',$field_def,array('usr_id' => array('integer',$this->getId())));
		}
		else
		{
			$field_def['usr_id'] = array('integer',$this->getId());
			$ilDB->insert('udf_data',$field_def);
		}
*/
		return true;
	}

	function readUserDefinedFields()
	{
		global $ilDB;

		include_once("./Services/User/classes/class.ilUserDefinedData.php");
		$udata = new ilUserDefinedData($this->getId());

/*		$query = "SELECT * FROM udf_data ".
			"WHERE usr_id = ".$ilDB->quote($this->getId(),'integer');

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->user_defined_data = $row;
		}*/
		
		$this->user_defined_data = $udata->getAll();
		
		return true;
	}

	function addUserDefinedFieldEntry()
	{
		global $ilDB;

// not needed. no entry in udf_text/udf_clob means no value

/*		$query = "INSERT INTO udf_data (usr_id ) ".
			"VALUES( ".
			$ilDB->quote($this->getId(),'integer').
			")";
		$res = $ilDB->manipulate($query);
*/
		return true;
	}

	function deleteUserDefinedFieldEntries()
	{
		global $ilDB;

		include_once("./Services/User/classes/class.ilUserDefinedData.php");
		ilUserDefinedData::deleteEntriesOfUser($this->getId());
		
		// wrong place...
/*		$query = "DELETE FROM udf_data  ".
			"WHERE usr_id = ".$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->manipulate($query);*/

		return true;
	}

	/**
	* Get formatted mail body text of user profile data.
	*
	* @param	object	  Language object (choose user language of recipient) or null to use language of current user
	*/
	function getProfileAsString(&$a_language)
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		include_once 'classes/class.ilFormat.php';

		global $lng,$rbacreview;

		$language =& $a_language;
		$language->loadLanguageModule('registration');
		$language->loadLanguageModule('crs');

		$body = '';
        $body .= ($language->txt("login").": ".$this->getLogin()."\n");

		if(strlen($this->getUTitle()))
		{
			$body .= ($language->txt("title").": ".$this->getUTitle()."\n");
		}
		if(strlen($this->getGender()))
		{
			$gender = ($this->getGender() == 'm') ?
				$language->txt('gender_m') :
				$language->txt('gender_f');
			$body .= ($language->txt("gender").": ".$gender."\n");
		}
		if(strlen($this->getFirstname()))
		{
			$body .= ($language->txt("firstname").": ".$this->getFirstname()."\n");
		}
		if(strlen($this->getLastname()))
		{
			$body .= ($language->txt("lastname").": ".$this->getLastname()."\n");
		}
		if(strlen($this->getInstitution()))
		{
			$body .= ($language->txt("institution").": ".$this->getInstitution()."\n");
		}
		if(strlen($this->getDepartment()))
		{
			$body .= ($language->txt("department").": ".$this->getDepartment()."\n");
		}
		if(strlen($this->getStreet()))
		{
			$body .= ($language->txt("street").": ".$this->getStreet()."\n");
		}
		if(strlen($this->getCity()))
		{
			$body .= ($language->txt("city").": ".$this->getCity()."\n");
		}
		if(strlen($this->getZipcode()))
		{
			$body .= ($language->txt("zipcode").": ".$this->getZipcode()."\n");
		}
		if(strlen($this->getCountry()))
		{
			$body .= ($language->txt("country").": ".$this->getCountry()."\n");
		}
		if(strlen($this->getPhoneOffice()))
		{
			$body .= ($language->txt("phone_office").": ".$this->getPhoneOffice()."\n");
		}
		if(strlen($this->getPhoneHome()))
		{
			$body .= ($language->txt("phone_home").": ".$this->getPhoneHome()."\n");
		}
		if(strlen($this->getPhoneMobile()))
		{
			$body .= ($language->txt("phone_mobile").": ".$this->getPhoneMobile()."\n");
		}
		if(strlen($this->getFax()))
		{
			$body .= ($language->txt("fax").": ".$this->getFax()."\n");
		}
		if(strlen($this->getEmail()))
		{
			$body .= ($language->txt("email").": ".$this->getEmail()."\n");
		}
		if(strlen($this->getHobby()))
		{
			$body .= ($language->txt("hobby").": ".$this->getHobby()."\n");
		}
		if(strlen($this->getComment()))
		{
			$body .= ($language->txt("referral_comment").": ".$this->getComment()."\n");
		}
		if(strlen($this->getMatriculation()))
		{
			$body .= ($language->txt("matriculation").": ".$this->getMatriculation()."\n");
		}
		if(strlen($this->getCreateDate()))
		{
			ilDatePresentation::setUseRelativeDates(false);
			ilDatePresentation::setLanguage($language);
			$date = ilDatePresentation::formatDate(new ilDateTime($this->getCreateDate(),IL_CAL_DATETIME));
			ilDatePresentation::resetToDefaults();
			
			$body .= ($language->txt("create_date").": ".$date."\n");
		}

		foreach($rbacreview->getGlobalRoles() as $role)
		{
			if($rbacreview->isAssigned($this->getId(),$role))
			{
				$gr[] = ilObjRole::_lookupTitle($role);
			}
		}
		if(count($gr))
		{
			$body .= ($language->txt('reg_role_info').': '.implode(',',$gr)."\n");
		}

		// Time limit
		if($this->getTimeLimitUnlimited())
		{
			$body .= ($language->txt('time_limit').": ".$language->txt('crs_unlimited')."\n");
		}
		else
		{
			ilDatePresentation::setUseRelativeDates(false);
			ilDatePresentation::setLanguage($language);
			$period = ilDatePresentation::formatPeriod(new ilDateTime($this->getTimeLimitFrom(),IL_CAL_UNIX),
				new ilDateTime($this->getTimeLimitUntil(),IL_CAL_UNIX));
			ilDatePresentation::resetToDefaults();
			
			$body .= $language->txt('time_limit').': '.$period; 
			/*
			$body .= ($language->txt('time_limit').": ".$language->txt('crs_from')." ".
					  ilFormat::formatUnixTime($this->getTimeLimitFrom(), true)." ".
					  $language->txt('crs_to')." ".
					  ilFormat::formatUnixTime($this->getTimeLimitUntil(), true)."\n");
			*/
		}
		return $body;
	}

	function setInstantMessengerId($a_im_type, $a_im_id)
	{
		$var = "im_".$a_im_type;
		$this->$var = $a_im_id;
	}

	function getInstantMessengerId($a_im_type)
	{
		$var = "im_".$a_im_type;
		return $this->$var;
	}

	function setDelicious($a_delicious)
	{
		$this->delicious = $a_delicious;
	}

	function getDelicious()
	{
		return $this->delicious;
	}

	/**
	* Lookup news feed hash for user. If hash does not exist, create one.
	*/
	function _lookupFeedHash($a_user_id, $a_create = false)
	{
		global $ilDB;

		if ($a_user_id > 0)
		{
			$set = $ilDB->queryF("SELECT feed_hash from usr_data WHERE usr_id = %s",
				array("integer"), array($a_user_id));
			if ($rec = $ilDB->fetchAssoc($set))
			{
				if (strlen($rec["feed_hash"]) == 32)
				{
					return $rec["feed_hash"];
				}
				else if($a_create)
				{
					$hash = md5(rand(1,9999999) + str_replace(" ", "", (string) microtime()));
					$ilDB->manipulateF("UPDATE usr_data SET feed_hash = %s".
						" WHERE usr_id = %s",
						array("text", "integer"),
						array($hash, $a_user_id));
					return $hash;
				}
			}
		}

		return false;
	}

	/**
	* Lookup news feed password for user
	* @param	integer	user_id
	* @return	string	feed_password md5-encoded, or false
	*/
	function _getFeedPass($a_user_id)
	{
		global $ilDB;

		if ($a_user_id > 0)
		{
			return ilObjUser::_lookupPref($a_user_id, "priv_feed_pass");
		}
		return false;
	}

	/**
	* Set news feed password for user
	* @param	integer	user_id
	* @param 	string	new password
	*/
	function _setFeedPass($a_user_id, $a_password)
	{
		global $ilDB;
		
		ilObjUser::_writePref($a_user_id, "priv_feed_pass",
			($a_password=="") ? "" : md5($a_password));
	}

	/**
	* check if a login name already exists
	* You may exclude a user from the check by giving his user id as 2nd paramter
	* @access	public
	* @access	static
	* @param	string	login name
	* @param	integer	user id of user to exclude (optional)
	* @return	boolean
	*/
	public static function _loginExists($a_login,$a_user_id = 0)
	{
		global $ilDB;

		$q = "SELECT DISTINCT login, usr_id FROM usr_data ".
			 "WHERE login = %s";
		$types[] = "text";
		$values[] = $a_login;
			 
		if ($a_user_id != 0)
		{
			$q.= " AND usr_id != %s ";
			$types[] = "integer";
			$values[] = $a_user_id;
		}
			 
		$r = $ilDB->queryF($q, $types, $values);

		if ($row = $ilDB->fetchAssoc($r))
		{
			return $row['usr_id'];
		}
		return false;
	}

	/**
	 * Check if an external account name already exists
	 *
	 * @access public
	 * @static
	 *
	 * @param string external account
	 * @param string auth mode
	 *
	 */
	public static function _externalAccountExists($a_external_account,$a_auth_mode)
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT * FROM usr_data ".
			"WHERE ext_account = %s AND auth_mode = %s",
			array("text", "text"),
			array($a_external_account, $a_auth_mode));
		return $ilDB->fetchAssoc($res) ? true :false;
	}

	/**
	 * return array of complete users which belong to a specific role
	 *
	 * @param int role id
	 * @param int $active 	if -1, all users will be delivered, 0 only non active, 1 only active users
	 */

	public static function _getUsersForRole($role_id, $active = -1) {
		global $ilDB, $rbacreview;
		$data = array();

		$ids = $rbacreview->assignedUsers($role_id);

		if (count ($ids) == 0)
		{
			$ids = array (-1);
		}

		$query = "SELECT usr_data.*, usr_pref.value AS language
							FROM usr_data
							LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
							WHERE ".$ilDB->in("usr_data.usr_id", $ids, false, "integer");
		$values[] = "language";
		$types[] = "text";


		if (is_numeric($active) && $active > -1)
		{
			$query .= " AND usr_data.active = %s";
			$values[] = $active;
			$types[] = "integer";
		}
		
		$query .= " ORDER BY usr_data.lastname, usr_data.firstname ";
		
		$r = $ilDB->queryF($query, $types, $values);
		$data = array();
		while ($row = $ilDB->fetchAssoc($r))
		{
			$data[] = $row;
		}
		return $data;
	}


	/**
	*	get users for a category or from system folder
	* @param	$ref_id		ref id of object
	* @param 	$active		can be -1 (ignore), 1 = active, 0 = not active user
	*/
	public static function _getUsersForFolder ($ref_id, $active) {
		global $ilDB;
		$data = array();
		$query = "SELECT usr_data.*, usr_pref.value AS language FROM usr_data LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id and usr_pref.keyword = %s WHERE 1 = 1 ";
		$types[] = "text";
		$values[] = "language";

		if (is_numeric($active) && $active > -1)
		{
			$query .= " AND usr_data.active = %s";
			$values[] = $active;
			$types[] = "integer";
		}

		if ($ref_id != USER_FOLDER_ID)
		{
		    $query.= " AND usr_data.time_limit_owner = %s";
			$values[] = $ref_id;
			$types[] = "integer";
		}

		$query .=	" AND usr_data.usr_id != %s ";
		$values[] = ANONYMOUS_USER_ID;
		$types[] = "integer";

		$query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

		$result = $ilDB->queryF($query, $types, $values);
		$data = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($data, $row);
		}

		return $data;
	}


	/**
	* return user data for group members
	* @param int array of member ids
	* @param int active can be -1 (ignore), 1 = active, 0 = not active user
	*/
	public static function _getUsersForGroup ($a_mem_ids, $active = -1)
	{
		return ilObjUser::_getUsersForIds($a_mem_ids, $active);
	}


	/**
	* return user data for given user id
	* @param int array of member ids
	* @param int active can be -1 (ignore), 1 = active, 0 = not active user
	*/
	public static function _getUsersForIds ($a_mem_ids, $active = -1, $timelimitowner = -1)
	{
		global $rbacadmin, $rbacreview, $ilDB;

		// quote all ids
		$ids = array();
		foreach ($a_mem_ids as $mem_id) {
			$ids [] = $ilDB->quote($mem_id);
		}

		$query = "SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
		          WHERE ".$ilDB->in("usr_data.usr_id", $ids, false, "integer")."
					AND usr_data.usr_id != %s";
		$values[] = "language";
		$types[] = "text";
		$values[] = ANONYMOUS_USER_ID;
		$types[] = "integer";

  	    if (is_numeric($active) && $active > -1)
		{
			$query .= " AND active = %s";
			$values[] = $active;
			$types[] = "integer";
		}

  		if ($timelimitowner != USER_FOLDER_ID && $timelimitowner != -1)
		{
		    $query.= " AND usr_data.time_limit_owner = %s";
			$values[] = $timelimitowner;
			$types[] = "integer";

		}

  		$query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

		$result = $ilDB->queryF($query, $types, $values);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$mem_arr[] = $row;
		}

		return $mem_arr ? $mem_arr : array();
	}



	/**
	 * return user data for given user ids
	 *
	 * @param array of internal ids or numerics $a_internalids
	 */
	public static function _getUserData ($a_internalids) {
		global $ilDB;

		$ids = array();
		if (is_array($a_internalids)) {
			foreach ($a_internalids as $internalid) {
				if (is_numeric ($internalid))
				{
					$ids[] = $internalid;
				}
				else
				{
					$parsedid = ilUtil::__extractId($internalid, IL_INST_ID);
					if (is_numeric($parsedid) && $parsedid > 0)
					{
						$ids[] = $parsedid;
					}
				}
			}
		}
		if (count($ids) == 0)
			$ids [] = -1;

		$query = "SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref
		          ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
		          WHERE ".$ilDB->in("usr_data.usr_id", $ids, false, "integer");
		$values[] = "language";
		$types[] = "text";

		$query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

		$data = array();
		$result = $ilDB->queryF($query, $types, $values);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * get preferences for user
	 *
	 * @param int $user_id
	 * @return array of keys (pref_keys) and values
	 */
	public static function _getPreferences ($user_id)
	{
		global $ilDB;

		$prefs = array();

		$r = $ilDB->queryF("SELECT * FROM usr_pref WHERE usr_id = %s",
			array("integer"), array($user_id));

		while($row = $ilDB->fetchAssoc($r))
		{
			$prefs[$row["keyword"]] = $row["value"];
		}

		return $prefs;
	}


	public static function _resetLoginAttempts($a_usr_id)
	{
		global $ilDB;

		$query = "UPDATE usr_data SET usr_data.login_attempts = 0 WHERE usr_data.usr_id = %s";
		$affected = $ilDB->manipulateF( $query, array('integer'), array($a_usr_id) );

		if($affected) return true;
		else return false;
	}

	public static function _getLoginAttempts($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT usr_data.login_attempts FROM usr_data WHERE usr_data.usr_id = %s";
		$result = $ilDB->queryF( $query, array('integer'), array($a_usr_id) );
		$record = $ilDB->fetchAssoc( $result );
		$login_attempts = $record['login_attempts'];

		return $login_attempts;
	}

	public static function _incrementLoginAttempts($a_usr_id)
	{
		global $ilDB;

		$query = "UPDATE usr_data SET usr_data.login_attempts = (usr_data.login_attempts + 1) WHERE usr_data.usr_id = %s";
		$affected = $ilDB->manipulateF( $query, array('integer'), array($a_usr_id) );

		if($affected) return true;
		else return false;
	}

	public static function _setUserInactive($a_usr_id)
	{
		global $ilDB;

		$query = "UPDATE usr_data SET usr_data.active = 0 WHERE usr_data.usr_id = %s";
		$affected = $ilDB->manipulateF( $query, array('integer'), array($a_usr_id) );

		if($affected) return true;
		else return false;
	}
	
	/**
	 * returns true if public is profile, false otherwise
	 *
	 * @return boolean
	 */
	public function hasPublicProfile() {
		return $this->getPref("public_profile") == "y";
	}
	
	/**
	 * returns firstname lastname and login if profile is public, login otherwise
	 *
	 * @return string
	 */	
	public function getPublicName() 
	{
		if ($this->hasPublicProfile())
			return $this->getFirstname()." ".$this->getLastname()." (".$this->getLogin().")";
		else 	
			return $this->getLogin();
		
	}
	
	public static function _writeHistory($a_usr_id, $a_login)
	{
		global $ilDB;

//		return true; // Temporarily disabled (missing oracle support)
			
		$res = $ilDB->queryF('SELECT * FROM loginname_history WHERE usr_id = %s AND login = %s AND history_date = %s',
						array('integer', 'text', 'integer'),
						array($a_usr_id, $a_login, time()));
		
		if($count = $ilDB->numRows($res) == 0 )
		{
			$result = $ilDB->manipulateF('
						INSERT INTO loginname_history 
								(usr_id, login, history_date)
						VALUES 	(%s, %s, %s)',
						array('integer', 'text', 'integer'),
						array($a_usr_id, $a_login, time()));
		}
		
		
		return true;
	}
	
	/**
	* reads all active sessions from db and returns users that are online
	* OR returns only one active user if a user_id is given
	*
	* @param	integer	user_id (optional)
	* @return	array
	*/
	function _getUsersOnline($a_user_id = 0, $a_no_anonymous = false)
	{
		global $ilDB;

		$pd_set = new ilSetting("pd");
		$atime = $pd_set->get("user_activity_time") * 60;
		$ctime = time();

		if ($a_user_id == 0)
		{
			$where = "WHERE user_id != 0 AND NOT agree_date IS NULL ";
			$type_array = array("integer");
			$val_array = array(time());
		}
		else
		{
			$where = "WHERE user_id = %s ";
			$type_array = array("integer", "integer");
			$val_array = array($a_user_id, time());
		}

		$no_anonym = ($a_no_anonymous)
			? "AND user_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer")." "
			: "";

		$r = $ilDB->queryF("SELECT count(user_id) as num,user_id,firstname,lastname,title,login,last_login,max(ctime) AS ctime ".
			"FROM usr_session ".
			"LEFT JOIN usr_data u ON user_id = u.usr_id ".
			"LEFT JOIN usr_pref p ON (p.usr_id = u.usr_id AND p.keyword = ".
				$ilDB->quote("hide_own_online_status", "text").") ".$where.
			"AND expires > %s ".
			"AND (p.value IS NULL OR NOT p.value = ".$ilDB->quote("y", "text").") ".
			$no_anonym.
			"GROUP BY user_id,firstname,lastname,title,login,last_login ".
			"ORDER BY lastname, firstname", $type_array, $val_array);

		while ($user = $ilDB->fetchAssoc($r))
		{
			if ($atime <= 0
				|| $user["ctime"] + $atime > $ctime)
			{
				$users[$user["user_id"]] = $user;
			}
		}

		return $users ? $users : array();
	}

	/**
	* reads all active sessions from db and returns users that are online
	* and who have a local role in a group or a course for which the
    * the current user has also a local role.
	*
	* @param	integer	user_id User ID of the current user.
	* @return	array
	*/
	function _getAssociatedUsersOnline($a_user_id, $a_no_anonymous = false)
	{
		global $ilias, $ilDB;

		$pd_set = new ilSetting("pd");
		$atime = $pd_set->get("user_activity_time") * 60;
		$ctime = time();
		$no_anonym = ($a_no_anonymous)
			? "AND user_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer")." "
			: "";

		// Get a list of object id's of all courses and groups for which
		// the current user has local roles.
		// Note: we have to use DISTINCT here, because a user may assume
		// multiple roles in a group or a course.
		$q = "SELECT DISTINCT dat.obj_id as obj_id ".
			"FROM rbac_ua ua ".
			"JOIN rbac_fa fa ON fa.rol_id = ua.rol_id ".
			"JOIN object_reference r1 ON r1.ref_id = fa.parent ".
			"JOIN tree ON tree.child = r1.ref_id ".
			"JOIN object_reference r2 ON r2.ref_id = tree.parent ".
			"JOIN object_data dat ON dat.obj_id = r2.obj_id ".
			"WHERE ua.usr_id = ".$ilDB->quote($a_user_id, "integer")." ".
			"AND fa.assign = ".$ilDB->quote("y", "text")." ".
			"AND dat.type IN (".$ilDB->quote("crs", "text").",".
			$ilDB->quote("grp", "text").")";
		$r = $ilDB->query($q);

		while ($row = $ilDB->fetchAssoc($r))
		{
			$groups_and_courses_of_user[] = $row["obj_id"];
		}
		// If the user is not in a course or a group, he has no associated users.
		if (count($groups_and_courses_of_user) == 0)
		{
			$q = "SELECT count(user_id) as num,ctime,user_id,firstname,lastname,title,login,last_login ".
				"FROM usr_session ".
				"JOIN usr_data ON user_id=usr_id ".
				"WHERE user_id = ".$ilDB->quote($a_user_id, "integer")." ".
				$no_anonym.
				" AND NOT agree_date IS NULL ".
				"AND expires > ".$ilDB->quote(time(), "integer")." ".
				"GROUP BY user_id,ctime,firstname,lastname,title,login,last_login";
			$r = $ilDB->query($q);
		}
		else
		{
			$q = "SELECT count(user_id) as num,s.ctime,s.user_id,ud.firstname,ud.lastname,ud.title,ud.login,ud.last_login ".
				"FROM usr_session s ".
				"JOIN usr_data ud ON ud.usr_id = s.user_id ".
				"JOIN rbac_ua ua ON ua.usr_id = s.user_id ".
				"JOIN rbac_fa fa ON fa.rol_id = ua.rol_id ".
				"JOIN tree ON tree.child = fa.parent ".
				"JOIN object_reference or1 ON or1.ref_id = tree.parent ".
				"JOIN object_data od ON od.obj_id = or1.obj_id ".
				"LEFT JOIN usr_pref p ON (p.usr_id = ud.usr_id AND p.keyword = ".
					$ilDB->quote("hide_own_online_status", "text").") ".
				"WHERE s.user_id != 0 ".
				$no_anonym.
				"AND (p.value IS NULL OR NOT p.value = ".$ilDB->quote("y", "text").") ".
				"AND s.expires > ".$ilDB->quote(time(),"integer")." ".
				"AND fa.assign = ".$ilDB->quote("y", "text")." ".
				" AND NOT ud.agree_date IS NULL ".
				"AND ".$ilDB->in("od.obj_id", $groups_and_courses_of_user, false, "integer")." ".
				"GROUP BY s.user_id,s.ctime,ud.firstname,ud.lastname,ud.title,ud.login,ud.last_login ".
				"ORDER BY ud.lastname, ud.firstname";
			$r = $ilDB->query($q);
		}

		while ($user = $ilDB->fetchAssoc($r))
		{
			if ($atime <= 0
				|| $user["ctime"] + $atime > $ctime)
			{
				$users[$user["user_id"]] = $user;
			}
		}

		return $users ? $users : array();
	}
	
	/**
	* Generates a unique hashcode for activating a user profile after registration
	* 
	* @param integer $a_usr_id user id of the current user
	* @return string generated hashcode
	*/
	public static function _generateRegistrationHash($a_usr_id)
	{
		global $ilDB;
		
		do
		{
			$continue = false;
			
			$hashcode = substr(md5(uniqid(rand(), true)), 0, 16);
			
			$res = $ilDB->queryf('
				SELECT COUNT(usr_id) cnt FROM usr_data 
				WHERE reg_hash = %s',
		        array('text'),
		        array($hashcode));		         
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if($row->cnt > 0) $continue = true;
				break;
			}
			
			if($continue) continue;
			
			$ilDB->manipulateF('
				UPDATE usr_data	
				SET reg_hash = %s	
				WHERE usr_id = %s',
				array('text', 'integer'),
				array($hashcode, (int)$a_usr_id)
			);
			
			break;
			
		} while(true);		
		
		return $hashcode;
	}
	
	/**
	* Verifies a registration hash
	* 
	* @throws ilRegistrationHashExpiredException
	* @throws ilRegistrationHashNotFoundException
	* @param string $a_hash hashcode
	* @return integer user id of the user
	*/
	public static function _verifyRegistrationHash($a_hash)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT usr_id, create_date FROM usr_data 
			WHERE reg_hash = %s',
	        array('text'),
	        array($a_hash));		         
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			require_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
			$oRegSettigs = new ilRegistrationSettings();
			
			if((int)$oRegSettigs->getRegistrationHashLifetime() != 0 &&
			   time() - (int)$oRegSettigs->getRegistrationHashLifetime() > strtotime($row->create_date))
			{
				require_once 'Services/Registration/exceptions/class.ilRegConfirmationLinkExpiredException.php';
				throw new ilRegConfirmationLinkExpiredException('reg_confirmation_hash_life_time_expired');	
			}		
			
			$ilDB->manipulateF('
				UPDATE usr_data	
				SET reg_hash = %s
				WHERE usr_id = %s',
				array('text', 'integer'),
				array('', (int)$row->usr_id)
			);
			
			return $row->usr_id;
		}		
		
		require_once 'Services/Registration/exceptions/class.ilRegistrationHashNotFoundException.php';
		throw new ilRegistrationHashNotFoundException('reg_confirmation_hash_not_found');
	}

	function setBirthday($a_birthday)
	{
		$this->birthday = $a_birthday;
	}
	
	function getBirthday()
	{
		return $this->birthday;
	}
} // END class ilObjUser
?>
