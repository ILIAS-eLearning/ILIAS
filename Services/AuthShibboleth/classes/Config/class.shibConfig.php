<?php

/**
 * Class shibConfig
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibConfig
{

    /**
     * @var string
     */
    protected $firstname = '';
    /**
     * @var bool
     */
    protected $update_firstname = false;
    /**
     * @var string
     */
    protected $lastname = '';
    /**
     * @var bool
     */
    protected $update_lastname = false;
    /**
     * @var string
     */
    protected $gender = '';
    /**
     * @var bool
     */
    protected $update_gender = false;
    /**
     * @var string
     */
    protected $login = '';
    /**
     * @var bool
     */
    protected $update_login = false;
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var
     */
    protected $update_title;
    /**
     * @var string
     */
    protected $institution = '';
    /**
     * @var bool
     */
    protected $update_institution = false;
    /**
     * @var string
     */
    protected $department = '';
    /**
     * @var bool
     */
    protected $update_department = false;
    /**
     * @var string
     */
    protected $street = '';
    /**
     * @var bool
     */
    protected $update_street = false;
    /**
     * @var string
     */
    protected $city = '';
    /**
     * @var bool
     */
    protected $update_city = false;
    /**
     * @var int
     */
    protected $zipcode = 0;
    /**
     * @var bool
     */
    protected $update_zipcode = false;
    /**
     * @var string
     */
    protected $country = '';
    /**
     * @var bool
     */
    protected $update_country = false;
    /**
     * @var string
     */
    protected $phone_office = '';
    /**
     * @var bool
     */
    protected $update_phone_office = false;
    /**
     * @var string
     */
    protected $phone_home = '';
    /**
     * @var bool
     */
    protected $update_phone_home = false;
    /**
     * @var string
     */
    protected $phone_mobile = '';
    /**
     * @var bool
     */
    protected $update_phone_mobile = false;
    /**
     * @var string
     */
    protected $fax = '';
    /**
     * @var bool
     */
    protected $update_fax = false;
    /**
     * @var string
     */
    protected $matriculation = '';
    /**
     * @var bool
     */
    protected $update_matriculation = false;
    /**
     * @var string
     */
    protected $email = '';
    /**
     * @var bool
     */
    protected $update_email = false;
    /**
     * @var string
     */
    protected $hobby = '';
    /**
     * @var bool
     */
    protected $update_hobby = false;
    /**
     * @var string
     */
    protected $language = '';
    /**
     * @var bool
     */
    protected $update_language = false;
    /**
     * @var string
     */
    protected $data_conv = '';
    /**
     * @var bool
     */
    protected $update_data_conv = false;
    /**
     * @var int
     */
    protected $user_default_role = 4;
    /**
     * @var bool
     */
    protected $activate_new = false;
    /**
     * @var bool
     */
    protected static $cache = null;


    protected function __construct()
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];
        /**
         * @var $ilSetting ilSetting
         */
        foreach (get_class_vars('shibConfig') as $field => $val) {
            $str = $ilSetting->get('shib_' . $field);
            if ($str !== null) {
                $this->{$field} = $str;
            }
        }

        if (!in_array(strtolower($this->getGender()), ['n', 'm', 'f'])) {
            $this->setGender(null);
        }
    }


    /**
     * @return bool|\shibConfig
     */
    public static function getInstance()
    {
        if (!isset(self::$cache)) {
            self::$cache = new self();
        }

        return self::$cache;
    }


    /**
     * @param $key
     *
     * @return mixed
     */
    public function getValueByKey($key)
    {
        return $this->{$key};
    }


    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }


    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }


    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }


    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * @param string $data_conv
     */
    public function setDataConv($data_conv)
    {
        $this->data_conv = $data_conv;
    }


    /**
     * @return string
     */
    public function getDataConv()
    {
        return $this->data_conv;
    }


    /**
     * @param string $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }


    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }


    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }


    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }


    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }


    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }


    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }


    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }


    /**
     * @param string $hobby
     */
    public function setHobby($hobby)
    {
        $this->hobby = $hobby;
    }


    /**
     * @return string
     */
    public function getHobby()
    {
        return $this->hobby;
    }


    /**
     * @param string $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }


    /**
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }


    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }


    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }


    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }


    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }


    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }


    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }


    /**
     * @param string $matriculation
     */
    public function setMatriculation($matriculation)
    {
        $this->matriculation = $matriculation;
    }


    /**
     * @return string
     */
    public function getMatriculation()
    {
        return $this->matriculation;
    }


    /**
     * @param string $phone_home
     */
    public function setPhoneHome($phone_home)
    {
        $this->phone_home = $phone_home;
    }


    /**
     * @return string
     */
    public function getPhoneHome()
    {
        return $this->phone_home;
    }


    /**
     * @param string $phone_mobile
     */
    public function setPhoneMobile($phone_mobile)
    {
        $this->phone_mobile = $phone_mobile;
    }


    /**
     * @return string
     */
    public function getPhoneMobile()
    {
        return $this->phone_mobile;
    }


    /**
     * @param string $phone_office
     */
    public function setPhoneOffice($phone_office)
    {
        $this->phone_office = $phone_office;
    }


    /**
     * @return string
     */
    public function getPhoneOffice()
    {
        return $this->phone_office;
    }


    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }


    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param boolean $update_city
     */
    public function setUpdateCity($update_city)
    {
        $this->update_city = $update_city;
    }


    /**
     * @return boolean
     */
    public function getUpdateCity()
    {
        return $this->update_city;
    }


    /**
     * @param boolean $update_country
     */
    public function setUpdateCountry($update_country)
    {
        $this->update_country = $update_country;
    }


    /**
     * @return boolean
     */
    public function getUpdateCountry()
    {
        return $this->update_country;
    }


    /**
     * @param boolean $update_data_conv
     */
    public function setUpdateDataConv($update_data_conv)
    {
        $this->update_data_conv = $update_data_conv;
    }


    /**
     * @return boolean
     */
    public function getUpdateDataConv()
    {
        return $this->update_data_conv;
    }


    /**
     * @param boolean $update_department
     */
    public function setUpdateDepartment($update_department)
    {
        $this->update_department = $update_department;
    }


    /**
     * @return boolean
     */
    public function getUpdateDepartment()
    {
        return $this->update_department;
    }


    /**
     * @param boolean $update_email
     */
    public function setUpdateEmail($update_email)
    {
        $this->update_email = $update_email;
    }


    /**
     * @return boolean
     */
    public function getUpdateEmail()
    {
        return $this->update_email;
    }


    /**
     * @param boolean $update_fax
     */
    public function setUpdateFax($update_fax)
    {
        $this->update_fax = $update_fax;
    }


    /**
     * @return boolean
     */
    public function getUpdateFax()
    {
        return $this->update_fax;
    }


    /**
     * @param boolean $update_gender
     */
    public function setUpdateGender($update_gender)
    {
        $this->update_gender = $update_gender;
    }


    /**
     * @return boolean
     */
    public function getUpdateGender()
    {
        return $this->update_gender;
    }


    /**
     * @param boolean $update_hobby
     */
    public function setUpdateHobby($update_hobby)
    {
        $this->update_hobby = $update_hobby;
    }


    /**
     * @return boolean
     */
    public function getUpdateHobby()
    {
        return $this->update_hobby;
    }


    /**
     * @param boolean $update_institution
     */
    public function setUpdateInstitution($update_institution)
    {
        $this->update_institution = $update_institution;
    }


    /**
     * @return boolean
     */
    public function getUpdateInstitution()
    {
        return $this->update_institution;
    }


    /**
     * @param boolean $update_language
     */
    public function setUpdateLanguage($update_language)
    {
        $this->update_language = $update_language;
    }


    /**
     * @return boolean
     */
    public function getUpdateLanguage()
    {
        return $this->update_language;
    }


    /**
     * @param boolean $update_login
     */
    public function setUpdateLogin($update_login)
    {
        $this->update_login = $update_login;
    }


    /**
     * @return boolean
     */
    public function getUpdateLogin()
    {
        return $this->update_login;
    }


    /**
     * @param boolean $update_matriculation
     */
    public function setUpdateMatriculation($update_matriculation)
    {
        $this->update_matriculation = $update_matriculation;
    }


    /**
     * @return boolean
     */
    public function getUpdateMatriculation()
    {
        return $this->update_matriculation;
    }


    /**
     * @param boolean $update_phone_home
     */
    public function setUpdatePhoneHome($update_phone_home)
    {
        $this->update_phone_home = $update_phone_home;
    }


    /**
     * @return boolean
     */
    public function getUpdatePhoneHome()
    {
        return $this->update_phone_home;
    }


    /**
     * @param boolean $update_phone_mobile
     */
    public function setUpdatePhoneMobile($update_phone_mobile)
    {
        $this->update_phone_mobile = $update_phone_mobile;
    }


    /**
     * @return boolean
     */
    public function getUpdatePhoneMobile()
    {
        return $this->update_phone_mobile;
    }


    /**
     * @param boolean $update_phone_office
     */
    public function setUpdatePhoneOffice($update_phone_office)
    {
        $this->update_phone_office = $update_phone_office;
    }


    /**
     * @return boolean
     */
    public function getUpdatePhoneOffice()
    {
        return $this->update_phone_office;
    }


    /**
     * @param boolean $update_street
     */
    public function setUpdateStreet($update_street)
    {
        $this->update_street = $update_street;
    }


    /**
     * @return boolean
     */
    public function getUpdateStreet()
    {
        return $this->update_street;
    }


    /**
     * @param mixed $update_title
     */
    public function setUpdateTitle($update_title)
    {
        $this->update_title = $update_title;
    }


    /**
     * @return mixed
     */
    public function getUpdateTitle()
    {
        return $this->update_title;
    }


    /**
     * @param boolean $update_zipcode
     */
    public function setUpdateZipcode($update_zipcode)
    {
        $this->update_zipcode = $update_zipcode;
    }


    /**
     * @return boolean
     */
    public function getUpdateZipcode()
    {
        return $this->update_zipcode;
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
     * @param int $user_default_role
     */
    public function setUserDefaultRole($user_default_role)
    {
        $this->user_default_role = $user_default_role;
    }


    /**
     * @return int
     */
    public function getUserDefaultRole()
    {
        return $this->user_default_role;
    }


    /**
     * @param boolean $update_firstname
     */
    public function setUpdateFirstname($update_firstname)
    {
        $this->update_firstname = $update_firstname;
    }


    /**
     * @return boolean
     */
    public function getUpdateFirstname()
    {
        return $this->update_firstname;
    }


    /**
     * @param boolean $update_lastname
     */
    public function setUpdateLastname($update_lastname)
    {
        $this->update_lastname = $update_lastname;
    }


    /**
     * @return boolean
     */
    public function getUpdateLastname()
    {
        return $this->update_lastname;
    }


    /**
     * @return bool
     */
    public function isActivateNew()
    {
        return $this->activate_new;
    }


    /**
     * @param bool $activate_new
     */
    public function setActivateNew($activate_new)
    {
        $this->activate_new = $activate_new;
    }
}
