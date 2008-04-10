<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

define ("IL_PASSWD_PLAIN", "plain");
define ("IL_PASSWD_MD5", "md5");			// ILIAS 3 Password
define ("IL_PASSWD_CRYPT", "crypt");		// ILIAS 2 Password


require_once "classes/class.ilObject.php";

/**
* user class for ilias
*
* @author	Sascha Hofmann <saschahofmann@gmx.de>
* @author	Stefan Meyer <smeyer@databay.de>
* @author	Peter Gabriel <pgabriel@databay.de>
* @version	$Id$
*
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
	var $approve_date;
	var $active;
	//var $ilinc_id; // unique Id for netucate ilinc service
	var $client_ip; // client ip to check before login
	var $auth_mode; // authentication mode

	var $im_icq;
	var $im_yahoo;
	var $im_msn;
	var $im_aim;
	var $im_skype;

	var $delicious;
	var $latitude;
	var $longitude;
	var $loc_zoom;

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

		// TODO: fetching default role should be done in rbacadmin
		$q = "SELECT * FROM usr_data ".
			 "LEFT JOIN rbac_ua ON usr_data.usr_id=rbac_ua.usr_id ".
			 "WHERE usr_data.usr_id= ".$ilDB->quote($this->id);
		$r = $this->ilias->db->query($q);

		if ($r->numRows() > 0)
		{
			$data = $r->fetchRow(DB_FETCHMODE_ASSOC);

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
		global $ilErr;

		// basic personal data
		$this->setLogin($a_data["login"]);
		if (! $a_data["passwd_type"])
		{
			 $ilErr->raiseError("<b>Error: passwd_type missing in function assignData(). ".
								$this->id."!</b><br />class: ".get_class($this)."<br />Script: "
								.__FILE__."<br />Line: ".__LINE__, $ilErr->FATAL);
		}

		if ($a_data["passwd"] != "********")
		{
			$this->setPasswd($a_data["passwd"], $a_data["passwd_type"]);
		}

		$this->setGender($a_data["gender"]);
		$this->setUTitle($a_data["title"]);
		$this->setFirstname($a_data["firstname"]);
		$this->setLastname($a_data["lastname"]);
		$this->setFullname();

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
		$this->accept_date = $a_data["agree_date"];

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
		global $ilErr, $ilDB;

		switch ($this->passwd_type)
		{
			case IL_PASSWD_PLAIN:
				$pw_field = "passwd";
				$pw_value = md5($this->passwd);
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

		if ($a_from_formular)
		{
            $q = "INSERT INTO usr_data "
                . "(usr_id,login,".$pw_field.",firstname,lastname,title,gender,"
                . "email,hobby,institution,department,street,city,zipcode,country,"
                . "phone_office,phone_home,phone_mobile,fax,last_login,last_update,create_date,"
                . "referral_comment,matriculation,client_ip, approve_date,active,"
                . "time_limit_unlimited,time_limit_until,time_limit_from,time_limit_owner,auth_mode,ext_account,profile_incomplete,"
                . "im_icq,im_yahoo,im_msn,im_aim,im_skype,delicious,latitude,longitude,loc_zoom) "
                . "VALUES "
                . "(".
				$ilDB->quote($this->id).",".
				$ilDB->quote($this->login).",".
				$ilDB->quote($pw_value).",".
                $ilDB->quote($this->firstname).",".
				$ilDB->quote($this->lastname).",".
                $ilDB->quote($this->utitle).",".
				$ilDB->quote($this->gender).",".
                $ilDB->quote($this->email).",".
				$ilDB->quote($this->hobby).",".
                $ilDB->quote($this->institution).",".
				$ilDB->quote($this->department).",".
                $ilDB->quote($this->street).",".
                $ilDB->quote($this->city).",".
				$ilDB->quote($this->zipcode).",".
				$ilDB->quote($this->country).",".
                $ilDB->quote($this->phone_office).",".
				$ilDB->quote($this->phone_home).",".
                $ilDB->quote($this->phone_mobile).",".
				$ilDB->quote($this->fax).", 0, now(), now(),".
                $ilDB->quote($this->referral_comment).",".
				$ilDB->quote($this->matriculation).",".
				$ilDB->quote($this->client_ip).",".
				$ilDB->quote($this->approve_date).",".
				$ilDB->quote($this->active).",".
                $ilDB->quote($this->getTimeLimitUnlimited()).",".
				$ilDB->quote($this->getTimeLimitUntil()).",".
				$ilDB->quote($this->getTimeLimitFrom()).",".
				$ilDB->quote($this->getTimeLimitOwner()).",".
                $ilDB->quote($this->getAuthMode()).",".
				$ilDB->quote($this->getExternalAccount()).",".
				$ilDB->quote($this->getProfileIncomplete()).",".
				$ilDB->quote($this->im_icq).",".
				$ilDB->quote($this->im_yahoo).",".
				$ilDB->quote($this->im_msn).",".
				$ilDB->quote($this->im_aim).",".
				$ilDB->quote($this->im_skype).",".
				$ilDB->quote($this->delicious).",".
				$ilDB->quote($this->latitude).",".
				$ilDB->quote($this->longitude).",".
				$ilDB->quote($this->loc_zoom).
				")";
		}
		else
		{
            $q = "INSERT INTO usr_data ".
                "(usr_id,login,".$pw_field.",firstname,lastname,title,gender,"
                . "email,hobby,institution,department,street,city,zipcode,country,"
                . "phone_office,phone_home,phone_mobile,fax,last_login,last_update,create_date,"
                . "referral_comment,matriculation,client_ip, approve_date,active,"
                . "time_limit_unlimited,time_limit_until,time_limit_from,time_limit_owner,auth_mode,ext_account,profile_incomplete,"
                . "im_icq,im_yahoo,im_msn,im_aim,im_skype,delicious,latitude,longitude,loc_zoom) "
                . "VALUES "
                ."(".
				$ilDB->quote($this->id).",".
				$ilDB->quote($this->login).",".
				$ilDB->quote($pw_value).",".
                $ilDB->quote($this->firstname).",".
				$ilDB->quote($this->lastname).",".
                $ilDB->quote($this->utitle).",".
				$ilDB->quote($this->gender).",".
                $ilDB->quote($this->email).",".
				$ilDB->quote($this->hobby).",".
                $ilDB->quote($this->institution).",".
				$ilDB->quote($this->department).",".
                $ilDB->quote($this->street).",".
                $ilDB->quote($this->city).",".
				$ilDB->quote($this->zipcode).",".
				$ilDB->quote($this->country).",".
                $ilDB->quote($this->phone_office).",".
				$ilDB->quote($this->phone_home).",".
                $ilDB->quote($this->phone_mobile).",".
				$ilDB->quote($this->fax).", 0, now(), now(),".
                $ilDB->quote($this->referral_comment).",".
				$ilDB->quote($this->matriculation).",".
				$ilDB->quote($this->client_ip).",".
				$ilDB->quote($this->approve_date).",".
				$ilDB->quote($this->active).",".
                $ilDB->quote($this->getTimeLimitUnlimited()).",".
				$ilDB->quote($this->getTimeLimitUntil()).",".
				$ilDB->quote($this->getTimeLimitFrom()).",".
				$ilDB->quote($this->getTimeLimitOwner()).",".
				$ilDB->quote($this->getAuthMode()).",".
				$ilDB->quote($this->getExternalAccount()).",".
				$ilDB->quote($this->getProfileIncomplete()).",".
				$ilDB->quote($this->im_icq).",".
				$ilDB->quote($this->im_yahoo).",".
				$ilDB->quote($this->im_msn).",".
				$ilDB->quote($this->im_aim).",".
				$ilDB->quote($this->im_skype).",".
				$ilDB->quote($this->delicious).",".
				$ilDB->quote($this->latitude).",".
				$ilDB->quote($this->longitude).",".
				$ilDB->quote($this->loc_zoom).
				")";
		}

		$this->ilias->db->query($q);

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

		//$this->id = $this->data["Id"];

        $this->syncActive();

		$pw_udpate = '';
		switch ($this->passwd_type)
		{
			case IL_PASSWD_PLAIN:
				$pw_update = "i2passwd='', passwd='".md5($this->passwd)."'";
				break;

			case IL_PASSWD_MD5:
				$pw_update = "i2passwd='', passwd='".$this->passwd."'";
				break;

			case IL_PASSWD_CRYPT:
				$pw_update = "passwd='', i2passwd='".$this->passwd."'";
				break;

			default :
				$ilErr->raiseError("<b>Error: passwd_type missing in function update()".$this->id."!</b><br />class: ".
								   get_class($this)."<br />Script: ".__FILE__."<br />Line: ".__LINE__, $ilErr->FATAL);
		}
		$q = "UPDATE usr_data SET ".
            "gender = ".$ilDB->quote($this->gender).",".
            "title= ".$ilDB->quote($this->utitle).",".
            "firstname= ".$ilDB->quote($this->firstname).",".
            "lastname= ".$ilDB->quote($this->lastname).",".
            "email= ".$ilDB->quote($this->email).",".
            "hobby= ".$ilDB->quote($this->hobby).",".
            "institution= ".$ilDB->quote($this->institution).",".
            "department= ".$ilDB->quote($this->department).",".
            "street= ".$ilDB->quote($this->street).",".
            "city= ".$ilDB->quote($this->city).",".
            "zipcode= ".$ilDB->quote($this->zipcode).",".
            "country= ".$ilDB->quote($this->country).",".
            "phone_office= ".$ilDB->quote($this->phone_office).",".
            "phone_home= ".$ilDB->quote($this->phone_home).",".
            "phone_mobile= ".$ilDB->quote($this->phone_mobile).",".
            "fax= ".$ilDB->quote($this->fax).",".
            "referral_comment= ".$ilDB->quote($this->referral_comment).",".
            "matriculation= ".$ilDB->quote($this->matriculation).",".
            "client_ip= ".$ilDB->quote($this->client_ip).",".
            "approve_date= ".$ilDB->quote($this->approve_date).",".
            "active= ".$ilDB->quote($this->active).",".
            "time_limit_owner= ".$ilDB->quote($this->getTimeLimitOwner()).",".
            "time_limit_unlimited= ".$ilDB->quote($this->getTimeLimitUnlimited()).",".
            "time_limit_from= ".$ilDB->quote($this->getTimeLimitFrom()).",".
            "time_limit_until= ".$ilDB->quote($this->getTimeLimitUntil()).",".
            "time_limit_message= ".$ilDB->quote($this->getTimeLimitMessage()).",".
			"profile_incomplete = ".$ilDB->quote($this->getProfileIncomplete()).",".
            "auth_mode= ".$ilDB->quote($this->getAuthMode()).", ".
			"ext_account= ".$ilDB->quote($this->getExternalAccount()).",".
			$pw_update.", ".
			"im_icq= ".$ilDB->quote($this->getInstantMessengerId('icq')).",".
			"im_yahoo= ".$ilDB->quote($this->getInstantMessengerId('yahoo')).",".
			"im_msn= ".$ilDB->quote($this->getInstantMessengerId('msn')).",".
			"im_aim= ".$ilDB->quote($this->getInstantMessengerId('aim')).",".
			"im_skype= ".$ilDB->quote($this->getInstantMessengerId('skype')).",".
			"delicious= ".$ilDB->quote($this->getDelicious()).",".
			"latitude= ".$ilDB->quote($this->getLatitude()).",".
			"longitude= ".$ilDB->quote($this->getLongitude()).",".
			"loc_zoom= ".$ilDB->quote($this->getLocationZoom()).",".
            "last_update=now()".
		//	"ilinc_id= ".$ilDB->quote($this->ilinc_id).",".
		//	"ilinc_login= ".$ilDB->quote($this->ilinc_login).",".
		//	"ilinc_passwd= ".$ilDB->quote($this->ilinc_passwd)." ".
            "WHERE usr_id= ".$ilDB->quote($this->id);

		$this->ilias->db->query($q);
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

		$q = "UPDATE usr_data SET agree_date = now()".
			 "WHERE usr_id = ".$ilDB->quote($this->getId());
		$ilDB->query($q);

	}

	function _lookupEmail($a_user_id)
	{
		global $ilDB;

		$query = "SELECT email FROM usr_data WHERE usr_id = ".$ilDB->quote((int) $a_user_id);
		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->email;
		}
		return false;
	}

	function _lookupGender($a_user_id)
	{
		global $ilDB;

		$query = "SELECT gender FROM usr_data WHERE usr_id = ".
			$ilDB->quote((int) $a_user_id);
		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->gender;
		}
		return false;
	}

	function _lookupClientIP($a_user_id)
	{
		global $ilDB;

		$query = "SELECT client_ip FROM usr_data WHERE usr_id = ".
			$ilDB->quote((int) $a_user_id);
		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->client_ip;
		}
		return "";
	}


	/**
	* lookup user name
	*/
	function _lookupName($a_user_id)
	{
		global $ilDB;

		$q = "SELECT firstname, lastname, title FROM usr_data".
			" WHERE usr_id =".$ilDB->quote($a_user_id);
		$user_set = $ilDB->query($q);
		$user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC);
		return array("user_id" => $a_user_id,
			"firstname" => $user_rec["firstname"],
			"lastname" => $user_rec["lastname"],
			"title" => $user_rec["title"]);
	}

	/**
	* lookup user name
	*/
	function _lookupFields($a_user_id)
	{
		global $ilDB;

		$q = "SELECT * FROM usr_data".
			" WHERE usr_id =".$ilDB->quote($a_user_id);
		$user_set = $ilDB->query($q);
		$user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC);
		return $user_rec;
	}

	/**
	* lookup login
	*/
	function _lookupLogin($a_user_id)
	{
		global $ilDB;

		$q = "SELECT login FROM usr_data".
			" WHERE usr_id =".$ilDB->quote($a_user_id);
		$user_set = $ilDB->query($q);
		$user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC);
		return $user_rec["login"];
	}
	
	/**
	* lookup external account for login and authmethod
	*/
	function _lookupExternalAccount($a_user_id)
	{
		global $ilDB;

		$q = "SELECT ext_account FROM usr_data".
			" WHERE usr_id =".$ilDB->quote($a_user_id);
		$user_set = $ilDB->query($q);
		$user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC);
		return $user_rec["ext_account"];
	}

	/**
	* lookup id by login
	*/
	function _lookupId($a_user_str)
	{
		global $ilDB;

		$q = "SELECT usr_id FROM usr_data".
			" WHERE login =".$ilDB->quote($a_user_str);
		$user_set = $ilDB->query($q);
		$user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC);
		return $user_rec["usr_id"];
	}

	/**
	* lookup last login
	*/
	function _lookupLastLogin($a_user_id)
	{
		global $ilDB;

		$q = "SELECT last_login FROM usr_data".
			" WHERE usr_id =".$ilDB->quote($a_user_id);
		$user_set = $ilDB->query($q);
		$user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC);
		return $user_rec["last_login"];
	}


	/**
	* updates the login data of a "user"
	* // TODO set date with now() should be enough
	* @access	public
	*/
	function refreshLogin()
	{
		global $ilDB;

		$q = "UPDATE usr_data SET ".
			 "last_login = now() ".
			 "WHERE usr_id = ".$ilDB->quote($this->id);

		$this->ilias->db->query($q);
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

		$q = "UPDATE usr_data SET ".
			 "passwd= ".$ilDB->quote($this->passwd)." ".
			 "WHERE usr_id= ".$ilDB->quote($this->id);

		$this->ilias->db->query($q);

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

		$q = "UPDATE usr_data SET ".
			 "passwd= ".$ilDB->quote($this->passwd)." ".
			 "WHERE usr_id= ".$ilDB->quote($this->id)." ";
		$this->ilias->db->query($q);

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

		$q = "UPDATE usr_data SET ".
			 "passwd= ".$ilDB->quote($this->passwd)." ".
			 "WHERE usr_id= ".$ilDB->quote($this->id);
		$this->ilias->db->query($q);

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

		$q = "SELECT i2passwd FROM usr_data ".
			 "WHERE login = ".$ilDB->quote($a_user_login)."";
		$user_set = $ilias->db->query($q);

		if ($user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($user_rec["i2passwd"] != "")
			{
				return true;
			}
		}

		return false;
	}

	function _switchToIlias3Password($a_user, $a_pw)
	{
		global $ilias, $ilDB;

		$q = "SELECT i2passwd FROM usr_data ".
			 "WHERE login = ".$ilDB->quote($a_user);

		$user_set = $ilias->db->query($q);

		if ($user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($user_rec["i2passwd"] == ilObjUser::_makeIlias2Password($a_pw))
			{
				$q = "UPDATE usr_data SET passwd= ".$ilDB->quote(md5($a_pw)).", i2passwd=''".
					"WHERE login = ".$ilDB->quote($a_user);
				$ilias->db->query($q);
				return true;
			}
		}

		return false;
	}

	/**
	* update login name
	* @param	string	new login
	* @return	boolean	true on success; otherwise false
	* @access	public
	*/
	function updateLogin($a_login)
	{
		global $ilDB;

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
			 "login= ".$ilDB->quote($this->login)." ".
			 "WHERE usr_id= ".$ilDB->quote($this->id);
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
		global $ilDB;

		$query = sprintf("DELETE FROM usr_pref WHERE usr_id = %s AND keyword = %s",
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($a_keyword . "")
		);
		$ilDB->query($query);
	}

	function _writePref($a_usr_id, $a_keyword, $a_value)
	{
		global $ilDB;

		$query = "";
		if (strlen($a_value))
		{
			$query = sprintf("REPLACE INTO usr_pref VALUES (%s, %s, %s)",
					$ilDB->quote($a_usr_id),
					$ilDB->quote($a_keyword),
					$ilDB->quote($a_value)
				);
		}
		else
		{
			$query = sprintf("DELETE FROM usr_pref WHERE usr_id = %s AND keyword = %s",
				$ilDB->quote($a_usr_id),
				$ilDB->quote($a_keyword)
			);
		}
		$ilDB->query($query);
	}

	/**
	* write all userprefs
	* @access	private
	*/
	function writePrefs()
	{
		global $ilDB;

		//DELETE
		$q = "DELETE FROM usr_pref ".
			 "WHERE usr_id= ".$ilDB->quote($this->id);
		$this->ilias->db->query($q);

		foreach ($this->prefs as $keyword => $value)
		{
			//INSERT
			$q = "INSERT INTO usr_pref ".
				 "(usr_id, keyword, value) ".
				 "VALUES ".
				 "(".$ilDB->quote($this->id).",".$ilDB->quote($keyword).",".
				 $ilDB->quote($value).")";
			$this->ilias->db->query($q);
		}
	}
	
	/**
	 * get timezone of user
	 *
	 * @access public
	 * 
	 */
	public function getUserTimeZone()
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

		$query = "SELECT * FROM usr_pref WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND keyword = ".$ilDB->quote($a_keyword);
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

		$this->prefs = array();

		$q = "SELECT * FROM usr_pref WHERE usr_id = ".
			$ilDB->quote($this->id);
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->prefs[$row["keyword"]] = $row["value"];
		} // while

		return $r->numRows();
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
		$this->ilias->db->query("DELETE FROM usr_data WHERE usr_id = ".
			$ilDB->quote($this->getId()));

		// delete user_prefs
		$this->ilias->db->query("DELETE FROM usr_pref WHERE usr_id= ".
			$ilDB->quote($this->getId()));

		// delete user_session
		$this->ilias->db->query("DELETE FROM usr_session WHERE user_id= ".
			$ilDB->quote($this->getId()));

		// remove user from rbac
		$rbacadmin->removeUser($this->getId());

		// remove bookmarks
		// TODO: move this to class.ilBookmarkFolder
		$q = "DELETE FROM bookmark_tree WHERE tree = ".
			$ilDB->quote($this->getId());
		$this->ilias->db->query($q);

		$q = "DELETE FROM bookmark_data WHERE user_id= ".
			$ilDB->quote($this->getId());
		$this->ilias->db->query($q);

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

		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';
		ilEventParticipants::_deleteByUser($this->getId());

		// Delete group registrations
		$q = "DELETE FROM grp_registration WHERE user_id= ".
			$ilDB->quote($this->getId());
		$this->ilias->db->query($q);

		// Delete user defined field entries
		$this->deleteUserDefinedFieldEntries();

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
			"WHERE usr_id= ".$ilDB->quote($this->id)." ".
			"ORDER BY timestamp DESC";
		$rst = $this->ilias->db->query($q);

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
			"WHERE usr_id= ".$ilDB->quote($this->id)." ";
		$rst = $this->ilias->db->query($q);

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
		
		$query = "SELECT usr_id FROM usr_data ".
			"WHERE login = ".$ilDB->quote($a_username)." ".
			"AND agree_date != '0000-00-00 00:00:00'";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}


	/**
	* check wether user has accepted user agreement
	*/
	function hasAcceptedUserAgreement()
	{
		if ($this->accept_date != "0000-00-00 00:00:00" || $this->login == "root")
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

	function _lookupLanguage($a_usr_id)
	{
		global $ilDB;

		$q = "SELECT value FROM usr_pref WHERE usr_id= ".
			$ilDB->quote($a_usr_id)." AND keyword = 'language'";
		$r = $ilDB->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $row['value'];
		}
		return 'en';
	}


	function _checkPassword($a_usr_id, $a_pw)
	{
		global $ilDB;

		$q = "SELECT passwd FROM usr_data ".
			" WHERE usr_id = ".$ilDB->quote($a_usr_id);
		$usr_set = $ilDB->query($q);

		if($usr_rec = $usr_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($usr_rec["passwd"] == md5($a_pw))
			{
				return true;
			}
		}
		return false;
	}

	function _writeExternalAccount($a_usr_id, $a_ext_id)
	{
		global $ilDB;

		$q = "UPDATE usr_data ".
			" SET ext_account = ".$ilDB->quote($a_ext_id).
			" WHERE usr_id = ".$ilDB->quote($a_usr_id);
		$usr_set = $ilDB->query($q);
	}

	function _writeAuthMode($a_usr_id, $a_auth_mode)
	{
		global $ilDB;

		$q = "UPDATE usr_data ".
			" SET auth_mode = ".$ilDB->quote($a_auth_mode).
			" WHERE usr_id = ".$ilDB->quote($a_usr_id);
		$usr_set = $ilDB->query($q);
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
    * 0000-00-00 00:00:00 indicates that the user has not yet been activated
    * @access   public
    * @return   string      date of last update
    */
    function setApproveDate($a_str)
    {
        $this->approve_date = $a_str;
    }

    /**
    * get the date when the user account was approved
    * @access   public
    * @return   string      date of last update
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
    function getAcceptDate()
    {
        return $this->accept_date;
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
            $this->setApproveDate('0000-00-00 00:00:00');
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
            $this->setActive($currentActive, $this->getUserIdByLogin($ilAuth->getUsername()));
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
        global $ilias, $ilDB;

        $query = "SELECT active FROM usr_data ".
            "WHERE usr_id = ".$ilDB->quote($a_id);

        $row = $ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

        return $row->active ? true : false;
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

		$query = "SELECT usr_id FROM usr_data ".
			"WHERE time_limit_owner = ".$ilDB->quote($a_parent_id);

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
		global $ilDB,$ilAuth;

		// BEGIN WebDAV: Strip Microsoft Domain Names from logins
		require_once ('Services/WebDAV/classes/class.ilDAVServer.php');
		if (ilDAVServer::_isActive()) 
		{
			require_once ('Services/Authentication/classes/class.ilAuthContainerMDB2.php');
			$username = ilAuthContainerMDB2::toUsernameWithoutDomain($this->ilias->auth->getUsername());
			$r = $this->ilias->db->query("SELECT usr_id FROM usr_data WHERE login = ".
				$ilDB->quote($username));
		}
		else
		{
			$r = $this->ilias->db->query("SELECT usr_id FROM usr_data WHERE login = ".
				$ilDB->quote($this->ilias->auth->getUsername()));
		}
		// END WebDAV: Strip Microsoft Domain Names from logins



		$r = $this->ilias->db->query("SELECT usr_id FROM usr_data WHERE login = ".
			$ilDB->quote($ilAuth->getUsername()));
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
     * check to see if current user has been made active
     * @access  public
     * @return  true if active, otherwise false
     */
    function isCurrentUserActive()
    {
		global $ilDB,$ilAuth;

        $r = $this->ilias->db->query("SELECT active FROM usr_data WHERE login= ".
			$ilDB->quote($ilAuth->getUsername()));
        //query has got a result
        if ($r->numRows() > 0)
        {
            $data = $r->fetchRow();
            if (!empty($data[0]))
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
		global $ilias, $ilDB;

		$query = "SELECT usr_id FROM usr_data ".
			"WHERE login = ".$ilDB->quote($a_login);

		$row = $ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

		return $row->usr_id ? $row->usr_id : 0;
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

		$query = "SELECT login FROM usr_data ".
			"WHERE email = ".$ilDB->quote($a_email)." and active=1";

 		$res = $ilias->db->query($query);
 		$ids = array ();
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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

		$query = "SELECT usr_id FROM usr_data ".
			"WHERE email = ".$ilDB->quote($a_email);

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
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
        global $ilias, $ilDB;

        $query = "SELECT login FROM usr_data ".
            "WHERE usr_id = ".$ilDB->quote($a_userid);

        $row = $ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

        return $row->login ? $row->login : false;
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
	function searchUsers($a_search_str, $active = 1, $a_return_ids_only = false, $filter_settings = FALSE)
	{
		// NO CLASS VARIABLES IN STATIC METHODS
		global $ilias, $ilDB;
		
		$active_filter = "";
		$time_limit_filter = "";
		$join_filter = " WHERE ";
		$last_login_filter = "";
		$without_anonymous_users = "AND usr_data.usr_id != ".$ilDB->quote(ANONYMOUS_USER_ID);
		if (is_numeric($active) && $active > -1 && $filter_settings === FALSE) $active_filter = " AND active = ".$ilDB->quote($active);
		global $ilLog; $ilLog->write("active = $active, filter settings = $filter_settings, active_filter = $active_filter");

		
		if ($filter_settings !== FALSE && strlen($filter_settings))
		{
			switch ($filter_settings)
			{
				case -1:
					$active_filter = "";
					// show all users
					break;
				case 0:
					$active_filter = " AND usr_data.active = " . $ilDB->quote("0");
					// show only inactive users
					break;
				case 1:
					$active_filter = " AND usr_data.active = " . $ilDB->quote("1");
					// show only active users
					break;
				case 2:
					$time_limit_filter = " AND usr_data.time_limit_unlimited = " . $ilDB->quote("0");
					// show only users with limited access
					break;
				case 3:
					// show only users without courses
					$join_filter = " LEFT JOIN crs_members ON usr_data.usr_id = crs_members.usr_id WHERE crs_members.usr_id IS NULL AND ";
					break;
				case 4:
					$date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"], $_SESSION["user_filter_data"]["y"]));
					$last_login_filter = sprintf(" AND last_login < %s", $ilDB->quote($date));
					break;
				case 5:
					// show only users with a certain course membership
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$join_filter = " LEFT JOIN crs_members ON usr_data.usr_id = crs_members.usr_id WHERE crs_members.obj_id = (SELECT obj_id FROM object_reference WHERE ref_id = " .
							$ilDB->quote($ref_id) . ") AND ";
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
							$role_ids = join("','", $local_roles);
							$join_filter = " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id IN ('" . $role_ids . "') AND ";
						}
					}
					break;
				case 7:
					global $rbacreview;
					$rol_id = $_SESSION["user_filter_data"];
					if ($rol_id)
					{
						$join_filter = sprintf(" LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id = %s AND ", $ilDB->quote($rol_id));
						$without_anonymous_users = "";
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
				"WHERE object_data.title LIKE ".$ilDB->quote("%".substr($a_search_str,5)."%").
				" and object_data.type = 'role' ".
				"and rbac_ua.rol_id = object_data.obj_id ".
				"and usr_data.usr_id = rbac_ua.usr_id ".
				"AND rbac_ua.usr_id != ".$ilDB->quote(ANONYMOUS_USER_ID);
		}
		else
		{
			$query = "SELECT usr_data.usr_id, usr_data.login, usr_data.firstname, usr_data.lastname, usr_data.email, usr_data.active FROM usr_data ".
				$join_filter .
				"(usr_data.login LIKE ".$ilDB->quote("%".$a_search_str."%")." ".
				"OR usr_data.firstname LIKE ".$ilDB->quote("%".$a_search_str."%")." ".
				"OR usr_data.lastname LIKE ".$ilDB->quote("%".$a_search_str."%")." ".
				"OR usr_data.email LIKE ".$ilDB->quote("%".$a_search_str."%").") ".
				$without_anonymous_users .
				$active_filter . $time_limit_filter . $last_login_filter;
		}
		$ilLog->write($query);
		$res = $ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
	 * search for user data. This method is called from class.ilSearch
	 * @param	object object of search class
	 * @static
	 * @access	public
	 */
	function _search(&$a_search_obj, $active=1)
	{
		global $ilBench, $ilDB;

		// NO CLASS VARIABLES IN STATIC METHODS

		// TODO: CHECK IF ITEMS ARE PUBLIC VISIBLE

		$where_condition = $a_search_obj->getWhereCondition("like",array("login","firstname","lastname","title",
																		 "email","institution","street","city",
																		 "zipcode","country","phone_home","fax"));
		$in = $a_search_obj->getInStatement("usr_data.usr_id");

		$query = "SELECT DISTINCT(usr_data.usr_id) FROM usr_data ".
			"LEFT JOIN usr_pref USING (usr_id) ".
			$where_condition." ".
			$in." ".
			"AND usr_data.usr_id != '".ANONYMOUS_USER_ID."' ";
#			"AND usr_pref.keyword = 'public_profile' ";
#			"AND usr_pref.value = 'y'";

  		if (is_numeric($active)  && $active > -1)
        	$query .= "AND active = ".$ilDB->quote($active);

		$ilBench->start("Search", "ilObjUser_search");
		$res = $a_search_obj->ilias->db->query($query);
		$ilBench->stop("Search", "ilObjUser_search");

		$counter = 0;

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_data[$counter++]["id"]				=  $row->usr_id;

		}
		return $result_data ? $result_data : array();
	}

	/**
	 * STATIC METHOD
	 * create a link to the object
	 * This method used by class.ilSearchGUI.php to a link to the results
	 * @param	int uniq id
	 * @return array array('link','target')
	 * @static
	 * @access	public
	 */
	function _getLinkToObject($a_id)
	{
		return array("profile.php?user=".$a_id,"");
	}

	/*
	* get the memberships(group_ids) of groups that are subscribed to the current user object
	* @param	integer optional user_id
	* @access	public
	*/
	function getGroupMemberships($a_user_id = "")
	{
		global $rbacreview, $tree;

		if (strlen($a_user_id) > 0)
		{
			$user_id = $a_user_id;
		}
		else
		{
			$user_id = $this->getId();
		}

		$grp_memberships = array();

		// get all roles which the user is assigned to
		$roles = $rbacreview->assignedRoles($user_id);

		foreach ($roles as $role)
		{
			$ass_rolefolders = $rbacreview->getFoldersAssignedToRole($role);	//rolef_refids

			foreach ($ass_rolefolders as $role_folder)
			{
				$node = $tree->getParentNodeData($role_folder);

				if ($node["type"] == "grp")
				{
					$group =& $this->ilias->obj_factory->getInstanceByRefId($node["child"]);

					if ($group->isMember($user_id) == true && !in_array($group->getId(), $grp_memberships) )
					{
						array_push($grp_memberships, $group->getId());
					}
				}

				unset($group);
			}
		}

		return $grp_memberships;
	}

	/*
	* get the memberships(course_ids) of courses that are subscribed to the current user object
	* @param	integer optional user_id
	* @access	public
	*/
	function getCourseMemberships($a_user_id = "")
	{
		global $rbacreview, $tree;

		if (strlen($a_user_id) > 0)
		{
			$user_id = $a_user_id;
		}
		else
		{
			$user_id = $this->getId();
		}

		$crs_memberships = array();

		// get all roles which the user is assigned to
		$roles = $rbacreview->assignedRoles($user_id);

		foreach ($roles as $role)
		{
			$ass_rolefolders = $rbacreview->getFoldersAssignedToRole($role);	//rolef_refids

			foreach ($ass_rolefolders as $role_folder)
			{
				$node = $tree->getParentNodeData($role_folder);

				if ($node["type"] == "crs")
				{
					include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
					$crsmem = ilCourseParticipants::_getInstanceByObjId($node['obj_id']);

					if ($crsmem->isAssigned($user_id) && !in_array($node['obj_id'], $crs_memberships))
					{
						array_push($crs_memberships, $node['obj_id']);
					}
				}
			}
		}

		return $crs_memberships ? $crs_memberships : array();
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
		$query = "SELECT login FROM usr_data ";

		$res = $ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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

		$where = ("WHERE usr_id IN(".implode(",",ilUtil::quoteArray($a_user_ids)).") ");
		$query = "SELECT * FROM usr_data ".$where;
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
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
					$q .= "WHERE active= ".$ilDB->quote($active);
					break;
				case 2:
					$q .= "WHERE time_limit_unlimited='0'";
					break;
				case 3:
					$qtemp = $q . ", rbac_ua, object_data WHERE rbac_ua.rol_id = object_data.obj_id AND object_data.title LIKE '%crs%' AND usr_data.usr_id = rbac_ua.usr_id";
					$r = $ilDB->query($qtemp);
					$course_users = array();
					while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
					{
						array_push($course_users, $row["usr_id"]);
					}
					if (count($course_users))
					{
						$q .= " WHERE usr_data.usr_id NOT IN ('" . join($course_users, "','") . "')";
					}
					else
					{
						$q = "";
					}
					break;
				case 4:
					$date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"], $_SESSION["user_filter_data"]["y"]));
					$q .= sprintf("WHERE last_login < %s", $ilDB->quote($date));
					break;
				case 5:
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$q .= " LEFT JOIN crs_members ON usr_data.usr_id = crs_members.usr_id WHERE crs_members.obj_id = (SELECT obj_id FROM object_reference WHERE ref_id = " .
							$ilDB->quote($ref_id) . ")";
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
							$role_ids = join("','", $local_roles);
							$q .= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id IN ('" . $role_ids . "')";
						}
					}
					break;
				case 7:
					$rol_id = $_SESSION["user_filter_data"];
					if ($rol_id)
					{
						$q .= sprintf(" LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id = %s", $ilDB->quote($rol_id));;
					}
					break;
			}
			$r = $ilDB->query($q);

			while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
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

		$q = "SELECT count(*) as cnt FROM usr_pref AS up1, usr_pref AS up2 ".
			" WHERE up1.keyword= ".$ilDB->quote("style")." AND up1.value= ".$ilDB->quote($a_style).
			" AND up2.keyword= ".$ilDB->quote("skin")." AND up2.value= ".$ilDB->quote($a_skin).
			" AND up1.usr_id = up2.usr_id ";

		$cnt_set = $ilDB->query($q);

		$cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $cnt_rec["cnt"];
	}

	/**
	* skins and styles
	*/
	function _getAllUserAssignedStyles()
	{
		global $ilDB;

		$q = "SELECT DISTINCT up1.value as style, up2.value as skin FROM usr_pref AS up1, usr_pref AS up2 ".
			" WHERE up1.keyword= ".$ilDB->quote("style").
			" AND up2.keyword= ".$ilDB->quote("skin").
			" AND up1.usr_id = up2.usr_id ";


		$sty_set = $ilDB->query($q);

		$styles = array();
		while($sty_rec = $sty_set->fetchRow(DB_FETCHMODE_ASSOC))
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

		$q = "SELECT up1.usr_id as usr_id FROM usr_pref AS up1, usr_pref AS up2 ".
			" WHERE up1.keyword= ".$ilDB->quote("style")." AND up1.value= ".$ilDB->quote($a_from_style).
			" AND up2.keyword= ".$ilDB->quote("skin")." AND up2.value= ".$ilDB->quote($a_from_skin).
			" AND up1.usr_id = up2.usr_id ";

		$usr_set = $ilDB->query($q);

		while ($usr_rec = $usr_set->fetchRow(DB_FETCHMODE_ASSOC))
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

		$q = "SELECT * FROM desktop_item WHERE ".
			"item_id = ".$ilDB->quote($a_item_id)." AND type = ".
			$ilDB->quote($a_type)." AND user_id = ".
			$ilDB->quote($a_usr_id);
		$item_set = $ilDB->query($q);

		// only insert if item is not already on desktop
		if (!$d = $item_set->fetchRow())
		{
			$q = "INSERT INTO desktop_item (item_id, type, user_id, parameters) VALUES ".
				" (".$ilDB->quote($a_item_id).",".
				$ilDB->quote($a_type).",".
				$ilDB->quote($a_usr_id).",".
				$ilDB->quote($a_par).")";
			$ilDB->query($q);
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
/*		global $ilDB;

		$q = "SELECT * FROM desktop_item WHERE ".
			"item_id = ".$ilDB->quote($a_item_id)." AND type = ".
			$ilDB->quote($a_type)." AND user_id = ".
			$ilDB->quote($this->getId());
		$item_set = $this->ilias->db->query($q);

		// only insert if item is not already on desktop
		if (!$d = $item_set->fetchRow())
		{
			$q = "INSERT INTO desktop_item (item_id, type, user_id, parameters) VALUES ".
				" (".$ilDB->quote($a_item_id).",".
				$ilDB->quote($a_type).",".
				$ilDB->quote($this->getId()).",".
				$ilDB->quote($a_par).")";
			$this->ilias->db->query($q);
		}
*/	}

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

		$q = "UPDATE desktop_item SET parameters = ".$ilDB->quote($a_par)." ".
			" WHERE item_id = ".$ilDB->quote($a_item_id)." AND type = ".
			$ilDB->quote($a_type)." ".
			" AND user_id = ".$ilDB->quote($this->getId())." ";
		$this->ilias->db->query($q);
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

		$q = "DELETE FROM desktop_item WHERE ".
			" item_id = ".$ilDB->quote($a_item_id)." AND ".
			" type = ".$ilDB->quote($a_type)." AND ".
			" user_id = ".$ilDB->quote($a_usr_id);
		$ilDB->query($q);
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
/*		global $ilDB;

		$q = "DELETE FROM desktop_item WHERE ".
			" item_id = ".$ilDB->quote($a_item_id)." AND ".
			" type = ".$ilDB->quote($a_type)." AND ".
			" user_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
*/	}
	
	
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

		$q = "SELECT * FROM desktop_item WHERE ".
			"item_id = ".$ilDB->quote($a_item_id)." AND type = ".
			$ilDB->quote($a_type)." AND user_id = ".
			$ilDB->quote($a_usr_id);
		$item_set = $ilDB->query($q);

		if ($d = $item_set->fetchRow())
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
/*		global $ilDB;

		$q = "SELECT * FROM desktop_item WHERE ".
			"item_id = ".$ilDB->quote($a_item_id)." AND type = ".
			$ilDB->quote($a_type)." AND user_id = ".
			$ilDB->quote($this->getId());
		$item_set = $this->ilias->db->query($q);

		if ($d = $item_set->fetchRow())
		{
			return true;
		}
		else
		{
			return false;
		}*/
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
			$q = "SELECT obj.obj_id, obj.description, oref.ref_id, obj.title, obj.type ".
				" FROM desktop_item AS it, object_reference AS oref ".
					", object_data AS obj".
				" WHERE ".
				"it.item_id = oref.ref_id AND ".
				"oref.obj_id = obj.obj_id AND ".
				"it.user_id = ".$ilDB->quote($user_id);

			$item_set = $ilDB->query($q);
			$items = array();
			while ($item_rec = $item_set->fetchRow(DB_FETCHMODE_ASSOC))
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
				$q = "SELECT obj.obj_id, obj.description, oref.ref_id, obj.title FROM desktop_item AS it, object_reference AS oref ".
					", object_data AS obj WHERE ".
					"it.item_id = oref.ref_id AND ".
					"oref.obj_id = obj.obj_id AND ".
					"it.type = ".$ilDB->quote($a_type)." AND ".
					"it.user_id = ".$ilDB->quote($user_id)." ".
					"ORDER BY title";

				$item_set = $ilDB->query($q);
				while ($item_rec = $item_set->fetchRow(DB_FETCHMODE_ASSOC))
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
	function addObjectToClipboard($a_item_id, $a_type, $a_title)
	{
		global $ilDB;

		$q = "SELECT * FROM personal_clipboard WHERE ".
			"item_id = ".$ilDB->quote($a_item_id)." AND type = ".
			$ilDB->quote($a_type)." AND user_id = ".
			$ilDB->quote($this->getId());
		$item_set = $this->ilias->db->query($q);

		// only insert if item is not already on desktop
		if (!$d = $item_set->fetchRow())
		{
			$q = "INSERT INTO personal_clipboard (item_id, type, user_id, title) VALUES ".
				" (".$ilDB->quote($a_item_id).",".$ilDB->quote($a_type).",".
				$ilDB->quote($this->getId()).",".$ilDB->quote($a_title).")";
			$this->ilias->db->query($q);
		}
	}

	/**
	* get all clipboard objects of user and specified type
	*/
	function getClipboardObjects($a_type = "")
	{
		global $ilDB;

		$type_str = ($a_type != "")
			? " AND type = ".$ilDB->quote($a_type)." "
			: "";
		$q = "SELECT * FROM personal_clipboard WHERE ".
			"user_id = ".$ilDB->quote($this->getId())." ".
			$type_str;
		$objs = $this->ilias->db->query($q);
		$objects = array();
		while ($obj = $objs->fetchRow(DB_FETCHMODE_ASSOC))
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
			"item_id = ".$ilDB->quote($a_id)." AND ".
			"type = ".$ilDB->quote($a_type);
		$user_set = $ilDB->query($q);
		$users = array();
		while ($user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC))
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
			"item_id = ".$ilDB->quote($a_item_id)." AND type = ".$ilDB->quote($a_type)." ".
			" AND user_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
	}

	function _getImportedUserId($i2_id)
	{
		global $ilDB;

		$query = "SELECT obj_id FROM object_data WHERE import_id = ".
			$ilDB->quote($i2_id);

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
	 	if($a_read_auth_default and ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode',AUTH_LOCAL)) == $a_auth_mode)
	 	{
	 		$or = "OR auth_mode = 'default' ";
	 	}
		else
		{
			$or = " ";
		}
	 	$query = "SELECT login,usr_id,ext_account,auth_mode FROM usr_data ".
	 		"WHERE auth_mode = ".$ilDB->quote($a_auth_mode)." ".
	 		$or;

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
		$where = ("WHERE usr_id IN(".implode(",",ilUtil::quoteArray($a_usr_ids)).") ");
	 	$query = "UPDATE usr_data SET active = ".$ilDB->quote($a_status ? 1 : 0)." ".
	 	$where;
		$ilDB->query($query);

		return true;
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
		$r = $ilDB->query("SELECT * FROM usr_data WHERE ".
			" ext_account = ".$ilDB->quote($a_account)." AND ".
			" auth_mode = ".$ilDB->quote($a_auth));
		if ($usr = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $usr["login"];
		}
		
		// For compatibility, check for login (no ext_account entry given)
		$query = "SELECT login FROM usr_data ".
			"WHERE login = ".$ilDB->quote($a_account)." ".
			"AND auth_mode = ".$ilDB->quote($a_auth)." ";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$usr = $res->fetchRow(DB_FETCHMODE_ASSOC);
			return $usr['login'];
		}
		
		// If auth_default == $a_auth => check for login
		if(ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode')) == $a_auth)
		{
			// First search for ext_account
			$query = "SELECT login FROM usr_data ".
				"WHERE ext_account = ".$ilDB->quote($a_account)." ".
				"AND auth_mode = 'default'";
			
			$res = $ilDB->query($query);
			if ($usr = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				return $usr["login"];
			}
			
			// Search for login (no ext_account given)
			$query = "SELECT login FROM usr_data ".
				"WHERE (login =".$ilDB->quote($a_account)." AND ext_account = '') ".
				"AND auth_mode = 'default'";
			
			$res = $ilDB->query($query);
			if ($usr = $res->fetchRow(DB_FETCHMODE_ASSOC))
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
		while($cnt = $r->fetchRow(DB_FETCHMODE_ASSOC))
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
		$or_str = "";
		if ($ilSetting->get("auth_mode") == 1)
		{
			$or_str = " OR auth_mode = ".$ilDB->quote("default");
		}

		$usr_set = $ilDB->query("SELECT * FROM usr_data WHERE ".
			" email = ".$ilDB->quote($a_email)." AND ".
			" (auth_mode = ".$ilDB->quote("local").$or_str.")");

		$users = array();

		while ($usr_rec = $usr_set->fetchRow(DB_FETCHMODE_ASSOC))
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
	function _getPersonalPicturePath($a_usr_id,$a_size = "small", $a_force_pic = false)
	{
		global $ilDB;

		// BEGIN DiskQuota: Fetch all user preferences in a single query
		$query = "SELECT * FROM usr_pref WHERE ".
			"keyword IN ('public_upload','public_profile') ".
			"AND usr_id = ".$ilDB->quote($a_usr_id);

		$res = $ilDB->query($query);
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			switch ($row['keyword'])
			{
				case 'public_upload' :
					$upload = $row['value'] == 'y';
					break;
				case 'public_profile' :
					$profile = $row['value'] == 'y';
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
			$file = ilUtil::getImagePath("no_photo_".$a_size.".jpg");
		}

		return $file;
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
			$this->user_defined_data[$field] = $data;
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

		foreach($this->user_defined_data as $field => $value)
		{
			if($field != 'usr_id')
			{
				$fields .= ("`".$field."` = ".$ilDB->quote($value).", ");
			}
		}

		$query = "REPLACE INTO usr_defined_data ".
			"SET ".$fields." ".
			"usr_id = ".$ilDB->quote($this->getId());

		$this->db->query($query);
		return true;
	}

	function readUserDefinedFields()
	{
		global $ilDB;

		$query = "SELECT * FROM usr_defined_data ".
			"WHERE usr_id = ".$ilDB->quote($this->getId());

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->user_defined_data = $row;
		}
		return true;
	}

	function addUserDefinedFieldEntry()
	{
		global $ilDB;

		$query = "INSERT INTO usr_defined_data ".
			"SET usr_id = ".$ilDB->quote($this->getId());
		$this->db->query($query);

		return true;
	}

	function deleteUserDefinedFieldEntries()
	{
		global $ilDB;

		$query = "DELETE FROM usr_defined_data ".
			"WHERE usr_id = ".$ilDB->quote($this->getId());
		$this->db->query($query);

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
			$body .= ($language->txt("create_date").": ".ilFormat::formatDate($this->getCreateDate(), "datetime", true)."\n");
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
			$body .= ($language->txt('time_limit').": ".$language->txt('crs_from')." ".
					  ilFormat::formatUnixTime($this->getTimeLimitFrom(), true)." ".
					  $language->txt('crs_to')." ".
					  ilFormat::formatUnixTime($this->getTimeLimitUntil(), true)."\n");
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
			$query = "SELECT feed_hash from usr_data WHERE usr_id = ".
				$ilDB->quote($a_user_id);
			$set = $ilDB->query($query);
			if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (strlen($rec["feed_hash"]) == 32)
				{
					return $rec["feed_hash"];
				}
				else if($a_create)
				{
					$hash = md5(rand(1,9999999) + str_replace(" ", "", (string) microtime()));
					$query = "UPDATE usr_data SET feed_hash = ".
						$ilDB->quote($hash).
						" WHERE usr_id = ".$ilDB->quote($a_user_id);
					$ilDB->query($query);
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
			$query = "SELECT value from usr_pref WHERE usr_id = ".
				$ilDB->quote($a_user_id) ." AND keyword=\"priv_feed_pass\"";
			$set = $ilDB->query($query);
			if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				
				return $rec["value"];
			}
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

		if ($a_user_id > 0 )
		{
		   if ($a_password=="")
		   {
		    $statement = $ilDB->prepare("REPLACE INTO usr_pref (usr_id,keyword,value) VALUES (? ,? , ?)");
		    $data = array($a_user_id, "priv_feed_pass", "");
		   }
		   else
		   {
		    $statement = $ilDB->prepare("REPLACE INTO usr_pref (usr_id,keyword,value) VALUES (? ,? , ?)");
		    $data = array($a_user_id, "priv_feed_pass", md5($a_password));
		   }
		  $statement->execute($data);
		}	
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

		if ($a_user_id == 0)
		{
			$clause = "";
		}
		else
		{
			$clause = "AND usr_id != ".$ilDB->quote($a_user_id)." ";
		}

		$q = "SELECT DISTINCT login FROM usr_data ".
			 "WHERE login = ".$ilDB->quote($a_login)." ".$clause;
		$r = $ilDB->query($q);

		if ($r->numRows() == 1)
		{
			return true;
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
		
		$query = "SELECT * FROM usr_data ".
			"WHERE ext_account = ".$ilDB->quote($a_external_account)." ".
			"AND auth_mode = ".$ilDB->quote($a_auth_mode);
		$res = $ilDB->query($query);
		return $res->numRows() ? true :false; 
	}

	/**
	 * return array of complete users which belong to a specific role
	 *
	 * @param int role id
	 * @param int $active 	if -1, all users will be delivered, 0 only non active, 1 only active users
	 */

	public static function _getUsersForRole($role_id, $active = -1) {
		global $ilDB;
		$data = array();

		$query = "SELECT usr_data.*, usr_pref.value AS language
		          FROM  usr_pref,usr_data
		          LEFT JOIN rbac_ua ON usr_data.usr_id=rbac_ua.usr_id
		          WHERE
		           usr_pref.usr_id = usr_data.usr_id AND
		           usr_pref.keyword = 'language' AND
		           usr_data.usr_id != '".ANONYMOUS_USER_ID."' AND
		           rbac_ua.rol_id=". $ilDB->quote($role_id);

		 if (is_numeric($active) && $active > -1)
			$query .= " AND usr_data.active = ".$ilDB->quote($active);

		 $query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

#		 echo $query;

		 $r = $ilDB->query($query);

		 $data = array();
         while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
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
		$query = "SELECT usr_data.*, usr_pref.value AS language FROM usr_data LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id and usr_pref.keyword = 'language' WHERE 1 ";

		if (is_numeric($active) && $active > -1)
			$query .= " AND usr_data.active = ".$ilDB->quote($active);

		if ($ref_id != USER_FOLDER_ID)
		    $query .= " AND usr_data.time_limit_owner = ".$ilDB->quote($ref_id);

		$query .=	" AND usr_data.usr_id != '".ANONYMOUS_USER_ID."'";

		$query .= " ORDER BY usr_data.lastname, usr_data.firstname ";
		//echo $query;
		$result = $ilDB->query($query);
		$data = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
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
		global $rbacadmin, $rbacreview, $ilDB;

		// quote all ids
		$ids = array();
		foreach ($a_mem_ids as $mem_id) {
			$ids [] = $ilDB->quote($mem_id);
		}

		$query = "SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = 'language'
		          WHERE usr_data.usr_id IN (".implode(',',$ids).")
					AND usr_data.usr_id != '".ANONYMOUS_USER_ID."'";

  	    if (is_numeric($active) && $active > -1)
  			$query .= " AND active = '$active'";

  		$query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

  	    $r = $ilDB->query($query);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
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
		          ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = 'language'
		          WHERE  usr_data.usr_id IN (".join(",",$ids).")";

		$query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

		#echo $query;
		$r = $ilDB->query($query);
		$data = array();
		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$data[] = $row;
		}
		return $data;
	}

} // END class ilObjUser
?>
