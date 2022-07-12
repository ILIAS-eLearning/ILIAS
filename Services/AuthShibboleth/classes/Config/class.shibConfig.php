<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class shibConfig
 * @deprecated
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibConfig
{
    protected string $firstname = '';
    protected bool $update_firstname = false;
    protected string $lastname = '';
    protected bool $update_lastname = false;
    protected string $gender = '';
    protected bool $update_gender = false;
    protected string $login = '';
    protected bool $update_login = false;
    protected string $title = '';
    protected bool $update_title = false;
    protected string $institution = '';
    protected bool $update_institution = false;
    protected string $department = '';
    protected bool $update_department = false;
    protected string $street = '';
    protected bool $update_street = false;
    protected string $city = '';
    protected bool $update_city = false;
    protected string $zipcode = '';
    protected bool $update_zipcode = false;
    protected string $country = '';
    protected bool $update_country = false;
    protected string $phone_office = '';
    protected bool $update_phone_office = false;
    protected string $phone_home = '';
    protected bool $update_phone_home = false;
    protected string $phone_mobile = '';
    protected bool $update_phone_mobile = false;
    protected string $fax = '';
    protected bool $update_fax = false;
    protected string $matriculation = '';
    protected bool $update_matriculation = false;
    protected string $email = '';
    protected bool $update_email = false;
    protected string $hobby = '';
    protected bool $update_hobby = false;
    protected string $language = '';
    protected bool $update_language = false;
    protected string $data_conv = '';
    protected bool $update_data_conv = false;
    protected string $user_default_role = '4';
    protected bool $activate_new = false;
    protected static ?shibConfig $cache = null;

    protected function __construct()
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];
        /**
         * @var $ilSetting ilSetting
         */
        foreach (array_keys(get_class_vars('shibConfig')) as $field) {
            $str = $ilSetting->get('shib_' . $field);
            if ($str !== null) {
                $this->{$field} = $str;
            }
        }

        if (!in_array(strtolower($this->getGender()), ['n', 'm', 'f'])) {
            $this->setGender('');
        }
    }

    public static function getInstance() : shibConfig
    {
        if (!isset(self::$cache)) {
            self::$cache = new self();
        }

        return self::$cache;
    }

    /**
     * @return mixed
     */
    public function getValueByKey(string $key)
    {
        if ($key === 'cache') {
            return null;
        }
        return $this->{$key};
    }

    public function setCity(string $city) : void
    {
        $this->city = $city;
    }

    public function getCity() : string
    {
        return $this->city;
    }

    public function setCountry(string $country) : void
    {
        $this->country = $country;
    }

    public function getCountry() : string
    {
        return $this->country;
    }

    public function setDataConv(string $data_conv) : void
    {
        $this->data_conv = $data_conv;
    }

    public function getDataConv() : string
    {
        return $this->data_conv;
    }

    public function setDepartment(string $department) : void
    {
        $this->department = $department;
    }

    public function getDepartment() : string
    {
        return $this->department;
    }

    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setFax(string $fax) : void
    {
        $this->fax = $fax;
    }

    public function getFax() : string
    {
        return $this->fax;
    }

    public function setFirstname(string $firstname) : void
    {
        $this->firstname = $firstname;
    }

    public function getFirstname() : string
    {
        return $this->firstname;
    }

    public function setGender(string $gender) : void
    {
        $this->gender = $gender;
    }

    public function getGender() : string
    {
        return $this->gender;
    }

    public function setHobby(string $hobby) : void
    {
        $this->hobby = $hobby;
    }

    public function getHobby() : string
    {
        return $this->hobby;
    }

    public function setInstitution(string $institution) : void
    {
        $this->institution = $institution;
    }

    public function getInstitution() : string
    {
        return $this->institution;
    }

    public function setLanguage(string $language) : void
    {
        $this->language = $language;
    }

    public function getLanguage() : string
    {
        return $this->language;
    }

    public function setLastname(string $lastname) : void
    {
        $this->lastname = $lastname;
    }

    public function getLastname() : string
    {
        return $this->lastname;
    }

    public function setLogin(string $login) : void
    {
        $this->login = $login;
    }

    public function getLogin() : string
    {
        return $this->login;
    }

    public function setMatriculation(string $matriculation) : void
    {
        $this->matriculation = $matriculation;
    }

    public function getMatriculation() : string
    {
        return $this->matriculation;
    }

    public function setPhoneHome(string $phone_home) : void
    {
        $this->phone_home = $phone_home;
    }

    public function getPhoneHome() : string
    {
        return $this->phone_home;
    }

    public function setPhoneMobile(string $phone_mobile) : void
    {
        $this->phone_mobile = $phone_mobile;
    }

    public function getPhoneMobile() : string
    {
        return $this->phone_mobile;
    }

    public function setPhoneOffice(string $phone_office) : void
    {
        $this->phone_office = $phone_office;
    }

    public function getPhoneOffice() : string
    {
        return $this->phone_office;
    }

    public function setStreet(string $street) : void
    {
        $this->street = $street;
    }

    public function getStreet() : string
    {
        return $this->street;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setUpdateCity(bool $update_city) : void
    {
        $this->update_city = $update_city;
    }

    public function getUpdateCity() : bool
    {
        return $this->update_city;
    }

    public function setUpdateCountry(bool $update_country) : void
    {
        $this->update_country = $update_country;
    }

    public function getUpdateCountry() : bool
    {
        return $this->update_country;
    }

    public function setUpdateDataConv(bool $update_data_conv) : void
    {
        $this->update_data_conv = $update_data_conv;
    }

    public function getUpdateDataConv() : bool
    {
        return $this->update_data_conv;
    }

    public function setUpdateDepartment(bool $update_department) : void
    {
        $this->update_department = $update_department;
    }

    public function getUpdateDepartment() : bool
    {
        return $this->update_department;
    }

    public function setUpdateEmail(bool $update_email) : void
    {
        $this->update_email = $update_email;
    }

    public function getUpdateEmail() : bool
    {
        return $this->update_email;
    }

    public function setUpdateFax(bool $update_fax) : void
    {
        $this->update_fax = $update_fax;
    }

    public function getUpdateFax() : bool
    {
        return $this->update_fax;
    }

    public function setUpdateGender(bool $update_gender) : void
    {
        $this->update_gender = $update_gender;
    }

    public function getUpdateGender() : bool
    {
        return $this->update_gender;
    }

    public function setUpdateHobby(bool $update_hobby) : void
    {
        $this->update_hobby = $update_hobby;
    }

    public function getUpdateHobby() : bool
    {
        return $this->update_hobby;
    }

    public function setUpdateInstitution(bool $update_institution) : void
    {
        $this->update_institution = $update_institution;
    }

    public function getUpdateInstitution() : bool
    {
        return $this->update_institution;
    }

    public function setUpdateLanguage(bool $update_language) : void
    {
        $this->update_language = $update_language;
    }

    public function getUpdateLanguage() : bool
    {
        return $this->update_language;
    }

    public function setUpdateLogin(bool $update_login) : void
    {
        $this->update_login = $update_login;
    }

    public function getUpdateLogin() : bool
    {
        return $this->update_login;
    }

    public function setUpdateMatriculation(bool $update_matriculation) : void
    {
        $this->update_matriculation = $update_matriculation;
    }

    public function getUpdateMatriculation() : bool
    {
        return $this->update_matriculation;
    }

    public function setUpdatePhoneHome(bool $update_phone_home) : void
    {
        $this->update_phone_home = $update_phone_home;
    }

    public function getUpdatePhoneHome() : bool
    {
        return $this->update_phone_home;
    }

    public function setUpdatePhoneMobile(bool $update_phone_mobile) : void
    {
        $this->update_phone_mobile = $update_phone_mobile;
    }

    public function getUpdatePhoneMobile() : bool
    {
        return $this->update_phone_mobile;
    }

    public function setUpdatePhoneOffice(bool $update_phone_office) : void
    {
        $this->update_phone_office = $update_phone_office;
    }

    public function getUpdatePhoneOffice() : bool
    {
        return $this->update_phone_office;
    }

    public function setUpdateStreet(bool $update_street) : void
    {
        $this->update_street = $update_street;
    }

    public function getUpdateStreet() : bool
    {
        return $this->update_street;
    }

    /**
     * @param mixed $update_title
     */
    public function setUpdateTitle(bool $update_title) : void
    {
        $this->update_title = $update_title;
    }

    /**
     * @return mixed
     */
    public function getUpdateTitle() : bool
    {
        return $this->update_title;
    }

    public function setUpdateZipcode(bool $update_zipcode) : void
    {
        $this->update_zipcode = $update_zipcode;
    }

    public function getUpdateZipcode() : bool
    {
        return $this->update_zipcode;
    }

    public function setZipcode(string $zipcode) : void
    {
        $this->zipcode = $zipcode;
    }

    public function getZipcode() : string
    {
        return $this->zipcode;
    }

    public function setUserDefaultRole(int $user_default_role) : void
    {
        $this->user_default_role = $user_default_role;
    }

    public function getUserDefaultRole() : int
    {
        return $this->user_default_role;
    }

    public function setUpdateFirstname(bool $update_firstname) : void
    {
        $this->update_firstname = $update_firstname;
    }

    public function getUpdateFirstname() : bool
    {
        return $this->update_firstname;
    }

    public function setUpdateLastname(bool $update_lastname) : void
    {
        $this->update_lastname = $update_lastname;
    }

    public function getUpdateLastname() : bool
    {
        return $this->update_lastname;
    }

    public function isActivateNew() : bool
    {
        return $this->activate_new;
    }

    public function setActivateNew(bool $activate_new) : void
    {
        $this->activate_new = $activate_new;
    }
}
