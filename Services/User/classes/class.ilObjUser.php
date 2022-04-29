<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\UI\Component\Symbol\Avatar\Avatar;

/**
 * User class
 * @author	Sascha Hofmann <saschahofmann@gmx.de>
 * @author	Stefan Meyer <meyer@leifos.com>
 * @author	Peter Gabriel <pgabriel@databay.de>
 */
class ilObjUser extends ilObject
{
    public const PASSWD_PLAIN = "plain";
    public const PASSWD_CRYPTED = "crypted";
    protected string $ext_account = "";
    protected string $time_limit_message = "";
    protected bool $time_limit_unlimited = false;
    protected ?int $time_limit_until = null;
    protected ?int $time_limit_from = null;
    protected ?int $time_limit_owner = null;
    protected string $last_login = "";

    public string $login = '';
    protected string $passwd = ""; // password encoded in the format specified by $passwd_type
    protected string $passwd_type = "";
    // specifies the password format.
    // value: ilObjUser::PASSWD_PLAIN or ilObjUser::PASSWD_CRYPTED.
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
    protected ?string $password_encoding_type = null; // The encoding algorithm of the user's password stored in the database
    // A salt used to encrypt the user's password
    protected ?string $password_salt = null;
    public string $gender = "";	// 'm' or 'f'
    public string $utitle = "";	// user title (keep in mind, that we derive $title from object also!)
    public string $firstname = "";
    public string $lastname = "";
    protected ?string $birthday = null;
    public string $fullname = "";	// title + firstname + lastname in one string
    public string $institution = "";
    public string $department = "";
    public string $street = "";
    public string $city = "";
    public string $zipcode = "";
    public string $country = "";
    public string $sel_country = "";
    public string $phone_office = "";
    public string $phone_home = "";
    public string $phone_mobile = "";
    public string $fax = "";
    public string $email = "";
    protected ?string $second_email = null;
    public string $hobby = "";
    public string $matriculation = "";
    public string $referral_comment = "";
    public ?string $approve_date = null;
    public ?string $agree_date = null;
    public int $active = 0;
    public string $client_ip = ""; // client ip to check before login
    public string $auth_mode; // authentication mode
    public ?string $latitude = null;
    public ?string $longitude = null;
    public ?string $loc_zoom = null;
    public int $last_password_change_ts = 0;
    protected bool $passwd_policy_reset = false;
    public int $login_attempts = 0;
    public array $user_defined_data = array(); // Missing array type.
    /** @var array<string, string> */
    protected array $oldPrefs = [];
    /** @var array<string, string> */
    public array $prefs = [];
    public string $skin = "";
    protected static array $personal_image_cache = array();
    protected ?string $inactivation_date = null;
    private bool $is_self_registered = false; // flag for self registered users
    protected string $org_units = "";    // ids of assigned org-units, comma seperated
    /** @var string[] */
    protected array $interests_general = [];
    /** @var string[] */
    protected array $interests_help_offered = [];
    /** @var string[] */
    protected array $interests_help_looking = [];
    protected string $last_profile_prompt = "";	// timestamp
    protected string $first_login = "";	// timestamp
    protected bool $profile_incomplete = false;

    public function __construct(
        int $a_user_id = 0,
        bool $a_call_by_reference = false
    ) {
        global $DIC;

        $ilias = $DIC['ilias'];
        $this->ilias = $ilias;
        $this->db = $DIC->database();
        $this->type = "usr";
        parent::__construct($a_user_id, $a_call_by_reference);
        $this->auth_mode = "default";
        $this->passwd_type = self::PASSWD_PLAIN;
        if ($a_user_id > 0) {
            $this->setId($a_user_id);
            $this->read();
        } else {
            $this->prefs = array();
            $this->prefs["language"] = $this->ilias->ini->readVariable("language", "default");
            $this->skin = $this->ilias->ini->readVariable("layout", "skin");
            $this->prefs["skin"] = $this->skin;
            $this->prefs["style"] = $this->ilias->ini->readVariable("layout", "style");
        }

        $this->app_event_handler = $DIC['ilAppEventHandler'];
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     * @throws ilSystemStyleException
     */
    public function read() : void
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $this->db;

        $r = $ilDB->queryF("SELECT * FROM usr_data " .
             "WHERE usr_id= %s", array("integer"), array($this->id));

        if ($data = $ilDB->fetchAssoc($r)) {
            // convert password storage layout used by table usr_data into
            // storage layout used by class ilObjUser
            $data["passwd_type"] = self::PASSWD_CRYPTED;

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

            if (!isset($this->prefs['language']) || $this->prefs['language'] === '') {
                $this->prefs['language'] = $this->oldPrefs['language'] ?? '';
            }

            if (
                !isset($this->prefs['skin']) || $this->prefs['skin'] === '' ||
                !ilStyleDefinition::skinExists($this->prefs['skin'])
            ) {
                $this->prefs['skin'] = $this->oldPrefs['skin'] ?? '';
            }

            $this->skin = $this->prefs["skin"];

            if (
                !isset($this->prefs['style']) ||
                $this->prefs['style'] === '' ||
                !ilStyleDefinition::styleExists($this->prefs['style']) ||
                (
                    !ilStyleDefinition::skinExists($this->skin) &&
                    ilStyleDefinition::styleExistsForSkinId($this->skin, $this->prefs['style'])
                )
            ) {
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

    public function getPasswordEncodingType() : ?string
    {
        return $this->password_encoding_type;
    }

    public function setPasswordEncodingType(?string $password_encryption_type) : void
    {
        $this->password_encoding_type = $password_encryption_type;
    }

    public function getPasswordSalt() : ?string
    {
        return $this->password_salt;
    }

    public function setPasswordSalt(?string $password_salt) : void
    {
        $this->password_salt = $password_salt;
    }

    /**
     * loads a record "user" from array
     * @param array $a_data<string,mixed>
     */
    public function assignData(array $a_data) : void
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

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

        $this->setGender((string) $a_data["gender"]);
        $this->setUTitle((string) $a_data["title"]);
        $this->setFirstname((string) $a_data["firstname"]);
        $this->setLastname((string) $a_data["lastname"]);
        $this->setFullname();
        if (!is_array($a_data['birthday'])) {
            $this->setBirthday($a_data['birthday']);
        } else {
            $this->setBirthday(null);
        }
        
        // address data
        $this->setInstitution((string) $a_data["institution"]);
        $this->setDepartment((string) $a_data["department"]);
        $this->setStreet((string) $a_data["street"]);
        $this->setCity((string) $a_data["city"]);
        $this->setZipcode((string) $a_data["zipcode"]);
        $this->setCountry((string) $a_data["country"]);
        $this->setSelectedCountry((string) $a_data["sel_country"]);
        $this->setPhoneOffice((string) $a_data["phone_office"]);
        $this->setPhoneHome((string) $a_data["phone_home"]);
        $this->setPhoneMobile((string) $a_data["phone_mobile"]);
        $this->setFax((string) $a_data["fax"]);
        $this->setMatriculation((string) $a_data["matriculation"]);
        $this->setEmail((string) $a_data["email"]);
        $this->setSecondEmail((string) $a_data["second_email"]);
        $this->setHobby((string) $a_data["hobby"]);
        $this->setClientIP((string) $a_data["client_ip"]);
        $this->setPasswordEncodingType($a_data['passwd_enc_type']);
        $this->setPasswordSalt($a_data['passwd_salt']);

        // other data
        $this->setLatitude($a_data["latitude"]);
        $this->setLongitude($a_data["longitude"]);
        $this->setLocationZoom($a_data["loc_zoom"]);

        // system data
        $this->setLastLogin((string) $a_data["last_login"]);
        $this->setFirstLogin((string) $a_data["first_login"]);
        $this->setLastProfilePrompt((string) $a_data["last_profile_prompt"]);
        $this->setLastUpdate((string) $a_data["last_update"]);
        $this->create_date = $a_data["create_date"] ?? "";
        $this->setComment((string) $a_data["referral_comment"]);
        $this->approve_date = $a_data["approve_date"];
        $this->active = $a_data["active"];
        $this->agree_date = $a_data["agree_date"];
        
        $this->setInactivationDate((string) $a_data["inactivation_date"]);

        // time limitation
        $this->setTimeLimitOwner($a_data["time_limit_owner"]);
        $this->setTimeLimitUnlimited($a_data["time_limit_unlimited"]);
        $this->setTimeLimitFrom($a_data["time_limit_from"]);
        $this->setTimeLimitUntil($a_data["time_limit_until"]);
        $this->setTimeLimitMessage($a_data['time_limit_message']);

        // user profile incomplete?
        $this->setProfileIncomplete((bool) $a_data["profile_incomplete"]);

        //authentication
        $this->setAuthMode($a_data['auth_mode']);
        $this->setExternalAccount((string) $a_data['ext_account']);
        
        $this->setIsSelfRegistered((bool) $a_data['is_self_registered']);
    }

    /**
     * @todo drop fields last_update & create_date. redundant data in object_data!
     * @throws ilPasswordException
     * @throws ilUserException
     */
    public function saveAsNew() : void
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $ilErr = $DIC['ilErr'];
        $ilDB = $this->db;
        $pw_value = "";

        switch ($this->passwd_type) {
            case self::PASSWD_PLAIN:
                if (strlen($this->passwd)) {
                    ilUserPasswordManager::getInstance()->encodePassword($this, $this->passwd);
                    $pw_value = $this->getPasswd();
                } else {
                    $pw_value = $this->passwd;
                }
                break;

            case self::PASSWD_CRYPTED:
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
            "active" => array("integer", $this->active),
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
            "last_password_change" => array("integer", $this->last_password_change_ts),
            "passwd_policy_reset" => array("integer", (int) $this->passwd_policy_reset),
            'inactivation_date' => array('timestamp', $this->inactivation_date),
            'is_self_registered' => array('integer', (int) $this->is_self_registered),
            );
        $ilDB->insert("usr_data", $insert_array);

        $this->updateMultiTextFields(true);
        $this->updateUserDefinedFields();

        // CREATE ENTRIES FOR MAIL BOX
        $mbox = new ilMailbox($this->id);
        $mbox->createDefaultFolder();

        $mail_options = new ilMailOptions($this->id);
        $mail_options->createMailOptionsEntry();

        $ilAppEventHandler->raise(
            "Services/User",
            "afterCreate",
            array("user_obj" => $this)
        );
    }

    public function update() : bool
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $this->db;
        $ilAppEventHandler = $this->app_event_handler;

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
            case self::PASSWD_PLAIN:
                if (strlen($this->passwd)) {
                    ilUserPasswordManager::getInstance()->encodePassword($this, $this->passwd);
                    $update_array['passwd'] = array('text', $this->getPasswd());
                } else {
                    $update_array["passwd"] = array("text", $this->passwd);
                }
                break;

            case self::PASSWD_CRYPTED:
                $update_array["passwd"] = array("text", $this->passwd);
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
        $this->updateOwner();

        $this->read();
        
        $ilAppEventHandler->raise(
            "Services/User",
            "afterUpdate",
            array("user_obj" => $this)
        );

        return true;
    }

    /**
     * write accept date of user agreement
     */
    public function writeAccepted() : void
    {
        $ilDB = $this->db;
        $ilDB->manipulateF("UPDATE usr_data SET agree_date = " . $ilDB->now() .
             " WHERE usr_id = %s", array("integer"), array($this->getId()));
    }

    private static function _lookup(
        int $a_user_id,
        string $a_field
    ) : ?string {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            "SELECT " . $a_field . " FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );

        while ($set = $ilDB->fetchAssoc($res)) {
            return $set[$a_field];
        }
        return null;
    }
    
    public static function _lookupFullname(int $a_user_id) : string
    {
        global $DIC;

        $fullname = "";
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

    public static function _lookupEmail(int $a_user_id) : string
    {
        return self::_lookup($a_user_id, "email");
    }
    
    public static function _lookupGender(int $a_user_id) : string
    {
        return (string) self::_lookup($a_user_id, "gender");
    }

    public static function _lookupClientIP(int $a_user_id) : string
    {
        return self::_lookup($a_user_id, "client_ip");
    }

    /**
     * lookup user name
     * @return array array('user_id' => ...,'firstname' => ...,'lastname' => ...,'login' => ...,'title' => ...)
     */
    public static function _lookupName(int $a_user_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT firstname, lastname, title, login FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );
        if ($user_rec = $ilDB->fetchAssoc($res)) {
            return array("user_id" => $a_user_id,
                         "firstname" => $user_rec["firstname"],
                         "lastname" => $user_rec["lastname"],
                         "title" => $user_rec["title"],
                         "login" => $user_rec["login"]
            );
        }
        return array("user_id" => 0,
                     "firstname" => "",
                     "lastname" => "",
                     "title" => "",
                     "login" => ""
        );
    }

    /**
     * lookup fields (deprecated; use more specific methods instead)
     * @deprecated
     */
    public static function _lookupFields(int $a_user_id) : array // Missing array type.
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

    public static function _lookupLogin(int $a_user_id) : string
    {
        return (string) self::_lookup($a_user_id, "login");
    }

    public static function _lookupExternalAccount(int $a_user_id) : string
    {
        return self::_lookup($a_user_id, "ext_account");
    }

    /**
     * @param string|string[] $a_user_str
     * @return int|null|int[]
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
            if (is_array($user_rec)) {
                return (int) $user_rec["usr_id"];
            }

            return null;
        }

        $set = $ilDB->query(
            "SELECT usr_id FROM usr_data " .
            " WHERE " . $ilDB->in("login", $a_user_str, false, "text")
        );

        $ids = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = (int) $rec['usr_id'];
        }

        return $ids;
    }

    public static function _lookupLastLogin(int $a_user_id) : string
    {
        return self::_lookup($a_user_id, "last_login");
    }

    public static function _lookupFirstLogin(int $a_user_id) : string
    {
        return self::_lookup($a_user_id, "first_login");
    }


    /**
     * updates the login data of a "user"
     * @todo set date with now() should be enough
     */
    public function refreshLogin() : void
    {
        $ilDB = $this->db;

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
            $this->app_event_handler->raise(
                "Services/User",
                "firstLogin",
                array("user_obj" => $this)
            );
        }
    }


    /**
     * Resets the user password
     * @param    string $raw        Password as plaintext
     * @param    string $raw_retype Retyped password as plaintext
     * @return    bool    true on success otherwise false
     * @throws ilPasswordException
     * @throws ilUserException
     */
    public function resetPassword(
        string $raw,
        string $raw_retype
    ) : bool {
        $ilDB = $this->db;

        if (func_num_args() != 2) {
            return false;
        }

        if (!isset($raw) || !isset($raw_retype)) {
            return false;
        }

        if ($raw != $raw_retype) {
            return false;
        }

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
     * Checks whether the passed loginname already exists in history
     */
    public static function _doesLoginnameExistInHistory(string $a_login) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
            
        $res = $ilDB->queryF(
            '
			SELECT * FROM loginname_history
			WHERE login = %s',
            array('text'),
            array($a_login)
        );

        return (bool) $ilDB->fetchAssoc($res);
    }
    
    /**
     * Returns the last used loginname and the changedate of the passed user_id.
     * Throws an ilUserException in case no entry could be found.
     * @return	array	Associative array, first index is the loginname, second index a unix_timestamp
     * @throws	ilUserException
     */
    public static function _getLastHistoryDataByUserId(int $a_usr_id) : array
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
     * @return    bool    true on success; otherwise false
     * @throws ilDateTimeException
     * @throws ilUserException
     */
    public function updateLogin(string $a_login) : bool
    {
        global $DIC;

        $ilDB = $this->db;
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
            $last_history_entry = self::_getLastHistoryDataByUserId($this->getId());
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
                self::_writeHistory($this->getId(), $former_login);
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

    public function writePref(
        string $a_keyword,
        string $a_value
    ) : void {
        self::_writePref($this->id, $a_keyword, $a_value);
        $this->setPref($a_keyword, $a_value);
    }

    public function deletePref(string $a_keyword) : void
    {
        self::_deletePref($this->getId(), $a_keyword);
    }

    public static function _deletePref(int $a_user_id, string $a_keyword) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            'DELETE FROM usr_pref WHERE usr_id = %s AND keyword = %s',
            array('integer', 'text'),
            array($a_user_id, $a_keyword)
        );
    }

    /**
     * Deletes a userpref value of the user from the database
     */
    public static function _deleteAllPref(int $a_user_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            "DELETE FROM usr_pref WHERE usr_id = %s",
            array("integer"),
            array($a_user_id)
        );
    }

    public static function _writePref(
        int $a_usr_id,
        string $a_keyword,
        string $a_value
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
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
    }

    public function writePrefs() : void
    {
        self::_deleteAllPref($this->id);
        foreach ($this->prefs as $keyword => $value) {
            self::_writePref($this->id, $keyword, (string) $value);
        }
    }

    public function getTimeZone() : string
    {
        if ($tz = $this->getPref('user_tz')) {
            return $tz;
        } else {
            $settings = ilCalendarSettings::_getInstance();
            return $settings->getDefaultTimeZone();
        }
    }

    public function getTimeFormat() : string
    {
        if ($format = $this->getPref('time_format')) {
            return $format;
        } else {
            $settings = ilCalendarSettings::_getInstance();
            return $settings->getDefaultTimeFormat();
        }
    }

    public function getDateFormat() : string
    {
        if ($format = $this->getPref('date_format')) {
            return $format;
        } else {
            $settings = ilCalendarSettings::_getInstance();
            return $settings->getDefaultDateFormat();
        }
    }

    public function setPref(string $a_keyword, ?string $a_value) : void
    {
        if ($a_keyword != "") {
            $this->prefs[$a_keyword] = $a_value;
        }
    }

    public function getPref(string $a_keyword) : ?string
    {
        return $this->prefs[$a_keyword] ?? null;
    }

    public function existsPref(string $a_keyword) : bool
    {
        return (array_key_exists($a_keyword, $this->prefs));
    }

    public static function _lookupPref(
        int $a_usr_id,
        string $a_keyword
    ) : ?string {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM usr_pref WHERE usr_id = " . $ilDB->quote($a_usr_id, "integer") . " " .
            "AND keyword = " . $ilDB->quote($a_keyword, "text");
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->value;
        }
        return null;
    }

    public function readPrefs() : void
    {
        if (is_array($this->prefs)) {
            $this->oldPrefs = $this->prefs;
        }
        $this->prefs = self::_getPreferences($this->id);
    }

    public function delete() : bool
    {
        global $DIC;

        $rbacadmin = $DIC->rbac()->admin();
        $ilDB = $this->db;

        // deassign from ldap groups
        $mapping = ilLDAPRoleGroupMapping::_getInstance();
        $mapping->deleteUser($this->getId());

        // remove mailbox / update sent mails
        $mailbox = new ilMailbox($this->getId());
        $mailbox->delete();
        $mailbox->updateMailsOfDeletedUser($this->getLogin());

        // delete block settings
        ilBlockSetting::_deleteSettingsOfUser($this->getId());

        // delete user_account
        $ilDB->manipulateF(
            "DELETE FROM usr_data WHERE usr_id = %s",
            array("integer"),
            array($this->getId())
        );
        
        $this->deleteMultiTextFields();

        // delete user_prefs
        self::_deleteAllPref($this->getId());
            
        $this->removeUserPicture(false); // #8597

        // delete user_session
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
        ilObjForum::_deleteUser($this->getId());

        // Delete crs entries
        ilObjCourse::_deleteUser($this->getId());

        // Delete user tracking
        ilObjUserTracking::_deleteUser($this->getId());

        ilEventParticipants::_deleteByUser($this->getId());
        
        // Delete Tracking data SCORM 2004 RTE
        ilSCORM13Package::_removeTrackingDataForUser($this->getId());
        
        // Delete Tracking data SCORM 1.2 RTE
        ilObjSCORMLearningModule::_removeTrackingDataForUser($this->getId());

        // remove all notifications
        ilNotification::removeForUser($this->getId());
        
        // remove portfolios
        ilObjPortfolio::deleteUserPortfolios($this->getId());
        
        // remove workspace
        $tree = new ilWorkspaceTree($this->getId());
        $tree->cascadingDelete();

        // remove reminder entries
        ilCronDeleteInactiveUserReminderMail::removeSingleUserFromTable($this->getId());
        
        // badges
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
     */
    public function setFullname() : void
    {
        $this->fullname = ($this->utitle != "")
            ? $this->utitle . " "
            : "";
        $this->fullname .= $this->firstname . " ";
        $this->fullname .= $this->lastname;
    }

    /**
     * @param int $a_max_strlen max. string length to return (optional)
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
    public function getFullname(int $a_max_strlen = 0) : string
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

    public function setLogin(string $a_str) : void
    {
        $this->login = $a_str;
    }

    public function getLogin() : string
    {
        return $this->login;
    }

    public function setPasswd(
        string $a_str,
        string $a_type = ilObjUser::PASSWD_PLAIN
    ) : void {
        $this->passwd = $a_str;
        $this->passwd_type = $a_type;
    }

    /**
     * @return string The password is encoded depending on the current password type.
     */
    public function getPasswd() : string
    {
        return $this->passwd;
    }

    /**
     * @return string password type (ilObjUser::PASSWD_PLAIN, ilObjUser::PASSWD_CRYPTED).
     */
    public function getPasswdType() : string
    {
        return $this->passwd_type;
    }

    public function setGender(string $a_str) : void
    {
        $this->gender = substr($a_str, -1);
    }

    public function getGender() : string
    {
        return $this->gender;
    }

    /**
     * set user title
     * (note: don't mix up this method with setTitle() that is derived from
     * ilObject and sets the user object's title)
     */
    public function setUTitle(string $a_str) : void
    {
        $this->utitle = $a_str;
    }

    public function getUTitle() : string
    {
        return $this->utitle;
    }

    public function setFirstname(string $a_str) : void
    {
        $this->firstname = $a_str;
    }

    public function getFirstname() : string
    {
        return $this->firstname;
    }

    public function setLastname(string $a_str) : void
    {
        $this->lastname = $a_str;
    }

    public function getLastname() : string
    {
        return $this->lastname;
    }

    public function setInstitution(string $a_str) : void
    {
        $this->institution = $a_str;
    }

    public function getInstitution() : string
    {
        return $this->institution;
    }

    public function setDepartment(string $a_str) : void
    {
        $this->department = $a_str;
    }

    public function getDepartment() : string
    {
        return $this->department;
    }

    public function setStreet(string $a_str) : void
    {
        $this->street = $a_str;
    }

    public function getStreet() : string
    {
        return $this->street;
    }

    public function setCity(string $a_str) : void
    {
        $this->city = $a_str;
    }

    public function getCity() : string
    {
        return $this->city;
    }

    public function setZipcode(string $a_str) : void
    {
        $this->zipcode = $a_str;
    }

    public function getZipcode() : string
    {
        return $this->zipcode;
    }

    public function setCountry(string $a_str) : void
    {
        $this->country = $a_str;
    }

    public function getCountry() : string
    {
        return $this->country;
    }

    /**
     * Set selected country (selection drop down)
     */
    public function setSelectedCountry(string $a_val) : void
    {
        $this->sel_country = $a_val;
    }

    /**
     * Get selected country (selection drop down)
     */
    public function getSelectedCountry() : string
    {
        return $this->sel_country;
    }

    public function setPhoneOffice(string $a_str) : void
    {
        $this->phone_office = $a_str;
    }

    public function getPhoneOffice() : string
    {
        return $this->phone_office;
    }

    public function setPhoneHome(string $a_str) : void
    {
        $this->phone_home = $a_str;
    }

    public function getPhoneHome() : string
    {
        return $this->phone_home;
    }

    public function setPhoneMobile(string $a_str) : void
    {
        $this->phone_mobile = $a_str;
    }

    public function getPhoneMobile() : string
    {
        return $this->phone_mobile;
    }

    public function setFax(string $a_str) : void
    {
        $this->fax = $a_str;
    }

    public function getFax() : string
    {
        return $this->fax;
    }

    public function setClientIP(string $a_str) : void
    {
        $this->client_ip = $a_str;
    }

    public function getClientIP() : string
    {
        return $this->client_ip;
    }

    public function setMatriculation(string $a_str) : void
    {
        $this->matriculation = $a_str;
    }

    public function getMatriculation() : string
    {
        return $this->matriculation;
    }

    public static function lookupMatriculation(int $a_usr_id) : string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT matriculation FROM usr_data " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id);
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->matriculation ?: '';
    }

    public function setEmail(string $a_str) : void
    {
        $this->email = $a_str;
    }

    public function getEmail() : string
    {
        return $this->email;
    }
    
    public function getSecondEmail() : ?string
    {
        return $this->second_email;
    }
    
    public function setSecondEmail(?string $second_email) : void
    {
        $this->second_email = $second_email;
    }
    
    public function setHobby(string $a_str) : void
    {
        $this->hobby = $a_str;
    }

    public function getHobby() : string
    {
        return $this->hobby;
    }

    public function setLanguage(string $a_str) : void
    {
        $this->setPref("language", $a_str);
        ilSession::clear('lang');
    }

    public function getLanguage() : string
    {
        return $this->prefs["language"];
    }

    public function setLastPasswordChangeTS(int $a_last_password_change_ts) : void
    {
        $this->last_password_change_ts = $a_last_password_change_ts;
    }

    public function getLastPasswordChangeTS() : int
    {
        return $this->last_password_change_ts;
    }

    public function getPasswordPolicyResetStatus() : bool
    {
        return $this->passwd_policy_reset;
    }

    public function setPasswordPolicyResetStatus(bool $status) : void
    {
        $this->passwd_policy_reset = $status;
    }

    public static function _lookupLanguage(int $a_usr_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $q = "SELECT value FROM usr_pref WHERE usr_id= " .
            $ilDB->quote($a_usr_id, "integer") . " AND keyword = " .
            $ilDB->quote('language', "text");
        $r = $ilDB->query($q);

        while ($row = $ilDB->fetchAssoc($r)) {
            return (string) $row['value'];
        }
        if (is_object($lng)) {
            return $lng->getDefaultLanguage();
        }
        return 'en';
    }

    public static function _writeExternalAccount(
        int $a_usr_id,
        string $a_ext_id
    ) : void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "UPDATE usr_data " .
            " SET ext_account = %s WHERE usr_id = %s",
            array("text", "integer"),
            array($a_ext_id, $a_usr_id)
        );
    }

    public static function _writeAuthMode(int $a_usr_id, string $a_auth_mode) : void
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
     */
    public function getCurrentLanguage() : string
    {
        return (string) ilSession::get('lang');
    }

    /**
     * Set current language
     */
    public function setCurrentLanguage(string $a_val) : void
    {
        ilSession::set('lang', $a_val);
    }

    public function setLastLogin(string $a_str) : void
    {
        $this->last_login = $a_str;
    }

    public function getLastLogin() : string
    {
        return $this->last_login;
    }

    public function setFirstLogin(string $a_str) : void
    {
        $this->first_login = $a_str;
    }

    public function getFirstLogin() : string
    {
        return $this->first_login;
    }

    public function setLastProfilePrompt(string $a_str) : void
    {
        $this->last_profile_prompt = $a_str;
    }

    public function getLastProfilePrompt() : string
    {
        return $this->last_profile_prompt;
    }

    public function setLastUpdate(string $a_str) : void
    {
        $this->last_update = $a_str;
    }

    public function getLastUpdate() : string
    {
        return $this->last_update;
    }

    public function setComment(string $a_str) : void
    {
        $this->referral_comment = $a_str;
    }

    public function getComment() : string
    {
        return $this->referral_comment;
    }

    /**
     * set date the user account was activated
     * null indicates that the user has not yet been activated
     */
    public function setApproveDate(?string $a_str) : void
    {
        $this->approve_date = $a_str;
    }

    public function getApproveDate() : ?string
    {
        return $this->approve_date;
    }

    public function getAgreeDate() : ?string
    {
        return $this->agree_date;
    }
    public function setAgreeDate(?string $a_str) : void
    {
        $this->agree_date = $a_str;
    }

    /**
    * set user active state and updates system fields appropriately
     * @param int  $a_owner the id of the person who approved the account, defaults to 6 (root)
     */
    public function setActive(
        bool $a_active,
        int $a_owner = 0
    ) : void {
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

    public function getActive() : bool
    {
        return (bool) $this->active;
    }

    public static function _lookupActive(int $a_usr_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT usr_id FROM usr_data ' .
            'WHERE active = ' . $ilDB->quote(1, 'integer') . ' ' .
            'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);
        while ($res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    /**
     * synchronizes current and stored user active values
     * for the owner value to be set correctly, this function should only be called
     * when an admin is approving a user account
     */
    public function syncActive() : void
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
            $this->setActive($currentActive, self::getUserIdByLogin(self::getLoginFromAuth()));
        }
    }

    /**
     * get user active state
     */
    public function getStoredActive(int $a_id) : bool
    {
        return (bool) self::_lookup($a_id, "active");
    }

    public function setSkin(string $a_str) : void
    {
        $this->skin = $a_str;
    }

    public function setTimeLimitOwner(int $a_owner) : void
    {
        $this->time_limit_owner = $a_owner;
    }

    public function getTimeLimitOwner() : int
    {
        return $this->time_limit_owner ?: 7;
    }

    public function setTimeLimitFrom(?int $a_from) : void
    {
        $this->time_limit_from = $a_from;
    }

    public function getTimeLimitFrom() : ?int
    {
        return $this->time_limit_from;
    }

    public function setTimeLimitUntil(?int $a_until) : void
    {
        $this->time_limit_until = $a_until;
    }

    public function getTimeLimitUntil() : ?int
    {
        return $this->time_limit_until;
    }

    public function setTimeLimitUnlimited(bool $a_unlimited) : void
    {
        $this->time_limit_unlimited = $a_unlimited;
    }

    public function getTimeLimitUnlimited() : bool
    {
        return $this->time_limit_unlimited;
    }
    
    public function setTimeLimitMessage(string $a_time_limit_message) : void
    {
        $this->time_limit_message = $a_time_limit_message;
    }
    
    public function getTimeLimitMessage() : string
    {
        return $this->time_limit_message;
    }

    public function setLoginAttempts(int $a_login_attempts) : void
    {
        $this->login_attempts = $a_login_attempts;
    }

    public function getLoginAttempts() : int
    {
        return $this->login_attempts;
    }

    public function checkTimeLimit() : bool
    {
        if ($this->getTimeLimitUnlimited()) {
            return true;
        }
        if ($this->getTimeLimitFrom() < time() and $this->getTimeLimitUntil() > time()) {
            return true;
        }
        return false;
    }

    public function setProfileIncomplete(bool $a_prof_inc) : void
    {
        $this->profile_incomplete = $a_prof_inc;
    }

    public function getProfileIncomplete() : bool
    {
        if ($this->id == ANONYMOUS_USER_ID) {
            return false;
        }
        return $this->profile_incomplete;
    }

    public function isPasswordChangeDemanded() : bool
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

    public function isPasswordExpired() : bool
    {
        if ($this->id == ANONYMOUS_USER_ID) {
            return false;
        }

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

    public function getPasswordAge() : int
    {
        $current_ts = time();
        $pass_change_ts = $this->getLastPasswordChangeTS();
        $password_age = (int) (($current_ts - $pass_change_ts) / 86400);
        return $password_age;
    }

    public function setLastPasswordChangeToNow() : bool
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

    public function resetLastPasswordChange() : bool
    {
        $ilDB = $this->db;
        
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

    public function setLatitude(?string $a_latitude) : void
    {
        $this->latitude = $a_latitude;
    }

    public function getLatitude() : ?string
    {
        return $this->latitude;
    }

    public function setLongitude(?string $a_longitude) : void
    {
        $this->longitude = $a_longitude;
    }

    public function getLongitude() : ?string
    {
        return $this->longitude;
    }

    public function setLocationZoom(?int $a_locationzoom) : void
    {
        $this->loc_zoom = $a_locationzoom;
    }

    public function getLocationZoom() : ?int
    {
        return $this->loc_zoom;
    }

    
    public static function hasActiveSession(
        int $a_user_id,
        string $a_session_id
    ) : bool {
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

    /**
     * check user id with login name
     */
    public function checkUserId() : bool
    {
        $login = self::getLoginFromAuth();
        $id = self::_lookupId($login);
        if ($id > 0) {
            return $id;
        }
        return false;
    }

    /**
     * Gets the username from $ilAuth, and converts it into an ILIAS login name.
     */
    private static function getLoginFromAuth() : string
    {
        $uid = $GLOBALS['DIC']['ilAuthSession']->getUserId();
        $login = self::_lookupLogin($uid);

        // BEGIN WebDAV: Strip Microsoft Domain Names from logins
        if (ilDAVActivationChecker::_isActive()) {
            $login = self::toUsernameWithoutDomain($login);
        }
        return $login;
    }
    
    /**
     * Static function removes Microsoft domain name from username
     * webdav related
     */
    public static function toUsernameWithoutDomain(string $a_login) : string
    {
        // Remove all characters including the last slash or the last backslash
        // in the username
        $pos = strrpos($a_login, '/');
        $pos2 = strrpos($a_login, '\\');
        if ($pos === false || $pos < $pos2) {
            $pos = $pos2;
        }
        if (is_int($pos)) {
            $a_login = substr($a_login, $pos + 1);
        }
        return $a_login;
    }

    /*
     * check to see if current user has been made active
     */
    public function isCurrentUserActive() : bool
    {
        $ilDB = $this->db;

        $login = self::getLoginFromAuth();
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

    public static function getUserIdByLogin(string $a_login) : int
    {
        return (int) self::_lookupId($a_login);
    }

    /**
     * @return int[] of user ids
     */
    public static function getUserIdsByEmail(string $a_email) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "SELECT usr_id FROM usr_data " .
            "WHERE email = %s and active = 1",
            array("text"),
            array($a_email)
        );
        $ids = array();
        while ($row = $ilDB->fetchObject($res)) {
            $ids[] = (int) $row->usr_id;
        }

        return $ids;
    }


    /**
     * @return string[] with all user login names
     */
    public static function getUserLoginsByEmail(string $a_email) : array
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

    public function getLoginByUserId(int $a_userid) : ?string
    {
        $login = self::_lookupLogin($a_userid);
        return $login ?: null;
    }

    /**
     * @return string[]
     */
    public static function getAllUserLogins() : array
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
     * @param int[] $a_user_ids
     * @return array
     */
    public static function _readUsersProfileData(array $a_user_ids) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $res = $ilDB->query("SELECT * FROM usr_data WHERE " .
            $ilDB->in("usr_id", $a_user_ids, false, "integer"));
        $user_data = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $user_data[$row['usr_id']] = $row;
        }
        return $user_data;
    }

    /**
     * @param ?array $a_fields
     * @param int        $active    all kind of undocumented options, see code, needs refactoring
     * @return array
     */
    public static function _getAllUserData(
        ?array $a_fields = null,
        int $active = -1
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $result_arr = array();

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
                    $q .= "WHERE time_limit_unlimited= " . $ilDB->quote(0, "integer");
                    break;
                case 3:
                    $qtemp = $q . ", rbac_ua, object_data WHERE rbac_ua.rol_id = object_data.obj_id AND " .
                        $ilDB->like("object_data.title", "text", "%crs%") . " AND usr_data.usr_id = rbac_ua.usr_id";
                    $r = $ilDB->query($qtemp);
                    $course_users = array();
                    while ($row = $ilDB->fetchAssoc($r)) {
                        $course_users[] = $row["usr_id"];
                    }
                    if (count($course_users)) {
                        $q .= " WHERE " . $ilDB->in("usr_data.usr_id", $course_users, true, "integer") . " ";
                    } else {
                        return $result_arr;
                    }
                    break;
                case 4:
                    $session_data = ilSession::get('user_filter_data');
                    $date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $session_data["m"], $session_data["d"], $session_data["y"]));
                    $q .= " AND last_login < " . $ilDB->quote($date, "timestamp");
                    break;
                case 5:
                    $ref_id = ilSession::get('user_filter_data');
                    if ($ref_id) {
                        $q .= " LEFT JOIN obj_members ON usr_data.usr_id = obj_members.usr_id " .
                            "WHERE obj_members.obj_id = (SELECT obj_id FROM object_reference " .
                            "WHERE ref_id = " . $ilDB->quote($ref_id, "integer") . ") ";
                    }
                    break;
                case 6:
                    global $DIC;

                    $rbacreview = $DIC['rbacreview'];
                    $ref_id = ilSession::get('user_filter_data');
                    if ($ref_id) {
                        $local_roles = $rbacreview->getRolesOfRoleFolder($ref_id, false);
                        if (is_array($local_roles) && count($local_roles)) {
                            $q .= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE " .
                                $ilDB->in("rbac_ua.rol_id", $local_roles, false, "integer") . " ";
                        }
                    }
                    break;
                case 7:
                    $rol_id = ilSession::get('user_filter_data');
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

    public static function _getNumberOfUsersForStyle(
        string $a_skin,
        string $a_style
    ) : int {
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

        return (int) $cnt_rec["cnt"];
    }

    /**
     * @return string[]
     */
    public static function _getAllUserAssignedStyles() : array
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

    public static function _moveUsersToStyle(
        string $a_from_skin,
        string $a_from_style,
        string $a_to_skin,
        string $a_to_style
    ) : void {
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
     * @param	int		$a_item_id		ref_id for objects, that are in the main tree
     *									(learning modules, forums) obj_id for others
     * @param	string	$a_type			object type
     */
    public function addObjectToClipboard(
        int $a_item_id,
        string $a_type,
        string $a_title,
        int $a_parent = 0,
        int $a_time = 0,
        int $a_order_nr = 0
    ) : void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_time == 0) {
            $a_time = date("Y-m-d H:i:s");
        }

        $item_set = $ilDB->queryF(
            "SELECT * FROM personal_clipboard WHERE " .
            "parent = %s AND item_id = %s AND type = %s AND user_id = %s",
            array("integer", "integer", "text", "integer"),
            array(0, $a_item_id, $a_type, $this->getId())
        );

        // only insert if item is not already in clipboard
        if (!$item_set->fetchRow()) {
            $ilDB->manipulateF(
                "INSERT INTO personal_clipboard " .
                "(item_id, type, user_id, title, parent, insert_time, order_nr) VALUES " .
                " (%s,%s,%s,%s,%s,%s,%s)",
                array("integer", "text", "integer", "text", "integer", "timestamp", "integer"),
                array($a_item_id, $a_type, $this->getId(), $a_title, $a_parent, $a_time, $a_order_nr)
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
     * @todo move to COPage service
     */
    public function addToPCClipboard(
        string $a_content,
        string $a_time,
        int $a_nr
    ) : void {
        $ilDB = $this->db;
        if ($a_time == 0) {
            $a_time = date("Y-m-d H:i:s");
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
     * @todo move to COPage service
     */
    public function getPCClipboardContent() : array // Missing array type.
    {
        $ilDB = $this->db;

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
    public function clipboardHasObjectsOfType(string $a_type) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->queryF(
            "SELECT * FROM personal_clipboard WHERE " .
            "parent = %s AND type = %s AND user_id = %s",
            array("integer", "text", "integer"),
            array(0, $a_type, $this->getId())
        );
        if ($ilDB->fetchAssoc($set)) {
            return true;
        }

        return false;
    }

    public function clipboardDeleteObjectsOfType(string $a_type) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulateF(
            "DELETE FROM personal_clipboard WHERE " .
            "type = %s AND user_id = %s",
            array("text", "integer"),
            array($a_type, $this->getId())
        );
    }

    public function clipboardDeleteAll() : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF("DELETE FROM personal_clipboard WHERE " .
            "user_id = %s", array("integer"), array($this->getId()));
    }

    /**
     * get all clipboard objects of user and specified type
     */
    public function getClipboardObjects(
        string $a_type = "",
        bool $a_top_nodes_only = false
    ) : array {
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
                $obj["title"] = ilMediaPoolPage::lookupTitle($obj["item_id"]);
            }
            $objects[] = array("id" => $obj["item_id"],
                "type" => $obj["type"], "title" => $obj["title"],
                "insert_time" => $obj["insert_time"]);
        }
        return $objects;
    }

    /**
     * Get children of an item
     */
    public function getClipboardChilds(
        int $a_parent,
        string $a_insert_time
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $objs = $ilDB->queryF(
            "SELECT * FROM personal_clipboard WHERE " .
            "user_id = %s AND parent = %s AND insert_time = %s " .
            " ORDER BY order_nr",
            array("integer", "integer", "timestamp"),
            array($ilUser->getId(), $a_parent, $a_insert_time)
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
     * @return	int[]		array of user IDs
     */
    public static function _getUsersForClipboadObject(
        string $a_type,
        int $a_id
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $q = "SELECT DISTINCT user_id FROM personal_clipboard WHERE " .
            "item_id = " . $ilDB->quote($a_id, "integer") . " AND " .
            "type = " . $ilDB->quote($a_type, "text");
        $user_set = $ilDB->query($q);
        $users = array();
        while ($user_rec = $ilDB->fetchAssoc($user_set)) {
            $users[] = (int) $user_rec["user_id"];
        }

        return $users;
    }

    public function removeObjectFromClipboard(
        int $a_item_id,
        string $a_type
    ) : void {
        $ilDB = $this->db;

        $q = "DELETE FROM personal_clipboard WHERE " .
            "item_id = " . $ilDB->quote($a_item_id, "integer") .
            " AND type = " . $ilDB->quote($a_type, "text") . " " .
            " AND user_id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);
    }

    public static function _getImportedUserId(
        string $i2_id
    ) : int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT obj_id FROM object_data WHERE import_id = " .
            $ilDB->quote($i2_id, "text");

        $res = $ilDB->query($query);
        $id = 0;
        while ($row = $ilDB->fetchObject($res)) {
            $id = (int) $row->obj_id;
        }
        return $id;
    }
    
    /**
     * lookup org unit representation
     */
    public static function lookupOrgUnitsRepresentation(
        int $a_usr_id
    ) : string {
        return ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($a_usr_id);
    }

    public function getOrgUnitsRepresentation() : string
    {
        return self::lookupOrgUnitsRepresentation($this->getId());
    }

    public function setAuthMode(string $a_str) : void
    {
        $this->auth_mode = $a_str;
    }

    public function getAuthMode(bool $a_auth_key = false) : string
    {
        if (!$a_auth_key) {
            return $this->auth_mode;
        }
        return ilAuthUtils::_getAuthMode($this->auth_mode);
    }

    public function setExternalAccount(string $a_str) : void
    {
        $this->ext_account = $a_str;
    }

    public function getExternalAccount() : string
    {
        return $this->ext_account;
    }

    /**
     * Get list of external account by authentication method
     * Note: If login == ext_account for two user with auth_mode 'default' and auth_mode 'ldap'
     * 	The ldap auth mode chosen
     * @param bool $a_read_auth_default also get users with authentication method 'default'
     */
    public static function _getExternalAccountsByAuthMode(
        string $a_auth_mode,
        bool $a_read_auth_default = false
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        $q = "SELECT login,usr_id,ext_account,auth_mode FROM usr_data " .
            "WHERE auth_mode = %s";
        $types[] = "text";
        $values[] = $a_auth_mode;
        if ($a_read_auth_default and ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode', ilAuthUtils::AUTH_LOCAL)) == $a_auth_mode) {
            $q .= " OR auth_mode = %s ";
            $types[] = "text";
            $values[] = 'default';
        }

        $res = $ilDB->queryF($q, $types, $values);
        $accounts = [];
        while ($row = $ilDB->fetchObject($res)) {
            if ($row->auth_mode == 'default') {
                $accounts[$row->usr_id] = $row->login;
            } else {
                $accounts[$row->usr_id] = $row->ext_account;
            }
        }
        return $accounts;
    }

    public static function _toggleActiveStatusOfUsers(
        array $a_usr_ids,
        bool $a_status
    ) : void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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
    }

    public static function _lookupAuthMode(int $a_usr_id) : string
    {
        return (string) self::_lookup($a_usr_id, "auth_mode");
    }

    /**
     * check whether external account and authentication method
     * matches with a user
     */
    public static function _checkExternalAuthAccount(
        string $a_auth,
        string $a_account,
        bool $tryFallback = true
    ) : ?string {
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
            return null;
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
        return null;
    }

    /**
     * get number of users per auth mode
     */
    public static function _getNumberOfUsersPerAuthMode() : array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $r = $ilDB->query("SELECT count(*) AS cnt, auth_mode FROM usr_data " .
            "GROUP BY auth_mode");
        $cnt_arr = array();
        while ($cnt = $ilDB->fetchAssoc($r)) {
            $cnt_arr[$cnt["auth_mode"]] = (int) $cnt["cnt"];
        }

        return $cnt_arr;
    }

    public static function _getLocalAccountsForEmail(string $a_email) : array // Missing array type.
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
     * @param	int	$obj_id The object id of the related user account
     */
    public static function _uploadPersonalPicture(
        string $tmp_file,
        int $obj_id
    ) : bool {
        $webspace_dir = ilFileUtils::getWebspaceDir();
        $image_dir = $webspace_dir . "/usr_images";
        $store_file = "usr_" . $obj_id . "." . "jpg";

        chmod($tmp_file, 0770);

        // take quality 100 to avoid jpeg artefacts when uploading jpeg files
        // taking only frame [0] to avoid problems with animated gifs
        $show_file = "$image_dir/usr_" . $obj_id . ".jpg";
        $thumb_file = "$image_dir/usr_" . $obj_id . "_small.jpg";
        $xthumb_file = "$image_dir/usr_" . $obj_id . "_xsmall.jpg";
        $xxthumb_file = "$image_dir/usr_" . $obj_id . "_xxsmall.jpg";

        ilShellUtil::execConvert($tmp_file . "[0] -geometry 200x200 -quality 100 JPEG:" . $show_file);
        ilShellUtil::execConvert($tmp_file . "[0] -geometry 100x100 -quality 100 JPEG:" . $thumb_file);
        ilShellUtil::execConvert($tmp_file . "[0] -geometry 75x75 -quality 100 JPEG:" . $xthumb_file);
        ilShellUtil::execConvert($tmp_file . "[0] -geometry 30x30 -quality 100 JPEG:" . $xxthumb_file);

        // store filename
        self::_writePref($obj_id, "profile_image", $store_file);

        return true;
    }


    /**
     * @param string $a_size       "small", "xsmall" or "xxsmall"
     * @throws ilWACException
     */
    public function getPersonalPicturePath(
        string $a_size = "small",
        bool $a_force_pic = false
    ) : string {
        if (isset(self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic])) {
            return self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic];
        }

        self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic] = self::_getPersonalPicturePath($this->getId(), $a_size, $a_force_pic);

        return self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic];
    }

    public function getAvatar() : Avatar
    {
        return self::_getAvatar($this->getId());
    }

    public static function _getAvatar(int $a_usr_id) : Avatar
    {
        $define = new ilUserAvatarResolver($a_usr_id ?: ANONYMOUS_USER_ID);
        return $define->getAvatar();
    }

    /**
     * @param string $a_size "small", "xsmall" or "xxsmall"
     * @throws ilWACException
     */
    public static function _getPersonalPicturePath(
        int $a_usr_id,
        string $a_size = "small",
        bool $a_force_pic = false,
        bool $a_prevent_no_photo_image = false,
        bool $html_export = false
    ) : string {
        $define = new ilUserAvatarResolver($a_usr_id);
        $define->setForcePicture($a_force_pic);
        $define->setSize($a_size);
        return ilWACSignedPath::signFile($define->getLegacyPictureURL());
    }

    public static function copyProfilePicturesToDirectory(
        int $a_user_id,
        string $a_dir
    ) : void {
        $a_dir = trim(str_replace("..", "", $a_dir));
        if ($a_dir == "" || !is_dir($a_dir)) {
            return;
        }
        
        $webspace_dir = ilFileUtils::getWebspaceDir();
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
    
    
    public function removeUserPicture(
        bool $a_do_update = true
    ) : void {
        $webspace_dir = ilFileUtils::getWebspaceDir();
        $image_dir = $webspace_dir . "/usr_images";
        $file = $image_dir . "/usr_" . $this->getId() . "." . "jpg";
        $thumb_file = $image_dir . "/usr_" . $this->getId() . "_small.jpg";
        $xthumb_file = $image_dir . "/usr_" . $this->getId() . "_xsmall.jpg";
        $xxthumb_file = $image_dir . "/usr_" . $this->getId() . "_xxsmall.jpg";
        $upload_file = $image_dir . "/upload_" . $this->getId();

        if ($a_do_update) {
            // remove user pref file name
            $this->setPref("profile_image", "");
            $this->update();
        }

        if (is_file($file)) {
            unlink($file);
        }
        if (is_file($thumb_file)) {
            unlink($thumb_file);
        }
        if (is_file($xthumb_file)) {
            unlink($xthumb_file);
        }
        if (is_file($xxthumb_file)) {
            unlink($xxthumb_file);
        }
        if (is_file($upload_file)) {
            unlink($upload_file);
        }
    }
    
    
    public function setUserDefinedData(array $a_data) : void // Missing array type.
    {
        foreach ($a_data as $field => $data) {
            $this->user_defined_data['f_' . $field] = $data;
        }
    }

    public function getUserDefinedData() : array // Missing array type.
    {
        return $this->user_defined_data ?: array();
    }

    public function updateUserDefinedFields() : void
    {
        $udata = new ilUserDefinedData($this->getId());
        foreach ($this->user_defined_data as $field => $value) {
            if ($field != 'usr_id') {
                $udata->set($field, $value);
            }
        }
        $udata->update();
    }

    public function readUserDefinedFields() : void
    {
        $udata = new ilUserDefinedData($this->getId());
        $this->user_defined_data = $udata->getAll();
    }

    public function deleteUserDefinedFieldEntries() : void
    {
        ilUserDefinedData::deleteEntriesOfUser($this->getId());
    }

    /**
     * Get formatted mail body text of user profile data.
     * @throws ilDateTimeException
     */
    public function getProfileAsString(ilLanguage $language) : string
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        $language->loadLanguageModule('registration');
        $language->loadLanguageModule('crs');

        $body = ($language->txt("login") . ": " . $this->getLogin() . "\n");

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
            ilDatePresentation::formatPeriod(
                new ilDateTime($this->getTimeLimitFrom(), IL_CAL_UNIX),
                new ilDateTime($this->getTimeLimitUntil(), IL_CAL_UNIX)
            );
            ilDatePresentation::resetToDefaults();
            
            $start = new ilDateTime($this->getTimeLimitFrom(), IL_CAL_UNIX);
            $end = new ilDateTime($this->getTimeLimitUntil(), IL_CAL_UNIX);
            
            $body .= $language->txt('time_limit') . ': ' . $start->get(IL_CAL_DATETIME);
            $body .= $language->txt('time_limit') . ': ' . $end->get(IL_CAL_DATETIME);
        }

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
    public static function _lookupFeedHash(
        int $a_user_id,
        bool $a_create = false
    ) : ?string {
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
                    $hash = md5(random_int(1, 9999999) + str_replace(" ", "", microtime()));
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
        return null;
    }

    /**
     * Lookup news feed password for user
     * @return	?string	feed_password md5-encoded, or false
     * @todo move to news service
     */
    public static function _getFeedPass(
        int $a_user_id
    ) : ?string {
        if ($a_user_id > 0) {
            return self::_lookupPref($a_user_id, "priv_feed_pass");
        }
        return null;
    }

    /**
     * Set news feed password for user
     * @todo move to news service
     */
    public static function _setFeedPass(
        int $a_user_id,
        string $a_password
    ) : void {
        self::_writePref(
            $a_user_id,
            "priv_feed_pass",
            ($a_password == "") ? "" : md5($a_password)
        );
    }

    /**
     * check if a login name already exists
     * You may exclude a user from the check by giving his user id as 2nd paramter
     */
    public static function _loginExists(
        string $a_login,
        int $a_user_id = 0
    ) : ?int {
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
            return (int) $row['usr_id'];
        }
        return null;
    }

    /**
     * Check if an external account name already exists
     */
    public static function _externalAccountExists(
        string $a_external_account,
        string $a_auth_mode
    ) : bool {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT * FROM usr_data " .
            "WHERE ext_account = %s AND auth_mode = %s",
            array("text", "text"),
            array($a_external_account, $a_auth_mode)
        );
        return (bool) $ilDB->fetchAssoc($res);
    }

    /**
     * return array of complete users which belong to a specific role
     * @param int $active 	if -1, all users will be delivered, 0 only non active, 1 only active users
     */
    public static function _getUsersForRole(
        int $role_id,
        int $active = -1
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];
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
     * get users for a category or from system folder
     * @param $active -1 (ignore), 1 = active, 0 = not active user
     */
    public static function _getUsersForFolder(
        int $ref_id,
        int $active
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT usr_data.*, usr_pref.value AS language FROM usr_data LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id and usr_pref.keyword = %s";
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
            $data[] = $row;
        }

        return $data;
    }


    /**
     * return user data for group members
     * @param int[] $a_mem_ids array of member ids
     * @param int $active active can be -1 (ignore), 1 = active, 0 = not active user
     */
    public static function _getUsersForGroup(
        array $a_mem_ids,
        int $active = -1
    ) : array {
        return self::_getUsersForIds($a_mem_ids, $active);
    }


    /**
    * return user data for given user id
    * @param int[] array of member ids
    * @param int active can be -1 (ignore), 1 = active, 0 = not active user
    */
    public static function _getUsersForIds(
        array $a_mem_ids,
        int $active = -1,
        int $timelimitowner = -1
    ) : array {
        global $DIC;

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
        $mem_arr = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $mem_arr[] = $row;
        }

        return $mem_arr;
    }



    /**
     * return user data for given user ids
     * @param array $a_internalids of internal ids or numerics
     */
    public static function _getUserData(array $a_internalids) : array
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
    public static function _getPreferences(int $user_id) : array
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
     */
    public static function getUserSubsetByPreferenceValue(
        array $a_user_ids,
        string $a_keyword,
        string $a_val
    ) : array {
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


    public static function _resetLoginAttempts(
        int $a_usr_id
    ) : bool {
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

    public static function _getLoginAttempts(
        int $a_usr_id
    ) : int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT login_attempts FROM usr_data WHERE usr_id = %s";
        $result = $ilDB->queryF($query, array('integer'), array($a_usr_id));
        $record = $ilDB->fetchAssoc($result);
        return (int) ($record['login_attempts'] ?? 0);
    }

    public static function _incrementLoginAttempts(
        int $a_usr_id
    ) : bool {
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

    public static function _setUserInactive(
        int $a_usr_id
    ) : bool {
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
     */
    public function hasPublicProfile() : bool
    {
        return in_array($this->getPref("public_profile"), array("y", "g"));
    }
    
    /**
     * returns firstname lastname and login if profile is public, login otherwise
     */
    public function getPublicName() : string
    {
        if ($this->hasPublicProfile()) {
            return $this->getFirstname() . " " . $this->getLastname() . " (" . $this->getLogin() . ")";
        } else {
            return $this->getLogin();
        }
    }
    
    public static function _writeHistory(
        int $a_usr_id,
        string $a_login
    ) : void {
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
    }
    
    /**
     * reads all active sessions from db and returns users that are online
     * OR returns only one active user if a user_id is given
     */
    public static function _getUsersOnline(
        int $a_user_id = 0,
        bool $a_no_anonymous = false
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $log = ilLoggerFactory::getLogger("user");

        $pd_set = new ilSetting('pd');
        $atime = $pd_set->get('user_activity_time') * 60;
        $ctime = time();
        
        $where = array();

        if ($a_user_id === 0) {
            $where[] = 'user_id > 0';
        } else {
            $where[] = 'user_id = ' . $ilDB->quote($a_user_id, 'integer');
        }

        if ($a_no_anonymous) {
            $where[] = 'user_id != ' . $ilDB->quote(ANONYMOUS_USER_ID, 'integer');
        }

        if (ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
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
			$where
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

        if (ilTermsOfServiceHelper::isEnabled()) {
            $users = array_filter($users, static function (array $user) {
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
     * Generates a unique hashcode for activating a user
     * profile after registration
     */
    public static function _generateRegistrationHash(int $a_usr_id) : string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        do {
            $continue = false;
            
            $hashcode = substr(md5(uniqid(mt_rand(), true)), 0, 16);
            
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
                array($hashcode, $a_usr_id)
            );
            
            break;
        } while (true);
        
        return $hashcode;
    }
    
    /**
     * Verifies a registration hash
     * @throws ilRegConfirmationLinkExpiredException
     * @throws ilRegistrationHashNotFoundException
     */
    public static function _verifyRegistrationHash(
        string $a_hash
    ) : int {
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
            $oRegSettigs = new ilRegistrationSettings();
            
            if ($oRegSettigs->getRegistrationHashLifetime() != 0 &&
               time() - $oRegSettigs->getRegistrationHashLifetime() > strtotime($row['create_date'])) {
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
        
        throw new ilRegistrationHashNotFoundException('reg_confirmation_hash_not_found');
    }

    public function setBirthday(?string $a_birthday) : void
    {
        if (strlen($a_birthday)) {
            $date = new ilDate($a_birthday, IL_CAL_DATE);
            $this->birthday = $date->get(IL_CAL_DATE);
        } else {
            $this->birthday = null;
        }
    }
    
    public function getBirthday() : ?string
    {
        return $this->birthday;
    }

    /**
     * Get ids of all users that have been inactive for at least the given period
     * @param int $periodInDays
     * @return int[]
     * @throws ilException
     */
    public static function getUserIdsByInactivityPeriod(
        int $periodInDays
    ) : array {
        global $DIC;

        if ($periodInDays < 1) {
            throw new ilException('Invalid period given');
        }

        $date = date('Y-m-d H:i:s', (time() - ($periodInDays * 24 * 60 * 60)));

        $query = "SELECT usr_id FROM usr_data WHERE last_login IS NOT NULL AND last_login < %s";

        $ids = [];

        $types = ['timestamp'];
        $values = [$date];

        $res = $DIC->database()->queryF($query, $types, $values);
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $ids[] = (int) $row['usr_id'];
        }

        return $ids;
    }

    /**
     * Get ids of all users that have never logged in
     * @param int $thresholdInDays
     * @return int[]
     */
    public static function getUserIdsNeverLoggedIn(
        int $thresholdInDays
    ) : array {
        global $DIC;

        $date = date('Y-m-d H:i:s', (time() - ($thresholdInDays * 24 * 60 * 60)));

        $query = "SELECT usr_id FROM usr_data WHERE last_login IS NULL AND create_date < %s";

        $ids = [];

        $types = ['timestamp'];
        $values = [$date];

        $res = $DIC->database()->queryF($query, $types, $values);
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $ids[] = (int) $row['usr_id'];
        }

        return $ids;
    }
    
    /**
     * get ids of all users that have been inactivated since at least the given period
     * @param int $period (in days)
     * @return	int[] of user ids
     * @throws ilException
     */
    public static function _getUserIdsByInactivationPeriod(
        int $period
    ) : array {
        /////////////////////////////
        $field = 'inactivation_date';
        /////////////////////////////
        
        if (!$period) {
            throw new ilException('no valid period given');
        }

        global $DIC;

        $ilDB = $DIC['ilDB'];

        $date = date('Y-m-d H:i:s', (time() - ($period * 24 * 60 * 60)));

        $query = "SELECT usr_id FROM usr_data WHERE $field < %s AND active = %s";

        $res = $ilDB->queryF($query, array('timestamp', 'integer'), array($date, 0));
        
        $ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->usr_id;
        }

        return $ids;
    }

    public function resetOwner() : void
    {
        $ilDB = $this->db;
        
        $query = "UPDATE object_data SET owner = 0 " .
            "WHERE owner = " . $ilDB->quote($this->getId(), 'integer');
        $ilDB->query($query);
    }

    /**
     * Get first letters of all lastnames
     * @param int[] $user_ids
     */
    public static function getFirstLettersOfLastnames(
        ?array $user_ids = null
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT DISTINCT " . $ilDB->upper($ilDB->substr("lastname", 1, 1)) . " let" .
            " FROM usr_data" .
            " WHERE usr_id <> " . $ilDB->quote(ANONYMOUS_USER_ID, "integer") .
            ($user_ids !== null ? " AND " . $ilDB->in('usr_id', $user_ids, false, "integer") : "") .
            " ORDER BY let";
        $let_set = $ilDB->query($q);

        $let = array();
        while ($let_rec = $ilDB->fetchAssoc($let_set)) {
            $let[$let_rec["let"]] = $let_rec["let"];
        }
        return $let;
    }
    
    public static function userExists(
        array $a_usr_ids = array()
    ) : bool {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT count(*) num FROM object_data od ' .
                'JOIN usr_data ud ON obj_id = usr_id ' .
                'WHERE ' . $ilDB->in('obj_id', $a_usr_ids, false, 'integer') . ' ';
        $res = $ilDB->query($query);
        $num_rows = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)->num;
        return $num_rows == count($a_usr_ids);
    }

    public function exportPersonalData() : void
    {
        $exp = new ilExport();
        $dir = ilExport::_getExportDirectory($this->getId(), "xml", "usr", "personal_data");
        ilFileUtils::delDir($dir, true);
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
    
    public function getPersonalDataExportFile() : string
    {
        $dir = ilExport::_getExportDirectory($this->getId(), "xml", "usr", "personal_data");
        if (!is_dir($dir)) {
            return "";
        }
        foreach (ilFileUtils::getDir($dir) as $entry) {
            if (is_int(strpos($entry["entry"], ".zip"))) {
                return $entry["entry"];
            }
        }
        
        return "";
    }
    
    public function sendPersonalDataFile() : void
    {
        $file = ilExport::_getExportDirectory($this->getId(), "xml", "usr", "personal_data") .
            "/" . $this->getPersonalDataExportFile();
        if (is_file($file)) {
            ilFileDelivery::deliverFileLegacy($file, $this->getPersonalDataExportFile());
        }
    }
    
    public function importPersonalData(
        array $a_file,
        bool $a_profile_data,
        bool $a_settings,
        bool $a_notes,
        bool $a_calendar
    ) : void {
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
    
    public function setInactivationDate(?string $inactivation_date) : void
    {
        $this->inactivation_date = $inactivation_date;
    }
    
    public function getInactivationDate() : ?string
    {
        return $this->inactivation_date;
    }

    public function hasToAcceptTermsOfService() : bool
    {
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
     * @param bool $a_agreed true, if users that have agreed should be returned
     * @param ?int[] $a_users array of user ids (subset used as base) or null for all users
     * @return int[] array of user IDs
     */
    public static function getUsersAgreed(
        bool $a_agreed = true,
        ?array $a_users = null
    ) : array {
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
            $ret[] = (int) $rec["usr_id"];
        }
        return $ret;
    }

    public function hasToAcceptTermsOfServiceInSession(
        ?bool $status = null
    ) : bool {
        if (null === $status) {
            return (bool) ilSession::get('has_to_accept_agr_in_session');
        }
        
        if (ilTermsOfServiceHelper::isEnabled()) {
            ilSession::set('has_to_accept_agr_in_session', $status);
        }
        return $status;
    }

    public function isAnonymous() : bool
    {
        return self::_isAnonymous($this->getId());
    }

    public static function _isAnonymous(int $usr_id) : bool
    {
        return $usr_id == ANONYMOUS_USER_ID;
    }
    
    public function activateDeletionFlag() : void
    {
        $this->writePref("delete_flag", true);
    }
    
    public function removeDeletionFlag() : void
    {
        $this->writePref("delete_flag", false);
    }
    
    public function hasDeletionFlag() : bool
    {
        return (bool) $this->getPref("delete_flag");
    }

    public function setIsSelfRegistered(bool $status) : void
    {
        $this->is_self_registered = $status;
    }
    
    public function isSelfRegistered() : bool
    {
        return $this->is_self_registered;
    }
    
    
    //
    // MULTI-TEXT / INTERESTS
    //

    /**
     * @param string[]|null $value
     */
    public function setGeneralInterests(?array $value = null) : void
    {
        $this->interests_general = $value ?? [];
    }

    /**
     * @return string[]
     */
    public function getGeneralInterests() : array
    {
        return $this->interests_general;
    }
    
    /**
     * Get general interests as plain text
     */
    public function getGeneralInterestsAsText() : string
    {
        return $this->buildTextFromArray($this->interests_general);
    }

    /**
     * @param string[]|null $value
     */
    public function setOfferingHelp(?array $value = null) : void
    {
        $this->interests_help_offered = $value ?? [];
    }

    /**
     * @return string[]
     */
    public function getOfferingHelp() : array
    {
        return $this->interests_help_offered;
    }
    
    /**
     * Get help offering as plain text
     */
    public function getOfferingHelpAsText() : string
    {
        return $this->buildTextFromArray($this->interests_help_offered);
    }

    /**
     * @param string[]|null $value
     */
    public function setLookingForHelp(?array $value = null) : void
    {
        $this->interests_help_looking = $value ?? [];
    }

    /**
     * @return string[]
     */
    public function getLookingForHelp() : array
    {
        return $this->interests_help_looking;
    }
    
    /**
     * Get help looking for as plain text
     */
    public function getLookingForHelpAsText() : string
    {
        return $this->buildTextFromArray($this->interests_help_looking);
    }
    
    /**
     * Convert multi-text values to plain text
     * @param string[]
     * @return string
     */
    protected function buildTextFromArray(array $a_attr) : string
    {
        if (count($a_attr) > 0) {
            return implode(", ", $a_attr);
        }
        return "";
    }
    
    protected function readMultiTextFields() : void
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
    
    public function updateMultiTextFields(bool $a_create = false) : void
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
            if (is_array($values) && count($values)) {
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
    
    protected function deleteMultiTextFields() : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getId()) {
            return;
        }
        
        $ilDB->manipulate("DELETE FROM usr_data_multi" .
            " WHERE usr_id = " . $ilDB->quote($this->getId(), "integer"));
    }
    
    public static function findInterests(
        string $a_term,
        ?int $a_user_id = null,
        string $a_field_id = null
    ) : array {
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
     * @param int[] $a_user_ids user ids
     * @return array[] 	array["global"] => all user ids having their profile global (www) activated,
     * 					array["local"] => all user ids having their profile only locally (logged in users) activated,
     * 					array["public"] => all user ids having their profile either locally or globally activated,
     * 					array["not_public"] => all user ids having their profile deactivated
     */
    public static function getProfileStatusOfUsers(
        array $a_user_ids
    ) : array {
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
}
