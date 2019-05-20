<?php



/**
 * UsrData
 */
class UsrData
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string|null
     */
    private $login;

    /**
     * @var string|null
     */
    private $passwd;

    /**
     * @var string|null
     */
    private $firstname;

    /**
     * @var string|null
     */
    private $lastname;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $gender = 'm';

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $institution;

    /**
     * @var string|null
     */
    private $street;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var string|null
     */
    private $zipcode;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var string|null
     */
    private $phoneOffice;

    /**
     * @var \DateTime|null
     */
    private $lastLogin;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var \DateTime|null
     */
    private $createDate;

    /**
     * @var string|null
     */
    private $hobby;

    /**
     * @var string|null
     */
    private $department;

    /**
     * @var string|null
     */
    private $phoneHome;

    /**
     * @var string|null
     */
    private $phoneMobile;

    /**
     * @var string|null
     */
    private $fax;

    /**
     * @var int|null
     */
    private $timeLimitOwner = '0';

    /**
     * @var int|null
     */
    private $timeLimitUnlimited = '0';

    /**
     * @var int|null
     */
    private $timeLimitFrom = '0';

    /**
     * @var int|null
     */
    private $timeLimitUntil = '0';

    /**
     * @var int|null
     */
    private $timeLimitMessage = '0';

    /**
     * @var string|null
     */
    private $referralComment;

    /**
     * @var string|null
     */
    private $matriculation;

    /**
     * @var int
     */
    private $active = '0';

    /**
     * @var \DateTime|null
     */
    private $approveDate;

    /**
     * @var \DateTime|null
     */
    private $agreeDate;

    /**
     * @var string|null
     */
    private $clientIp;

    /**
     * @var string|null
     */
    private $authMode = 'default';

    /**
     * @var int|null
     */
    private $profileIncomplete = '0';

    /**
     * @var string|null
     */
    private $extAccount;

    /**
     * @var string|null
     */
    private $feedHash;

    /**
     * @var string|null
     */
    private $latitude;

    /**
     * @var string|null
     */
    private $longitude;

    /**
     * @var int
     */
    private $locZoom = '0';

    /**
     * @var bool
     */
    private $loginAttempts = '0';

    /**
     * @var int
     */
    private $lastPasswordChange = '0';

    /**
     * @var string|null
     */
    private $regHash;

    /**
     * @var \DateTime|null
     */
    private $birthday;

    /**
     * @var string|null
     */
    private $selCountry;

    /**
     * @var string|null
     */
    private $lastVisited;

    /**
     * @var \DateTime|null
     */
    private $inactivationDate;

    /**
     * @var bool
     */
    private $isSelfRegistered = '0';

    /**
     * @var string|null
     */
    private $passwdEncType;

    /**
     * @var string|null
     */
    private $passwdSalt;

    /**
     * @var string|null
     */
    private $secondEmail;

    /**
     * @var \DateTime|null
     */
    private $firstLogin;

    /**
     * @var \DateTime|null
     */
    private $lastProfilePrompt;

    /**
     * @var bool
     */
    private $passwdPolicyReset = '0';


    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set login.
     *
     * @param string|null $login
     *
     * @return UsrData
     */
    public function setLogin($login = null)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login.
     *
     * @return string|null
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set passwd.
     *
     * @param string|null $passwd
     *
     * @return UsrData
     */
    public function setPasswd($passwd = null)
    {
        $this->passwd = $passwd;

        return $this;
    }

    /**
     * Get passwd.
     *
     * @return string|null
     */
    public function getPasswd()
    {
        return $this->passwd;
    }

    /**
     * Set firstname.
     *
     * @param string|null $firstname
     *
     * @return UsrData
     */
    public function setFirstname($firstname = null)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname.
     *
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname.
     *
     * @param string|null $lastname
     *
     * @return UsrData
     */
    public function setLastname($lastname = null)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname.
     *
     * @return string|null
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return UsrData
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set gender.
     *
     * @param string|null $gender
     *
     * @return UsrData
     */
    public function setGender($gender = null)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender.
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set email.
     *
     * @param string|null $email
     *
     * @return UsrData
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set institution.
     *
     * @param string|null $institution
     *
     * @return UsrData
     */
    public function setInstitution($institution = null)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution.
     *
     * @return string|null
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set street.
     *
     * @param string|null $street
     *
     * @return UsrData
     */
    public function setStreet($street = null)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street.
     *
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city.
     *
     * @param string|null $city
     *
     * @return UsrData
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zipcode.
     *
     * @param string|null $zipcode
     *
     * @return UsrData
     */
    public function setZipcode($zipcode = null)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode.
     *
     * @return string|null
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set country.
     *
     * @param string|null $country
     *
     * @return UsrData
     */
    public function setCountry($country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set phoneOffice.
     *
     * @param string|null $phoneOffice
     *
     * @return UsrData
     */
    public function setPhoneOffice($phoneOffice = null)
    {
        $this->phoneOffice = $phoneOffice;

        return $this;
    }

    /**
     * Get phoneOffice.
     *
     * @return string|null
     */
    public function getPhoneOffice()
    {
        return $this->phoneOffice;
    }

    /**
     * Set lastLogin.
     *
     * @param \DateTime|null $lastLogin
     *
     * @return UsrData
     */
    public function setLastLogin($lastLogin = null)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin.
     *
     * @return \DateTime|null
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return UsrData
     */
    public function setLastUpdate($lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime|null $createDate
     *
     * @return UsrData
     */
    public function setCreateDate($createDate = null)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime|null
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set hobby.
     *
     * @param string|null $hobby
     *
     * @return UsrData
     */
    public function setHobby($hobby = null)
    {
        $this->hobby = $hobby;

        return $this;
    }

    /**
     * Get hobby.
     *
     * @return string|null
     */
    public function getHobby()
    {
        return $this->hobby;
    }

    /**
     * Set department.
     *
     * @param string|null $department
     *
     * @return UsrData
     */
    public function setDepartment($department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department.
     *
     * @return string|null
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set phoneHome.
     *
     * @param string|null $phoneHome
     *
     * @return UsrData
     */
    public function setPhoneHome($phoneHome = null)
    {
        $this->phoneHome = $phoneHome;

        return $this;
    }

    /**
     * Get phoneHome.
     *
     * @return string|null
     */
    public function getPhoneHome()
    {
        return $this->phoneHome;
    }

    /**
     * Set phoneMobile.
     *
     * @param string|null $phoneMobile
     *
     * @return UsrData
     */
    public function setPhoneMobile($phoneMobile = null)
    {
        $this->phoneMobile = $phoneMobile;

        return $this;
    }

    /**
     * Get phoneMobile.
     *
     * @return string|null
     */
    public function getPhoneMobile()
    {
        return $this->phoneMobile;
    }

    /**
     * Set fax.
     *
     * @param string|null $fax
     *
     * @return UsrData
     */
    public function setFax($fax = null)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax.
     *
     * @return string|null
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set timeLimitOwner.
     *
     * @param int|null $timeLimitOwner
     *
     * @return UsrData
     */
    public function setTimeLimitOwner($timeLimitOwner = null)
    {
        $this->timeLimitOwner = $timeLimitOwner;

        return $this;
    }

    /**
     * Get timeLimitOwner.
     *
     * @return int|null
     */
    public function getTimeLimitOwner()
    {
        return $this->timeLimitOwner;
    }

    /**
     * Set timeLimitUnlimited.
     *
     * @param int|null $timeLimitUnlimited
     *
     * @return UsrData
     */
    public function setTimeLimitUnlimited($timeLimitUnlimited = null)
    {
        $this->timeLimitUnlimited = $timeLimitUnlimited;

        return $this;
    }

    /**
     * Get timeLimitUnlimited.
     *
     * @return int|null
     */
    public function getTimeLimitUnlimited()
    {
        return $this->timeLimitUnlimited;
    }

    /**
     * Set timeLimitFrom.
     *
     * @param int|null $timeLimitFrom
     *
     * @return UsrData
     */
    public function setTimeLimitFrom($timeLimitFrom = null)
    {
        $this->timeLimitFrom = $timeLimitFrom;

        return $this;
    }

    /**
     * Get timeLimitFrom.
     *
     * @return int|null
     */
    public function getTimeLimitFrom()
    {
        return $this->timeLimitFrom;
    }

    /**
     * Set timeLimitUntil.
     *
     * @param int|null $timeLimitUntil
     *
     * @return UsrData
     */
    public function setTimeLimitUntil($timeLimitUntil = null)
    {
        $this->timeLimitUntil = $timeLimitUntil;

        return $this;
    }

    /**
     * Get timeLimitUntil.
     *
     * @return int|null
     */
    public function getTimeLimitUntil()
    {
        return $this->timeLimitUntil;
    }

    /**
     * Set timeLimitMessage.
     *
     * @param int|null $timeLimitMessage
     *
     * @return UsrData
     */
    public function setTimeLimitMessage($timeLimitMessage = null)
    {
        $this->timeLimitMessage = $timeLimitMessage;

        return $this;
    }

    /**
     * Get timeLimitMessage.
     *
     * @return int|null
     */
    public function getTimeLimitMessage()
    {
        return $this->timeLimitMessage;
    }

    /**
     * Set referralComment.
     *
     * @param string|null $referralComment
     *
     * @return UsrData
     */
    public function setReferralComment($referralComment = null)
    {
        $this->referralComment = $referralComment;

        return $this;
    }

    /**
     * Get referralComment.
     *
     * @return string|null
     */
    public function getReferralComment()
    {
        return $this->referralComment;
    }

    /**
     * Set matriculation.
     *
     * @param string|null $matriculation
     *
     * @return UsrData
     */
    public function setMatriculation($matriculation = null)
    {
        $this->matriculation = $matriculation;

        return $this;
    }

    /**
     * Get matriculation.
     *
     * @return string|null
     */
    public function getMatriculation()
    {
        return $this->matriculation;
    }

    /**
     * Set active.
     *
     * @param int $active
     *
     * @return UsrData
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set approveDate.
     *
     * @param \DateTime|null $approveDate
     *
     * @return UsrData
     */
    public function setApproveDate($approveDate = null)
    {
        $this->approveDate = $approveDate;

        return $this;
    }

    /**
     * Get approveDate.
     *
     * @return \DateTime|null
     */
    public function getApproveDate()
    {
        return $this->approveDate;
    }

    /**
     * Set agreeDate.
     *
     * @param \DateTime|null $agreeDate
     *
     * @return UsrData
     */
    public function setAgreeDate($agreeDate = null)
    {
        $this->agreeDate = $agreeDate;

        return $this;
    }

    /**
     * Get agreeDate.
     *
     * @return \DateTime|null
     */
    public function getAgreeDate()
    {
        return $this->agreeDate;
    }

    /**
     * Set clientIp.
     *
     * @param string|null $clientIp
     *
     * @return UsrData
     */
    public function setClientIp($clientIp = null)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * Get clientIp.
     *
     * @return string|null
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * Set authMode.
     *
     * @param string|null $authMode
     *
     * @return UsrData
     */
    public function setAuthMode($authMode = null)
    {
        $this->authMode = $authMode;

        return $this;
    }

    /**
     * Get authMode.
     *
     * @return string|null
     */
    public function getAuthMode()
    {
        return $this->authMode;
    }

    /**
     * Set profileIncomplete.
     *
     * @param int|null $profileIncomplete
     *
     * @return UsrData
     */
    public function setProfileIncomplete($profileIncomplete = null)
    {
        $this->profileIncomplete = $profileIncomplete;

        return $this;
    }

    /**
     * Get profileIncomplete.
     *
     * @return int|null
     */
    public function getProfileIncomplete()
    {
        return $this->profileIncomplete;
    }

    /**
     * Set extAccount.
     *
     * @param string|null $extAccount
     *
     * @return UsrData
     */
    public function setExtAccount($extAccount = null)
    {
        $this->extAccount = $extAccount;

        return $this;
    }

    /**
     * Get extAccount.
     *
     * @return string|null
     */
    public function getExtAccount()
    {
        return $this->extAccount;
    }

    /**
     * Set feedHash.
     *
     * @param string|null $feedHash
     *
     * @return UsrData
     */
    public function setFeedHash($feedHash = null)
    {
        $this->feedHash = $feedHash;

        return $this;
    }

    /**
     * Get feedHash.
     *
     * @return string|null
     */
    public function getFeedHash()
    {
        return $this->feedHash;
    }

    /**
     * Set latitude.
     *
     * @param string|null $latitude
     *
     * @return UsrData
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param string|null $longitude
     *
     * @return UsrData
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set locZoom.
     *
     * @param int $locZoom
     *
     * @return UsrData
     */
    public function setLocZoom($locZoom)
    {
        $this->locZoom = $locZoom;

        return $this;
    }

    /**
     * Get locZoom.
     *
     * @return int
     */
    public function getLocZoom()
    {
        return $this->locZoom;
    }

    /**
     * Set loginAttempts.
     *
     * @param bool $loginAttempts
     *
     * @return UsrData
     */
    public function setLoginAttempts($loginAttempts)
    {
        $this->loginAttempts = $loginAttempts;

        return $this;
    }

    /**
     * Get loginAttempts.
     *
     * @return bool
     */
    public function getLoginAttempts()
    {
        return $this->loginAttempts;
    }

    /**
     * Set lastPasswordChange.
     *
     * @param int $lastPasswordChange
     *
     * @return UsrData
     */
    public function setLastPasswordChange($lastPasswordChange)
    {
        $this->lastPasswordChange = $lastPasswordChange;

        return $this;
    }

    /**
     * Get lastPasswordChange.
     *
     * @return int
     */
    public function getLastPasswordChange()
    {
        return $this->lastPasswordChange;
    }

    /**
     * Set regHash.
     *
     * @param string|null $regHash
     *
     * @return UsrData
     */
    public function setRegHash($regHash = null)
    {
        $this->regHash = $regHash;

        return $this;
    }

    /**
     * Get regHash.
     *
     * @return string|null
     */
    public function getRegHash()
    {
        return $this->regHash;
    }

    /**
     * Set birthday.
     *
     * @param \DateTime|null $birthday
     *
     * @return UsrData
     */
    public function setBirthday($birthday = null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday.
     *
     * @return \DateTime|null
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set selCountry.
     *
     * @param string|null $selCountry
     *
     * @return UsrData
     */
    public function setSelCountry($selCountry = null)
    {
        $this->selCountry = $selCountry;

        return $this;
    }

    /**
     * Get selCountry.
     *
     * @return string|null
     */
    public function getSelCountry()
    {
        return $this->selCountry;
    }

    /**
     * Set lastVisited.
     *
     * @param string|null $lastVisited
     *
     * @return UsrData
     */
    public function setLastVisited($lastVisited = null)
    {
        $this->lastVisited = $lastVisited;

        return $this;
    }

    /**
     * Get lastVisited.
     *
     * @return string|null
     */
    public function getLastVisited()
    {
        return $this->lastVisited;
    }

    /**
     * Set inactivationDate.
     *
     * @param \DateTime|null $inactivationDate
     *
     * @return UsrData
     */
    public function setInactivationDate($inactivationDate = null)
    {
        $this->inactivationDate = $inactivationDate;

        return $this;
    }

    /**
     * Get inactivationDate.
     *
     * @return \DateTime|null
     */
    public function getInactivationDate()
    {
        return $this->inactivationDate;
    }

    /**
     * Set isSelfRegistered.
     *
     * @param bool $isSelfRegistered
     *
     * @return UsrData
     */
    public function setIsSelfRegistered($isSelfRegistered)
    {
        $this->isSelfRegistered = $isSelfRegistered;

        return $this;
    }

    /**
     * Get isSelfRegistered.
     *
     * @return bool
     */
    public function getIsSelfRegistered()
    {
        return $this->isSelfRegistered;
    }

    /**
     * Set passwdEncType.
     *
     * @param string|null $passwdEncType
     *
     * @return UsrData
     */
    public function setPasswdEncType($passwdEncType = null)
    {
        $this->passwdEncType = $passwdEncType;

        return $this;
    }

    /**
     * Get passwdEncType.
     *
     * @return string|null
     */
    public function getPasswdEncType()
    {
        return $this->passwdEncType;
    }

    /**
     * Set passwdSalt.
     *
     * @param string|null $passwdSalt
     *
     * @return UsrData
     */
    public function setPasswdSalt($passwdSalt = null)
    {
        $this->passwdSalt = $passwdSalt;

        return $this;
    }

    /**
     * Get passwdSalt.
     *
     * @return string|null
     */
    public function getPasswdSalt()
    {
        return $this->passwdSalt;
    }

    /**
     * Set secondEmail.
     *
     * @param string|null $secondEmail
     *
     * @return UsrData
     */
    public function setSecondEmail($secondEmail = null)
    {
        $this->secondEmail = $secondEmail;

        return $this;
    }

    /**
     * Get secondEmail.
     *
     * @return string|null
     */
    public function getSecondEmail()
    {
        return $this->secondEmail;
    }

    /**
     * Set firstLogin.
     *
     * @param \DateTime|null $firstLogin
     *
     * @return UsrData
     */
    public function setFirstLogin($firstLogin = null)
    {
        $this->firstLogin = $firstLogin;

        return $this;
    }

    /**
     * Get firstLogin.
     *
     * @return \DateTime|null
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * Set lastProfilePrompt.
     *
     * @param \DateTime|null $lastProfilePrompt
     *
     * @return UsrData
     */
    public function setLastProfilePrompt($lastProfilePrompt = null)
    {
        $this->lastProfilePrompt = $lastProfilePrompt;

        return $this;
    }

    /**
     * Get lastProfilePrompt.
     *
     * @return \DateTime|null
     */
    public function getLastProfilePrompt()
    {
        return $this->lastProfilePrompt;
    }

    /**
     * Set passwdPolicyReset.
     *
     * @param bool $passwdPolicyReset
     *
     * @return UsrData
     */
    public function setPasswdPolicyReset($passwdPolicyReset)
    {
        $this->passwdPolicyReset = $passwdPolicyReset;

        return $this;
    }

    /**
     * Get passwdPolicyReset.
     *
     * @return bool
     */
    public function getPasswdPolicyReset()
    {
        return $this->passwdPolicyReset;
    }
}
