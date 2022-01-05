<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Symbol\Avatar\Avatar;

define("IL_PASSWD_PLAIN", "plain");
define("IL_PASSWD_CRYPTED", "crypted");


require_once "./Services/Object/classes/class.ilObject.php";
require_once './Services/User/exceptions/class.ilUserException.php';
require_once './Modules/OrgUnit/classes/class.ilObjOrgUnit.php';
require_once './Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php';

/**
* @defgroup ServicesUser Services/User
*
* User application class
*
* @author	Sascha Hofmann <saschahofmann@gmx.de>
* @author	Stefan Meyer <meyer@leifos.com>
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

    public $login;		// username in system

    /**
     * @var string
     */
    protected $passwd; // password encoded in the format specified by $passwd_type

    /**
     * @var string
     */
    protected $passwd_type;
    // specifies the password format.
    // value: IL_PASSWD_PLAIN or IL_PASSWD_CRYPTED.

    // Differences between password format in class ilObjUser and
    // in table usr_data:
    // Class ilObjUser supports two different password types
    // (plain and crypted) and it uses the variables $passwd
    // and $passwd_type to store them.
    // Table usr_data supports only two different password types
    // (md5 and bcrypt) and it uses the columns "passwd" and "passwd_type" to store them.
    // The conversion between these two storage layouts is done
    // in the methods that perform SQL statements. All other
    // methods work exclusively with the $passwd and $passwd_type
    // variables.

    /**
     * The encoding algorithm of the user's password stored in the database
     * @var string
     */
    protected $password_encoding_type;

    /**
     * A salt used to encrypt the user's password
     * @var string|null
     */
    protected $password_salt = null;
    
    public $gender;	// 'm' or 'f'
    public $utitle;	// user title (keep in mind, that we derive $title from object also!)
    public $firstname;
    public $lastname;
    protected $birthday;
    public $fullname;	// title + firstname + lastname in one string
    //var $archive_dir = "./image";  // point to image file (should be flexible)
    // address data
    public $institution;
    public $department;
    public $street;
    public $city;
    public $zipcode;
    public $country;
    public $sel_country;
    public $phone_office;
    public $phone_home;
    public $phone_mobile;
    public $fax;
    public $email;
    protected $second_email = null;
    public $hobby;
    public $matriculation;
    public $referral_comment;
    public $approve_date = null;
    public $agree_date = null;
    public $active;
    public $client_ip; // client ip to check before login
    public $auth_mode; // authentication mode

    public $latitude;
    public $longitude;
    public $loc_zoom;

    public $last_password_change_ts;
    protected $passwd_policy_reset = false;
    public $login_attempts;

    public $user_defined_data = array();
    
    /**
    * Contains variable Userdata (Prefs, Settings)
    * @var		array
    * @access	public
    */
    public $prefs;

    /**
    * Contains template set
    * @var		string
    * @access	public
    */
    public $skin;


    /**
    * default role
    * @var		string
    * @access	private
    */
    public $default_role;

    /**
    * ilias object
    * @var object ilias
    * @access private
    */
    public $ilias;

    public static $is_desktop_item_loaded;
    public static $is_desktop_item_cache;

    /**
     * @var array
     */
    protected static $personal_image_cache = array();
    
    /**
     * date of setting the user inactivated
     *
     * @var string
     */
    protected $inactivation_date = null;

    /**
     * flag for self registered users
     * @var bool
     */
    private $is_self_registered = false;

    /**
     * ids of assigned org-units, comma seperated
     * @var string
     */
    protected $org_units;
    
    protected $interests_general; // [array]
    protected $interests_help_offered; // [array]
    protected $interests_help_looking; // [array]

    /**
     * @var string
     */
    protected $last_profile_prompt;	// timestamp

    /**
     * @var string
     */
    protected $first_login;	// timestamp


    /**
    * Constructor
    * @access	public
    * @param	integer		user_id
    */
    public function __construct($a_user_id = 0, $a_call_by_reference = false)
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilDB = $DIC['ilDB'];

        // init variables
        $this->ilias = &$ilias;
        $this->db = &$ilDB;

        $this->type = "usr";
        parent::__construct($a_user_id, $a_call_by_reference);
        $this->auth_mode = "default";
        $this->passwd_type = IL_PASSWD_PLAIN;

        // for gender selection. don't change this
        /*$this->gender = array(
                              'n'    => "salutation_n",
                              'm'    => "salutation_m",
                              'f'    => "salutation_f"
                              );*/
        if ($a_user_id > 0) {
            $this->setId($a_user_id);
            $this->read();
        } else {
            // TODO: all code in else-structure doesn't belongs in class user !!!
            //load default data
            $this->prefs = array();
            //language
            $this->prefs["language"] = $this->ilias->ini->readVariable("language", "default");

            //skin and pda support
            $this->skin = $this->ilias->ini->readVariable("layout", "skin");

            $this->prefs["skin"] = $this->skin;
            //			$this->prefs["show_users_online"] = "y";

            //style (css)
            $this->prefs["style"] = $this->ilias->ini->readVariable("layout", "style");
        }
    }

    /**
    * loads a record "user" from database
    * @access private
    */
    public function read()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];

        // Alex: I have removed the JOIN to rbac_ua, since there seems to be no
        // use (3.11.0 alpha)
        /*$q = "SELECT * FROM usr_data ".
             "LEFT JOIN rbac_ua ON usr_data.usr_id=rbac_ua.usr_id ".
             "WHERE usr_data.usr_id= ".$ilDB->quote($this->id); */
        $r = $ilDB->queryF("SELECT * FROM usr_data " .
             "WHERE usr_id= %s", array("integer"), array($this->id));

        if ($data = $ilDB->fetchAssoc($r)) {
            // convert password storage layout used by table usr_data into
            // storage layout used by class ilObjUser
            $data["passwd_type"] = IL_PASSWD_CRYPTED;

            // this assign must not be set via $this->assignData($data)
            // because this method will be called on profile updates and
            // would set this values to 0, because they arent posted from form
            $this->setLastPasswordChangeTS($data['last_password_change']);
            $this->setLoginAttempts($data['login_attempts']);
            $this->setPasswordPolicyResetStatus((bool) $data['passwd_policy_reset']);


            // fill member vars in one shot
            $this->assignData($data);

            //get userpreferences from usr_pref table
            $this->readPrefs();

            //set language to default if not set
            if ($this->prefs["language"] == "") {
                $this->prefs["language"] = $this->oldPrefs["language"];
            }

            //check skin-setting
            include_once("./Services/Style/System/classes/class.ilStyleDefinition.php");
            if ($this->prefs["skin"] == "" ||
                    !ilStyleDefinition::skinExists($this->prefs["skin"])) {
                $this->prefs["skin"] = $this->oldPrefs["skin"];
            }

            $this->skin = $this->prefs["skin"];

            //check style-setting (skins could have more than one stylesheet
            if ($this->prefs["style"] == "" ||
                    (!ilStyleDefinition::skinExists($this->skin) && ilStyleDefinition::styleExistsForSkinId($this->skin, $this->prefs["style"])) ||
                    !ilStyleDefinition::styleExists($this->prefs["style"])) {
                //load default (css)
                $this->prefs["style"] = $this->ilias->ini->readVariable("layout", "style");
            }

            if (empty($this->prefs["hits_per_page"])) {
                $this->prefs["hits_per_page"] = 10;
            }
        } else {
            $ilErr->raiseError("<b>Error: There is no dataset with id " .
                               $this->id . "!</b><br />class: " . get_class($this) . "<br />Script: " . __FILE__ .
                               "<br />Line: " . __LINE__, $ilErr->FATAL);
        }

        $this->readMultiTextFields();
        $this->readUserDefinedFields();

        parent::read();
    }

    /**
     * @return string
     */
    public function getPasswordEncodingType()
    {
        return $this->password_encoding_type;
    }

    /**
     * @param string $password_encryption_type
     */
    public function setPasswordEncodingType($password_encryption_type)
    {
        $this->password_encoding_type = $password_encryption_type;
    }

    /**
     * @return string|null
     */
    public function getPasswordSalt()
    {
        return $this->password_salt;
    }

    /**
     * @param string|null $password_salt
     */
    public function setPasswordSalt($password_salt)
    {
        $this->password_salt = $password_salt;
    }

    /**
    * loads a record "user" from array
    * @access	public
    * @param	array		userdata
    */
    public function assignData($a_data)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        
        // basic personal data
        $this->setLogin($a_data["login"]);
        if (!$a_data["passwd_type"]) {
            $ilErr->raiseError("<b>Error: passwd_type missing in function assignData(). " .
                                $this->id . "!</b><br />class: " . get_class($this) . "<br />Script: "
                                . __FILE__ . "<br />Line: " . __LINE__, $ilErr->FATAL);
        }
        if ($a_data["passwd"] != "********" and strlen($a_data['passwd'])) {
            $this->setPasswd($a_data["passwd"], $a_data["passwd_type"]);
        }

        $this->setGender($a_data["gender"]);
        $this->setUTitle($a_data["title"]);
        $this->setFirstname($a_data["firstname"]);
        $this->setLastname($a_data["lastname"]);
        $this->setFullname();
        if (!is_array($a_data['birthday'])) {
            $this->setBirthday($a_data['birthday']);
        } else {
            $this->setBirthday(null);
        }
        
        // address data
        $this->setInstitution($a_data["institution"]);
        $this->setDepartment($a_data["department"]);
        $this->setStreet($a_data["street"]);
        $this->setCity($a_data["city"]);
        $this->setZipcode($a_data["zipcode"]);
        $this->setCountry($a_data["country"]);
        $this->setSelectedCountry($a_data["sel_country"]);
        $this->setPhoneOffice($a_data["phone_office"]);
        $this->setPhoneHome($a_data["phone_home"]);
        $this->setPhoneMobile($a_data["phone_mobile"]);
        $this->setFax($a_data["fax"]);
        $this->setMatriculation($a_data["matriculation"]);
        $this->setEmail($a_data["email"]);
        $this->setSecondEmail($a_data["second_email"]);
        $this->setHobby($a_data["hobby"]);
        $this->setClientIP($a_data["client_ip"]);
        $this->setPasswordEncodingType($a_data['passwd_enc_type']);
        $this->setPasswordSalt($a_data['passwd_salt']);

        // other data
        $this->setLatitude($a_data["latitude"]);
        $this->setLongitude($a_data["longitude"]);
        $this->setLocationZoom($a_data["loc_zoom"]);

        // system data
        $this->setLastLogin($a_data["last_login"]);
        $this->setFirstLogin($a_data["first_login"]);
        $this->setLastProfilePrompt($a_data["last_profile_prompt"]);
        $this->setLastUpdate($a_data["last_update"]);
        $this->create_date = $a_data["create_date"];
        $this->setComment($a_data["referral_comment"]);
        $this->approve_date = $a_data["approve_date"];
        $this->active = $a_data["active"];
        $this->agree_date = $a_data["agree_date"];
        
        $this->setInactivationDate($a_data["inactivation_date"]);

        // time limitation
        $this->setTimeLimitOwner($a_data["time_limit_owner"]);
        $this->setTimeLimitUnlimited($a_data["time_limit_unlimited"]);
        $this->setTimeLimitFrom($a_data["time_limit_from"]);
        $this->setTimeLimitUntil($a_data["time_limit_until"]);
        $this->setTimeLimitMessage($a_data['time_limit_message']);

        // user profile incomplete?
        $this->setProfileIncomplete($a_data["profile_incomplete"]);

        //authentication
        $this->setAuthMode($a_data['auth_mode']);
        $this->setExternalAccount($a_data['ext_account']);
        
        $this->setIsSelfRegistered((bool) $a_data['is_self_registered']);
    }

    /**
    * TODO: drop fields last_update & create_date. redundant data in object_data!
    * saves a new record "user" to database
    * @access	public
    * @param	boolean	user data from formular (addSlashes) or not (prepareDBString)
    */
    public function saveAsNew($a_from_formular = true)
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        
        /**
         * @var $ilErr ilErrorHandling
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];

        switch ($this->passwd_type) {
            case IL_PASSWD_PLAIN:
                if (strlen($this->passwd)) {
                    require_once 'Services/User/classes/class.ilUserPasswordManager.php';
                    ilUserPasswordManager::getInstance()->encodePassword($this, $this->passwd);
                    $pw_value = $this->getPasswd();
                } else {
                    $pw_value = $this->passwd;
                }
                break;

            case IL_PASSWD_CRYPTED:
                $pw_value = $this->passwd;
                break;

            default:
                 $ilErr->raiseError("<b>Error: passwd_type missing in function saveAsNew. " .
                                    $this->id . "!</b><br />class: " . get_class($this) . "<br />Script: " . __FILE__ .
                                    "<br />Line: " . __LINE__, $ilErr->FATAL);
        }

        if (!$this->active) {
            $this->setInactivationDate(ilUtil::now());
        } else {
            $this->setInactivationDate(null);
        }

        $insert_array = array(
            "usr_id" => array("integer", $this->id),
            "login" => array("text", $this->login),
            "passwd" => array("text", $pw_value),
            'passwd_enc_type' => array("text", $this->getPasswordEncodingType()),
            'passwd_salt' => array("text", $this->getPasswordSalt()),
            "firstname" => array("text", $this->firstname),
            "lastname" => array("text", $this->lastname),
            "title" => array("text", $this->utitle),
            "gender" => array("text", $this->gender),
            "email" => array("text", trim($this->email)),
            "second_email" => array("text", trim($this->second_email)),
            "hobby" => array("text", (string) $this->hobby),
            "institution" => array("text", $this->institution),
            "department" => array("text", $this->department),
            "street" => array("text", $this->street),
            "city" => array("text", $this->city),
            "zipcode" => array("text", $this->zipcode),
            "country" => array("text", $this->country),
            "sel_country" => array("text", $this->sel_country),
            "phone_office" => array("text", $this->phone_office),
            "phone_home" => array("text", $this->phone_home),
            "phone_mobile" => array("text", $this->phone_mobile),
            "fax" => array("text", $this->fax),
            "birthday" => array('date', $this->getBirthday()),
            "last_login" => array("timestamp", null),
            "first_login" => array("timestamp", null),
            "last_profile_prompt" => array("timestamp", null),
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
            "latitude" => array("text", $this->latitude),
            "longitude" => array("text", $this->longitude),
            "loc_zoom" => array("integer", (int) $this->loc_zoom),
            "last_password_change" => array("integer", (int) $this->last_password_change_ts),
            "passwd_policy_reset" => array("integer", (int) $this->passwd_policy_reset),
            'inactivation_date' => array('timestamp', $this->inactivation_date),
            'is_self_registered' => array('integer', (int) $this->is_self_registered),
            );
        $ilDB->insert("usr_data", $insert_array);

        $this->updateMultiTextFields(true);

        // add new entry in usr_defined_data
        $this->addUserDefinedFieldEntry();
        // ... and update
        $this->updateUserDefinedFields();

        // CREATE ENTRIES FOR MAIL BOX
        include_once("Services/Mail/classes/class.ilMailbox.php");
        $mbox = new ilMailbox($this->id);
        $mbox->createDefaultFolder();

        include_once "Services/Mail/classes/class.ilMailOptions.php";
        $mail_options = new ilMailOptions($this->id);
        $mail_options->createMailOptionsEntry();


        $ilAppEventHandler->raise(
            "Services/User",
            "afterCreate",
            array("user_obj" => $this)
        );
    }

    /**
    * updates a record "user" and write it into database
    */
    public function update()
    {
        /**
         * @var $ilErr ilErrorHandling
         * @var $ilDB ilDB
         * @var $ilAppEventHandler ilAppEventHandler
         */
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $this->syncActive();

        if ($this->getStoredActive($this->id) && !$this->active) {
            $this->setInactivationDate(ilUtil::now());
        } elseif ($this->active) {
            $this->setInactivationDate(null);
        }

        $update_array = array(
            "gender" => array("text", $this->gender),
            "title" => array("text", $this->utitle),
            "firstname" => array("text", $this->firstname),
            "lastname" => array("text", $this->lastname),
            "email" => array("text", trim($this->email)),
            "second_email" => array("text", trim($this->second_email)),
            "birthday" => array('date', $this->getBirthday()),
            "hobby" => array("text", $this->hobby),
            "institution" => array("text", $this->institution),
            "department" => array("text", $this->department),
            "street" => array("text", $this->street),
            "city" => array("text", $this->city),
            "zipcode" => array("text", $this->zipcode),
            "country" => array("text", $this->country),
            "sel_country" => array("text", $this->sel_country),
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
            "latitude" => array("text", $this->latitude),
            "longitude" => array("text", $this->longitude),
            "loc_zoom" => array("integer", (int) $this->loc_zoom),
            "last_password_change" => array("integer", $this->last_password_change_ts),
            "passwd_policy_reset" => array("integer", $this->passwd_policy_reset),
            "last_update" => array("timestamp", ilUtil::now()),
            'inactivation_date' => array('timestamp', $this->inactivation_date)
            );
            
        if ($this->agree_date === null || (is_string($this->agree_date) && strtotime($this->agree_date) !== false)) {
            $update_array["agree_date"] = array("timestamp", $this->agree_date);
        }
        switch ($this->passwd_type) {
            case IL_PASSWD_PLAIN:
                if (strlen($this->passwd)) {
                    require_once 'Services/User/classes/class.ilUserPasswordManager.php';
                    ilUserPasswordManager::getInstance()->encodePassword($this, $this->passwd);
                    $update_array['passwd'] = array('text', $this->getPasswd());
                } else {
                    $update_array["passwd"] = array("text", (string) $this->passwd);
                }
                break;

            case IL_PASSWD_CRYPTED:
                $update_array["passwd"] = array("text", (string) $this->passwd);
                break;

            default:
                $ilErr->raiseError("<b>Error: passwd_type missing in function update()" . $this->id . "!</b><br />class: " .
                                   get_class($this) . "<br />Script: " . __FILE__ . "<br />Line: " . __LINE__, $ilErr->FATAL);
        }

        $update_array['passwd_enc_type'] = array('text', $this->getPasswordEncodingType());
        $update_array['passwd_salt'] = array('text', $this->getPasswordSalt());

        $ilDB->update("usr_data", $update_array, array("usr_id" => array("integer", $this->id)));

        $this->updateMultiTextFields();
        
        $this->writePrefs();

        // update user defined fields
        $this->updateUserDefinedFields();

        parent::update();
        parent::updateOwner();

        $this->read();
        
        $ilAppEventHandler->raise(
            "Services/User",
            "afterUpdate",
            array("user_obj" => $this)
        );

        return true;
    }

    /**
    * write accept date of user agreement to db
    */
    public function writeAccepted()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF("UPDATE usr_data SET agree_date = " . $ilDB->now() .
             " WHERE usr_id = %s", array("integer"), array($this->getId()));
    }

    /**
    * Private function for lookup methods
    */
    private static function _lookup($a_user_id, $a_field)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = $ilDB->queryF(
            "SELECT " . $a_field . " FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );

        while ($set = $ilDB->fetchAssoc($res)) {
            return $set[$a_field];
        }
        return false;
    }
    
    /**
    * Lookup Full Name
    */
    public static function _lookupFullname($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->queryF(
            "SELECT title, firstname, lastname FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );

        if ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["title"]) {
                $fullname = $rec["title"] . " ";
            }
            if ($rec["firstname"]) {
                $fullname .= $rec["firstname"] . " ";
            }
            if ($rec["lastname"]) {
                $fullname .= $rec["lastname"];
            }
        }
        return $fullname;
    }

    /**
    * Lookup email
    */
    public static function _lookupEmail($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "email");
    }
    
    /**
     * Lookup second e-mail
     * @param $a_user_id
     * @return null|string
     */
    public static function _lookupSecondEmail($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "second_email");
    }

    /**
    * Lookup gender
    */
    public static function _lookupGender($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "gender");
    }

    /**
    * Lookup client ip
    *
    * @param	int		user id
    * @return	string	client ip
    */
    public static function _lookupClientIP($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "client_ip");
    }


    /**
    * lookup user name
     *
     * @return array array('user_id' => ...,'firstname' => ...,'lastname' => ...,'login' => ...,'title' => ...)
    */
    public static function _lookupName($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT firstname, lastname, title, login FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );
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
    public static function _lookupFields($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT * FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );
        $user_rec = $ilDB->fetchAssoc($res);
        return $user_rec;
    }

    /**
    * lookup login
    */
    public static function _lookupLogin($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "login");
    }

    /**
    * lookup external account for login and authmethod
    */
    public static function _lookupExternalAccount($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "ext_account");
    }

    /**
     * Lookup id by login
     */
    public static function _lookupId($a_user_str)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!is_array($a_user_str)) {
            $res = $ilDB->queryF(
                "SELECT usr_id FROM usr_data WHERE login = %s",
                array("text"),
                array($a_user_str)
            );
            $user_rec = $ilDB->fetchAssoc($res);
            return $user_rec["usr_id"];
        } else {
            $set = $ilDB->query(
                "SELECT usr_id FROM usr_data " .
                " WHERE " . $ilDB->in("login", $a_user_str, false, "text")
            );
            $ids = array();
            while ($rec = $ilDB->fetchAssoc($set)) {
                $ids[] = $rec["usr_id"];
            }
            return $ids;
        }
    }

    /**
    * lookup last login
    */
    public static function _lookupLastLogin($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "last_login");
    }

    /**
    * lookup first login
    */
    public static function _lookupFirstLogin($a_user_id)
    {
        return ilObjUser::_lookup($a_user_id, "first_login");
    }


    /**
    * updates the login data of a "user"
    * // TODO set date with now() should be enough
    * @access	public
    */
    public function refreshLogin()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "UPDATE usr_data SET " .
             "last_login = " . $ilDB->now() .
             " WHERE usr_id = %s",
            array("integer"),
            array($this->id)
        );

        if ($this->getFirstLogin() == "") {
            $ilDB->manipulateF(
                "UPDATE usr_data SET " .
                "first_login = " . $ilDB->now() .
                " WHERE usr_id = %s",
                array("integer"),
                array($this->id)
            );
        }
    }


    /**
     * Resets the user password
     * @param    string $raw        Password as plaintext
     * @param    string $raw_retype Retyped password as plaintext
     * @return    boolean    true on success otherwise false
     * @access    public
     */
    public function resetPassword($raw, $raw_retype)
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (func_num_args() != 2) {
            return false;
        }

        if (!isset($raw) || !isset($raw_retype)) {
            return false;
        }

        if ($raw != $raw_retype) {
            return false;
        }

        require_once 'Services/User/classes/class.ilUserPasswordManager.php';
        ilUserPasswordManager::getInstance()->encodePassword($this, $raw);

        $ilDB->manipulateF(
            'UPDATE usr_data
			SET passwd = %s, passwd_enc_type = %s, passwd_salt = %s
			WHERE usr_id = %s',
            array('text', 'text', 'text', 'integer'),
            array($this->getPasswd(), $this->getPasswordEncodingType(), $this->getPasswordSalt(), $this->getId())
        );

        return true;
    }

    /**
     *
     * Checks wether the passed loginname already exists in history
     *
     * @access	public
     * @param	string	$a_login	Loginname
     * @return	boolean	true or false
     * @static
     *
     */
    public static function _doesLoginnameExistInHistory($a_login)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
            
        $res = $ilDB->queryF(
            '
			SELECT * FROM loginname_history
			WHERE login = %s',
            array('text'),
            array($a_login)
        );

        return $ilDB->fetchAssoc($res) ? true : false;
    }
    
    /**
     *
     * Returns the last used loginname and the changedate of the passed user_id.
     * Throws an ilUserException in case no entry could be found.
     *
     * @access	public
     * @param	string	$a_usr_id	A user id
     * @return	array	Associative array, first index is the loginname, second index a unix_timestamp
     * @throws	ilUserException
     * @static
     *
     */
    public static function _getLastHistoryDataByUserId($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
            
        $ilDB->setLimit(1, 0);
        $res = $ilDB->queryF(
            '
			SELECT login, history_date FROM loginname_history
			WHERE usr_id = %s ORDER BY history_date DESC',
            array('integer'),
            array($a_usr_id)
        );
        $row = $ilDB->fetchAssoc($res);
        if (!is_array($row) || !count($row)) {
            throw new ilUserException('');
        }
        
        return array(
            $row['login'], $row['history_date']
        );
    }
    
    /**
    * update login name
    * @param	string	new login
    * @return	boolean	true on success; otherwise false
    * @access	public
    * @throws ilUserException
    */
    public function updateLogin($a_login)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        if (func_num_args() != 1) {
            return false;
        }

        if (!isset($a_login)) {
            return false;
        }
        
        $former_login = self::_lookupLogin($this->getId());

        // Update not necessary
        if (0 == strcmp($a_login, $former_login)) {
            return false;
        }
        
        try {
            $last_history_entry = ilObjUser::_getLastHistoryDataByUserId($this->getId());
        } catch (ilUserException $e) {
            $last_history_entry = null;
        }
    
        // throw exception if the desired loginame is already in history and it is not allowed to reuse it
        if ((int) $ilSetting->get('allow_change_loginname') &&
           (int) $ilSetting->get('reuse_of_loginnames') == 0 &&
           self::_doesLoginnameExistInHistory($a_login)) {
            throw new ilUserException($this->lng->txt('loginname_already_exists'));
        } elseif ((int) $ilSetting->get('allow_change_loginname') &&
                (int) $ilSetting->get('loginname_change_blocking_time') &&
                is_array($last_history_entry) &&
                $last_history_entry[1] + (int) $ilSetting->get('loginname_change_blocking_time') > time()) {
            include_once 'Services/Calendar/classes/class.ilDate.php';
            throw new ilUserException(
                sprintf(
                    $this->lng->txt('changing_loginname_not_possible_info'),
                    ilDatePresentation::formatDate(
                        new ilDateTime($last_history_entry[1], IL_CAL_UNIX)
                    ),
                    ilDatePresentation::formatDate(
                        new ilDateTime(($last_history_entry[1] + (int) $ilSetting->get('loginname_change_blocking_time')), IL_CAL_UNIX)
                    )
                )
            );
        } else {
            // log old loginname in history
            if ((int) $ilSetting->get('allow_change_loginname') &&
               (int) $ilSetting->get('create_history_loginname')) {
                ilObjUser::_writeHistory($this->getId(), $former_login);
            }

            //update login
            $this->login = $a_login;

            $ilDB->manipulateF(
                '
				UPDATE usr_data
				SET login = %s
				WHERE usr_id = %s',
                array('text', 'integer'),
                array($this->getLogin(), $this->getId())
            );
        }

        return true;
    }

    /**
    * write userpref to user table
    * @access	private
    * @param	string	keyword
    * @param	string		value
    */
    public function writePref($a_keyword, $a_value)
    {
        self::_writePref($this->id, $a_keyword, $a_value);
        $this->setPref($a_keyword, $a_value);
    }


    /**
    * Deletes a userpref value of the user from the database
    * @access	public
    * @param	string	keyword
    */
    public function deletePref($a_keyword)
    {
        self::_deletePref($this->getId(), $a_keyword);
    }

    /**
     * @static
     * @param int $a_user_id
     * @param string $a_keyword
     */
    public static function _deletePref($a_user_id, $a_keyword)
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            'DELETE FROM usr_pref WHERE usr_id = %s AND keyword = %s',
            array('integer', 'text'),
            array($a_user_id, $a_keyword)
        );
    }

    /**
    * Deletes a userpref value of the user from the database
    * @access	public
    * @param	string	keyword
    */
    public static function _deleteAllPref($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "DELETE FROM usr_pref WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );
    }

    /**
     * @static
     * @param int $a_usr_id
     * @param string $a_keyword
     * @param string $a_value
     */
    public static function _writePref($a_usr_id, $a_keyword, $a_value)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilDB->replace(
            "usr_pref",
            array(
                "usr_id" => array("integer", $a_usr_id),
                "keyword" => array("text", $a_keyword),
            ),
            array(
                "value" => array("text",$a_value)
            )
        );

        /*
        self::_deletePref($a_usr_id, $a_keyword);
        if(strlen($a_value))
        {
            $ilDB->manipulateF(
                'INSERT INTO usr_pref (usr_id, keyword, value) VALUES (%s, %s, %s)',
                array('integer', 'text', 'text'),
                array($a_usr_id, $a_keyword, $a_value)
            );
        }*/
    }

    /**
    * write all userprefs
    * @access	private
    */
    public function writePrefs()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        ilObjUser::_deleteAllPref($this->id);
        foreach ($this->prefs as $keyword => $value) {
            self::_writePref($this->id, $keyword, $value);
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
        if ($tz = $this->getPref('user_tz')) {
            return $tz;
        } else {
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
        if ($format = $this->getPref('time_format')) {
            return $format;
        } else {
            include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
            $settings = ilCalendarSettings::_getInstance();
            return $settings->getDefaultTimeFormat();
        }
    }

    /**
     * get date format
     *
     * @access public
     * @return
     */
    public function getDateFormat()
    {
        if ($format = $this->getPref('date_format')) {
            return $format;
        } else {
            include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
            $settings = ilCalendarSettings::_getInstance();
            return $settings->getDefaultDateFormat();
        }
    }

    /**
    * set a user preference
    * @param	string	name of parameter
    * @param	string	value
    * @access	public
    */
    public function setPref($a_keyword, $a_value)
    {
        if ($a_keyword != "") {
            $this->prefs[$a_keyword] = $a_value;
        }
    }

    /**
    * get a user preference
    * @param	string	name of parameter
    * @access	public
    */
    public function getPref($a_keyword)
    {
        if (array_key_exists($a_keyword, $this->prefs)) {
            return $this->prefs[$a_keyword];
        } else {
            return false;
        }
    }

    public static function _lookupPref($a_usr_id, $a_keyword)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM usr_pref WHERE usr_id = " . $ilDB->quote($a_usr_id, "integer") . " " .
            "AND keyword = " . $ilDB->quote($a_keyword, "text");
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->value;
        }
        return false;
    }

    /**
    * get all user preferences
    * @access	private
    */
    public function readPrefs()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (is_array($this->prefs)) {
            $this->oldPrefs = $this->prefs;
        }

        $this->prefs = ilObjUser::_getPreferences($this->id);
    }

    /**
    * deletes a user
    * @access	public
    * @param	integer		user_id
    */
    public function delete()
    {
        global $DIC;

        $rbacadmin = $DIC->rbac()->admin();
        $ilDB = $DIC['ilDB'];

        // deassign from ldap groups
        include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->deleteUser($this->getId());

        // remove mailbox / update sent mails
        include_once("Services/Mail/classes/class.ilMailbox.php");
        $mailbox = new ilMailbox($this->getId());
        $mailbox->delete();
        $mailbox->updateMailsOfDeletedUser($this->getLogin());

        // delete feed blocks on personal desktop
        include_once("./Services/Block/classes/class.ilCustomBlock.php");
        $costum_block = new ilCustomBlock();
        $costum_block->setContextObjId($this->getId());
        $costum_block->setContextObjType("user");
        $c_blocks = $costum_block->queryBlocksForContext();
        include_once("./Services/Feeds/classes/class.ilPDExternalFeedBlock.php");
        foreach ($c_blocks as $c_block) {
            if ($c_block["type"] == "pdfeed") {
                $fb = new ilPDExternalFeedBlock($c_block["id"]);
                $fb->delete();
            }
        }


        // delete block settings
        include_once("./Services/Block/classes/class.ilBlockSetting.php");
        ilBlockSetting::_deleteSettingsOfUser($this->getId());

        // delete user_account
        $ilDB->manipulateF(
            "DELETE FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($this->getId())
        );
        
        $this->deleteMultiTextFields();

        // delete user_prefs
        ilObjUser::_deleteAllPref($this->getId());
            
        $this->removeUserPicture(false); // #8597

        // delete user_session
        include_once("./Services/Authentication/classes/class.ilSession.php");
        ilSession::_destroyByUserId($this->getId());

        // remove user from rbac
        $rbacadmin->removeUser($this->getId());

        // remove bookmarks
        // TODO: move this to class.ilBookmarkFolder
        $q = "DELETE FROM bookmark_tree WHERE tree = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        $q = "DELETE FROM bookmark_data WHERE user_id = " .
            $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        // DELETE FORUM ENTRIES (not complete in the moment)
        include_once './Modules/Forum/classes/class.ilObjForum.php';
        ilObjForum::_deleteUser($this->getId());

        // Delete link check notify entries
        include_once './Services/LinkChecker/classes/class.ilLinkCheckNotify.php';
        ilLinkCheckNotify::_deleteUser($this->getId());

        // Delete crs entries
        include_once './Modules/Course/classes/class.ilObjCourse.php';
        ilObjCourse::_deleteUser($this->getId());

        // Delete user tracking
        include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
        ilObjUserTracking::_deleteUser($this->getId());

        include_once 'Modules/Session/classes/class.ilEventParticipants.php';
        ilEventParticipants::_deleteByUser($this->getId());
        
        // Delete Tracking data SCORM 2004 RTE
        include_once 'Modules/Scorm2004/classes/ilSCORM13Package.php';
        ilSCORM13Package::_removeTrackingDataForUser($this->getId());
        
        // Delete Tracking data SCORM 1.2 RTE
        include_once 'Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php';
        ilObjSCORMLearningModule::_removeTrackingDataForUser($this->getId());

        // remove all notifications
        include_once "./Services/Notification/classes/class.ilNotification.php";
        ilNotification::removeForUser($this->getId());
        
        // remove portfolios
        include_once "./Modules/Portfolio/classes/class.ilObjPortfolio.php";
        ilObjPortfolio::deleteUserPortfolios($this->getId());
        
        // remove workspace
        include_once "./Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
        $tree = new ilWorkspaceTree($this->getId());
        $tree->cascadingDelete();

        // remove reminder entries
        require_once 'Services/User/classes/class.ilCronDeleteInactiveUserReminderMail.php';
        ilCronDeleteInactiveUserReminderMail::removeSingleUserFromTable($this->getId());
        
        // badges
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        ilBadgeAssignment::deleteByUserId($this->getId());

        // remove org unit assignments
        $ilOrgUnitUserAssignmentQueries = ilOrgUnitUserAssignmentQueries::getInstance();
        $ilOrgUnitUserAssignmentQueries->deleteAllAssignmentsOfUser($this->getId());
        
        // Delete user defined field entries
        $this->deleteUserDefinedFieldEntries();
        
        // Delete clipboard entries
        $this->clipboardDeleteAll();
        
        // Reset owner
        $this->resetOwner();

        // Trigger deleteUser Event
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $ilAppEventHandler->raise(
            'Services/User',
            'deleteUser',
            array('usr_id' => $this->getId())
        );

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
    public function setFullname($a_title = "", $a_firstname = "", $a_lastname = "")
    {
        $this->fullname = "";

        if ($a_title) {
            $fullname = $a_title . " ";
        } elseif ($this->utitle) {
            $this->fullname = $this->utitle . " ";
        }

        if ($a_firstname) {
            $fullname .= $a_firstname . " ";
        } elseif ($this->firstname) {
            $this->fullname .= $this->firstname . " ";
        }

        if ($a_lastname) {
            return $fullname . $a_lastname;
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
    public function getFullname($a_max_strlen = 0)
    {
        if (!$a_max_strlen) {
            return ilUtil::stripSlashes($this->fullname);
        }

        if (strlen($this->fullname) <= $a_max_strlen) {
            return ilUtil::stripSlashes($this->fullname);
        }

        if ((strlen($this->utitle) + strlen($this->lastname) + 4) <= $a_max_strlen) {
            return ilUtil::stripSlashes($this->utitle . " " . substr($this->firstname, 0, 1) . ". " . $this->lastname);
        }

        if ((strlen($this->firstname) + strlen($this->lastname) + 1) <= $a_max_strlen) {
            return ilUtil::stripSlashes($this->firstname . " " . $this->lastname);
        }

        if ((strlen($this->lastname) + 3) <= $a_max_strlen) {
            return ilUtil::stripSlashes(substr($this->firstname, 0, 1) . ". " . $this->lastname);
        }

        return ilUtil::stripSlashes(substr($this->lastname, 0, $a_max_strlen));
    }

    /**
    * set login / username
    * @access	public
    * @param	string	username
    */
    public function setLogin($a_str)
    {
        $this->login = $a_str;
    }

    /**
    * get login / username
    * @access	public
    */
    public function getLogin()
    {
        return $this->login;
    }

    /**
    * set password
    * @access	public
    * @param	string	passwd
    */
    public function setPasswd($a_str, $a_type = IL_PASSWD_PLAIN)
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
    public function getPasswd()
    {
        return $this->passwd;
    }
    /**
    * get password type
    * @return password type (IL_PASSWD_PLAIN, IL_PASSWD_CRYPTED).
    * @access	public
    * @see getPasswd
    */
    public function getPasswdType()
    {
        return $this->passwd_type;
    }

    /**
    * set gender
    * @access	public
    * @param	string	gender
    */
    public function setGender($a_str)
    {
        $this->gender = substr($a_str, -1);
    }

    /**
    * get gender
    * @access	public
    */
    public function getGender()
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
    public function setUTitle($a_str)
    {
        $this->utitle = $a_str;
    }

    /**
    * get user title
    * (note: don't mix up this method with getTitle() that is derived from
    * ilObject and gets the user object's title)
    * @access	public
    */
    public function getUTitle()
    {
        return $this->utitle;
    }

    /**
    * set firstname
    * @access	public
    * @param	string	firstname
    */
    public function setFirstname($a_str)
    {
        $this->firstname = $a_str;
    }

    /**
    * get firstname
    * @access	public
    */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
    * set lastame
    * @access	public
    * @param	string	lastname
    */
    public function setLastname($a_str)
    {
        $this->lastname = $a_str;
    }

    /**
    * get lastname
    * @access	public
    */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
    * set institution
    * @access	public
    * @param	string	institution
    */
    public function setInstitution($a_str)
    {
        $this->institution = $a_str;
    }

    /**
    * get institution
    * @access	public
    */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
    * set department
    * @access	public
    * @param	string	department
    */
    public function setDepartment($a_str)
    {
        $this->department = $a_str;
    }

    /**
    * get department
    * @access	public
    */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
    * set street
    * @access	public
    * @param	string	street
    */
    public function setStreet($a_str)
    {
        $this->street = $a_str;
    }

    /**
    * get street
    * @access	public
    */
    public function getStreet()
    {
        return $this->street;
    }

    /**
    * set city
    * @access	public
    * @param	string	city
    */
    public function setCity($a_str)
    {
        $this->city = $a_str;
    }

    /**
    * get city
    * @access	public
    */
    public function getCity()
    {
        return $this->city;
    }

    /**
    * set zipcode
    * @access	public
    * @param	string	zipcode
    */
    public function setZipcode($a_str)
    {
        $this->zipcode = $a_str;
    }

    /**
    * get zipcode
    * @access	public
    */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set country (free text)
     *
     * @access	public
     * @param	string	country
     */
    public function setCountry($a_str)
    {
        $this->country = $a_str;
    }

    /**
     * Get country (free text)
     *
     * @access	public
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set selected country (selection drop down)
     *
     * @param	string	selected country
     */
    public function setSelectedCountry($a_val)
    {
        $this->sel_country = $a_val;
    }

    /**
     * Get selected country (selection drop down)
     *
     * @return	string	selected country
     */
    public function getSelectedCountry()
    {
        return $this->sel_country;
    }

    /**
    * set office phone
    * @access	public
    * @param	string	office phone
    */
    public function setPhoneOffice($a_str)
    {
        $this->phone_office = $a_str;
    }

    /**
    * get office phone
    * @access	public
    */
    public function getPhoneOffice()
    {
        return $this->phone_office;
    }

    /**
    * set home phone
    * @access	public
    * @param	string	home phone
    */
    public function setPhoneHome($a_str)
    {
        $this->phone_home = $a_str;
    }

    /**
    * get home phone
    * @access	public
    */
    public function getPhoneHome()
    {
        return $this->phone_home;
    }

    /**
    * set mobile phone
    * @access	public
    * @param	string	mobile phone
    */
    public function setPhoneMobile($a_str)
    {
        $this->phone_mobile = $a_str;
    }

    /**
    * get mobile phone
    * @access	public
    */
    public function getPhoneMobile()
    {
        return $this->phone_mobile;
    }

    /**
    * set fax
    * @access	public
    * @param	string	fax
    */
    public function setFax($a_str)
    {
        $this->fax = $a_str;
    }

    /**
    * get fax
    * @access	public
    */
    public function getFax()
    {
        return $this->fax;
    }

    /**
    * set client ip number
    * @access	public
    * @param	string	client ip
    */
    public function setClientIP($a_str)
    {
        $this->client_ip = $a_str;
    }

    /**
    * get client ip number
    * @access	public
    */
    public function getClientIP()
    {
        return $this->client_ip;
    }

    /**
    * set matriculation number
    * @access	public
    * @param	string	matriculation number
    */
    public function setMatriculation($a_str)
    {
        $this->matriculation = $a_str;
    }

    /**
    * get matriculation number
    * @access	public
    */
    public function getMatriculation()
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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT matriculation FROM usr_data " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id);
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->matriculation ? $row->matriculation : '';
    }

    /**
    * set email
    * @access	public
    * @param	string	email address
    */
    public function setEmail($a_str)
    {
        $this->email = $a_str;
    }

    /**
    * get email address
    * @access	public
    */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @return null|string
     */
    public function getSecondEmail()
    {
        return $this->second_email;
    }
    
    /**
     * @param null|string $second_email
     */
    public function setSecondEmail($second_email)
    {
        $this->second_email = $second_email;
    }
    
    /**
    * set hobby
    * @access	public
    * @param    string  hobby
    */
    public function setHobby($a_str)
    {
        $this->hobby = $a_str;
    }

    /**
    * get hobby
    * @access	public
    */
    public function getHobby()
    {
        return $this->hobby;
    }

    /**
    * set user language
    * @access	public
    * @param	string	lang_key (i.e. de,en,fr,...)
    */
    public function setLanguage($a_str)
    {
        $this->setPref("language", $a_str);
        unset($_SESSION['lang']);
    }

    /**
    * returns a 2char-language-string
    * @access	public
    * @return	string	language
    */
    public function getLanguage()
    {
        return $this->prefs["language"];
    }

    public function setLastPasswordChangeTS($a_last_password_change_ts)
    {
        $this->last_password_change_ts = $a_last_password_change_ts;
    }

    public function getLastPasswordChangeTS()
    {
        return $this->last_password_change_ts;
    }

    /**
     * @return int
     */
    public function getPasswordPolicyResetStatus() : bool
    {
        return (bool) $this->passwd_policy_reset;
    }

    /**
     * @param int $passwd_policy_reset
     */
    public function setPasswordPolicyResetStatus(bool $status)
    {
        $this->passwd_policy_reset = $status;
    }

    public static function _lookupLanguage($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $q = "SELECT value FROM usr_pref WHERE usr_id= " .
            $ilDB->quote($a_usr_id, "integer") . " AND keyword = " .
            $ilDB->quote('language', "text");
        $r = $ilDB->query($q);

        while ($row = $ilDB->fetchAssoc($r)) {
            return $row['value'];
        }
        if (is_object($lng)) {
            return $lng->getDefaultLanguage();
        }
        return 'en';
    }

    public static function _writeExternalAccount($a_usr_id, $a_ext_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "UPDATE usr_data " .
            " SET ext_account = %s WHERE usr_id = %s",
            array("text", "integer"),
            array($a_ext_id, $a_usr_id)
        );
    }

    public static function _writeAuthMode($a_usr_id, $a_auth_mode)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "UPDATE usr_data " .
            " SET auth_mode = %s WHERE usr_id = %s",
            array("text", "integer"),
            array($a_auth_mode, $a_usr_id)
        );
    }

    /**
     * returns the current language (may differ from user's pref setting!)
     *
     */
    public function getCurrentLanguage()
    {
        return $_SESSION['lang'];
    }

    /**
     * Set current language
     *
     * @param string $a_val current language
     */
    public function setCurrentLanguage($a_val)
    {
        $_SESSION['lang'] = $a_val;
    }

    /**
    * set user's last login
    * @access	public
    * @param	string	login date
    */
    public function setLastLogin($a_str)
    {
        $this->last_login = $a_str;
    }

    /**
    * returns last login date
    * @access	public
    * @return	string	date
    */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * set user's first login
     * @param	string	login date
     */
    public function setFirstLogin($a_str)
    {
        $this->first_login = $a_str;
    }

    /**
     * returns first login date
     * @return	string	date
     */
    public function getFirstLogin()
    {
        return $this->first_login;
    }

    /**
     * set user's last profile prompt
     * @param	string	last profile prompt timestamp
     */
    public function setLastProfilePrompt($a_str)
    {
        $this->last_profile_prompt = $a_str;
    }

    /**
     * returns user's last profile prompt
     * @return	string	ast profile prompt timestamp
     */
    public function getLastProfilePrompt()
    {
        return $this->last_profile_prompt;
    }

    /**
    * set last update of user data set
    * @access	public
    * @param	string	date
    */
    public function setLastUpdate($a_str)
    {
        $this->last_update = $a_str;
    }
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
    * set referral comment
    * @access   public
    * @param    string  hobby
    */
    public function setComment($a_str)
    {
        $this->referral_comment = $a_str;
    }

    /**
    * get referral comment
    * @access   public
    */
    public function getComment()
    {
        return $this->referral_comment;
    }

    /**
    * set date the user account was activated
    * null indicates that the user has not yet been activated
    * @access   public
    * @return   void
    */
    public function setApproveDate($a_str)
    {
        $this->approve_date = $a_str;
    }

    /**
    * get the date when the user account was approved
    * @access   public
    * @return   string      approve date
    */
    public function getApproveDate()
    {
        return $this->approve_date;
    }

    /**
    * get the date when the user accepted the user agreement
    * @access   public
    * @return   string      date of last update
    */
    public function getAgreeDate()
    {
        return $this->agree_date;
    }
    /**
    * set date the user account was accepted by the user
    * nullindicates that the user has not accepted his account
    * @access   public
    * @return   void
    */
    public function setAgreeDate($a_str)
    {
        $this->agree_date = $a_str;
    }

    /**
    * set user active state and updates system fields appropriately
    * @access   public
    * @param    string  $a_active the active state of the user account
    * @param    string  $a_owner the id of the person who approved the account, defaults to 6 (root)
    */
    public function setActive($a_active, $a_owner = 0)
    {
        $this->setOwner($a_owner);

        if ($a_active) {
            $this->active = 1;
            $this->setApproveDate(date('Y-m-d H:i:s'));
            $this->setOwner($a_owner);
        } else {
            $this->active = 0;
            $this->setApproveDate(null);
        }
    }

    /**
    * get user active state
    * @access   public
    */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Check user account active
     */
    public static function _lookupActive($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT usr_id FROM usr_data ' .
            'WHERE active = ' . $ilDB->quote(1, 'integer') . ' ' .
            'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    /**
    * synchronizes current and stored user active values
    * for the owner value to be set correctly, this function should only be called when an admin is approving a user account
    * @access  public
    */
    public function syncActive()
    {
        $storedActive = 0;
        if ($this->getStoredActive($this->id)) {
            $storedActive = 1;
        }

        $currentActive = 0;
        if ($this->active) {
            $currentActive = 1;
        }

        if ((!empty($storedActive) && empty($currentActive)) ||
                (empty($storedActive) && !empty($currentActive))) {
            $this->setActive($currentActive, self::getUserIdByLogin(ilObjUser::getLoginFromAuth()));
        }
    }

    /**
    * get user active state
    * @param   integer $a_id user id
    * @access  public
    * @return  true if active, otherwise false
    */
    public function getStoredActive($a_id)
    {
        $active = ilObjUser::_lookup($a_id, "active");
        return $active ? true : false;
    }

    /**
    * set user skin (template set)
    * @access	public
    * @param	string	directory name of template set
    */
    public function setSkin($a_str)
    {
        // TODO: exception handling (dir exists)
        $this->skin = $a_str;
    }

    public function setTimeLimitOwner($a_owner)
    {
        $this->time_limit_owner = $a_owner;
    }
    public function getTimeLimitOwner()
    {
        return $this->time_limit_owner ? $this->time_limit_owner : 7;
    }
    public function setTimeLimitFrom($a_from)
    {
        $this->time_limit_from = $a_from;
    }
    public function getTimeLimitFrom()
    {
        return $this->time_limit_from;
    }
    public function setTimeLimitUntil($a_until)
    {
        $this->time_limit_until = $a_until;
    }
    public function getTimeLimitUntil()
    {
        return $this->time_limit_until;
    }
    public function setTimeLimitUnlimited($a_unlimited)
    {
        $this->time_limit_unlimited = $a_unlimited;
    }
    public function getTimeLimitUnlimited()
    {
        return $this->time_limit_unlimited;
    }
    public function setTimeLimitMessage($a_time_limit_message)
    {
        return $this->time_limit_message = $a_time_limit_message;
    }
    public function getTimeLimitMessage()
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


    public function checkTimeLimit()
    {
        if ($this->getTimeLimitUnlimited()) {
            return true;
        }
        if ($this->getTimeLimitFrom() < time() and $this->getTimeLimitUntil() > time()) {
            return true;
        }
        return false;
    }
    public function setProfileIncomplete($a_prof_inc)
    {
        $this->profile_incomplete = (boolean) $a_prof_inc;
    }
    public function getProfileIncomplete()
    {
        if ($this->id == ANONYMOUS_USER_ID) {
            return false;
        }
        return $this->profile_incomplete;
    }

    /**
     * @return bool
     */
    public function isPasswordChangeDemanded()
    {
        if ($this->id == ANONYMOUS_USER_ID) {
            return false;
        }

        if ($this->id == SYSTEM_USER_ID) {
            if (
                \ilUserPasswordManager::getInstance()->verifyPassword($this, base64_decode('aG9tZXI=')) &&
                !ilAuthUtils::_needsExternalAccountByAuthMode($this->getAuthMode(true))
            ) {
                return true;
            } else {
                return false;
            }
        }

        $security = ilSecuritySettings::_getInstance();

        $authModeAllowsPasswordChange = !ilAuthUtils::_needsExternalAccountByAuthMode($this->getAuthMode(true));
        $passwordResetOnFirstLogin = (
            $security->isPasswordChangeOnFirstLoginEnabled() &&
            $this->getLastPasswordChangeTS() == 0 && $this->is_self_registered == false
        );
        $passwordResetOnChangedPolicy = $this->getPasswordPolicyResetStatus();

        return ($authModeAllowsPasswordChange && ($passwordResetOnFirstLogin || $passwordResetOnChangedPolicy));
    }

    public function isPasswordExpired()
    {
        if ($this->id == ANONYMOUS_USER_ID) {
            return false;
        }

        require_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
        $security = ilSecuritySettings::_getInstance();
        if ($this->getLastPasswordChangeTS() > 0) {
            $max_pass_age = $security->getPasswordMaxAge();
            if ($max_pass_age > 0) {
                $max_pass_age_ts = ($max_pass_age * 86400);
                $pass_change_ts = $this->getLastPasswordChangeTS();
                $current_ts = time();

                if (($current_ts - $pass_change_ts) > $max_pass_age_ts) {
                    if (!ilAuthUtils::_needsExternalAccountByAuthMode($this->getAuthMode(true))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getPasswordAge()
    {
        $current_ts = time();
        $pass_change_ts = $this->getLastPasswordChangeTS();
        $password_age = (int) (($current_ts - $pass_change_ts) / 86400);
        return $password_age;
    }

    public function setLastPasswordChangeToNow()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->setLastPasswordChangeTS(time());

        $query = "UPDATE usr_data SET last_password_change = %s " .
                "WHERE usr_id = %s";
        $affected = $ilDB->manipulateF(
            $query,
            array('integer','integer'),
            array($this->getLastPasswordChangeTS(),$this->id)
        );
        if ($affected) {
            return true;
        } else {
            return false;
        }
    }

    public function resetLastPasswordChange()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE usr_data SET last_password_change = 0 " .
                "WHERE usr_id = %s";
        $affected = $ilDB->manipulateF(
            $query,
            array('integer'),
            array($this->getId())
        );
        if ($affected) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Set Latitude.
    *
    * @param	string	$a_latitude	Latitude
    */
    public function setLatitude($a_latitude)
    {
        $this->latitude = $a_latitude;
    }

    /**
    * Get Latitude.
    *
    * @return	string	Latitude
    */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
    * Set Longitude.
    *
    * @param	string	$a_longitude	Longitude
    */
    public function setLongitude($a_longitude)
    {
        $this->longitude = $a_longitude;
    }

    /**
    * Get Longitude.
    *
    * @return	string	Longitude
    */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
    * Set Location Zoom.
    *
    * @param	int	$a_locationzoom	Location Zoom
    */
    public function setLocationZoom($a_locationzoom)
    {
        $this->loc_zoom = $a_locationzoom;
    }

    /**
    * Get Location Zoom.
    *
    * @return	int	Location Zoom
    */
    public function getLocationZoom()
    {
        return $this->loc_zoom;
    }

    
    /**
     * Check for simultaneous login
     *
     * @return bool
     */
    public static function hasActiveSession($a_user_id, $a_session_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
    
        $set = $ilDB->queryf(
            '
			SELECT COUNT(*) session_count
			FROM usr_session WHERE user_id = %s AND expires > %s AND session_id != %s ',
            array('integer', 'integer', 'text'),
            array($a_user_id, time(), $a_session_id)
        );
        $row = $ilDB->fetchAssoc($set);
        return (bool) $row['session_count'];
    }

    /*
     * check user id with login name
     * @access  public
     */
    public function checkUserId()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $login = ilObjUser::getLoginFromAuth();
        $id = ilObjUser::_lookupId($login);
        if ($id > 0) {
            return $id;
        }
        return false;
    }

    /**
     * Gets the username from $ilAuth, and converts it into an ILIAS login name.
     */
    private static function getLoginFromAuth()
    {
        $uid = $GLOBALS['DIC']['ilAuthSession']->getUserId();
        $login = ilObjUser::_lookupLogin($uid);

        // BEGIN WebDAV: Strip Microsoft Domain Names from logins
        require_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
        if (ilDAVActivationChecker::_isActive()) {
            $login = self::toUsernameWithoutDomain($login);
        }
        return $login;
    }
    
    /**
     * Static function removes Microsoft domain name from username
     * webdav related
     * @param string $a_login
     * @return string
     */
    public static function toUsernameWithoutDomain($a_login)
    {
        // Remove all characters including the last slash or the last backslash
        // in the username
        $pos = strrpos($a_login, '/');
        $pos2 = strrpos($a_login, '\\');
        if ($pos === false || $pos < $pos2) {
            $pos = $pos2;
        }
        if ($pos !== false) {
            $a_login = substr($a_login, $pos + 1);
        }
        return $a_login;
    }

    /*
     * check to see if current user has been made active
     * @access  public
     * @return  true if active, otherwise false
     */
    public function isCurrentUserActive()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $login = ilObjUser::getLoginFromAuth();
        $set = $ilDB->queryF(
            "SELECT active FROM usr_data WHERE login= %s",
            array("text"),
            array($login)
        );
        //query has got a result
        if ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["active"]) {
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
    public static function getUserIdByLogin($a_login)
    {
        return (int) ilObjUser::_lookupId($a_login);
    }

    /**
     * STATIC METHOD
     * get all user_ids of an email address
     * @param	string email of user
     * @return  array of user ids
     * @static
     * @access	public
     */
    public static function getUserIdsByEmail($a_email) : array
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT usr_id FROM usr_data " .
            "WHERE email = %s and active = 1",
            array("text"),
            array($a_email)
        );
        $ids = array();
        while ($row = $ilDB->fetchObject($res)) {
            $ids[] = $row->usr_id;
        }

        return $ids;
    }


    /**
     * get all user login names of an email address
     * @param	string email of user
     * @return  array with all user login names
     * @access	public
     */
    public static function getUserLoginsByEmail($a_email) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "SELECT login FROM usr_data " .
            "WHERE email = %s and active = 1",
            array("text"),
            array($a_email)
        );
        $ids = array();
        while ($row = $ilDB->fetchObject($res)) {
            $ids[] = $row->login;
        }

        return $ids;
    }

    /*
     * STATIC METHOD
     * get the login name of a user_id
     * @param   integer id of user
     * @return  string login name; false if not found
     * @static
     * @access  public
     */
    public function getLoginByUserId($a_userid)
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
    public static function searchUsers($a_search_str, $active = 1, $a_return_ids_only = false, $filter_settings = false)
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        
        $query = "SELECT usr_data.usr_id, usr_data.login, usr_data.firstname, usr_data.lastname, usr_data.email, usr_data.active FROM usr_data ";
        
        $without_anonymous_users = true;

        // determine join filter
        $join_filter = " WHERE ";
        if ($filter_settings !== false && strlen($filter_settings)) {
            switch ($filter_settings) {
                case 3:
                    // show only users without courses
                    $join_filter = " LEFT JOIN obj_members ON usr_data.usr_id = obj_members.usr_id WHERE obj_members.usr_id IS NULL AND ";
                    break;
                case 5:
                    // show only users with a certain course membership
                    $ref_id = $_SESSION["user_filter_data"];
                    if ($ref_id) {
                        $join_filter = " LEFT JOIN obj_members ON usr_data.usr_id = obj_members.usr_id WHERE obj_members.obj_id = " .
                            "(SELECT obj_id FROM object_reference WHERE ref_id = " . $ilDB->quote($ref_id, "integer") . ") AND ";
                    }
                    break;
                case 6:
                    global $DIC;

                    $rbacreview = $DIC['rbacreview'];
                    $ref_id = $_SESSION["user_filter_data"];
                    if ($ref_id) {
                        $local_roles = $rbacreview->getRolesOfRoleFolder($ref_id, false);
                        if (is_array($local_roles) && count($local_roles)) {
                            $join_filter = " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE " .
                                $ilDB->in("rbac_ua.rol_id", $local_roles, false, $local_roles) . " AND ";
                        }
                    }
                    break;
                case 7:
                    global $DIC;

                    $rbacreview = $DIC['rbacreview'];
                    $rol_id = $_SESSION["user_filter_data"];
                    if ($rol_id) {
                        $join_filter = " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id = " .
                            $ilDB->quote($rol_id, "integer") . " AND ";
                        $without_anonymous_users = false;
                    }
                    break;
            }
        }
        // This is a temporary hack to search users by their role
        // See Mantis #338. This is a hack due to Mantis #337.
        if (strtolower(substr($a_search_str, 0, 5)) == "role:") {
            $query = "SELECT DISTINCT usr_data.usr_id,usr_data.login,usr_data.firstname,usr_data.lastname,usr_data.email " .
                "FROM object_data,rbac_ua,usr_data " .
                "WHERE " . $ilDB->like("object_data.title", "text", "%" . substr($a_search_str, 5) . "%") .
                " AND object_data.type = 'role' " .
                "AND rbac_ua.rol_id = object_data.obj_id " .
                "AND usr_data.usr_id = rbac_ua.usr_id " .
                "AND rbac_ua.usr_id != " . $ilDB->quote(ANONYMOUS_USER_ID, "integer");
        } else {
            $query .= $join_filter .
                "(" . $ilDB->like("usr_data.login", "text", "%" . $a_search_str . "%") . " " .
                "OR " . $ilDB->like("usr_data.firstname", "text", "%" . $a_search_str . "%") . " " .
                "OR " . $ilDB->like("usr_data.lastname", "text", "%" . $a_search_str . "%") . " " .
                "OR " . $ilDB->like("usr_data.email", "text", "%" . $a_search_str . "%") . ") ";

            if ($filter_settings !== false && strlen($filter_settings)) {
                switch ($filter_settings) {
                    case 0:
                        $query .= " AND usr_data.active = " . $ilDB->quote(0, "integer") . " ";
                        break;
                    case 1:
                        $query .= " AND usr_data.active = " . $ilDB->quote(1, "integer") . " ";
                        break;
                    case 2:
                        $query .= " AND usr_data.time_limit_unlimited = " . $ilDB->quote(0, "integer") . " ";
                        break;
                    case 4:
                        $date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"], $_SESSION["user_filter_data"]["y"]));
                        $query .= " AND last_login < " . $ilDB->quote($date, "timestamp") . " ";
                        break;
                }
            }
                
            if ($without_anonymous_users) {
                $query .= "AND usr_data.usr_id != " . $ilDB->quote(ANONYMOUS_USER_ID, "integer");
            }

            if (is_numeric($active) && $active > -1 && $filter_settings === false) {
                $query .= " AND active = " . $ilDB->quote($active, "integer") . " ";
            }
        }
        $ilLog->write($query);
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $users[] = array(
                "usr_id" => $row->usr_id,
                "login" => $row->login,
                "firstname" => $row->firstname,
                "lastname" => $row->lastname,
                "email" => $row->email,
                "active" => $row->active);
            $ids[] = $row->usr_id;
        }
        if ($a_return_ids_only) {
            return $ids ? $ids : array();
        } else {
            return $users ? $users : array();
        }
    }

    /**
    * @return array of logins
    */
    public static function getAllUserLogins()
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $logins = array();

        $res = $ilDB->query(
            "SELECT login FROM usr_data WHERE " . $ilDB->in('usr_id', array(ANONYMOUS_USER_ID), true, 'integer')
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $logins[] = $row['login'];
        }

        return $logins;
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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $res = $ilDB->query("SELECT * FROM usr_data WHERE " .
            $ilDB->in("usr_id", $a_user_ids, false, "integer"));
        while ($row = $ilDB->fetchAssoc($res)) {
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
    public static function _getAllUserData($a_fields = null, $active = -1)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $result_arr = array();
        $types = array();
        $values = array();

        if ($a_fields !== null and is_array($a_fields)) {
            if (count($a_fields) == 0) {
                $select = "*";
            } else {
                if (($usr_id_field = array_search("usr_id", $a_fields)) !== false) {
                    unset($a_fields[$usr_id_field]);
                }

                $select = implode(",", $a_fields) . ",usr_data.usr_id";
                // online time
                if (in_array('online_time', $a_fields)) {
                    $select .= ",ut_online.online_time ";
                }
            }

            $q = "SELECT " . $select . " FROM usr_data ";

            // Add online_time if desired
            // Need left join here to show users that never logged in
            if (in_array('online_time', $a_fields)) {
                $q .= "LEFT JOIN ut_online ON usr_data.usr_id = ut_online.usr_id ";
            }

            switch ($active) {
                case 0:
                case 1:
                    $q .= "WHERE active = " . $ilDB->quote($active, "integer");
                    break;
                case 2:
                    $q .= "WHERE time_limit_unlimited= " . $ilDB->quote(0, "integer");;
                    break;
                case 3:
                    $qtemp = $q . ", rbac_ua, object_data WHERE rbac_ua.rol_id = object_data.obj_id AND " .
                        $ilDB->like("object_data.title", "text", "%crs%") . " AND usr_data.usr_id = rbac_ua.usr_id";
                    $r = $ilDB->query($qtemp);
                    $course_users = array();
                    while ($row = $ilDB->fetchAssoc($r)) {
                        array_push($course_users, $row["usr_id"]);
                    }
                    if (count($course_users)) {
                        $q .= " WHERE " . $ilDB->in("usr_data.usr_id", $course_users, true, "integer") . " ";
                    } else {
                        return $result_arr;
                    }
                    break;
                case 4:
                    $date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"], $_SESSION["user_filter_data"]["y"]));
                    $q .= " AND last_login < " . $ilDB->quote($date, "timestamp");
                    break;
                case 5:
                    $ref_id = $_SESSION["user_filter_data"];
                    if ($ref_id) {
                        $q .= " LEFT JOIN obj_members ON usr_data.usr_id = obj_members.usr_id " .
                            "WHERE obj_members.obj_id = (SELECT obj_id FROM object_reference " .
                            "WHERE ref_id = " . $ilDB->quote($ref_id, "integer") . ") ";
                    }
                    break;
                case 6:
                    global $DIC;

                    $rbacreview = $DIC['rbacreview'];
                    $ref_id = $_SESSION["user_filter_data"];
                    if ($ref_id) {
                        $local_roles = $rbacreview->getRolesOfRoleFolder($ref_id, false);
                        if (is_array($local_roles) && count($local_roles)) {
                            $q .= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE " .
                                $ilDB->in("rbac_ua.rol_id", $local_roles, false, "integer") . " ";
                        }
                    }
                    break;
                case 7:
                    $rol_id = $_SESSION["user_filter_data"];
                    if ($rol_id) {
                        $q .= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id = " .
                            $ilDB->quote($rol_id, "integer");
                    }
                    break;
            }
            $r = $ilDB->query($q);

            while ($row = $ilDB->fetchAssoc($r)) {
                $result_arr[] = $row;
            }
        }

        return $result_arr;
    }

    /**
    * skins and styles
    */
    public static function _getNumberOfUsersForStyle($a_skin, $a_style)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT count(*) as cnt FROM usr_pref up1, usr_pref up2 " .
            " WHERE up1.keyword= " . $ilDB->quote("style", "text") .
            " AND up1.value= " . $ilDB->quote($a_style, "text") .
            " AND up2.keyword= " . $ilDB->quote("skin", "text") .
            " AND up2.value= " . $ilDB->quote($a_skin, "text") .
            " AND up1.usr_id = up2.usr_id ";

        $cnt_set = $ilDB->query($q);

        $cnt_rec = $ilDB->fetchAssoc($cnt_set);

        return $cnt_rec["cnt"];
    }

    /**
    * skins and styles
    */
    public static function _getAllUserAssignedStyles()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT DISTINCT up1.value style, up2.value skin FROM usr_pref up1, usr_pref up2 " .
            " WHERE up1.keyword = " . $ilDB->quote("style", "text") .
            " AND up2.keyword = " . $ilDB->quote("skin", "text") .
            " AND up1.usr_id = up2.usr_id";
            
        $sty_set = $ilDB->query($q);

        $styles = array();
        while ($sty_rec = $ilDB->fetchAssoc($sty_set)) {
            $styles[] = $sty_rec["skin"] . ":" . $sty_rec["style"];
        }

        return $styles;
    }

    /**
    * skins and styles
    */
    public static function _moveUsersToStyle($a_from_skin, $a_from_style, $a_to_skin, $a_to_style)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT up1.usr_id usr_id FROM usr_pref up1, usr_pref up2 " .
            " WHERE up1.keyword= " . $ilDB->quote("style", "text") .
            " AND up1.value= " . $ilDB->quote($a_from_style, "text") .
            " AND up2.keyword= " . $ilDB->quote("skin", "text") .
            " AND up2.value= " . $ilDB->quote($a_from_skin, "text") .
            " AND up1.usr_id = up2.usr_id ";

        $usr_set = $ilDB->query($q);

        while ($usr_rec = $ilDB->fetchAssoc($usr_set)) {
            self::_writePref($usr_rec["usr_id"], "skin", $a_to_skin);
            self::_writePref($usr_rec["usr_id"], "style", $a_to_style);
        }
    }


    ////
    ////
    ////	Edit Clipboard
    ////
    ////
    
    /**
    * add an item to user's personal clipboard
    *
    * @param	int		$a_item_id		ref_id for objects, that are in the main tree
    *									(learning modules, forums) obj_id for others
    * @param	string	$a_type			object type
    */
    public function addObjectToClipboard(
        $a_item_id,
        $a_type,
        $a_title,
        $a_parent = 0,
        $a_time = 0,
        $a_order_nr = 0
    ) {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_time == 0) {
            $a_time = date("Y-m-d H:i:s", time());
        }

        $item_set = $ilDB->queryF(
            "SELECT * FROM personal_clipboard WHERE " .
            "parent = %s AND item_id = %s AND type = %s AND user_id = %s",
            array("integer", "integer", "text", "integer"),
            array(0, $a_item_id, $a_type, $this->getId())
        );

        // only insert if item is not already in clipboard
        if (!$d = $item_set->fetchRow()) {
            $ilDB->manipulateF(
                "INSERT INTO personal_clipboard " .
                "(item_id, type, user_id, title, parent, insert_time, order_nr) VALUES " .
                " (%s,%s,%s,%s,%s,%s,%s)",
                array("integer", "text", "integer", "text", "integer", "timestamp", "integer"),
                array($a_item_id, $a_type, $this->getId(), $a_title, (int) $a_parent, $a_time, (int) $a_order_nr)
            );
        } else {
            $ilDB->manipulateF(
                "UPDATE personal_clipboard SET insert_time = %s " .
                "WHERE user_id = %s AND item_id = %s AND type = %s AND parent = 0",
                array("timestamp", "integer", "integer", "text"),
                array($a_time, $this->getId(), $a_item_id, $a_type)
            );
        }
    }

    /**
    * Add a page content item to PC clipboard (should go to another class)
    */
    public function addToPCClipboard($a_content, $a_time, $a_nr)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        if ($a_time == 0) {
            $a_time = date("Y-m-d H:i:s", time());
        }
        ilSession::set("user_pc_clip", true);
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
    public function getPCClipboardContent()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!ilSession::get("user_pc_clip")) {
            return [];
        }

        $set = $ilDB->queryF("SELECT MAX(insert_time) mtime FROM personal_pc_clipboard " .
            " WHERE user_id = %s", array("integer"), array($this->getId()));
        $row = $ilDB->fetchAssoc($set);
        
        $set = $ilDB->queryF(
            "SELECT * FROM personal_pc_clipboard " .
            " WHERE user_id = %s AND insert_time = %s ORDER BY order_nr ASC",
            array("integer", "timestamp"),
            array($this->getId(), $row["mtime"])
        );
        $content = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $content[] = $row["content"];
        }

        return $content;
    }

    /**
    * Check whether clipboard has objects of a certain type
    */
    public function clipboardHasObjectsOfType($a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->queryF(
            "SELECT * FROM personal_clipboard WHERE " .
            "parent = %s AND type = %s AND user_id = %s",
            array("integer", "text", "integer"),
            array(0, $a_type, $this->getId())
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }

        return false;
    }

    /**
    * Delete objects of type for user
    */
    public function clipboardDeleteObjectsOfType($a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "DELETE FROM personal_clipboard WHERE " .
            "type = %s AND user_id = %s",
            array("text", "integer"),
            array($a_type, $this->getId())
        );
    }

    /**
    * Delete objects of type for user
    */
    public function clipboardDeleteAll()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF("DELETE FROM personal_clipboard WHERE " .
            "user_id = %s", array("integer"), array($this->getId()));
    }

    /**
    * get all clipboard objects of user and specified type
    */
    public function getClipboardObjects($a_type = "", $a_top_nodes_only = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $par = "";
        if ($a_top_nodes_only) {
            $par = " AND parent = " . $ilDB->quote(0, "integer") . " ";
        }
        
        $type_str = ($a_type != "")
            ? " AND type = " . $ilDB->quote($a_type, "text") . " "
            : "";
        $q = "SELECT * FROM personal_clipboard WHERE " .
            "user_id = " . $ilDB->quote($this->getId(), "integer") . " " .
            $type_str . $par .
            " ORDER BY order_nr";
        $objs = $ilDB->query($q);
        $objects = array();
        while ($obj = $ilDB->fetchAssoc($objs)) {
            if ($obj["type"] == "mob") {
                $obj["title"] = ilObject::_lookupTitle($obj["item_id"]);
            }
            if ($obj["type"] == "incl") {
                include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
                $obj["title"] = ilMediaPoolPage::lookupTitle($obj["item_id"]);
            }
            $objects[] = array("id" => $obj["item_id"],
                "type" => $obj["type"], "title" => $obj["title"],
                "insert_time" => $obj["insert_time"]);
        }
        return $objects;
    }

    /**
    * Get childs of an item
    */
    public function getClipboardChilds($a_parent, $a_insert_time)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $objs = $ilDB->queryF(
            "SELECT * FROM personal_clipboard WHERE " .
            "user_id = %s AND parent = %s AND insert_time = %s " .
            " ORDER BY order_nr",
            array("integer", "integer", "timestamp"),
            array($ilUser->getId(), (int) $a_parent, $a_insert_time)
        );
        $objects = array();
        while ($obj = $ilDB->fetchAssoc($objs)) {
            if ($obj["type"] == "mob") {
                $obj["title"] = ilObject::_lookupTitle($obj["item_id"]);
            }
            $objects[] = array("id" => $obj["item_id"],
                "type" => $obj["type"], "title" => $obj["title"], "insert_time" => $obj["insert_time"]);
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
    public static function _getUsersForClipboadObject($a_type, $a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT DISTINCT user_id FROM personal_clipboard WHERE " .
            "item_id = " . $ilDB->quote($a_id, "integer") . " AND " .
            "type = " . $ilDB->quote($a_type, "text");
        $user_set = $ilDB->query($q);
        $users = array();
        while ($user_rec = $ilDB->fetchAssoc($user_set)) {
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
    public function removeObjectFromClipboard($a_item_id, $a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "DELETE FROM personal_clipboard WHERE " .
            "item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND type = " . $ilDB->quote($a_type, "text") . " " .
            " AND user_id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);
    }

    public static function _getImportedUserId($i2_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT obj_id FROM object_data WHERE import_id = " .
            $ilDB->quote($i2_id, "text");

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $id = $row->obj_id;
        }
        return $id ? $id : 0;
    }
    
    /**
     * lokup org unit representation
     * @param int $a_usr_id
     * @return string
     */
    public static function lookupOrgUnitsRepresentation($a_usr_id)
    {
        require_once('./Modules/OrgUnit/classes/PathStorage/class.ilOrgUnitPathStorage.php');
        return ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($a_usr_id);
    }


    /**
     * @return String
     */
    public function getOrgUnitsRepresentation()
    {
        return self::lookupOrgUnitsRepresentation($this->getId());
    }


    /**
    * set auth mode
    * @access	public
    */
    public function setAuthMode($a_str)
    {
        $this->auth_mode = $a_str;
    }

    /**
    * get auth mode
    * @access	public
    */
    public function getAuthMode($a_auth_key = false)
    {
        if (!$a_auth_key) {
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
    public function setExternalAccount($a_str)
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
    public function getExternalAccount()
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
    public static function _getExternalAccountsByAuthMode($a_auth_mode, $a_read_auth_default = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
        $q = "SELECT login,usr_id,ext_account,auth_mode FROM usr_data " .
            "WHERE auth_mode = %s";
        $types[] = "text";
        $values[] = $a_auth_mode;
        if ($a_read_auth_default and ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode', AUTH_LOCAL)) == $a_auth_mode) {
            $q .= " OR auth_mode = %s ";
            $types[] = "text";
            $values[] = 'default';
        }

        $res = $ilDB->queryF($q, $types, $values);
        while ($row = $ilDB->fetchObject($res)) {
            if ($row->auth_mode == 'default') {
                $accounts[$row->usr_id] = $row->login;
            } else {
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
    public static function _toggleActiveStatusOfUsers($a_usr_ids, $a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!is_array($a_usr_ids)) {
            return false;
        }
        
        
        if ($a_status) {
            $q = "UPDATE usr_data SET active = 1, inactivation_date = NULL WHERE " .
                $ilDB->in("usr_id", $a_usr_ids, false, "integer");
            $ilDB->manipulate($q);
        } else {
            $usrId_IN_usrIds = $ilDB->in("usr_id", $a_usr_ids, false, "integer");

            $q = "UPDATE usr_data SET active = 0 WHERE $usrId_IN_usrIds";
            $ilDB->manipulate($q);
            
            $queryString = "
				UPDATE usr_data
				SET inactivation_date = %s
				WHERE inactivation_date IS NULL
				AND $usrId_IN_usrIds
			";
            $ilDB->manipulateF($queryString, array('timestamp'), array(ilUtil::now()));
        }
        
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
    public static function _checkExternalAuthAccount($a_auth, $a_account, $tryFallback = true)
    {
        $db = $GLOBALS['DIC']->database();
        $settings = $GLOBALS['DIC']->settings();

        // Check directly with auth_mode
        $r = $db->queryF(
            "SELECT * FROM usr_data WHERE " .
            " ext_account = %s AND auth_mode = %s",
            array("text", "text"),
            array($a_account, $a_auth)
        );
        if ($usr = $db->fetchAssoc($r)) {
            return $usr["login"];
        }

        if (!$tryFallback) {
            return false;
        }

        // For compatibility, check for login (no ext_account entry given)
        $res = $db->queryF(
            "SELECT login FROM usr_data " .
            "WHERE login = %s AND auth_mode = %s AND (ext_account IS NULL OR ext_account = '') ",
            array("text", "text"),
            array($a_account, $a_auth)
        );
        if ($usr = $db->fetchAssoc($res)) {
            return $usr['login'];
        }

        // If auth_default == $a_auth => check for login
        if (ilAuthUtils::_getAuthModeName($settings->get('auth_mode')) == $a_auth) {
            $res = $db->queryF(
                "SELECT login FROM usr_data WHERE " .
                " ext_account = %s AND auth_mode = %s",
                array("text", "text"),
                array($a_account, "default")
            );
            if ($usr = $db->fetchAssoc($res)) {
                return $usr["login"];
            }
            // Search for login (no ext_account given)
            $res = $db->queryF(
                "SELECT login FROM usr_data " .
                "WHERE login = %s AND (ext_account IS NULL OR ext_account = '') AND auth_mode = %s",
                array("text", "text"),
                array($a_account, "default")
            );
            if ($usr = $db->fetchAssoc($res)) {
                return $usr["login"];
            }
        }
        return false;
    }

    /**
    * get number of users per auth mode
    */
    public static function _getNumberOfUsersPerAuthMode()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $r = $ilDB->query("SELECT count(*) AS cnt, auth_mode FROM usr_data " .
            "GROUP BY auth_mode");
        $cnt_arr = array();
        while ($cnt = $ilDB->fetchAssoc($r)) {
            $cnt_arr[$cnt["auth_mode"]] = $cnt["cnt"];
        }

        return $cnt_arr;
    }

    /**
    * check whether external account and authentication method
    * matches with a user
    *
    */
    public static function _getLocalAccountsForEmail($a_email)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        // default set to local (1)?

        $q = "SELECT * FROM usr_data WHERE " .
            " email = %s AND (auth_mode = %s ";
        $types = array("text", "text");
        $values = array($a_email, "local");

        if ($ilSetting->get("auth_mode") == 1) {
            $q .= " OR auth_mode = %s";
            $types[] = "text";
            $values[] = "default";
        }
        
        $q .= ")";

        $users = array();
        $usr_set = $ilDB->queryF($q, $types, $values);
        while ($usr_rec = $ilDB->fetchAssoc($usr_set)) {
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
    public static function _uploadPersonalPicture($tmp_file, $obj_id)
    {
        $webspace_dir = ilUtil::getWebspaceDir();
        $image_dir = $webspace_dir . "/usr_images";
        $store_file = "usr_" . $obj_id . "." . "jpg";
        $target_file = $image_dir . "/$store_file";

        chmod($tmp_file, 0770);

        // take quality 100 to avoid jpeg artefacts when uploading jpeg files
        // taking only frame [0] to avoid problems with animated gifs
        $show_file = "$image_dir/usr_" . $obj_id . ".jpg";
        $thumb_file = "$image_dir/usr_" . $obj_id . "_small.jpg";
        $xthumb_file = "$image_dir/usr_" . $obj_id . "_xsmall.jpg";
        $xxthumb_file = "$image_dir/usr_" . $obj_id . "_xxsmall.jpg";

        ilUtil::execConvert($tmp_file . "[0] -geometry 200x200 -quality 100 JPEG:" . $show_file);
        ilUtil::execConvert($tmp_file . "[0] -geometry 100x100 -quality 100 JPEG:" . $thumb_file);
        ilUtil::execConvert($tmp_file . "[0] -geometry 75x75 -quality 100 JPEG:" . $xthumb_file);
        ilUtil::execConvert($tmp_file . "[0] -geometry 30x30 -quality 100 JPEG:" . $xxthumb_file);

        // store filename
        self::_writePref($obj_id, "profile_image", $store_file);

        return true;
    }


    /**
     * Get path to personal picture. The result will be cached.
     * The result will be cached.
     *
     * @param string $a_size       "small", "xsmall" or "xxsmall"
     * @param bool   $a_force_pic
     * @return mixed
     */
    public function getPersonalPicturePath($a_size = "small", $a_force_pic = false)
    {
        if (isset(self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic])) {
            return self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic];
        }

        self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic] = ilObjUser::_getPersonalPicturePath($this->getId(), $a_size, $a_force_pic);

        return self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic];
    }

    public function getAvatar() : Avatar
    {
        return self::_getAvatar($this->getId());
    }

    public static function _getAvatar($a_usr_id) : Avatar
    {
        $define = new ilUserAvatarResolver((int) ($a_usr_id ? $a_usr_id : ANONYMOUS_USER_ID));

        return $define->getAvatar();
    }

    /**
     * Get path to personal picture.
     * @static
     * @param        $a_usr_id
     * @param string $a_size "small", "xsmall" or "xxsmall"
     * @param bool   $a_force_pic
     * @param bool   $a_prevent_no_photo_image
     * @return string
     *
     * @throws ilWACException
     */
    public static function _getPersonalPicturePath(
        $a_usr_id,
        $a_size = "small",
        $a_force_pic = false,
        $a_prevent_no_photo_image = false,
        $html_export = false
    ) {
        $define = new ilUserAvatarResolver((int) $a_usr_id);
        $define->setForcePicture($a_force_pic);
        $define->setSize($a_size);

        return ilWACSignedPath::signFile($define->getLegacyPictureURL());
    }

    /**
     * Get profile picture direcotory
     *
     * @param
     * @return
     */
    public static function copyProfilePicturesToDirectory($a_user_id, $a_dir)
    {
        $a_dir = trim(str_replace("..", "", $a_dir));
        if ($a_dir == "" || !is_dir($a_dir)) {
            return;
        }
        
        $webspace_dir = ilUtil::getWebspaceDir();
        $image_dir = $webspace_dir . "/usr_images";
        $images = array(
            "upload_" . $a_user_id . "pic",
            "usr_" . $a_user_id . "." . "jpg",
            "usr_" . $a_user_id . "_small.jpg",
            "usr_" . $a_user_id . "_xsmall.jpg",
            "usr_" . $a_user_id . "_xxsmall.jpg",
            "upload_" . $a_user_id);
        foreach ($images as $image) {
            if (is_file($image_dir . "/" . $image)) {
                copy($image_dir . "/" . $image, $a_dir . "/" . $image);
            }
        }
    }
    
    
    /**
    * Remove user picture.
    */
    public function removeUserPicture($a_do_update = true)
    {
        $webspace_dir = ilUtil::getWebspaceDir();
        $image_dir = $webspace_dir . "/usr_images";
        $file = $image_dir . "/usr_" . $this->getID() . "." . "jpg";
        $thumb_file = $image_dir . "/usr_" . $this->getID() . "_small.jpg";
        $xthumb_file = $image_dir . "/usr_" . $this->getID() . "_xsmall.jpg";
        $xxthumb_file = $image_dir . "/usr_" . $this->getID() . "_xxsmall.jpg";
        $upload_file = $image_dir . "/upload_" . $this->getID();

        if ($a_do_update) {
            // remove user pref file name
            $this->setPref("profile_image", "");
            $this->update();
        }

        if (@is_file($file)) {
            unlink($file);
        }
        if (@is_file($thumb_file)) {
            unlink($thumb_file);
        }
        if (@is_file($xthumb_file)) {
            unlink($xthumb_file);
        }
        if (@is_file($xxthumb_file)) {
            unlink($xxthumb_file);
        }
        if (@is_file($upload_file)) {
            unlink($upload_file);
        }
    }
    
    
    public function setUserDefinedData($a_data)
    {
        if (!is_array($a_data)) {
            return false;
        }
        foreach ($a_data as $field => $data) {
            #$new_data[$field] = ilUtil::stripSlashes($data);
            // Assign it directly to avoid update problems of unchangable fields
            $this->user_defined_data['f_' . $field] = $data;
        }
        #$this->user_defined_data = $new_data;

        return true;
    }

    public function getUserDefinedData()
    {
        return $this->user_defined_data ? $this->user_defined_data : array();
    }

    public function updateUserDefinedFields()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $fields = '';

        $field_def = array();
        
        include_once("./Services/User/classes/class.ilUserDefinedData.php");
        $udata = new ilUserDefinedData($this->getId());

        foreach ($this->user_defined_data as $field => $value) {
            if ($field != 'usr_id') {
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

    public function readUserDefinedFields()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once("./Services/User/classes/class.ilUserDefinedData.php");
        $udata = new ilUserDefinedData($this->getId());

        /*		$query = "SELECT * FROM udf_data ".
                    "WHERE usr_id = ".$ilDB->quote($this->getId(),'integer');

                $res = $this->db->query($query);
                while($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC))
                {
                    $this->user_defined_data = $row;
                }*/
        
        $this->user_defined_data = $udata->getAll();
        
        return true;
    }

    public function addUserDefinedFieldEntry()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // not needed. no entry in udf_text/udf_clob means no value

        /*		$query = "INSERT INTO udf_data (usr_id ) ".
                    "VALUES( ".
                    $ilDB->quote($this->getId(),'integer').
                    ")";
                $res = $ilDB->manipulate($query);
        */
        return true;
    }

    public function deleteUserDefinedFieldEntries()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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
    public function getProfileAsString(&$a_language)
    {
        include_once './Services/AccessControl/classes/class.ilObjRole.php';

        global $DIC;

        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];

        $language = &$a_language;
        $language->loadLanguageModule('registration');
        $language->loadLanguageModule('crs');

        $body = '';
        $body .= ($language->txt("login") . ": " . $this->getLogin() . "\n");

        if (strlen($this->getUTitle())) {
            $body .= ($language->txt("title") . ": " . $this->getUTitle() . "\n");
        }
        if (1 === strlen($this->getGender())) {
            $body .= ($language->txt("gender") . ": " . $language->txt('gender_' . strtolower($this->getGender())) . "\n");
        }
        if (strlen($this->getFirstname())) {
            $body .= ($language->txt("firstname") . ": " . $this->getFirstname() . "\n");
        }
        if (strlen($this->getLastname())) {
            $body .= ($language->txt("lastname") . ": " . $this->getLastname() . "\n");
        }
        if (strlen($this->getInstitution())) {
            $body .= ($language->txt("institution") . ": " . $this->getInstitution() . "\n");
        }
        if (strlen($this->getDepartment())) {
            $body .= ($language->txt("department") . ": " . $this->getDepartment() . "\n");
        }
        if (strlen($this->getStreet())) {
            $body .= ($language->txt("street") . ": " . $this->getStreet() . "\n");
        }
        if (strlen($this->getCity())) {
            $body .= ($language->txt("city") . ": " . $this->getCity() . "\n");
        }
        if (strlen($this->getZipcode())) {
            $body .= ($language->txt("zipcode") . ": " . $this->getZipcode() . "\n");
        }
        if (strlen($this->getCountry())) {
            $body .= ($language->txt("country") . ": " . $this->getCountry() . "\n");
        }
        if (strlen($this->getSelectedCountry())) {
            $body .= ($language->txt("sel_country") . ": " . $this->getSelectedCountry() . "\n");
        }
        if (strlen($this->getPhoneOffice())) {
            $body .= ($language->txt("phone_office") . ": " . $this->getPhoneOffice() . "\n");
        }
        if (strlen($this->getPhoneHome())) {
            $body .= ($language->txt("phone_home") . ": " . $this->getPhoneHome() . "\n");
        }
        if (strlen($this->getPhoneMobile())) {
            $body .= ($language->txt("phone_mobile") . ": " . $this->getPhoneMobile() . "\n");
        }
        if (strlen($this->getFax())) {
            $body .= ($language->txt("fax") . ": " . $this->getFax() . "\n");
        }
        if (strlen($this->getEmail())) {
            $body .= ($language->txt("email") . ": " . $this->getEmail() . "\n");
        }
        if (strlen($this->getSecondEmail())) {
            $body .= ($language->txt("second_email") . ": " . $this->getSecondEmail() . "\n");
        }
        if (strlen($this->getHobby())) {
            $body .= ($language->txt("hobby") . ": " . $this->getHobby() . "\n");
        }
        if (strlen($this->getComment())) {
            $body .= ($language->txt("referral_comment") . ": " . $this->getComment() . "\n");
        }
        if (strlen($this->getMatriculation())) {
            $body .= ($language->txt("matriculation") . ": " . $this->getMatriculation() . "\n");
        }
        if (strlen($this->getCreateDate())) {
            ilDatePresentation::setUseRelativeDates(false);
            ilDatePresentation::setLanguage($language);
            $date = ilDatePresentation::formatDate(new ilDateTime($this->getCreateDate(), IL_CAL_DATETIME));
            ilDatePresentation::resetToDefaults();
            
            $body .= ($language->txt("create_date") . ": " . $date . "\n");
        }

        $gr = [];
        foreach ($rbacreview->getGlobalRoles() as $role) {
            if ($rbacreview->isAssigned($this->getId(), $role)) {
                $gr[] = ilObjRole::_lookupTitle($role);
            }
        }
        if (count($gr)) {
            $body .= ($language->txt('reg_role_info') . ': ' . implode(',', $gr) . "\n");
        }

        // Time limit
        if ($this->getTimeLimitUnlimited()) {
            $body .= ($language->txt('time_limit') . ": " . $language->txt('crs_unlimited') . "\n");
        } else {
            ilDatePresentation::setUseRelativeDates(false);
            ilDatePresentation::setLanguage($language);
            $period = ilDatePresentation::formatPeriod(
                new ilDateTime($this->getTimeLimitFrom(), IL_CAL_UNIX),
                new ilDateTime($this->getTimeLimitUntil(), IL_CAL_UNIX)
            );
            ilDatePresentation::resetToDefaults();
            
            $start = new ilDateTime($this->getTimeLimitFrom(), IL_CAL_UNIX);
            $end = new ilDateTime($this->getTimeLimitUntil(), IL_CAL_UNIX);
            
            $body .= $language->txt('time_limit') . ': ' . $start->get(IL_CAL_DATETIME);
            $body .= $language->txt('time_limit') . ': ' . $end->get(IL_CAL_DATETIME);
        }

        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        /**
         * @var ilUserDefinedFields $user_defined_fields
         */
        $user_defined_fields = ilUserDefinedFields::_getInstance();
        $user_defined_data = $this->getUserDefinedData();

        foreach ($user_defined_fields->getDefinitions() as $field_id => $definition) {
            $data = $user_defined_data["f_" . $field_id];
            if (strlen($data)) {
                if ($definition['field_type'] == UDF_TYPE_WYSIWYG) {
                    $data = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $data);
                    $data = strip_tags($data);
                }

                $body .= $definition['field_name'] . ': ' . $data . "\n";
            }
        }

        return $body;
    }

    /**
    * Lookup news feed hash for user. If hash does not exist, create one.
    */
    public static function _lookupFeedHash($a_user_id, $a_create = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_user_id > 0) {
            $set = $ilDB->queryF(
                "SELECT feed_hash from usr_data WHERE usr_id = %s",
                array("integer"),
                array($a_user_id)
            );
            if ($rec = $ilDB->fetchAssoc($set)) {
                if (strlen($rec["feed_hash"]) == 32) {
                    return $rec["feed_hash"];
                } elseif ($a_create) {
                    $hash = md5(rand(1, 9999999) + str_replace(" ", "", (string) microtime()));
                    $ilDB->manipulateF(
                        "UPDATE usr_data SET feed_hash = %s" .
                        " WHERE usr_id = %s",
                        array("text", "integer"),
                        array($hash, $a_user_id)
                    );
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
    public static function _getFeedPass($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_user_id > 0) {
            return ilObjUser::_lookupPref($a_user_id, "priv_feed_pass");
        }
        return false;
    }

    /**
    * Set news feed password for user
    * @param	integer	user_id
    * @param 	string	new password
    */
    public static function _setFeedPass($a_user_id, $a_password)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        self::_writePref(
            $a_user_id,
            "priv_feed_pass",
            ($a_password == "") ? "" : md5($a_password)
        );
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
    public static function _loginExists($a_login, $a_user_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT DISTINCT login, usr_id FROM usr_data " .
             "WHERE login = %s";
        $types[] = "text";
        $values[] = $a_login;
             
        if ($a_user_id != 0) {
            $q .= " AND usr_id != %s ";
            $types[] = "integer";
            $values[] = $a_user_id;
        }
             
        $r = $ilDB->queryF($q, $types, $values);

        if ($row = $ilDB->fetchAssoc($r)) {
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
    public static function _externalAccountExists($a_external_account, $a_auth_mode)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT * FROM usr_data " .
            "WHERE ext_account = %s AND auth_mode = %s",
            array("text", "text"),
            array($a_external_account, $a_auth_mode)
        );
        return $ilDB->fetchAssoc($res) ? true :false;
    }

    /**
     * return array of complete users which belong to a specific role
     *
     * @param int role id
     * @param int $active 	if -1, all users will be delivered, 0 only non active, 1 only active users
     */

    public static function _getUsersForRole($role_id, $active = -1)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];
        $data = array();

        $ids = $rbacreview->assignedUsers($role_id);

        if (count($ids) == 0) {
            $ids = array(-1);
        }

        $query = "SELECT usr_data.*, usr_pref.value AS language
							FROM usr_data
							LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
							WHERE " . $ilDB->in("usr_data.usr_id", $ids, false, "integer");
        $values[] = "language";
        $types[] = "text";


        if (is_numeric($active) && $active > -1) {
            $query .= " AND usr_data.active = %s";
            $values[] = $active;
            $types[] = "integer";
        }
        
        $query .= " ORDER BY usr_data.lastname, usr_data.firstname ";
        
        $r = $ilDB->queryF($query, $types, $values);
        $data = array();
        while ($row = $ilDB->fetchAssoc($r)) {
            $data[] = $row;
        }
        return $data;
    }


    /**
    *	get users for a category or from system folder
    * @param	$ref_id		ref id of object
    * @param 	$active		can be -1 (ignore), 1 = active, 0 = not active user
    */
    public static function _getUsersForFolder($ref_id, $active)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $data = array();
        $query = "SELECT usr_data.*, usr_pref.value AS language FROM usr_data LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id and usr_pref.keyword = %s WHERE 1 = 1 ";
        $types[] = "text";
        $values[] = "language";

        if (is_numeric($active) && $active > -1) {
            $query .= " AND usr_data.active = %s";
            $values[] = $active;
            $types[] = "integer";
        }

        if ($ref_id != USER_FOLDER_ID) {
            $query .= " AND usr_data.time_limit_owner = %s";
            $values[] = $ref_id;
            $types[] = "integer";
        }

        $query .= " AND usr_data.usr_id != %s ";
        $values[] = ANONYMOUS_USER_ID;
        $types[] = "integer";

        $query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

        $result = $ilDB->queryF($query, $types, $values);
        $data = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            array_push($data, $row);
        }

        return $data;
    }


    /**
    * return user data for group members
    * @param int array of member ids
    * @param int active can be -1 (ignore), 1 = active, 0 = not active user
    */
    public static function _getUsersForGroup($a_mem_ids, $active = -1)
    {
        return ilObjUser::_getUsersForIds($a_mem_ids, $active);
    }


    /**
    * return user data for given user id
    * @param int array of member ids
    * @param int active can be -1 (ignore), 1 = active, 0 = not active user
    */
    public static function _getUsersForIds($a_mem_ids, $active = -1, $timelimitowner = -1)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $ilDB = $DIC['ilDB'];

        $query = "SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
		          WHERE " . $ilDB->in("usr_data.usr_id", $a_mem_ids, false, "integer") . "
					AND usr_data.usr_id != %s";
        $values[] = "language";
        $types[] = "text";
        $values[] = ANONYMOUS_USER_ID;
        $types[] = "integer";

        if (is_numeric($active) && $active > -1) {
            $query .= " AND active = %s";
            $values[] = $active;
            $types[] = "integer";
        }

        if ($timelimitowner != USER_FOLDER_ID && $timelimitowner != -1) {
            $query .= " AND usr_data.time_limit_owner = %s";
            $values[] = $timelimitowner;
            $types[] = "integer";
        }

        $query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

        $result = $ilDB->queryF($query, $types, $values);
        while ($row = $ilDB->fetchAssoc($result)) {
            $mem_arr[] = $row;
        }

        return $mem_arr ? $mem_arr : array();
    }



    /**
     * return user data for given user ids
     *
     * @param array of internal ids or numerics $a_internalids
     */
    public static function _getUserData($a_internalids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ids = array();
        if (is_array($a_internalids)) {
            foreach ($a_internalids as $internalid) {
                if (is_numeric($internalid)) {
                    $ids[] = $internalid;
                } else {
                    $parsedid = ilUtil::__extractId($internalid, IL_INST_ID);
                    if (is_numeric($parsedid) && $parsedid > 0) {
                        $ids[] = $parsedid;
                    }
                }
            }
        }
        if (count($ids) == 0) {
            $ids [] = -1;
        }

        $query = "SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref
		          ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
		          WHERE " . $ilDB->in("usr_data.usr_id", $ids, false, "integer");
        $values[] = "language";
        $types[] = "text";

        $query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

        $data = array();
        $result = $ilDB->queryF($query, $types, $values);
        while ($row = $ilDB->fetchAssoc($result)) {
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
    public static function _getPreferences($user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $prefs = array();

        $r = $ilDB->queryF(
            "SELECT * FROM usr_pref WHERE usr_id = %s",
            array("integer"),
            array($user_id)
        );

        while ($row = $ilDB->fetchAssoc($r)) {
            $prefs[$row["keyword"]] = $row["value"];
        }

        return $prefs;
    }

    /**
     * For a given set of user IDs return a subset that has
     * a given user preference set.
     *
     * @param array $a_user_ids array of user IDs
     * @param string $a_keyword preference keyword
     * @param string $a_val value
     * @return array array of user IDs
     */
    public static function getUserSubsetByPreferenceValue($a_user_ids, $a_keyword, $a_val)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $users = array();
        $set = $ilDB->query(
            "SELECT usr_id FROM usr_pref " .
            " WHERE keyword = " . $ilDB->quote($a_keyword, "text") .
            " AND " . $ilDB->in("usr_id", $a_user_ids, false, "integer") .
            " AND value = " . $ilDB->quote($a_val, "text")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $users[] = $rec["usr_id"];
        }
        return $users;
    }


    public static function _resetLoginAttempts($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE usr_data SET login_attempts = 0 WHERE usr_id = %s";
        $affected = $ilDB->manipulateF($query, array('integer'), array($a_usr_id));

        if ($affected) {
            return true;
        } else {
            return false;
        }
    }

    public static function _getLoginAttempts($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT login_attempts FROM usr_data WHERE usr_id = %s";
        $result = $ilDB->queryF($query, array('integer'), array($a_usr_id));
        $record = $ilDB->fetchAssoc($result);
        $login_attempts = $record['login_attempts'];

        return $login_attempts;
    }

    public static function _incrementLoginAttempts($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE usr_data SET login_attempts = (login_attempts + 1) WHERE usr_id = %s";
        $affected = $ilDB->manipulateF($query, array('integer'), array($a_usr_id));

        if ($affected) {
            return true;
        } else {
            return false;
        }
    }

    public static function _setUserInactive($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE usr_data SET active = 0, inactivation_date = %s WHERE usr_id = %s";
        $affected = $ilDB->manipulateF($query, array('timestamp', 'integer'), array(ilUtil::now(), $a_usr_id));
        
        if ($affected) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * returns true if public is profile, false otherwise
     *
     * @return boolean
     */
    public function hasPublicProfile()
    {
        return in_array($this->getPref("public_profile"), array("y", "g"));
    }
    
    /**
     * returns firstname lastname and login if profile is public, login otherwise
     *
     * @return string
     */
    public function getPublicName()
    {
        if ($this->hasPublicProfile()) {
            return $this->getFirstname() . " " . $this->getLastname() . " (" . $this->getLogin() . ")";
        } else {
            return $this->getLogin();
        }
    }
    
    public static function _writeHistory($a_usr_id, $a_login)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $timestamp = time();
            
        $res = $ilDB->queryF(
            'SELECT * FROM loginname_history WHERE usr_id = %s AND login = %s AND history_date = %s',
            array('integer', 'text', 'integer'),
            array($a_usr_id, $a_login, $timestamp)
        );
        
        if ($ilDB->numRows($res) == 0) {
            $ilDB->manipulateF(
                '
				INSERT INTO loginname_history 
						(usr_id, login, history_date)
				VALUES 	(%s, %s, %s)',
                array('integer', 'text', 'integer'),
                array($a_usr_id, $a_login, $timestamp)
            );
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
    public static function _getUsersOnline($a_user_id = 0, $a_no_anonymous = false)
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilDB = $DIC->database();
        $rbacreview = $DIC->rbac()->review();

        $log = ilLoggerFactory::getLogger("user");

        $pd_set = new ilSetting('pd');
        $atime = $pd_set->get('user_activity_time') * 60;
        $ctime = time();
        
        $where = array();

        if ($a_user_id == 0) {
            $where[] = 'user_id > 0';
        } elseif (is_array($a_user_id)) {
            $where[] = $ilDB->in("user_id", $a_user_id, false, "integer");
        } else {
            $where[] = 'user_id = ' . $ilDB->quote($a_user_id, 'integer');
        }

        if ($a_no_anonymous) {
            $where[] = 'user_id != ' . $ilDB->quote(ANONYMOUS_USER_ID, 'integer');
        }

        include_once 'Services/User/classes/class.ilUserAccountSettings.php';
        if (ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
            include_once 'Services/User/classes/class.ilUserFilter.php';
            $where[] = $ilDB->in('time_limit_owner', ilUserFilter::getInstance()->getFolderIds(), false, 'integer');
        }

        $where[] = 'expires > ' . $ilDB->quote($ctime, 'integer');
        $where[] = '(p.value IS NULL OR NOT p.value = ' . $ilDB->quote('y', 'text') . ')';

        $where = 'WHERE ' . implode(' AND ', $where);

        $r = $ilDB->queryF(
            $q = "
			SELECT COUNT(user_id) num, user_id, firstname, lastname, title, login, last_login, MAX(ctime) ctime, context, agree_date
			FROM usr_session
			LEFT JOIN usr_data u
				ON user_id = u.usr_id
			LEFT JOIN usr_pref p
				ON (p.usr_id = u.usr_id AND p.keyword = %s)
			{$where}
			GROUP BY user_id, firstname, lastname, title, login, last_login, context, agree_date
			ORDER BY lastname, firstname
			",
            array('text'),
            array('hide_own_online_status')
        );

        $log->debug("Query: " . $q);

        $users = array();
        while ($user = $ilDB->fetchAssoc($r)) {
            if ($atime <= 0 || $user['ctime'] + $atime > $ctime) {
                $users[$user['user_id']] = $user;
            }
        }

        $log->debug("Found users: " . count($users));

        require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';
        if (ilTermsOfServiceHelper::isEnabled()) {
            $users = array_filter($users, function ($user) {
                if ($user['agree_date'] || $user['user_id'] == SYSTEM_USER_ID || 'root' === $user['login']) {
                    return true;
                }

                return false;
            });

            $log->debug("TOS filtered to users: " . count($users));
        }

        return $users;
    }

    /**
    * Generates a unique hashcode for activating a user profile after registration
    *
    * @param integer $a_usr_id user id of the current user
    * @return string generated hashcode
    */
    public static function _generateRegistrationHash($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        do {
            $continue = false;
            
            $hashcode = substr(md5(uniqid(rand(), true)), 0, 16);
            
            $res = $ilDB->queryf(
                '
				SELECT COUNT(usr_id) cnt FROM usr_data 
				WHERE reg_hash = %s',
                array('text'),
                array($hashcode)
            );
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if ($row->cnt > 0) {
                    $continue = true;
                }
                break;
            }
            
            if ($continue) {
                continue;
            }
            
            $ilDB->manipulateF(
                '
				UPDATE usr_data	
				SET reg_hash = %s	
				WHERE usr_id = %s',
                array('text', 'integer'),
                array($hashcode, (int) $a_usr_id)
            );
            
            break;
        } while (true);
        
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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = $ilDB->queryf(
            '
			SELECT usr_id, create_date FROM usr_data 
			WHERE reg_hash = %s',
            array('text'),
            array($a_hash)
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            require_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
            $oRegSettigs = new ilRegistrationSettings();
            
            if ((int) $oRegSettigs->getRegistrationHashLifetime() != 0 &&
               time() - (int) $oRegSettigs->getRegistrationHashLifetime() > strtotime($row['create_date'])) {
                require_once 'Services/Registration/exceptions/class.ilRegConfirmationLinkExpiredException.php';
                throw new ilRegConfirmationLinkExpiredException('reg_confirmation_hash_life_time_expired', $row['usr_id']);
            }
            
            $ilDB->manipulateF(
                '
				UPDATE usr_data	
				SET reg_hash = %s
				WHERE usr_id = %s',
                array('text', 'integer'),
                array('', (int) $row['usr_id'])
            );
            
            return (int) $row['usr_id'];
        }
        
        require_once 'Services/Registration/exceptions/class.ilRegistrationHashNotFoundException.php';
        throw new ilRegistrationHashNotFoundException('reg_confirmation_hash_not_found');
    }

    public function setBirthday($a_birthday)
    {
        if (strlen($a_birthday)) {
            $date = new ilDate($a_birthday, IL_CAL_DATE);
            $this->birthday = $date->get(IL_CAL_DATE);
        } else {
            $this->birthday = null;
        }
    }
    
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Get ids of all users that have been inactive for at least the given period
     * @param int $periodInDays
     * @param bool $includeNeverLoggedIn
     * @return array
     * @throws \ilException
     */
    public static function getUserIdsByInactivityPeriod(int $periodInDays) : array
    {
        global $DIC;

        if (!is_numeric($periodInDays) && $periodInDays < 1) {
            throw new \ilException('Invalid period given');
        }

        $date = date('Y-m-d H:i:s', (time() - ((int) $periodInDays * 24 * 60 * 60)));

        $query = "SELECT usr_id FROM usr_data WHERE last_login IS NOT NULL AND last_login < %s";

        $ids = [];

        $types = ['timestamp'];
        $values = [$date];

        $res = $DIC->database()->queryF($query, $types, $values);
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $ids[] = $row['usr_id'];
        }

        return $ids;
    }

    /**
     * Get ids of all users that have never logged in
     * @param int $thresholdInDays
     * @return array
     */
    public static function getUserIdsNeverLoggedIn(int $thresholdInDays) : array
    {
        global $DIC;

        $date = date('Y-m-d H:i:s', (time() - ((int) $thresholdInDays * 24 * 60 * 60)));

        $query = "SELECT usr_id FROM usr_data WHERE last_login IS NULL AND create_date < %s";

        $ids = [];

        $types = ['timestamp'];
        $values = [$date];

        $res = $DIC->database()->queryF($query, $types, $values);
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $ids[] = $row['usr_id'];
        }

        return $ids;
    }
    
    /**
     * get ids of all users that have been inactivated since at least the given period
     *
     * @static
     * @param	integer $period (in days)
     * @return	array of user ids
     * @access	public
     */
    public static function _getUserIdsByInactivationPeriod($period)
    {
        /////////////////////////////
        $field = 'inactivation_date';
        /////////////////////////////
        
        if (!(int) $period) {
            throw new ilException('no valid period given');
        }

        global $DIC;

        $ilDB = $DIC['ilDB'];

        $date = date('Y-m-d H:i:s', (time() - ((int) $period * 24 * 60 * 60)));

        $query = "SELECT usr_id FROM usr_data WHERE $field < %s AND active = %s";

        $res = $ilDB->queryF($query, array('timestamp', 'integer'), array($date, 0));
        
        $ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->usr_id;
        }

        return $ids;
    }

    /**
    * STATIC METHOD
    * updates the last_login field of user with given id to given or current date
    * @static
    * @param	integer $a_usr_id
    * @param	string $last_login (optional)
    * @return	$last_login or false
    * @access	public
    */
    public static function _updateLastLogin($a_usr_id, $a_last_login = null)
    {
        if ($a_last_login !== null) {
            $last_login = $a_last_login;
        } else {
            $last_login = date('Y-m-d H:i:s');
        }

        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE usr_data SET last_login = %s WHERE usr_id = %s";
        $affected = $ilDB->manipulateF($query, array('timestamp', 'integer'), array($last_login, $a_usr_id));

        $query = "UPDATE usr_data SET first_login = %s WHERE usr_id = %s AND first_login IS NULL";
        $ilDB->manipulateF($query, array('timestamp', 'integer'), array($last_login, $a_usr_id));
        

        if ($affected) {
            return $last_login;
        } else {
            return false;
        }
    }
    
    public function resetOwner()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE object_data SET owner = 0 " .
            "WHERE owner = " . $ilDB->quote($this->getId(), 'integer');
        $ilDB->query($query);
        
        return true;
    }


    /**
     * Get first letters of all lastnames
     *
     * @param int[] $user_ids
     * @return mixed
     */
    public static function getFirstLettersOfLastnames(?array $user_ids = null)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT DISTINCT " . $ilDB->upper($ilDB->substr("lastname", 1, 1)) . " let" .
            " FROM usr_data" .
            " WHERE usr_id <> " . $ilDB->quote(ANONYMOUS_USER_ID, "integer") .
            ($user_ids !== null ? " AND " . $ilDB->in('usr_id', $user_ids, false, "integer") : "") .
            " ORDER BY let";
        $let_set = $ilDB->query($q);

        $lets = array();
        while ($let_rec = $ilDB->fetchAssoc($let_set)) {
            $let[$let_rec["let"]] = $let_rec["let"];
        }
        return $let;
    }
    
    // begin-patch deleteProgress
    public static function userExists($a_usr_ids = array())
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT count(*) num FROM object_data od ' .
                'JOIN usr_data ud ON obj_id = usr_id ' .
                'WHERE ' . $ilDB->in('obj_id', $a_usr_ids, false, 'integer') . ' ';
        $res = $ilDB->query($query);
        $num_rows = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)->num;
        return $num_rows == count((array) $a_usr_ids);
    }
    // end-patch deleteProgress

    /**
     * Is user captcha verified?
     */
    public function isCaptchaVerified()
    {
        return (boolean) $_SESSION["user_captcha_verified"];
    }
    
    /**
     * Set captcha verified
     *
     * @param
     */
    public function setCaptchaVerified($a_val)
    {
        $_SESSION["user_captcha_verified"] = $a_val;
    }
    
    /**
     * Export personal data
     *
     * @param
     * @return
     */
    public function exportPersonalData()
    {
        include_once("./Services/Export/classes/class.ilExport.php");
        $exp = new ilExport();
        $dir = ilExport::_getExportDirectory($this->getId(), "xml", "usr", "personal_data");
        ilUtil::delDir($dir, true);
        $title = $this->getLastname() . ", " . $this->getLastname() . " [" . $this->getLogin() . "]";
        $exp->exportEntity(
            "personal_data",
            $this->getId(),
            "",
            "Services/User",
            $title,
            $dir
        );
    }
    
    /**
     * Get personal data export file
     *
     * @param
     * @return
     */
    public function getPersonalDataExportFile()
    {
        include_once("./Services/Export/classes/class.ilExport.php");
        $dir = ilExport::_getExportDirectory($this->getId(), "xml", "usr", "personal_data");
        if (!is_dir($dir)) {
            return "";
        }
        foreach (ilUtil::getDir($dir) as $entry) {
            if (is_int(strpos($entry["entry"], ".zip"))) {
                return $entry["entry"];
            }
        }
        
        return "";
    }
    
    /**
     * Send personal data file
     *
     * @param
     * @return
     */
    public function sendPersonalDataFile()
    {
        include_once("./Services/Export/classes/class.ilExport.php");
        $file = ilExport::_getExportDirectory($this->getId(), "xml", "usr", "personal_data") .
            "/" . $this->getPersonalDataExportFile();
        if (is_file($file)) {
            ilUtil::deliverFile($file, $this->getPersonalDataExportFile());
        }
    }
    
    /**
     * Import personal data
     *
     * @param
     * @return
     */
    public function importPersonalData(
        $a_file,
        $a_profile_data,
        $a_settings,
        $a_notes,
        $a_calendar
    ) {
        include_once("./Services/Export/classes/class.ilImport.php");
        $imp = new ilImport();
        // bookmarks need to be skipped, importer does not exist anymore
        $imp->addSkipImporter("Services/Bookmarks");
        if (!$a_profile_data) {
            $imp->addSkipEntity("Services/User", "usr_profile");
        }
        if (!$a_settings) {
            $imp->addSkipEntity("Services/User", "usr_setting");
        }
        if (!$a_notes) {
            $imp->addSkipEntity("Services/Notes", "user_notes");
        }
        if (!$a_calendar) {
            $imp->addSkipEntity("Services/Calendar", "calendar");
        }
        $imp->importEntity(
            $a_file["tmp_name"],
            $a_file["name"],
            "personal_data",
            "Services/User"
        );
    }
    
    /**
     *
     * @global type $ilDB
     * @param type $usrIds
     */
    private static function initInactivationDate($usrIds)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $NOW = $ilDB->now();
        
        $usrId_IN_usrIds = $ilDB->in('usr_id', $usrIds, false, 'integer');
        
        $queryString = "
			UPDATE usr_data
			SET inactivation_date = $NOW
			WHERE inactivation_date IS NULL
			AND $usrId_IN_usrIds
		";
        
        $ilDB->manipulate($queryString);
    }
    
    /**
     *
     * @global type $ilDB
     * @param type $usrIds
     */
    private static function resetInactivationDate($usrIds)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $usrId_IN_usrIds = $ilDB->in('usr_id', $usrIds, false, 'integer');
        
        $queryString = "
			UPDATE usr_data
			SET inactivation_date = NULL
			WHERE $usrId_IN_usrIds
		";
        
        $ilDB->manipulate($queryString);
    }
    
    /**
     * setter for inactivation date
     *
     * @param string $inactivationDate
     */
    public function setInactivationDate($inactivation_date)
    {
        $this->inactivation_date = $inactivation_date;
    }
    
    /**
     * getter for inactivation date
     *
     * @return string $inactivation_date
     */
    public function getInactivationDate()
    {
        return $this->inactivation_date;
    }

    /**
     * @return bool
     */
    public function hasToAcceptTermsOfService()
    {
        require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';

        if (
            ilTermsOfServiceHelper::isEnabled() &&
            null == $this->agree_date &&
            'root' != $this->login &&
            !in_array($this->getId(), array(ANONYMOUS_USER_ID, SYSTEM_USER_ID))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get users that have or have not agreed to the user agreement.
     *
     * @param bool $a_agreed true, if users that have agreed should be returned
     * $@param array $a_users array of user ids (subset used as base) or null for all users
     * @return array array of user IDs
     */
    public static function getUsersAgreed($a_agreed = true, $a_users = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $date_is = ($a_agreed)
            ? "IS NOT NULL"
            : "IS NULL";

        $users = (is_array($a_users))
            ? " AND " . $ilDB->in("usr_id", $a_users, false, "integer")
            : "";

        $set = $ilDB->query("SELECT usr_id FROM usr_data " .
            " WHERE agree_date " . $date_is .
            $users);
        $ret = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ret[] = $rec["usr_id"];
        }
        return $ret;
    }


    /**
     * @param bool|null $status
     * @return void|bool
     */
    public function hasToAcceptTermsOfServiceInSession($status = null)
    {
        if (null === $status) {
            return ilSession::get('has_to_accept_agr_in_session');
        }
        
        require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';
        if (ilTermsOfServiceHelper::isEnabled()) {
            ilSession::set('has_to_accept_agr_in_session', (int) $status);
        }
    }

    /**
     * @return bool
     */
    public function isAnonymous()
    {
        return self::_isAnonymous($this->getId());
    }

    /**
     * @param int $usr_id
     * @return bool
     */
    public static function _isAnonymous($usr_id)
    {
        return $usr_id == ANONYMOUS_USER_ID;
    }
    
    public function activateDeletionFlag()
    {
        $this->writePref("delete_flag", true);
    }
    
    public function removeDeletionFlag()
    {
        $this->writePref("delete_flag", false);
    }
    
    public function hasDeletionFlag()
    {
        return (bool) $this->getPref("delete_flag");
    }

    /**
     * @param bool $status
     */
    public function setIsSelfRegistered($status)
    {
        $this->is_self_registered = (bool) $status;
    }
    
    public function isSelfRegistered()
    {
        return (bool) $this->is_self_registered;
    }
    
    
    //
    // MULTI-TEXT / INTERESTS
    //
        
    /**
     * Set general interests
     *
     * @param array $value
     */
    public function setGeneralInterests(array $value = null)
    {
        $this->interests_general = $value;
    }
    
    /**
     * Get general interests
     *
     * @return array $value
     */
    public function getGeneralInterests()
    {
        return $this->interests_general;
    }
    
    /**
     * Get general interests as plain text
     *
     * @return string
     */
    public function getGeneralInterestsAsText()
    {
        return $this->buildTextFromArray("interests_general");
    }
    
    /**
     * Set help offering
     *
     * @param array $value
     */
    public function setOfferingHelp(array $value = null)
    {
        $this->interests_help_offered = $value;
    }
    
    /**
     * Get help offering
     *
     * @return array $value
     */
    public function getOfferingHelp()
    {
        return $this->interests_help_offered;
    }
    
    /**
     * Get help offering as plain text
     *
     * @return string
     */
    public function getOfferingHelpAsText()
    {
        return $this->buildTextFromArray("interests_help_offered");
    }
    
    /**
     * Set help looking for
     *
     * @param array $value
     */
    public function setLookingForHelp(array $value = null)
    {
        $this->interests_help_looking = $value;
    }
    
    /**
     * Get help looking for
     *
     * @return array $value
     */
    public function getLookingForHelp()
    {
        return $this->interests_help_looking;
    }
    
    /**
     * Get help looking for as plain text
     *
     * @return string
     */
    public function getLookingForHelpAsText()
    {
        return $this->buildTextFromArray("interests_help_looking");
    }
    
    /**
     * Convert multi-text values to plain text
     *
     * @param string $a_attr
     * @return string
     */
    protected function buildTextFromArray($a_attr)
    {
        $current = $this->$a_attr;
        if (is_array($current) && sizeof($current)) {
            return implode(", ", $current);
        }
    }
    
    /**
     * Fetch multi-text values from DB
     */
    protected function readMultiTextFields()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getId()) {
            return;
        }

        $set = $ilDB->query("SELECT field_id,value" .
            " FROM usr_data_multi" .
            " WHERE usr_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY value");
        while ($row = $ilDB->fetchAssoc($set)) {
            $values[$row["field_id"]][] = $row["value"];
        }
        
        if (isset($values["interests_general"])) {
            $this->setGeneralInterests($values["interests_general"]);
        } else {
            $this->setGeneralInterests();
        }
        if (isset($values["interests_help_offered"])) {
            $this->setOfferingHelp($values["interests_help_offered"]);
        } else {
            $this->setOfferingHelp();
        }
        if (isset($values["interests_help_looking"])) {
            $this->setLookingForHelp($values["interests_help_looking"]);
        } else {
            $this->setLookingForHelp();
        }
    }
    
    /**
     * Write multi-text values to DB
     *
     * @param bool $a_create
     */
    public function updateMultiTextFields($a_create = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getId()) {
            return;
        }
        
        if (!$a_create) {
            $this->deleteMultiTextFields();
        }
        
        $map = array(
            "interests_general" => $this->getGeneralInterests(),
            "interests_help_offered" => $this->getOfferingHelp(),
            "interests_help_looking" => $this->getLookingForHelp()
        );
        
        foreach ($map as $id => $values) {
            if (is_array($values) && sizeof($values)) {
                foreach ($values as $value) {
                    $value = trim($value);
                    if ($value) {
                        $uniq_id = $ilDB->nextId('usr_data_multi');

                        $ilDB->manipulate("INSERT usr_data_multi" .
                            " (id,usr_id,field_id,value) VALUES" .
                            " (" . $ilDB->quote($uniq_id, "integer") .
                            "," . $ilDB->quote($this->getId(), "integer") .
                            "," . $ilDB->quote($id, "text") .
                            "," . $ilDB->quote($value, "text") .
                            ")");
                    }
                }
            }
        }
    }
    
    /**
     * Remove multi-text values from DB
     */
    protected function deleteMultiTextFields()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getId()) {
            return;
        }
        
        $ilDB->manipulate("DELETE FROM usr_data_multi" .
            " WHERE usr_id = " . $ilDB->quote($this->getId(), "integer"));
    }
    
    public static function findInterests($a_term, $a_user_id = null, $a_field_id = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = array();
        
        $sql = "SELECT DISTINCT(value)" .
            " FROM usr_data_multi" .
            " WHERE " . $ilDB->like("value", "text", "%" . $a_term . "%");
        if ($a_field_id) {
            $sql .= " AND field_id = " . $ilDB->quote($a_field_id, "text");
        }
        if ($a_user_id) {
            $sql .= " AND usr_id <> " . $ilDB->quote($a_user_id, "integer");
        }
        $sql .= " ORDER BY value";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["value"];
        }
        
        return $res;
    }

    /**
     * Get profile status
     *
     * @param array[int] $a_user_ids user ids
     * @return array[] 	array["global"] => all user ids having their profile global (www) activated,
     * 					array["local"] => all user ids having their profile only locally (logged in users) activated,
     * 					array["public"] => all user ids having their profile either locally or globally activated,
     * 					array["not_public"] => all user ids having their profile deactivated
     */
    public static function getProfileStatusOfUsers($a_user_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM usr_pref " .
                " WHERE keyword = " . $ilDB->quote("public_profile", "text") .
                " AND " . $ilDB->in("usr_id", $a_user_ids, false, "integer")
            );
        $r = array(
            "global" => array(),
            "local" => array(),
            "public" => array(),
            "not_public" => array()
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["value"] == "g") {
                $r["global"][] = $rec["usr_id"];
                $r["public"][] = $rec["usr_id"];
            }
            if ($rec["value"] == "y") {
                $r["local"][] = $rec["usr_id"];
                $r["public"][] = $rec["usr_id"];
            }
        }
        foreach ($a_user_ids as $id) {
            if (!in_array($id, $r["public"])) {
                $r["not_public"][] = $id;
            }
        }

        return $r;
    }
} // END class ilObjUser
