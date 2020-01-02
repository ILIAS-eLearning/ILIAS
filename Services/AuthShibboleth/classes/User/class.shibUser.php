<?php
require_once('./Services/AuthShibboleth/classes/Config/class.shibConfig.php');
require_once('./Services/User/classes/class.ilObjUser.php');

/**
 * Class shibUser
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibUser extends ilObjUser
{

    /**
     * @var shibServerData
     */
    protected $shibServerData;


    /**
     * @param shibServerData $shibServerData
     *
     * @return shibUser
     */
    public static function buildInstance(shibServerData $shibServerData)
    {
        $shibUser = new self();
        $shibUser->shibServerData = $shibServerData;
        $ext_id = $shibUser->shibServerData->getLogin();
        $shibUser->setExternalAccount($ext_id);
        $existing_usr_id = self::getUsrIdByExtId($ext_id);
        if ($existing_usr_id) {
            $shibUser->setId($existing_usr_id);
            $shibUser->read();
        }
        $shibUser->setAuthMode('shibboleth');

        return $shibUser;
    }


    public function updateFields()
    {
        $shibConfig = shibConfig::getInstance();
        if ($shibConfig->getUpdateFirstname()) {
            $this->setFirstname($this->shibServerData->getFirstname());
        }
        if ($shibConfig->getUpdateLastname()) {
            $this->setLastname($this->shibServerData->getLastname());
        }
        if ($shibConfig->getUpdateGender()) {
            $this->setGender($this->shibServerData->getGender());
        }
        if ($shibConfig->getUpdateTitle()) {
            $this->setTitle($this->shibServerData->getTitle());
        }
        if ($shibConfig->getUpdateInstitution()) {
            $this->setInstitution($this->shibServerData->getInstitution());
        }
        if ($shibConfig->getUpdateDepartment()) {
            $this->setDepartment($this->shibServerData->getDepartment());
        }
        if ($shibConfig->getUpdateStreet()) {
            $this->setStreet($this->shibServerData->getStreet());
        }
        if ($shibConfig->getUpdateZipcode()) {
            $this->setZipcode($this->shibServerData->getZipcode());
        }
        if ($shibConfig->getUpdateCountry()) {
            $this->setCountry($this->shibServerData->getCountry());
        }
        if ($shibConfig->getUpdatePhoneOffice()) {
            $this->setPhoneOffice($this->shibServerData->getPhoneOffice());
        }
        if ($shibConfig->getUpdatePhoneHome()) {
            $this->setPhoneHome($this->shibServerData->getPhoneHome());
        }
        if ($shibConfig->getUpdatePhoneMobile()) {
            $this->setPhoneMobile($this->shibServerData->getPhoneMobile());
        }
        if ($shibConfig->getUpdateFax()) {
            $this->setFax($this->shibServerData->getFax());
        }
        if ($shibConfig->getUpdateMatriculation()) {
            $this->setMatriculation($this->shibServerData->getMatriculation());
        }
        if ($shibConfig->getUpdateEmail()) {
            $this->setEmail($this->shibServerData->getEmail());
        }
        if ($shibConfig->getUpdateHobby()) {
            $this->setHobby($this->shibServerData->getHobby());
        }
        if ($shibConfig->getUpdateLanguage()) {
            $this->setLanguage($this->shibServerData->getLanguage());
        }
        $this->setDescription($this->getEmail());
    }


    public function createFields()
    {
        $this->setFirstname($this->shibServerData->getFirstname());
        $this->setLastname($this->shibServerData->getLastname());
        $this->setLogin($this->returnNewLoginName());
        $this->setPasswd(md5(end(ilUtil::generatePasswords(1))), IL_PASSWD_CRYPTED);
        $this->setGender($this->shibServerData->getGender());
        $this->setExternalAccount($this->shibServerData->getLogin());
        $this->setTitle($this->shibServerData->getTitle());
        $this->setInstitution($this->shibServerData->getInstitution());
        $this->setDepartment($this->shibServerData->getDepartment());
        $this->setStreet($this->shibServerData->getStreet());
        $this->setZipcode($this->shibServerData->getZipcode());
        $this->setCountry($this->shibServerData->getCountry());
        $this->setPhoneOffice($this->shibServerData->getPhoneOffice());
        $this->setPhoneHome($this->shibServerData->getPhoneHome());
        $this->setPhoneMobile($this->shibServerData->getPhoneMobile());
        $this->setFax($this->shibServerData->getFax());
        $this->setMatriculation($this->shibServerData->getMatriculation());
        $this->setEmail($this->shibServerData->getEmail());
        $this->setHobby($this->shibServerData->getHobby());
        $this->setTitle($this->getFullname());
        $this->setDescription($this->getEmail());
        $this->setLanguage($this->shibServerData->getLanguage());
        $this->setTimeLimitOwner(7);
        $this->setTimeLimitUnlimited(1);
        $this->setTimeLimitFrom(time());
        $this->setTimeLimitUntil(time());
        $this->setActive(true);
    }


    public function create()
    {
        $c = shibConfig::getInstance();
        if ($c->isActivateNew()) {
            $this->setActive(false);
            require_once('./Services/Registration/classes/class.ilRegistrationMailNotification.php');
            require_once('./Services/Registration/classes/class.ilRegistrationSettings.php');
            $ilRegistrationSettings = new ilRegistrationSettings();
            $mail = new ilRegistrationMailNotification();
            $mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_CONFIRMATION);
            $mail->setRecipients($ilRegistrationSettings->getApproveRecipients());
            $mail->setAdditionalInformation(array('usr' => $this));
            $mail->send();
        }

        if ($this->getLogin() != '' and $this->getLogin() != '.') {
            parent::create();
        } else {
            throw new ilUserException('No Login-name created');
        }
    }


    /**
     * @return string
     */
    protected function returnNewLoginName()
    {
        $login = substr(self::cleanName($this->getFirstname()), 0, 1) . '.' . self::cleanName($this->getLastname());
        //remove whitespaces see mantis 0023123: https://www.ilias.de/mantis/view.php?id=23123
        $login = preg_replace('/\s+/', '', $login);
        $appendix = null;
        $login_tmp = $login;
        while (self::loginExists($login, $this->getId())) {
            $login = $login_tmp . $appendix;
            $appendix++;
        }

        return $login;
    }


    /**
     * @return boolean
     */
    public function isNew()
    {
        return (bool) ($this->getId() == 0);
    }


    /**
     * @param $name
     *
     * @return mixed
     */
    protected static function cleanName($name)
    {
        $name = strtolower(strtr(utf8_decode($name), utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'), 'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy'));

        return $name;
    }


    /**
     * @param $login
     * @param $usr_id
     *
     * @return bool
     */
    private static function loginExists($login, $usr_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /**
         * @var $ilDB ilDB
         */
        $query = 'SELECT usr_id FROM usr_data WHERE login = ' . $ilDB->quote($login, 'text');
        $query .= ' AND usr_id != ' . $ilDB->quote($usr_id, 'integer');

        return (bool) ($ilDB->numRows($ilDB->query($query)) > 0);
    }


    /**
     * @param $ext_id
     *
     * @return bool
     */
    protected static function getUsrIdByExtId($ext_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /**
         * @var $ilDB ilDB
         */
        $query = 'SELECT usr_id FROM usr_data WHERE ext_account = ' . $ilDB->quote($ext_id, 'text');
        $a_set = $ilDB->query($query);
        if ($ilDB->numRows($a_set) == 0) {
            return false;
        } else {
            $usr = $ilDB->fetchObject($a_set);

            return $usr->usr_id;
        }
    }
}
