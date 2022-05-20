<?php
declare(strict_types=1);

namespace ILIAS\MyStaff\ListUsers;

use ilObjUser;

/**
 * Class ilMStListUser
 * @author Martin Studer <ms@studer-raimann.ch>
 */
final class ilMStListUser
{
    private int $usr_id;
    private int $gender;
    private int $time_limit_owner;
    private int $active;
    private string $login;
    private string $title;
    private string $hobby;
    private string $institution;
    private string $department;
    private string $street;
    private string $zipcode;
    private string $city;
    private string $country;
    private string $sel_country;
    private string $matriculation;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $phone;
    private string $mobile_phone;

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function setUsrId(int $usr_id) : void
    {
        $this->usr_id = $usr_id;
    }

    public function getTimeLimitOwner() : int
    {
        return $this->time_limit_owner;
    }

    public function setTimeLimitOwner(int $time_limit_owner) : void
    {
        $this->time_limit_owner = $time_limit_owner;
    }

    public function getActive() : int
    {
        return $this->active;
    }

    public function setActive(int $active) : void
    {
        $this->active = $active;
    }

    public function getLogin() : string
    {
        return $this->login;
    }

    public function setLogin(string $login) : void
    {
        $this->login = $login;
    }

    public function getFirstname() : string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname) : void
    {
        $this->firstname = $firstname;
    }

    public function getLastname() : string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname) : void
    {
        $this->lastname = $lastname;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    public function getPhone() : string
    {
        return $this->phone;
    }

    public function setPhone(string $phone) : void
    {
        $this->phone = $phone;
    }

    public function getMobilePhone() : string
    {
        return $this->mobile_phone;
    }

    public function setMobilePhone(string $mobile_phone) : void
    {
        $this->mobile_phone = $mobile_phone;
    }

    public function getGender() : int
    {
        return $this->gender;
    }

    public function setGender(int $gender) : void
    {
        $this->gender = $gender;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public function getHobby() : string
    {
        return $this->hobby;
    }

    public function setHobby(string $hobby) : void
    {
        $this->hobby = $hobby;
    }

    public function getInstitution() : string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution) : void
    {
        $this->institution = $institution;
    }

    public function getDepartment() : string
    {
        return $this->department;
    }

    public function setDepartment(string $department) : void
    {
        $this->department = $department;
    }

    public function getStreet() : string
    {
        return $this->street;
    }

    public function setStreet(string $street) : void
    {
        $this->street = $street;
    }

    public function getZipcode() : string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode) : void
    {
        $this->zipcode = $zipcode;
    }

    public function getCity() : string
    {
        return $this->city;
    }

    public function setCity(string $city) : void
    {
        $this->city = $city;
    }

    public function getCountry() : string
    {
        return $this->country;
    }

    public function setCountry(string $country) : void
    {
        $this->country = $country;
    }

    public function getSelCountry() : string
    {
        return $this->sel_country;
    }

    public function setSelCountry(string $sel_country) : void
    {
        $this->sel_country = $sel_country;
    }

    public function getMatriculation() : string
    {
        return $this->matriculation;
    }

    public function setMatriculation(string $matriculation) : void
    {
        $this->matriculation = $matriculation;
    }

    public function returnIlUserObj() : ilObjUser
    {
        return new ilObjUser($this->usr_id);
    }
}
