<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class arUser
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arUser extends ActiveRecord
{

    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'usr_data';
    }


    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_primary true
     */
    protected $usr_id;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    80
     */
    protected $login;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $passwd;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $firstname;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $lastname;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $title;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    1
     */
    protected $gender;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    80
     */
    protected $email;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    80
     */
    protected $institution;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $street;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $city;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    10
     */
    protected $zipcode;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $country;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $phone_office;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $last_login;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $last_update;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $create_date;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    4000
     */
    protected $hobby;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    80
     */
    protected $department;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $phone_home;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $phone_mobile;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $fax;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $i2passwd;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     */
    protected $time_limit_owner;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     */
    protected $time_limit_unlimited;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     */
    protected $time_limit_from;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     */
    protected $time_limit_until;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     */
    protected $time_limit_message;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    250
     */
    protected $referral_comment;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    40
     */
    protected $matriculation;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     */
    protected $active;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $approve_date;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $agree_date;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    255
     */
    protected $client_ip;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    10
     */
    protected $auth_mode;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    4
     */
    protected $profile_incomplete;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    250
     */
    protected $ext_account;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $feed_hash;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    30
     */
    protected $latitude;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    30
     */
    protected $longitude;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     */
    protected $loc_zoom;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $login_attempts;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     */
    protected $last_password_change;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected $reg_hash;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $birthday;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    2
     */
    protected $sel_country;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $last_visited;
    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype
     * @con_length
     */
    protected $inactivation_date;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $is_self_registered;


    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }


    /**
     * @param int $agree_date
     */
    public function setAgreeDate($agree_date)
    {
        $this->agree_date = $agree_date;
    }


    /**
     * @return int
     */
    public function getAgreeDate()
    {
        return $this->agree_date;
    }


    /**
     * @param int $approve_date
     */
    public function setApproveDate($approve_date)
    {
        $this->approve_date = $approve_date;
    }


    /**
     * @return int
     */
    public function getApproveDate()
    {
        return $this->approve_date;
    }


    /**
     * @param int $auth_mode
     */
    public function setAuthMode($auth_mode)
    {
        $this->auth_mode = $auth_mode;
    }


    /**
     * @return int
     */
    public function getAuthMode()
    {
        return $this->auth_mode;
    }


    /**
     * @param int $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }


    /**
     * @return int
     */
    public function getBirthday()
    {
        return $this->birthday;
    }


    /**
     * @param int $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }


    /**
     * @return int
     */
    public function getCity()
    {
        return $this->city;
    }


    /**
     * @param int $client_ip
     */
    public function setClientIp($client_ip)
    {
        $this->client_ip = $client_ip;
    }


    /**
     * @return int
     */
    public function getClientIp()
    {
        return $this->client_ip;
    }


    /**
     * @param int $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }


    /**
     * @return int
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * @param int $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }


    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param int $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }


    /**
     * @return int
     */
    public function getDepartment()
    {
        return $this->department;
    }


    /**
     * @param int $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * @return int
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @param int $ext_account
     */
    public function setExtAccount($ext_account)
    {
        $this->ext_account = $ext_account;
    }


    /**
     * @return int
     */
    public function getExtAccount()
    {
        return $this->ext_account;
    }


    /**
     * @param int $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }


    /**
     * @return int
     */
    public function getFax()
    {
        return $this->fax;
    }


    /**
     * @param int $feed_hash
     */
    public function setFeedHash($feed_hash)
    {
        $this->feed_hash = $feed_hash;
    }


    /**
     * @return int
     */
    public function getFeedHash()
    {
        return $this->feed_hash;
    }


    /**
     * @param int $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }


    /**
     * @return int
     */
    public function getFirstname()
    {
        return $this->firstname;
    }


    /**
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }


    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }


    /**
     * @param int $hobby
     */
    public function setHobby($hobby)
    {
        $this->hobby = $hobby;
    }


    /**
     * @return int
     */
    public function getHobby()
    {
        return $this->hobby;
    }


    /**
     * @param int $i2passwd
     */
    public function setI2passwd($i2passwd)
    {
        $this->i2passwd = $i2passwd;
    }


    /**
     * @return int
     */
    public function getI2passwd()
    {
        return $this->i2passwd;
    }

    /**
     * @param int $inactivation_date
     */
    public function setInactivationDate($inactivation_date)
    {
        $this->inactivation_date = $inactivation_date;
    }


    /**
     * @return int
     */
    public function getInactivationDate()
    {
        return $this->inactivation_date;
    }


    /**
     * @param int $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }


    /**
     * @return int
     */
    public function getInstitution()
    {
        return $this->institution;
    }


    /**
     * @param int $is_self_registered
     */
    public function setIsSelfRegistered($is_self_registered)
    {
        $this->is_self_registered = $is_self_registered;
    }


    /**
     * @return int
     */
    public function getIsSelfRegistered()
    {
        return $this->is_self_registered;
    }


    /**
     * @param int $last_login
     */
    public function setLastLogin($last_login)
    {
        $this->last_login = $last_login;
    }


    /**
     * @return int
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }


    /**
     * @param int $last_password_change
     */
    public function setLastPasswordChange($last_password_change)
    {
        $this->last_password_change = $last_password_change;
    }


    /**
     * @return int
     */
    public function getLastPasswordChange()
    {
        return $this->last_password_change;
    }


    /**
     * @param int $last_update
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;
    }


    /**
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }


    /**
     * @param int $last_visited
     */
    public function setLastVisited($last_visited)
    {
        $this->last_visited = $last_visited;
    }


    /**
     * @return int
     */
    public function getLastVisited()
    {
        return $this->last_visited;
    }


    /**
     * @param int $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }


    /**
     * @return int
     */
    public function getLastname()
    {
        return $this->lastname;
    }


    /**
     * @param int $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }


    /**
     * @return int
     */
    public function getLatitude()
    {
        return $this->latitude;
    }


    /**
     * @param int $loc_zoom
     */
    public function setLocZoom($loc_zoom)
    {
        $this->loc_zoom = $loc_zoom;
    }


    /**
     * @return int
     */
    public function getLocZoom()
    {
        return $this->loc_zoom;
    }


    /**
     * @param int $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }


    /**
     * @return int
     */
    public function getLogin()
    {
        return $this->login;
    }


    /**
     * @param int $login_attempts
     */
    public function setLoginAttempts($login_attempts)
    {
        $this->login_attempts = $login_attempts;
    }


    /**
     * @return int
     */
    public function getLoginAttempts()
    {
        return $this->login_attempts;
    }


    /**
     * @param int $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }


    /**
     * @return int
     */
    public function getLongitude()
    {
        return $this->longitude;
    }


    /**
     * @param int $matriculation
     */
    public function setMatriculation($matriculation)
    {
        $this->matriculation = $matriculation;
    }


    /**
     * @return int
     */
    public function getMatriculation()
    {
        return $this->matriculation;
    }


    /**
     * @param int $passwd
     */
    public function setPasswd($passwd)
    {
        $this->passwd = $passwd;
    }


    /**
     * @return int
     */
    public function getPasswd()
    {
        return $this->passwd;
    }


    /**
     * @param int $phone_home
     */
    public function setPhoneHome($phone_home)
    {
        $this->phone_home = $phone_home;
    }


    /**
     * @return int
     */
    public function getPhoneHome()
    {
        return $this->phone_home;
    }


    /**
     * @param int $phone_mobile
     */
    public function setPhoneMobile($phone_mobile)
    {
        $this->phone_mobile = $phone_mobile;
    }


    /**
     * @return int
     */
    public function getPhoneMobile()
    {
        return $this->phone_mobile;
    }


    /**
     * @param int $phone_office
     */
    public function setPhoneOffice($phone_office)
    {
        $this->phone_office = $phone_office;
    }


    /**
     * @return int
     */
    public function getPhoneOffice()
    {
        return $this->phone_office;
    }


    /**
     * @param int $profile_incomplete
     */
    public function setProfileIncomplete($profile_incomplete)
    {
        $this->profile_incomplete = $profile_incomplete;
    }


    /**
     * @return int
     */
    public function getProfileIncomplete()
    {
        return $this->profile_incomplete;
    }


    /**
     * @param int $referral_comment
     */
    public function setReferralComment($referral_comment)
    {
        $this->referral_comment = $referral_comment;
    }


    /**
     * @return int
     */
    public function getReferralComment()
    {
        return $this->referral_comment;
    }


    /**
     * @param int $reg_hash
     */
    public function setRegHash($reg_hash)
    {
        $this->reg_hash = $reg_hash;
    }


    /**
     * @return int
     */
    public function getRegHash()
    {
        return $this->reg_hash;
    }


    /**
     * @param int $sel_country
     */
    public function setSelCountry($sel_country)
    {
        $this->sel_country = $sel_country;
    }


    /**
     * @return int
     */
    public function getSelCountry()
    {
        return $this->sel_country;
    }


    /**
     * @param int $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }


    /**
     * @return int
     */
    public function getStreet()
    {
        return $this->street;
    }


    /**
     * @param int $time_limit_from
     */
    public function setTimeLimitFrom($time_limit_from)
    {
        $this->time_limit_from = $time_limit_from;
    }


    /**
     * @return int
     */
    public function getTimeLimitFrom()
    {
        return $this->time_limit_from;
    }


    /**
     * @param int $time_limit_message
     */
    public function setTimeLimitMessage($time_limit_message)
    {
        $this->time_limit_message = $time_limit_message;
    }


    /**
     * @return int
     */
    public function getTimeLimitMessage()
    {
        return $this->time_limit_message;
    }


    /**
     * @param int $time_limit_owner
     */
    public function setTimeLimitOwner($time_limit_owner)
    {
        $this->time_limit_owner = $time_limit_owner;
    }


    /**
     * @return int
     */
    public function getTimeLimitOwner()
    {
        return $this->time_limit_owner;
    }


    /**
     * @param int $time_limit_unlimited
     */
    public function setTimeLimitUnlimited($time_limit_unlimited)
    {
        $this->time_limit_unlimited = $time_limit_unlimited;
    }


    /**
     * @return int
     */
    public function getTimeLimitUnlimited()
    {
        return $this->time_limit_unlimited;
    }


    /**
     * @param int $time_limit_until
     */
    public function setTimeLimitUntil($time_limit_until)
    {
        $this->time_limit_until = $time_limit_until;
    }


    /**
     * @return int
     */
    public function getTimeLimitUntil()
    {
        return $this->time_limit_until;
    }


    /**
     * @param int $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return int
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param int $usr_id
     */
    public function setUsrId($usr_id)
    {
        $this->usr_id = $usr_id;
    }


    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usr_id;
    }


    /**
     * @param int $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }


    /**
     * @return int
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }


    /**
     * @param int $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }


    /**
     * @return int
     */
    public function getKey()
    {
        return $this->key;
    }
}
