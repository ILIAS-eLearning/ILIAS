<?php

/**
 * Class ilMStListUser
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListUser
{

    /**
     * @var int
     */
    protected $usr_id;
    /**
     * @var int
     */
    protected $gender;
    /**
     * @var int
     */
    protected $time_limit_owner;
    /**
     * @var int
     */
    protected $active;
    /**
     * @var string
     */
    protected $login;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $hobby;
    /**
     * @var string
     */
    protected $institution;
    /**
     * @var string
     */
    protected $department;
    /**
     * @var string
     */
    protected $street;
    /**
     * @var string
     */
    protected $zipcode;
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $sel_country;
    /**
     * @var string
     */
    protected $matriculation;
    /**
     * @var string
     */
    protected $firstname;
    /**
     * @var string
     */
    protected $lastname;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $phone;
    /**
     * @var string
     */
    protected $mobile_phone;
    /**
     * @var ilObjUser
     */
    protected $il_user_obj;


    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usr_id;
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
    public function getTimeLimitOwner()
    {
        return $this->time_limit_owner;
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
    public function getActive()
    {
        return $this->active;
    }


    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
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
    public function getFirstname()
    {
        return $this->firstname;
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
    public function getLastname()
    {
        return $this->lastname;
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
    public function getEmail()
    {
        return $this->email;
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
    public function getPhone()
    {
        return $this->phone;
    }


    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }


    /**
     * @return string
     */
    public function getMobilePhone()
    {
        return $this->mobile_phone;
    }


    /**
     * @param string $mobile_phone
     */
    public function setMobilePhone($mobile_phone)
    {
        $this->mobile_phone = $mobile_phone;
    }


    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }


    /**
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
    public function getHobby()
    {
        return $this->hobby;
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
    public function getInstitution()
    {
        return $this->institution;
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
    public function getDepartment()
    {
        return $this->department;
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
    public function getStreet()
    {
        return $this->street;
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
    public function getZipcode()
    {
        return $this->zipcode;
    }


    /**
     * @param string $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }


    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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
    public function getCountry()
    {
        return $this->country;
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
    public function getSelCountry()
    {
        return $this->sel_country;
    }


    /**
     * @param string $sel_country
     */
    public function setSelCountry($sel_country)
    {
        $this->sel_country = $sel_country;
    }


    /**
     * @return string
     */
    public function getMatriculation()
    {
        return $this->matriculation;
    }


    /**
     * @param string $matriculation
     */
    public function setMatriculation($matriculation)
    {
        $this->matriculation = $matriculation;
    }


    /**
     * @return ilObjUser
     */
    public function returnIlUserObj()
    {
        $il_obj_user = new ilObjUser($this->usr_id);

        return $il_obj_user;
    }
}
