<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class shibUser
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibUser extends ilObjUser
{
    protected shibServerData $shibServerData;

    /**
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     * @throws ilSystemStyleException
     */
    public static function buildInstance(shibServerData $shibServerData): shibUser
    {
        $shib_user = new self();
        $shib_user->setLastPasswordChangeToNow();
        $shib_user->shibServerData = $shibServerData;
        $ext_id = $shib_user->shibServerData->getLogin();
        $shib_user->setExternalAccount($ext_id);
        $existing_usr_id = self::getUsrIdByExtId($ext_id);
        if ($existing_usr_id !== null) {
            $shib_user->setId($existing_usr_id);
            $shib_user->read();
        }
        $shib_user->setAuthMode('shibboleth');

        return $shib_user;
    }

    public function updateFields(): void
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

    public function createFields(): void
    {
        $this->setFirstname($this->shibServerData->getFirstname());
        $this->setLastname($this->shibServerData->getLastname());
        $this->setLogin($this->returnNewLoginName());
        $array = ilSecuritySettingsChecker::generatePasswords(1);
        $this->setPasswd(md5(end($array)), ilObjUser::PASSWD_CRYPTED);
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

    /**
     * @throws ilUserException
     */
    public function create(): int
    {
        $c = shibConfig::getInstance();
        $registration_settings = new ilRegistrationSettings();
        $recipients = array_filter($registration_settings->getApproveRecipients(), static function ($v) {
            return is_int($v);
        });
        if ($recipients !== [] && $c->isActivateNew()) {
            $this->setActive(false);
            $mail = new ilRegistrationMailNotification();
            $mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_CONFIRMATION);
            $mail->setRecipients($registration_settings->getApproveRecipients());
            $mail->setAdditionalInformation(array('usr' => $this));
            $mail->send();
        }

        if ($this->getLogin() !== '' && $this->getLogin() !== '.') {
            return parent::create();
        }

        throw new ilUserException('No Login-name created');
    }

    protected function returnNewLoginName(): ?string
    {
        $login = substr($this->cleanName($this->getFirstname()), 0, 1) . '.' . $this->cleanName($this->getLastname());
        //remove whitespaces see mantis 0023123: https://www.ilias.de/mantis/view.php?id=23123
        $login = preg_replace('/\s+/', '', $login);
        $appendix = null;
        $login_tmp = $login;
        while ($this->loginExists($login, $this->getId())) {
            $login = $login_tmp . $appendix;
            $appendix++;
        }

        return $login;
    }

    public function isNew(): bool
    {
        return $this->getId() === 0;
    }

    protected function cleanName(string $name): string
    {
        return strtolower(
            strtr(
                $name,
                'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ',
                'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy'
            )
        );
    }

    private function loginExists(string $login, int $usr_id): bool
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'SELECT usr_id FROM usr_data WHERE login = ' . $db->quote($login, 'text');
        $query .= ' AND usr_id != ' . $db->quote($usr_id, 'integer');

        return $db->numRows($db->query($query)) > 0;
    }

    protected static function getUsrIdByExtId(string $ext_id): ?int
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'SELECT usr_id FROM usr_data WHERE ext_account = ' . $db->quote($ext_id, 'text');
        $a_set = $db->query($query);
        if ($db->numRows($a_set) === 0) {
            return null;
        }

        $usr = $db->fetchObject($a_set);

        return ($usr !== null && isset($usr->usr_id)) ? (int) $usr->usr_id : null;
    }
}
