<?php

namespace ILIAS\MyStaff\ListUsers;

use ilObjUser;

/**
 * Class ilMStListUser
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListUser
{
    protected int $usr_id;
    protected int $gender;
    protected int $time_limit_owner;
    protected int $active;
    protected string $login;
    protected string $title;
    protected string $hobby;
    protected string $institution;
    protected string $department;
    protected string $street;
    protected string $zipcode;
    protected string $city;
    protected string $country;
    protected string $sel_country;
    protected string $matriculation;
    protected string $firstname;
    protected string $lastname;
    protected string $email;
    protected string $phone;
    protected string $mobile_phone;
    protected ilObjUser $il_user_obj;

    final public function getUsrId() : int
    {
        return $this->usr_id;
    }

    final public function setUsrId(int $usr_id)
    {
        $this->usr_id = $usr_id;
    }

    final public function getTimeLimitOwner() : int
    {
        return $this->time_limit_owner;
    }

    final public function setTimeLimitOwner(int $time_limit_owner)
    {
        $this->time_limit_owner = $time_limit_owner;
    }

    final  public function getActive() : int
    {
        return $this->active;
    }

    final public function setActive(int $active)
    {
        $this->active = $active;
    }

    final  public function getLogin() : string
    {
        return $this->login;
    }

    final  public function setLogin(string $login)
    {
        $this->login = $login;
    }

    final public function getFirstname() : string
    {
        return $this->firstname;
    }

    final public function setFirstname(string $firstname)
    {
        $this->firstname = $firstname;
    }

    final public function getLastname() : string
    {
        return $this->lastname;
    }

    final public function setLastname(string $lastname)
    {
        $this->lastname = $lastname;
    }

    final public function getEmail() : string
    {
        return $this->email;
    }

    final public function setEmail(string $email)
    {
        $this->email = $email;
    }

    final public function getPhone() : string
    {
        return $this->phone;
    }

    final public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    final public function getMobilePhone() : string
    {
        return $this->mobile_phone;
    }

    final public function setMobilePhone(string $mobile_phone)
    {
        $this->mobile_phone = $mobile_phone;
    }

    final  public function getGender() : int
    {
        return $this->gender;
    }

    final  public function setGender(int $gender)
    {
        $this->gender = $gender;
    }

    final  public function getTitle() : string
    {
        return $this->title;
    }

    final  public function setTitle(string $title)
    {
        $this->title = $title;
    }

    final public function getHobby() : string
    {
        return $this->hobby;
    }

    final public function setHobby(string $hobby)
    {
        $this->hobby = $hobby;
    }

    final public function getInstitution() : string
    {
        return $this->institution;
    }

    final public function setInstitution(string $institution)
    {
        $this->institution = $institution;
    }

    final public function getDepartment() : string
    {
        return $this->department;
    }

    final public function setDepartment(string $department)
    {
        $this->department = $department;
    }

    final public function getStreet() : string
    {
        return $this->street;
    }

    final public function setStreet(string $street)
    {
        $this->street = $street;
    }

    final public function getZipcode() : string
    {
        return $this->zipcode;
    }

    final public function setZipcode(string $zipcode)
    {
        $this->zipcode = $zipcode;
    }

    final public function getCity() : string
    {
        return $this->city;
    }

    final  public function setCity(string $city)
    {
        $this->city = $city;
    }

    final  public function getCountry() : string
    {
        return $this->country;
    }

    final public function setCountry(string $country)
    {
        $this->country = $country;
    }

    final public function getSelCountry() : string
    {
        return $this->sel_country;
    }

    final public function setSelCountry(string $sel_country)
    {
        $this->sel_country = $sel_country;
    }

    final public function getMatriculation() : string
    {
        return $this->matriculation;
    }

    final public function setMatriculation(string $matriculation)
    {
        $this->matriculation = $matriculation;
    }

    final public function returnIlUserObj() : ilObjUser
    {
        return new ilObjUser($this->usr_id);
    }
}
