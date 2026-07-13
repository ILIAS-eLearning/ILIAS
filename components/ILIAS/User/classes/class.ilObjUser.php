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

use ILIAS\User\LocalDIC;
use ILIAS\User\Context;
use ILIAS\User\Profile\Data;
use ILIAS\User\Profile\DataRepository as ProfileDataRepository;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileConfigurationRepository;
use ILIAS\User\Profile\Fields\Standard\Alias;
use ILIAS\User\Profile\Fields\Standard\Genders;
use ILIAS\User\Profile\Fields\Standard\Interests;
use ILIAS\User\Profile\Fields\Standard\HelpOffered;
use ILIAS\User\Profile\Fields\Standard\HelpLookedFor;
use ILIAS\User\Settings\DataRepository as SettingsDataRepository;
use ILIAS\Language\Language;
use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Authentication\Password\LocalUserPasswordManager;
use ILIAS\Export\ExportHandler\Factory as ExportFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileDelivery\Delivery\StreamDelivery;

/**
 * User class
 * @author	Sascha Hofmann <saschahofmann@gmx.de>
 * @author	Stefan Meyer <meyer@leifos.com>
 * @author	Peter Gabriel <pgabriel@databay.de>
 */
class ilObjUser extends ilObject
{
    public const PASSWD_PLAIN = 'plain';
    public const PASSWD_CRYPTED = 'crypted';

    public const DATABASE_DATE_FORMAT = 'Y-m-d H:i:s';

    private string $ext_account = '';
    private string $fullname;
    private bool $time_limit_unlimited = false;
    private ?int $time_limit_until = null;
    private ?int $time_limit_from = null;
    private int $time_limit_owner = 7;
    private string $last_login = '';
    private string $passwd = '';
    private string $passwd_type = '';
    private ?string $password_encoding_type = null;
    private ?string $password_salt = null;
    private ?string $approve_date = null;
    private ?string $agree_date = null;
    private int $active = 0;
    private string $client_ip = ''; // client ip to check before login
    private ?string $auth_mode = null; // authentication mode
    private int $last_password_change_ts = 0;
    private bool $passwd_policy_reset = false;
    private int $login_attempts = 0;
    /** @var array<string, string> */
    private array $user_settings = [];
    private static array $personal_image_cache = [];
    private ?string $inactivation_date = null;
    private bool $is_self_registered = false; // flag for self registered users
    private string $last_profile_prompt = '';	// timestamp
    private string $first_login = '';	// timestamp
    private bool $profile_incomplete = false;
    private array $last_visited = [];

    private Data $profile_data;
    private ProfileDataRepository $profile_data_repository;
    private ProfileConfigurationRepository $profile_configuration_repository;
    private SettingsDataRepository $settings_data_repository;

    private StreamDelivery $delivery;
    private DataFactory $data_factory;
    private ilCronDeleteInactiveUserReminderMail $cron_delete_user_reminder_mail;
    private Services $irss;
    private ilSetting $ilias_settings;
    private ilAuthSession $auth_session;
    private ilCtrl $ctrl;

    public function __construct(
        int $a_user_id = 0,
        bool $a_call_by_reference = false
    ) {
        global $DIC;
        $this->irss = $DIC['resource_storage'];
        $this->ilias_settings = $DIC['ilSetting'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->app_event_handler = $DIC['ilAppEventHandler'];
        $this->delivery = $DIC['file_delivery']->delivery();

        if (defined('USER_FOLDER_ID')) {
            $this->time_limit_owner = USER_FOLDER_ID;
        }

        $local_dic = LocalDIC::dic();
        $this->profile_data_repository = $local_dic[ProfileDataRepository::class];
        $this->profile_data = $this->profile_data_repository->getDefault();
        $this->profile_configuration_repository = $local_dic[ProfileConfigurationRepository::class];
        $this->settings_data_repository = $local_dic[SettingsDataRepository::class];

        $this->data_factory = (new DataFactory());

        $this->type = 'usr';
        parent::__construct($a_user_id, $a_call_by_reference);

        $this->cron_delete_user_reminder_mail = new ilCronDeleteInactiveUserReminderMail($this->db);

        $this->auth_mode = 'default';
        $this->passwd_type = self::PASSWD_PLAIN;
        if ($a_user_id > 0) {
            $this->setId($a_user_id);
            $this->read();
            return;
        }

        $this->user_settings = [];
        $this->user_settings['language'] = $this->ilias->ini->readVariable('language', 'default');
        $this->user_settings['skin'] = $this->ilias->ini->readVariable('layout', 'skin');
        $this->user_settings['style'] = $this->ilias->ini->readVariable('layout', 'style');
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     * @throws ilSystemStyleException
     */
    public function read(): void
    {
        $this->profile_data = $this->profile_data_repository->getSingle($this->id);
        $this->setFullname();
        $this->assignSystemInformationFromDB($this->profile_data->getSystemInformation());

        $this->readSettings();

        parent::read();
    }

    public function saveAsNew(): void
    {
        $this->inactivation_date = null;
        if ($this->active === 0) {
            $this->inactivation_date = date('Y-m-d H:i:s');
        }

        $system_information = $this->buildSystemInformationArrayForDB();
        $system_information['create_date'] = $this->data_factory->clock()->utc()->now()
            ->format(self::DATABASE_DATE_FORMAT);

        $this->profile_data = $this->profile_data->withId($this->id);
        $this->profile_data_repository->store(
            $this->profile_data->withSystemInformation($this->buildSystemInformationArrayForDB())
        );

        // CREATE ENTRIES FOR MAIL BOX
        $mbox = new ilMailbox($this->id);
        $mbox->createDefaultFolder();

        $mail_options = new ilMailOptions($this->id);
        $mail_options->createMailOptionsEntry();

        $this->app_event_handler->raise(
            'components/ILIAS/User',
            'afterCreate',
            ['user_obj' => $this]
        );
    }

    public function update(): bool
    {
        $this->profile_data_repository->store(
            $this->profile_data->withSystemInformation($this->buildSystemInformationArrayForDB())
        );

        $this->writePrefs();

        parent::update();
        $this->updateOwner();

        $this->read();

        $this->app_event_handler->raise(
            'components/ILIAS/User',
            'afterUpdate',
            ['user_obj' => $this]
        );

        return true;
    }

    private function assignSystemInformationFromDB(array $data): void
    {
        if (!empty($data['passwd'])) {
            $this->setPasswd($data['passwd'], self::PASSWD_CRYPTED);
        }

        $this->password_salt = $data['passwd_salt'];
        $this->password_encoding_type = $data['passwd_enc_type'];
        $this->last_password_change_ts = $data['last_password_change'];
        $this->login_attempts = $data['login_attempts'];
        $this->passwd_policy_reset = $data['passwd_policy_reset'];
        $this->client_ip = $data['client_ip'];
        $this->last_login = $data['last_login'];
        $this->first_login = $data['first_login'];
        $this->last_profile_prompt = $data['last_profile_prompt'];
        $this->last_update = $data['last_update'];
        $this->create_date = $data['create_date'];
        $this->approve_date = $data['approve_date'];
        $this->active = $data['active'];
        $this->agree_date = $data['agree_date'];
        $this->inactivation_date = $data['inactivation_date'];

        $this->time_limit_owner = $data['time_limit_owner'];
        $this->time_limit_unlimited = $data['time_limit_unlimited'];
        $this->time_limit_from = $data['time_limit_from'];
        $this->time_limit_until = $data['time_limit_until'];

        $this->profile_incomplete = $data['profile_incomplete'];

        $this->auth_mode = $data['auth_mode'];
        $this->ext_account = $data['ext_account'] ?? '';
        $this->is_self_registered = $data['is_self_registered'];
        $this->last_visited = $data['last_visited'];
    }

    private function buildSystemInformationArrayForDB(): array
    {
        return [
            'last_password_change' => $this->last_password_change_ts,
            'login_attempts' => $this->login_attempts,
            'passwd' => $this->prepareAndRetrievePasswordForStorage(),
            'passwd_salt' => $this->password_salt,
            'passwd_enc_type' => $this->password_encoding_type,
            'passwd_policy_reset' => $this->passwd_policy_reset,
            'client_ip' => $this->client_ip,
            'last_login' => $this->last_login,
            'first_login' => $this->first_login,
            'last_profile_prompt' => $this->last_profile_prompt,
            'active' => $this->active,
            'approve_date' => $this->approve_date,
            'agree_date' => $this->retrieveAgreeDateForStorage(),
            'inactivation_date' => $this->inactivation_date,
            'time_limit_owner' => $this->time_limit_owner,
            'time_limit_unlimited' => $this->time_limit_unlimited,
            'time_limit_from' => $this->time_limit_from,
            'time_limit_until' => $this->time_limit_until,
            'profile_incomplete' => $this->profile_incomplete,
            'auth_mode' => $this->auth_mode,
            'ext_account' => $this->ext_account ?? '',
            'is_self_registered' => $this->is_self_registered,
            'last_update' => $this->last_update,
            'create_date' => $this->create_date,
            'last_visited' => $this->last_visited
        ];
    }

    private function prepareAndRetrievePasswordForStorage(): string
    {
        if ($this->passwd_type === self::PASSWD_PLAIN
            && $this->passwd !== '') {
            LocalUserPasswordManager::getInstance()->encodePassword($this, $this->passwd);
        }

        return $this->passwd;
    }

    private function retrieveAgreeDateForStorage(): ?string
    {
        if (is_string($this->agree_date && strtotime($this->agree_date) === false)) {
            return null;
        }
        return $this->agree_date;
    }

    public function resetPassword(string $new_raw_password): bool
    {
        LocalUserPasswordManager::getInstance()->encodePassword($this, $new_raw_password);
        $this->profile_data_repository->storePasswordFor(
            $this->id,
            $this->passwd,
            $this->password_encoding_type,
            $this->password_salt
        );
        return true;
    }

    public function getLastHistoryData(): ?array
    {
        $this->db->setLimit(1, 0);
        $res = $this->db->queryF(
            '
			SELECT login, history_date FROM loginname_history
			WHERE usr_id = %s ORDER BY history_date DESC',
            ['integer'],
            [$this->id]
        );
        $row = $this->db->fetchAssoc($res);
        if ($row === null) {
            return null;
        }

        return [
            $row['login'],
            $row['history_date']
        ];
    }

    public function updateLogin(string $login, Context $context): bool
    {
        if ($login === '' || $login === $this->profile_data->getAlias()) {
            return false;
        }

        $last_history_entry = $this->getLastHistoryData();

        if (!$context->isFieldChangeable(
            $this->profile_configuration_repository->getByClass(Alias::class),
            $this
        )) {
            throw new ilUserException($this->lng->txt('permission_denied'));
        }

        // throw exception if the desired loginame is already in history and it is not allowed to reuse it
        if ($this->ilias_settings->get('reuse_of_loginnames') === '0'
            && self::_doesLoginnameExistInHistory($login)) {
            throw new ilUserException($this->lng->txt('loginname_already_exists'));
        }

        if ((int) $this->ilias_settings->get('loginname_change_blocking_time') > 0
            && is_array($last_history_entry)
            && $last_history_entry[1] + (int) $this->ilias_settings->get('loginname_change_blocking_time') > time()) {
            throw new ilUserException(
                sprintf(
                    $this->lng->txt('changing_loginname_not_possible_info'),
                    ilDatePresentation::formatDate(
                        new ilDateTime($last_history_entry[1], IL_CAL_UNIX)
                    ),
                    ilDatePresentation::formatDate(
                        new ilDateTime(($last_history_entry[1] + (int) $this->ilias_settings->get('loginname_change_blocking_time')), IL_CAL_UNIX)
                    )
                )
            );
        }

        if ($this->ilias_settings->get('create_history_loginname') === '1') {
            $this->writeHistory($this->profile_data->getAlias());
        }

        $this->profile_data = $this->profile_data->withAlias($login);
        $this->profile_data_repository->storeLoginFor($this->id, $this->profile_data->getAlias());

        return true;
    }

    private function writeHistory(string $login): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM loginname_history WHERE usr_id = %s AND login = %s AND history_date = %s',
            ['integer', 'text', 'integer'],
            [$this->id, $login, time()]
        );

        if ($this->db->numRows($res) == 0) {
            $this->db->manipulateF(
                '
				INSERT INTO loginname_history
						(usr_id, login, history_date)
				VALUES 	(%s, %s, %s)',
                ['integer', 'text', 'integer'],
                [$this->id, $login, time()]
            );
        }
    }

    public function writePref(
        string $key,
        string $value
    ): void {
        $this->settings_data_repository->storeSingleFor($this->id, $key, $value);
        $this->setPref($key, $value);
    }

    public function deletePref(string $key): void
    {
        $this->settings_data_repository->deleteSingleFor($this->id, $key);
        unset($this->user_settings[$key]);
    }

    public function writePrefs(): void
    {
        $this->settings_data_repository->deleteFor($this->id);
        $this->settings_data_repository->storeFor($this->id, $this->user_settings);
    }

    public function getTimeZone(): string
    {
        $tz = $this->getPref('user_tz');
        if ($tz !== null) {
            return $tz;
        }
        return ilCalendarSettings::_getInstance()->getDefaultTimeZone();
    }

    public function getTimeFormat(): string
    {
        $format = $this->getPref('time_format');
        if ($format !== null) {
            return $format;
        }
        return ilCalendarSettings::_getInstance()->getDefaultTimeFormat();
    }

    public function getDateFormat(): DateFormat
    {
        $format = $this->getPref('date_format');
        if ($format === null) {
            $format = ilCalendarSettings::_getInstance()->getDefaultDateFormat();
        }

        return match ((int) $format) {
            ilCalendarSettings::DATE_FORMAT_DMY => $this->data_factory->dateFormat()->germanShort(),
            ilCalendarSettings::DATE_FORMAT_MDY => $this->data_factory->dateFormat()->americanShort(),
            ilCalendarSettings::DATE_FORMAT_YMD => $this->data_factory->dateFormat()->standard(),
            default => $this->data_factory->dateFormat()->standard()
        };
    }

    public function getDateTimeFormat(): DateFormat
    {
        if ($this->getTimeFormat() === (string) \ilCalendarSettings::TIME_FORMAT_24) {
            return $this->data_factory->dateFormat()->withTime24($this->getDateFormat());
        }
        return $this->data_factory->dateFormat()->withTime12($this->getDateFormat());
    }

    public function setPref(string $a_keyword, ?string $a_value): void
    {
        if ($a_keyword !== '') {
            $this->user_settings[$a_keyword] = $a_value;
        }
    }

    public function getPref(string $keyword): ?string
    {
        return $this->user_settings[$keyword] ?? null;
    }

    /**
     * @deprecated 11
     */
    public function getPrefs(): array
    {
        return $this->user_settings;
    }

    private function readSettings(): void
    {
        $this->user_settings = $this->settings_data_repository->getFor($this->id);
        if (!isset($this->user_settings['style'])
            || $this->user_settings['style'] === ''
            || !ilStyleDefinition::styleExists($this->user_settings['style'])
            || !ilStyleDefinition::skinExists($this->user_settings['skin'])
                && ilStyleDefinition::styleExistsForSkinId($this->user_settings['skin'], $this->user_settings['style'])) {
            $this->user_settings['skin'] = $this->ilias->ini->readVariable('layout', 'skin');
            $this->user_settings['style'] = $this->ilias->ini->readVariable('layout', 'style');
        }
    }

    public function delete(): bool
    {
        $this->app_event_handler->raise(
            'Services/User',
            'deleteUser',
            ['usr_id' => $this->getId()]
        );

        ilSession::_destroyByUserId($this->getId());
        ilLDAPRoleGroupMapping::_getInstance()->deleteUser($this->getId());

        /**
         * skergomard, 2025-12-01: Ok, this is not right, but we can sadly not change this
         * as the User is initialized before RbacAdmin, so that the corresponding property
         * that actually does exist, is never initialized in this class.
         */
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $DIC['rbacadmin']->removeUser($this->getId());
        (ilOrgUnitUserAssignmentQueries::getInstance())->deleteAllAssignmentsOfUser($this->getId());

        $mailbox = new ilMailbox($this->getId());
        $mailbox->delete();
        $mailbox->updateMailsOfDeletedUser($this->getLogin());

        ilBlockSetting::_deleteSettingsOfUser($this->getId());
        ilObjCourse::_deleteUser($this->getId());
        ilObjUserTracking::_deleteUser($this->getId());
        ilEventParticipants::_deleteByUser($this->getId());
        ilSCORM13Package::_removeTrackingDataForUser($this->getId());
        ilObjSCORMLearningModule::_removeTrackingDataForUser($this->getId());
        ilNotification::removeForUser($this->getId());
        ilObjPortfolio::deleteUserPortfolios($this->getId());
        (new ilWorkspaceTree($this->id))->cascadingDelete();
        $this->cron_delete_user_reminder_mail->removeSingleUserFromTable($this->getId());
        ilBadgeAssignment::deleteByUserId($this->getId());
        $this->clipboardDeleteAll();

        $this->settings_data_repository->deleteFor($this->id);
        $this->removeUserPicture();
        $this->profile_data_repository->deleteForUser($this->getId());

        $this->resetOwner();
        parent::delete();

        return true;
    }

    public function withProfileData(Data $profile_data): self
    {
        $clone = clone $this;
        $clone->profile_data = $profile_data;
        return $clone;
    }

    public function getProfileData(): Data
    {
        return $this->profile_data;
    }

    public function setLogin(string $login): void
    {
        $this->profile_data = $this->profile_data->withAlias($login);
    }

    public function getLogin(): string
    {
        return $this->profile_data->getAlias();
    }

    public function setGender(string $gender_string): void
    {
        $this->profile_data = $this->profile_data->withGender(Genders::tryFrom($gender_string));
    }

    public function getGender(): string
    {
        return $this->profile_data->getGender()?->value ?? '';
    }

    /**
     * This sets the USER's title NOT the OBJECT's title!
     */
    public function setUTitle(string $user_title): void
    {
        $this->setFullname();
        $this->profile_data = $this->profile_data->withTitle($user_title);
    }

    public function getUTitle(): string
    {
        return $this->profile_data->getTitle();
    }

    public function setFirstname(string $firstname): void
    {
        $this->profile_data = $this->profile_data->withFirstname($firstname);
        $this->setFullname();
    }

    public function getFirstname(): string
    {
        return $this->profile_data->getFirstname();
    }

    public function setLastname(string $lastname): void
    {
        $this->profile_data = $this->profile_data->withLastname($lastname);
        $this->setFullname();
    }

    public function getLastname(): string
    {
        return $this->profile_data->getLastname();
    }

    public function setBirthday(?string $birthday): void
    {
        if ($birthday === null || $birthday === '') {
            $this->profile_data = $this->profile_data->withBirthday(null);
            return;
        }

        $this->profile_data = $this->profile_data->withBirthday(
            new \DateTimeImmutable($birthday, new DateTimeZone('UTC'))
        );
    }

    public function getBirthday(): ?string
    {
        return $this->profile_data->getBirthday()?->format('Y-m-d');
    }

    public function setInstitution(string $instituion): void
    {
        $this->profile_data = $this->profile_data->withInstitution($instituion);
    }

    public function getInstitution(): string
    {
        return $this->profile_data->getInstitution();
    }

    public function setDepartment(string $department): void
    {
        $this->profile_data = $this->profile_data->withDepartment($department);
    }

    public function getDepartment(): string
    {
        return $this->profile_data->getDepartment();
    }

    public function setStreet(string $street): void
    {
        $this->profile_data = $this->profile_data->withStreet($street);
    }

    public function getStreet(): string
    {
        return $this->profile_data->getStreet();
    }

    public function setCity(string $city): void
    {
        $this->profile_data = $this->profile_data->withCity($city);
    }

    public function getCity(): string
    {
        return $this->profile_data->getCity();
    }

    public function setZipcode(string $zipcode): void
    {
        $this->profile_data = $this->profile_data->withZipcode($zipcode);
    }

    public function getZipcode(): string
    {
        return $this->profile_data->getZipcode();
    }

    public function setCountry(string $country): void
    {
        $this->profile_data = $this->profile_data->withCountry($country);
    }

    public function getCountry(): string
    {
        return $this->profile_data->getCountry();
    }

    public function setPhoneOffice(string $phone): void
    {
        $this->profile_data = $this->profile_data->withPhoneOffice($phone);
    }

    public function getPhoneOffice(): string
    {
        return $this->profile_data->getPhoneOffice();
    }

    public function setPhoneHome(string $phone): void
    {
        $this->profile_data = $this->profile_data->withPhoneHome($phone);
    }

    public function getPhoneHome(): string
    {
        return $this->profile_data->getPhoneHome();
    }

    public function setPhoneMobile(string $phone): void
    {
        $this->profile_data = $this->profile_data->withPhoneMobile($phone);
    }

    public function getPhoneMobile(): string
    {
        return $this->profile_data->getPhoneMobile();
    }

    public function setFax(string $fax): void
    {
        $this->profile_data = $this->profile_data->withFax($fax);
    }

    public function getFax(): string
    {
        return $this->profile_data->getFax();
    }

    public function setMatriculation(string $matriculation): void
    {
        $this->profile_data = $this->profile_data->withMatriculation($matriculation);
    }

    public function getMatriculation(): string
    {
        return $this->profile_data->getMatriculation();
    }

    public function setEmail(string $email): void
    {
        $this->profile_data = $this->profile_data->withEmail($email);
    }

    public function getEmail(): string
    {
        return $this->profile_data->getEmail();
    }

    public function setSecondEmail(?string $email): void
    {
        $this->profile_data = $this->profile_data->withSecondEmail($email);
    }

    public function getSecondEmail(): ?string
    {
        return $this->profile_data->getSecondEmail();
    }

    public function setHobby(string $hobby): void
    {
        $this->profile_data = $this->profile_data->withHobby($hobby);
    }

    public function getHobby(): string
    {
        return $this->profile_data->getHobby();
    }

    public function setComment(string $referral_comment): void
    {
        $this->profile_data = $this->profile_data->withReferralComment($referral_comment);
    }

    public function getComment(): string
    {
        return $this->profile_data->getReferralComment();
    }

    public function setLatitude(?string $latitude): void
    {
        $coordinates = $this->profile_data->getGeoCoordinates();
        $coordinates['latitude'] = $latitude;
        $this->profile_data = $this->profile_data->withGeoCoordinates($coordinates);
    }

    public function getLatitude(): ?string
    {
        return $this->profile_data->getGeoCoordinates()['latitude'] ?? null;
    }

    public function setLongitude(?string $longitude): void
    {
        $coordinates = $this->profile_data->getGeoCoordinates();
        $coordinates['longitude'] = $longitude;
        $this->profile_data = $this->profile_data->withGeoCoordinates($coordinates);
    }

    public function getLongitude(): ?string
    {
        return $this->profile_data->getGeoCoordinates()['longitude'] ?? null;
    }

    public function setLocationZoom(?int $zoom): void
    {
        $coordinates = $this->profile_data->getGeoCoordinates();
        $coordinates['zoom'] = $zoom;
        $this->profile_data = $this->profile_data->withGeoCoordinates($coordinates);
    }

    public function getLocationZoom(): ?int
    {
        return $this->profile_data->getGeoCoordinates()['zoom'] ?? null;
    }

    public function getAvatarRid(): ?ResourceIdentification
    {
        return $this->profile_data->getAvatarRid();
    }

    public function setAvatarRid(?ResourceIdentification $avatar_rid): void
    {
        $this->profile_data = $this->profile_data->withAvatarRid($avatar_rid);
    }

    public function setClientIP(string $a_str): void
    {
        $this->client_ip = $a_str;
    }

    public function getClientIP(): string
    {
        return $this->client_ip;
    }

    public function setLanguage(string $language): void
    {
        $this->setPref('language', $language);
        ilSession::clear('lang');
    }

    public function getLanguage(): string
    {
        return $this->user_settings['language'] ?? '';
    }

    public function getPasswordEncodingType(): ?string
    {
        return $this->password_encoding_type;
    }

    public function setPasswordEncodingType(?string $password_encryption_type): void
    {
        $this->password_encoding_type = $password_encryption_type;
    }

    public function getPasswordSalt(): ?string
    {
        return $this->password_salt;
    }

    public function setPasswordSalt(?string $password_salt): void
    {
        $this->password_salt = $password_salt;
    }

    public function setFullname(): void
    {
        $title = $this->profile_data->getTitle() !== '' ? "{$this->profile_data->getTitle()} " : '';
        $this->fullname = "{$title}{$this->profile_data->getFirstname()} {$this->profile_data->getLastname()}";
    }

    /**
     * @param int $max_strlen max. string length to return (optional)
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
    public function getFullname(int $max_strlen = 0): string
    {
        if ($max_strlen === 0) {
            return ilUtil::stripSlashes($this->fullname);
        }

        if (mb_strlen($this->fullname) <= $max_strlen) {
            return ilUtil::stripSlashes($this->fullname);
        }

        $length_lastname = mb_strlen($this->lastname);
        if (mb_strlen($this->utitle) + $length_lastname + 4 <= $max_strlen) {
            return ilUtil::stripSlashes($this->utitle . ' ' . substr($this->firstname, 0, 1) . '. ' . $this->lastname);
        }

        if (mb_strlen($this->firstname) + $length_lastname + 1 <= $max_strlen) {
            return ilUtil::stripSlashes($this->firstname . ' ' . $this->lastname);
        }

        if ($length_lastname + 3 <= $max_strlen) {
            return ilUtil::stripSlashes(substr($this->firstname, 0, 1) . '. ' . $this->lastname);
        }

        return ilUtil::stripSlashes(substr($this->lastname, 0, $max_strlen));
    }

    public function setPasswd(
        string $a_str,
        string $a_type = ilObjUser::PASSWD_PLAIN
    ): void {
        $this->passwd = $a_str;
        $this->passwd_type = $a_type;
    }

    /**
     * @return string The password is encoded depending on the current password type.
     */
    public function getPasswd(): string
    {
        return $this->passwd;
    }

    /**
     * @return string password type (ilObjUser::PASSWD_PLAIN, ilObjUser::PASSWD_CRYPTED).
     */
    public function getPasswdType(): string
    {
        return $this->passwd_type;
    }

    public function setLastPasswordChangeTS(int $a_last_password_change_ts): void
    {
        $this->last_password_change_ts = $a_last_password_change_ts;
    }

    public function getLastPasswordChangeTS(): int
    {
        return $this->last_password_change_ts;
    }

    public function getPasswordPolicyResetStatus(): bool
    {
        return $this->passwd_policy_reset;
    }

    public function setPasswordPolicyResetStatus(bool $status): void
    {
        $this->passwd_policy_reset = $status;
    }

    /**
     * returns the current language (may differ from user's pref setting!)
     */
    public function getCurrentLanguage(): string
    {
        return ilSession::get('lang') ?? '';
    }

    /**
     * Set current language
     */
    public function setCurrentLanguage(string $language): void
    {
        ilSession::set('lang', $language);
    }

    public function setLastLogin(string $a_str): void
    {
        $this->last_login = $a_str;
    }

    public function getLastLogin(): string
    {
        return $this->last_login;
    }

    public function refreshLogin(): void
    {
        $this->last_login = $this->db->now();

        $old_first_login = $this->first_login;
        if ($old_first_login === '') {
            $this->first_login = $this->db->now();
            $this->app_event_handler->raise(
                'components/ILIAS/User',
                'firstLogin',
                ['user_obj' => $this]
            );
        }
    }

    public function setFirstLogin(string $date): void
    {
        $this->first_login = $date;
    }

    public function getFirstLogin(): string
    {
        return $this->first_login;
    }

    public function setLastProfilePrompt(string $date): void
    {
        $this->last_profile_prompt = $date;
    }

    public function getLastProfilePrompt(): string
    {
        return $this->last_profile_prompt;
    }

    public function setLastUpdate(string $date): void
    {
        $this->last_update = $date;
    }

    public function getLastUpdate(): string
    {
        return $this->last_update;
    }

    public function getLastVisited(): array
    {
        return $this->last_visited;
    }

    public function updateLastVisited(array $last_visited): void
    {
        $this->last_visited = $last_visited;
        $this->profile_data_repository->storeLastVisitedFor($this->id, $last_visited);
    }

    /**
     * set date the user account was activated
     * null indicates that the user has not yet been activated
     */
    public function setApproveDate(?string $a_str): void
    {
        $this->approve_date = $a_str;
    }

    public function getApproveDate(): ?string
    {
        return $this->approve_date;
    }

    public function getAgreeDate(): ?string
    {
        return $this->agree_date;
    }
    public function setAgreeDate(?string $date): void
    {
        $this->agree_date = $date;
    }

    /**
    * set user active state and updates system fields appropriately
     * @param int  $a_owner the id of the person who approved the account, defaults to 6 (root)
     */
    public function setActive(
        bool $active,
        int $owner = 0
    ): void {
        $this->setOwner($owner);

        $current_active = $this->active;
        if ($active) {
            $this->active = 1;
            $this->setApproveDate(date('Y-m-d H:i:s'));
            $this->setInactivationDate(null);
            return;
        }

        $this->active = 0;
        $this->setApproveDate(null);

        if ($this->getId() > 0 && $current_active !== $active) {
            $this->setInactivationDate(ilUtil::now());
        }
    }

    public function getActive(): bool
    {
        return $this->active === 1;
    }

    public function getSkin(): string
    {
        return $this->user_settings['skin'];
    }

    public function setTimeLimitOwner(int $a_owner): void
    {
        $this->time_limit_owner = $a_owner;
    }

    public function getTimeLimitOwner(): int
    {
        return $this->time_limit_owner;
    }

    public function setTimeLimitFrom(?int $a_from): void
    {
        $this->time_limit_from = $a_from;
    }

    public function getTimeLimitFrom(): ?int
    {
        return $this->time_limit_from;
    }

    public function setTimeLimitUntil(?int $a_until): void
    {
        $this->time_limit_until = $a_until;
    }

    public function getTimeLimitUntil(): ?int
    {
        return $this->time_limit_until;
    }

    public function setTimeLimitUnlimited(bool $unlimited): void
    {
        $this->time_limit_unlimited = $unlimited;
    }

    public function getTimeLimitUnlimited(): bool
    {
        return $this->time_limit_unlimited;
    }

    public function setLoginAttempts(int $a_login_attempts): void
    {
        $this->login_attempts = $a_login_attempts;
    }

    public function getLoginAttempts(): int
    {
        return $this->login_attempts;
    }

    public function checkTimeLimit(): bool
    {
        if ($this->getTimeLimitUnlimited()) {
            return true;
        }
        if ($this->getTimeLimitFrom() < time() and $this->getTimeLimitUntil() > time()) {
            return true;
        }
        return false;
    }

    public function setProfileIncomplete(bool $a_prof_inc): void
    {
        $this->profile_incomplete = $a_prof_inc;
    }

    public function getProfileIncomplete(): bool
    {
        if ($this->id == ANONYMOUS_USER_ID) {
            return false;
        }
        return $this->profile_incomplete;
    }

    public function isPasswordChangeDemanded(): bool
    {
        if ($this->id === ANONYMOUS_USER_ID) {
            return false;
        }

        if ($this->id === SYSTEM_USER_ID) {
            if (LocalUserPasswordManager::getInstance()->verifyPassword($this, base64_decode('aG9tZXI='))
                && !ilAuthUtils::_needsExternalAccountByAuthMode($this->getAuthMode(true))
            ) {
                return true;
            }
            return false;
        }

        return !ilAuthUtils::_needsExternalAccountByAuthMode($this->getAuthMode(true))
            && ($this->getPasswordPolicyResetStatus()
                || ilSecuritySettings::_getInstance()->isPasswordChangeOnFirstLoginEnabled()
                    && $this->getLastPasswordChangeTS() === 0
                    && $this->is_self_registered === false);
    }

    public function isPasswordExpired(): bool
    {
        if ($this->id === ANONYMOUS_USER_ID
            || $this->getLastPasswordChangeTS() === 0) {
            return false;
        }

        $max_pass_age_in_seconds = ilSecuritySettings::_getInstance()->getPasswordMaxAge() * 86400;
        if ($max_pass_age_in_seconds === 0) {
            return false;
        }

        if (time() - $this->getLastPasswordChangeTS() > $max_pass_age_in_seconds
            && !ilAuthUtils::_needsExternalAccountByAuthMode($this->getAuthMode(true))) {
            return true;
        }

        return false;
    }

    public function getPasswordAgeInDays(): int
    {
        return (int) floor((time() - $this->getLastPasswordChangeTS()) / 86400);
    }

    public function setLastPasswordChangeToNow(): void
    {
        $this->last_password_change_ts = time();
    }

    public function resetLastPasswordChange(): void
    {
        $this->last_password_change_ts = 0;
    }

    public function setAuthMode(?string $a_str): void
    {
        $this->auth_mode = $a_str;
    }

    public function getAuthMode(bool $a_auth_key = false): ?string
    {
        if (!$a_auth_key) {
            return $this->auth_mode;
        }
        return ilAuthUtils::_getAuthMode($this->auth_mode);
    }

    public function setExternalAccount(string $a_str): void
    {
        $this->ext_account = $a_str;
    }

    public function getExternalAccount(): string
    {
        return $this->ext_account;
    }

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
        string $a_time = '',
        int $a_order_nr = 0
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_time === '') {
            $a_time = date('Y-m-d H:i:s');
        }

        $item_set = $ilDB->queryF(
            'SELECT * FROM personal_clipboard WHERE ' .
            'parent = %s AND item_id = %s AND type = %s AND user_id = %s',
            ['integer', 'integer', 'text', 'integer'],
            [0, $a_item_id, $a_type, $this->getId()]
        );

        // only insert if item is not already in clipboard
        if (!$item_set->fetchRow()) {
            $ilDB->manipulateF(
                'INSERT INTO personal_clipboard ' .
                '(item_id, type, user_id, title, parent, insert_time, order_nr) VALUES ' .
                ' (%s,%s,%s,%s,%s,%s,%s)',
                ['integer', 'text', 'integer', 'text', 'integer', 'timestamp', 'integer'],
                [$a_item_id, $a_type, $this->getId(), $a_title, $a_parent, $a_time, $a_order_nr]
            );
        } else {
            $ilDB->manipulateF(
                'UPDATE personal_clipboard SET insert_time = %s ' .
                'WHERE user_id = %s AND item_id = %s AND type = %s AND parent = 0',
                ['timestamp', 'integer', 'integer', 'text'],
                [$a_time, $this->getId(), $a_item_id, $a_type]
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
    ): void {
        $ilDB = $this->db;
        if ($a_time == 0) {
            $a_time = date('Y-m-d H:i:s');
        }
        ilSession::set('user_pc_clip', true);
        $ilDB->insert('personal_pc_clipboard', [
            'user_id' => ['integer', $this->getId()],
            'content' => ['clob', $a_content],
            'insert_time' => ['timestamp', $a_time],
            'order_nr' => ['integer', $a_nr]
            ]);
    }

    /**
     * Add a page content item to PC clipboard (should go to another class)
     * @todo move to COPage service
     */
    public function getPCClipboardContent(): array // Missing array type.
    {
        $ilDB = $this->db;

        if (!ilSession::get('user_pc_clip')) {
            return [];
        }

        $set = $ilDB->queryF('SELECT MAX(insert_time) mtime FROM personal_pc_clipboard ' .
            ' WHERE user_id = %s', ['integer'], [$this->getId()]);
        $row = $ilDB->fetchAssoc($set);

        $set = $ilDB->queryF(
            'SELECT * FROM personal_pc_clipboard ' .
            ' WHERE user_id = %s AND insert_time = %s ORDER BY order_nr ASC',
            ['integer', 'timestamp'],
            [$this->getId(), $row['mtime']]
        );
        $content = [];
        while ($row = $ilDB->fetchAssoc($set)) {
            $content[] = $row['content'];
        }

        return $content;
    }

    /**
     * Check whether clipboard has objects of a certain type
     */
    public function clipboardHasObjectsOfType(string $a_type): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->queryF(
            'SELECT * FROM personal_clipboard WHERE ' .
            'parent = %s AND type = %s AND user_id = %s',
            ['integer', 'text', 'integer'],
            [0, $a_type, $this->getId()]
        );
        if ($ilDB->fetchAssoc($set)) {
            return true;
        }

        return false;
    }

    public function clipboardDeleteObjectsOfType(string $a_type): void
    {
        $ilDB = $this->db;

        $ilDB->manipulateF(
            'DELETE FROM personal_clipboard WHERE ' .
            'type = %s AND user_id = %s',
            ['text', 'integer'],
            [$a_type, $this->getId()]
        );
    }

    public function clipboardDeleteAll(): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF('DELETE FROM personal_clipboard WHERE ' .
            'user_id = %s', ['integer'], [$this->getId()]);
    }

    /**
     * get all clipboard objects of user and specified type
     */
    public function getClipboardObjects(
        string $a_type = '',
        bool $a_top_nodes_only = false
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $par = '';
        if ($a_top_nodes_only) {
            $par = ' AND parent = ' . $ilDB->quote(0, 'integer') . ' ';
        }

        $type_str = ($a_type != '')
            ? ' AND type = ' . $ilDB->quote($a_type, 'text') . ' '
            : '';
        $q = 'SELECT * FROM personal_clipboard WHERE ' .
            'user_id = ' . $ilDB->quote($this->getId(), 'integer') . ' ' .
            $type_str . $par .
            ' ORDER BY order_nr';
        $objs = $ilDB->query($q);
        $objects = [];
        while ($obj = $ilDB->fetchAssoc($objs)) {
            if ($obj['type'] == 'mob') {
                $obj['title'] = ilObject::_lookupTitle($obj['item_id']);
                if (ilObject::_lookupType((int) $obj['item_id']) !== 'mob') {
                    continue;
                }
            }
            if ($obj['type'] == 'incl') {
                $obj['title'] = ilMediaPoolPage::lookupTitle($obj['item_id']);
                if (!ilPageObject::_exists('mep', (int) $obj['item_id'], '-')) {
                    continue;
                }
            }
            $objects[] = ['id' => $obj['item_id'],
                'type' => $obj['type'], 'title' => $obj['title'],
                'insert_time' => $obj['insert_time']];
        }
        return $objects;
    }

    /**
     * Get children of an item
     */
    public function getClipboardChilds(
        int $a_parent,
        string $a_insert_time
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $objs = $ilDB->queryF(
            'SELECT * FROM personal_clipboard WHERE ' .
            'user_id = %s AND parent = %s AND insert_time = %s ' .
            ' ORDER BY order_nr',
            ['integer', 'integer', 'timestamp'],
            [$ilUser->getId(), $a_parent, $a_insert_time]
        );
        $objects = [];
        while ($obj = $ilDB->fetchAssoc($objs)) {
            if ($obj['type'] == 'mob') {
                $obj['title'] = ilObject::_lookupTitle($obj['item_id']);
            }
            $objects[] = ['id' => $obj['item_id'],
                'type' => $obj['type'], 'title' => $obj['title'], 'insert_time' => $obj['insert_time']];
        }
        return $objects;
    }

    public function removeObjectFromClipboard(
        int $a_item_id,
        string $a_type
    ): void {
        $ilDB = $this->db;

        $q = 'DELETE FROM personal_clipboard WHERE ' .
            'item_id = ' . $ilDB->quote($a_item_id, 'integer') .
            ' AND type = ' . $ilDB->quote($a_type, 'text') . ' ' .
            ' AND user_id = ' . $ilDB->quote($this->getId(), 'integer');
        $ilDB->manipulate($q);
    }

    public function getOrgUnitsRepresentation(): string
    {
        return self::lookupOrgUnitsRepresentation($this->getId());
    }

    /**
     * @param string $a_size       'small', 'xsmall' or 'xxsmall'
     * @throws ilWACException
     */
    public function getPersonalPicturePath(
        string $a_size = 'small',
        bool $a_force_pic = false
    ): string {
        if (isset(self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic])) {
            return self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic];
        }

        self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic] = self::_getPersonalPicturePath($this->getId(), $a_size, $a_force_pic);

        return self::$personal_image_cache[$this->getId()][$a_size][(int) $a_force_pic];
    }

    public function hasProfilePicture(): bool
    {
        return (new ilUserAvatarResolver($this->getId()))->hasProfilePicture();
    }

    public function getAvatar(): Avatar
    {
        return self::_getAvatar($this->getId());
    }

    public function removeUserPicture(): void
    {
        if ($this->getAvatarRid() !== null) {
            $this->irss->manage()->remove($this->getAvatarRid(), new ilUserProfilePictureStakeholder());
        }

        $this->profile_data = $this->profile_data->withAvatarRid(null);
        $this->update();
    }

    /**
     * Get formatted mail body text of user profile data.
     * @throws ilDateTimeException
     */
    public function getProfileAsString(Language $language): string
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];
        $profile = $DIC['user']->getProfile();

        $language->loadLanguageModule('registration');
        $language->loadLanguageModule('crs');

        $body = "{$language->txt('login')}: {$this->getLogin()}\n";

        if ($this->profile_data->getTitle() !== '') {
            $body .= "{$language->txt('title')}: {$this->profile_data->getTitle()}\n";
        }
        if ($this->getGender() !== '') {
            $body .= ($language->txt('gender') . ': ' . $language->txt('gender_' . strtolower($this->getGender())) . "\n");
        }
        if ($this->getFirstname() !== '') {
            $body .= ($language->txt('firstname') . ': ' . $this->getFirstname() . "\n");
        }
        if ($this->getLastname() !== '') {
            $body .= ($language->txt('lastname') . ': ' . $this->getLastname() . "\n");
        }
        if ($this->getInstitution() !== '') {
            $body .= ($language->txt('institution') . ': ' . $this->getInstitution() . "\n");
        }
        if ($this->getDepartment() !== '') {
            $body .= ($language->txt('department') . ': ' . $this->getDepartment() . "\n");
        }
        if ($this->getStreet() !== '') {
            $body .= ($language->txt('street') . ': ' . $this->getStreet() . "\n");
        }
        if ($this->getCity() !== '') {
            $body .= ($language->txt('city') . ': ' . $this->getCity() . "\n");
        }
        if ($this->getZipcode() !== '') {
            $body .= ($language->txt('zipcode') . ': ' . $this->getZipcode() . "\n");
        }
        if ($this->getCountry() !== '') {
            $body .= ($language->txt('country') . ': ' . $this->getCountry() . "\n");
        }
        if ($this->getPhoneOffice() !== '') {
            $body .= ($language->txt('phone_office') . ': ' . $this->getPhoneOffice() . "\n");
        }
        if ($this->getPhoneHome() !== '') {
            $body .= ($language->txt('phone_home') . ': ' . $this->getPhoneHome() . "\n");
        }
        if ($this->getPhoneMobile() !== '') {
            $body .= ($language->txt('phone_mobile') . ': ' . $this->getPhoneMobile() . "\n");
        }
        if ($this->getFax() !== '') {
            $body .= ($language->txt('fax') . ': ' . $this->getFax() . "\n");
        }
        if ($this->getEmail() !== '') {
            $body .= ($language->txt('email') . ': ' . $this->getEmail() . "\n");
        }
        if ($this->getSecondEmail() !== null
            && $this->getSecondEmail() !== '') {
            $body .= ($language->txt('second_email') . ': ' . $this->getSecondEmail() . "\n");
        }
        if ($this->getHobby() !== '') {
            $body .= ($language->txt('hobby') . ': ' . $this->getHobby() . "\n");
        }
        if ($this->getComment() !== '') {
            $body .= ($language->txt('referral_comment') . ': ' . $this->getComment() . "\n");
        }
        if ($this->getMatriculation() !== '') {
            $body .= ($language->txt('matriculation') . ': ' . $this->getMatriculation() . "\n");
        }
        if ($this->getCreateDate() !== '') {
            ilDatePresentation::setUseRelativeDates(false);
            ilDatePresentation::setLanguage($language);
            $date = ilDatePresentation::formatDate(new ilDateTime($this->getCreateDate(), IL_CAL_DATETIME));
            ilDatePresentation::resetToDefaults();

            $body .= ($language->txt('create_date') . ': ' . $date . "\n");
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
            $body .= ($language->txt('time_limit') . ': ' . $language->txt('crs_unlimited') . "\n");
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

            $body .= $language->txt('time_limit') . ': ' .
                $language->txt('from') . ' ' .
                $start->get(IL_CAL_DATETIME) . ' ';
            $body .= $language->txt('to') . ' ' . $end->get(IL_CAL_DATETIME) . "\n";
        }

        foreach ($profile->getAllUserDefinedFields() as $field) {
            $data = $field->retrieveValueFromUser($this);
            if ($data !== '') {
                $body .= "{$field->getLabel($this->lng)}: {$data}\n";
            }
        }

        return $body;
    }

    public function setFeedPass(
        string $a_password
    ): void {
        $this->writePref(
            'priv_feed_pass',
            ($a_password == '') ? '' : md5($a_password)
        );
    }

    public function hasPublicProfile(): bool
    {
        return in_array($this->getPref('public_profile'), ['y', 'g']);
    }

    public function getPublicName(): string
    {
        if ($this->hasPublicProfile()) {
            return $this->getFirstname() . ' ' . $this->getLastname() . ' (' . $this->getLogin() . ')';
        }

        return $this->getLogin();
    }

    public function resetOwner(): void
    {
        $ilDB = $this->db;

        $query = 'UPDATE object_data SET owner = 0 ' .
            'WHERE owner = ' . $ilDB->quote($this->getId(), 'integer');
        $ilDB->query($query);
    }


    public function exportPersonalData(): void
    {
        if (!isset($this->user)) {
            global $DIC;
            $this->user = $DIC->user();
        }
        $export_consumer = (new ExportFactory())->consumer()->handler();
        $configs = $export_consumer->exportConfig()->allExportConfigs();
        /** @var ilUserExportConfig $config */
        $config = $configs->getElementByClassName('ilUserExportConfig');
        $config->setExportType('personal_data');
        $export = $export_consumer->createStandardExportByObject(
            $this->user->getId(),
            $this,
            $configs
        );
        $stream = Streams::ofString($export->getIRSSInfo()->getStream()->getContents());
        $file_name = $export->getIRSSInfo()->getFileName();
        $export->getIRSS()->delete($export_consumer->exportStakeholderHandler());
        $this->delivery->deliver($stream, $file_name);
    }

    public function getPersonalDataExportFile(): string
    {
        $dir = ilExport::_getExportDirectory($this->getId(), 'xml', 'usr', 'personal_data');
        if (!is_dir($dir)) {
            return '';
        }
        foreach (ilFileUtils::getDir($dir) as $entry) {
            if (is_int(strpos($entry['entry'], '.zip'))) {
                return $entry['entry'];
            }
        }

        return '';
    }

    public function sendPersonalDataFile(): void
    {
        $file = ilExport::_getExportDirectory($this->getId(), 'xml', 'usr', 'personal_data') .
            '/' . $this->getPersonalDataExportFile();
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
    ): void {
        $imp = new ilImport();
        // bookmarks need to be skipped, importer does not exist anymore
        $imp->addSkipImporter('components/ILIAS/Bookmarks');
        if (!$a_profile_data) {
            $imp->addSkipEntity('components/ILIAS/User', 'usr_profile');
        }
        if (!$a_settings) {
            $imp->addSkipEntity('components/ILIAS/User', 'usr_setting');
        }
        if (!$a_notes) {
            $imp->addSkipEntity('components/ILIAS/Notes', 'user_notes');
        }
        if (!$a_calendar) {
            $imp->addSkipEntity('components/ILIAS/Calendar', 'calendar');
        }
        $imp->importEntity(
            $a_file['tmp_name'],
            $a_file['name'],
            'usr',
            'components/ILIAS/User'
        );
    }

    public function setInactivationDate(?string $inactivation_date): void
    {
        $this->inactivation_date = $inactivation_date;
    }

    public function getInactivationDate(): ?string
    {
        return $this->inactivation_date;
    }

    public function isAnonymous(): bool
    {
        return self::_isAnonymous($this->getId());
    }

    public static function _isAnonymous(int $usr_id): bool
    {
        return $usr_id == ANONYMOUS_USER_ID;
    }

    public function activateDeletionFlag(): void
    {
        $this->writePref('delete_flag', true);
    }

    public function removeDeletionFlag(): void
    {
        $this->writePref('delete_flag', false);
    }

    public function hasDeletionFlag(): bool
    {
        return (bool) $this->getPref('delete_flag');
    }

    public function setIsSelfRegistered(bool $status): void
    {
        $this->is_self_registered = $status;
    }

    public function isSelfRegistered(): bool
    {
        return $this->is_self_registered;
    }

    /**
     * @param array<string>|null $value
     */
    public function setGeneralInterests(?array $value = null): void
    {
        $this->profile_data = $this->profile_data->withAdditionalFieldByIdentifier(
            $this->profile_configuration_repository->getByClass(Interests::class)->getIdentifier(),
            $value ?? []
        );
    }

    /**
     * @return array<string>
     */
    public function getGeneralInterests(): array
    {
        return $this->profile_data->getAdditionalFieldByIdentifier(
            $this->profile_configuration_repository->getByClass(Interests::class)->getIdentifier()
        ) ?? [];
    }

    /**
     * Get general interests as plain text
     */
    public function getGeneralInterestsAsText(): string
    {
        return $this->buildTextFromArray($this->getGeneralInterests());
    }

    /**
     * @param string[]|null $value
     */
    public function setOfferingHelp(?array $value = null): void
    {
        $this->profile_data = $this->profile_data->withAdditionalFieldByIdentifier(
            $this->profile_configuration_repository->getByClass(HelpOffered::class)->getIdentifier(),
            $value ?? []
        );
    }

    /**
     * @return string[]
     */
    public function getOfferingHelp(): array
    {
        return $this->profile_data->getAdditionalFieldByIdentifier(
            $this->profile_configuration_repository->getByClass(HelpOffered::class)->getIdentifier()
        ) ?? [];
    }

    /**
     * Get help offering as plain text
     */
    public function getOfferingHelpAsText(): string
    {
        return $this->buildTextFromArray($this->getOfferingHelp());
    }

    public function setLookingForHelp(?array $value = null): void
    {
        $this->profile_data = $this->profile_data->withAdditionalFieldByIdentifier(
            $this->profile_configuration_repository->getByClass(HelpLookedFor::class)->getIdentifier(),
            $value ?? []
        );
    }

    public function getLookingForHelp(): array
    {
        return $this->profile_data->getAdditionalFieldByIdentifier(
            $this->profile_configuration_repository->getByClass(HelpLookedFor::class)->getIdentifier()
        ) ?? [];
    }

    public function getLookingForHelpAsText(): string
    {
        return $this->buildTextFromArray($this->getLookingForHelp());
    }


    public function uploadPersonalPicture(
        string $tmp_file
    ): void {
        $stakeholder = new ilUserProfilePictureStakeholder();
        $stakeholder->setOwner($this->getId());
        $stream = Streams::ofResource(fopen($tmp_file, 'rb'));

        if ($this->getAvatarRid() !== null) {
            // append profile picture
            $this->irss->manage()->replaceWithStream(
                $this->getAvatarRid(),
                $stream,
                $stakeholder
            );
        } else {
            // new profile picture
            $rid = $this->irss->manage()->stream(
                $stream,
                $stakeholder
            );
        }

        $this->setAvatarRid($rid);
        $this->update();
    }

    private function buildTextFromArray(array $a_attr): string
    {
        if (count($a_attr) > 0) {
            return implode(', ', $a_attr);
        }
        return '';
    }

    /*
     * 2025-07-16, sw: Hic sunt dracones. Static methods that need to be gone!
     */

    public static function _loginExists(
        string $a_login,
        int $a_user_id = 0
    ): ?int {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = 'SELECT DISTINCT login, usr_id FROM usr_data ' .
             'WHERE login = %s';
        $types[] = 'text';
        $values[] = $a_login;

        if ($a_user_id != 0) {
            $q .= ' AND usr_id != %s ';
            $types[] = 'integer';
            $values[] = $a_user_id;
        }

        $r = $ilDB->queryF($q, $types, $values);

        if (($row = $ilDB->fetchAssoc($r))) {
            return (int) $row['usr_id'];
        }
        return null;
    }

    public static function _externalAccountExists(
        string $a_external_account,
        string $a_auth_mode
    ): bool {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT * FROM usr_data ' .
            'WHERE ext_account = %s AND auth_mode = %s',
            ['text', 'text'],
            [$a_external_account, $a_auth_mode]
        );
        return (bool) $ilDB->fetchAssoc($res);
    }

    public static function _getUsersForRole(
        int $role_id,
        int $active = -1
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];

        $ids = $rbacreview->assignedUsers($role_id);

        if (count($ids) == 0) {
            $ids = [-1];
        }

        $query = 'SELECT usr_data.*, usr_pref.value AS language
							FROM usr_data
							LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
							WHERE ' . $ilDB->in('usr_data.usr_id', $ids, false, 'integer');
        $values[] = 'language';
        $types[] = 'text';


        if (is_numeric($active) && $active > -1) {
            $query .= ' AND usr_data.active = %s';
            $values[] = $active;
            $types[] = 'integer';
        }

        $query .= ' ORDER BY usr_data.lastname, usr_data.firstname ';

        $r = $ilDB->queryF($query, $types, $values);
        $data = [];
        while ($row = $ilDB->fetchAssoc($r)) {
            $data[] = $row;
        }
        return $data;
    }

    public static function _getUsersForFolder(
        int $ref_id,
        int $active
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT usr_data.*, usr_pref.value AS language FROM usr_data LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id and usr_pref.keyword = %s WHERE 1=1';
        $types[] = 'text';
        $values[] = 'language';

        if (is_numeric($active) && $active > -1) {
            $query .= ' AND usr_data.active = %s';
            $values[] = $active;
            $types[] = 'integer';
        }

        if ($ref_id != USER_FOLDER_ID) {
            $query .= ' AND usr_data.time_limit_owner = %s';
            $values[] = $ref_id;
            $types[] = 'integer';
        }

        $query .= ' AND usr_data.usr_id != %s ';
        $values[] = ANONYMOUS_USER_ID;
        $types[] = 'integer';

        $query .= ' ORDER BY usr_data.lastname, usr_data.firstname ';

        $result = $ilDB->queryF($query, $types, $values);
        $data = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $data[] = $row;
        }

        return $data;
    }

    public static function _getUsersForGroup(
        array $a_mem_ids,
        int $active = -1
    ): array {
        return self::_getUsersForIds($a_mem_ids, $active);
    }

    public static function _getUsersForIds(
        array $a_mem_ids,
        int $active = -1,
        int $timelimitowner = -1
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
		          WHERE ' . $ilDB->in('usr_data.usr_id', $a_mem_ids, false, 'integer') . '
					AND usr_data.usr_id != %s';
        $values[] = 'language';
        $types[] = 'text';
        $values[] = ANONYMOUS_USER_ID;
        $types[] = 'integer';

        if (is_numeric($active) && $active > -1) {
            $query .= ' AND active = %s';
            $values[] = $active;
            $types[] = 'integer';
        }

        if ($timelimitowner != USER_FOLDER_ID && $timelimitowner != -1) {
            $query .= ' AND usr_data.time_limit_owner = %s';
            $values[] = $timelimitowner;
            $types[] = 'integer';
        }

        $query .= ' ORDER BY usr_data.lastname, usr_data.firstname ';

        $result = $ilDB->queryF($query, $types, $values);
        $mem_arr = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $mem_arr[] = $row;
        }

        return $mem_arr;
    }

    public static function _getUserData(array $a_internalids): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ids = [];
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

        $query = 'SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref
		          ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = %s
		          WHERE ' . $ilDB->in('usr_data.usr_id', $ids, false, 'integer');
        $values[] = 'language';
        $types[] = 'text';

        $query .= ' ORDER BY usr_data.lastname, usr_data.firstname ';

        $data = [];
        $result = $ilDB->queryF($query, $types, $values);
        while ($row = $ilDB->fetchAssoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public static function getUserSubsetByPreferenceValue(
        array $a_user_ids,
        string $a_keyword,
        string $a_val
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $users = [];
        $set = $ilDB->query(
            'SELECT usr_id FROM usr_pref ' .
            ' WHERE keyword = ' . $ilDB->quote($a_keyword, 'text') .
            ' AND ' . $ilDB->in('usr_id', $a_user_ids, false, 'integer') .
            ' AND value = ' . $ilDB->quote($a_val, 'text')
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $users[] = $rec['usr_id'];
        }
        return $users;
    }

    public static function _getLoginAttempts(
        int $a_usr_id
    ): int {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT login_attempts FROM usr_data WHERE usr_id = %s';
        $result = $ilDB->queryF($query, ['integer'], [$a_usr_id]);
        $record = $ilDB->fetchAssoc($result);
        return (int) ($record['login_attempts'] ?? 0);
    }

    public static function _incrementLoginAttempts(
        int $a_usr_id
    ): bool {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'UPDATE usr_data SET login_attempts = (login_attempts + 1) WHERE usr_id = %s';
        $affected = $ilDB->manipulateF($query, ['integer'], [$a_usr_id]);

        if ($affected) {
            return true;
        } else {
            return false;
        }
    }

    public static function _setUserInactive(
        int $a_usr_id
    ): bool {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'UPDATE usr_data SET active = 0, inactivation_date = %s WHERE usr_id = %s';
        $affected = $ilDB->manipulateF($query, ['timestamp', 'integer'], [ilUtil::now(), $a_usr_id]);

        if ($affected) {
            return true;
        } else {
            return false;
        }
    }

    public static function _getUsersOnline(
        int $a_user_id = 0,
        bool $a_no_anonymous = false
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $log = ilLoggerFactory::getLogger('user');

        $pd_set = new ilSetting('pd');
        $atime = $pd_set->get('user_activity_time') * 60;
        $ctime = time();

        $where = [];

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
            {$where}
			GROUP BY user_id, firstname, lastname, title, login, last_login, context, agree_date
			ORDER BY lastname, firstname
			",
            ['text'],
            ['hide_own_online_status']
        );

        $log->debug('Query: ' . $q);

        $users = [];
        while ($user = $ilDB->fetchAssoc($r)) {
            if ($atime <= 0 || $user['ctime'] + $atime > $ctime) {
                $users[$user['user_id']] = $user;
            }
        }

        $log->debug('Found users: ' . count($users));

        $hide_users = $DIC['legalDocuments']->usersWithHiddenOnlineStatus(array_map(intval(...), array_column($users, 'user_id')));
        $users = array_filter(
            $users,
            fn($user) => !in_array((int) $user['user_id'], $hide_users, true)
        );

        return $users;
    }

    public static function getUserIdsByInactivityPeriod(
        int $periodInDays
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($periodInDays < 1) {
            throw new ilException('Invalid period given');
        }

        $date = date('Y-m-d H:i:s', (time() - ($periodInDays * 24 * 60 * 60)));

        $query = 'SELECT usr_id FROM usr_data WHERE last_login IS NOT NULL AND last_login < %s';

        $ids = [];

        $types = ['timestamp'];
        $values = [$date];

        $res = $ilDB->queryF($query, $types, $values);
        while ($row = $ilDB->fetchAssoc($res)) {
            $ids[] = (int) $row['usr_id'];
        }

        return $ids;
    }

    public static function getUserIdsNeverLoggedIn(
        int $thresholdInDays
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $date = date('Y-m-d H:i:s', (time() - ($thresholdInDays * 24 * 60 * 60)));

        $query = 'SELECT usr_id FROM usr_data WHERE last_login IS NULL AND create_date < %s';

        $ids = [];

        $types = ['timestamp'];
        $values = [$date];

        $res = $ilDB->queryF($query, $types, $values);
        while ($row = $ilDB->fetchAssoc($res)) {
            $ids[] = (int) $row['usr_id'];
        }

        return $ids;
    }

    public static function _getUserIdsByInactivationPeriod(
        int $period
    ): array {
        if (!$period) {
            throw new ilException('no valid period given');
        }

        global $DIC;
        $db = $DIC['ilDB'];

        $res = $db->queryF(
            'SELECT usr_id FROM usr_data WHERE inactivation_date < %s AND active = %s',
            ['timestamp', 'integer'],
            [
                date('Y-m-d H:i:s', (time() - ($period * 24 * 60 * 60))),
                0
            ]
        );

        $ids = [];
        while ($row = $db->fetchObject($res)) {
            $ids[] = (int) $row->usr_id;
        }

        return $ids;
    }

    public static function getFirstLettersOfLastnames(
        ?array $user_ids = null
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = 'SELECT DISTINCT ' . $ilDB->upper($ilDB->substr('lastname', 1, 1)) . ' let' .
            ' FROM usr_data' .
            ' WHERE usr_id <> ' . $ilDB->quote(ANONYMOUS_USER_ID, 'integer') .
            ($user_ids !== null ? ' AND ' . $ilDB->in('usr_id', $user_ids, false, 'integer') : '') .
            ' ORDER BY let';
        $let_set = $ilDB->query($q);

        $let = [];
        while ($let_rec = $ilDB->fetchAssoc($let_set)) {
            $let[$let_rec['let']] = $let_rec['let'];
        }
        return $let;
    }

    public static function userExists(
        array $a_usr_ids = []
    ): bool {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT count(*) num FROM object_data od ' .
                'JOIN usr_data ud ON obj_id = usr_id ' .
                'WHERE ' . $ilDB->in('obj_id', $a_usr_ids, false, 'integer') . ' ';
        $res = $ilDB->query($query);
        $num_rows = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)->num;
        return $num_rows == count($a_usr_ids);
    }

    public static function _doesLoginnameExistInHistory(string $a_login): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            '
			SELECT * FROM loginname_history
			WHERE login = %s',
            ['text'],
            [$a_login]
        );

        return (bool) $ilDB->fetchAssoc($res);
    }

    public static function _lookupPref(
        int $a_usr_id,
        string $a_keyword
    ): ?string {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM usr_pref WHERE usr_id = ' . $ilDB->quote($a_usr_id, 'integer') . ' ' .
            'AND keyword = ' . $ilDB->quote($a_keyword, 'text');
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->value;
        }
        return null;
    }

    public static function lookupMatriculation(int $a_usr_id): string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT matriculation FROM usr_data ' .
            'WHERE usr_id = ' . $ilDB->quote($a_usr_id);
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->matriculation ?: '';
    }

    public static function findInterests(
        string $a_term,
        ?int $a_user_id = null,
        ?string $a_field_id = null
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = [];

        $sql = 'SELECT DISTINCT(value)' .
            ' FROM usr_profile_data' .
            ' WHERE ' . $ilDB->like('value', 'text', '%' . $a_term . '%');
        if ($a_field_id) {
            $sql .= ' AND field_id = ' . $ilDB->quote($a_field_id, 'text');
        }
        if ($a_user_id) {
            $sql .= ' AND usr_id <> ' . $ilDB->quote($a_user_id, 'integer');
        }
        $sql .= ' ORDER BY value';
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row['value'];
        }

        return $res;
    }

    public static function getProfileStatusOfUsers(
        array $a_user_ids
    ): array {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query(
            'SELECT * FROM usr_pref ' .
                ' WHERE keyword = ' . $ilDB->quote('public_profile', 'text') .
                ' AND ' . $ilDB->in('usr_id', $a_user_ids, false, 'integer')
        );
        $r = [
            'global' => [],
            'local' => [],
            'public' => [],
            'not_public' => []
        ];
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec['value'] == 'g') {
                $r['global'][] = $rec['usr_id'];
                $r['public'][] = $rec['usr_id'];
            }
            if ($rec['value'] == 'y') {
                $r['local'][] = $rec['usr_id'];
                $r['public'][] = $rec['usr_id'];
            }
        }
        foreach ($a_user_ids as $id) {
            if (!in_array($id, $r['public'])) {
                $r['not_public'][] = $id;
            }
        }

        return $r;
    }

    private static function _lookup(
        int $a_user_id,
        string $a_field
    ): ?string {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT ' . $a_field . ' FROM usr_data WHERE usr_id = %s',
            ['integer'],
            [$a_user_id]
        );

        while ($set = $ilDB->fetchAssoc($res)) {
            return $set[$a_field];
        }
        return null;
    }

    public static function _lookupFullname(int $a_user_id): string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $fullname = '';

        $set = $ilDB->queryF(
            'SELECT title, firstname, lastname FROM usr_data WHERE usr_id = %s',
            ['integer'],
            [$a_user_id]
        );

        if ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec['title']) {
                $fullname = $rec['title'] . ' ';
            }
            if ($rec['firstname']) {
                $fullname .= $rec['firstname'] . ' ';
            }
            if ($rec['lastname']) {
                $fullname .= $rec['lastname'];
            }
        }
        return $fullname;
    }

    public static function _lookupEmail(int $a_user_id): string
    {
        return self::_lookup($a_user_id, 'email') ?? '';
    }

    public static function _lookupGender(int $a_user_id): string
    {
        return (string) self::_lookup($a_user_id, 'gender') ?? '';
    }

    public static function _lookupClientIP(int $a_user_id): string
    {
        return self::_lookup($a_user_id, 'client_ip') ?? '';
    }

    public static function _lookupName(int $a_user_id): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT firstname, lastname, title, login FROM usr_data WHERE usr_id = %s',
            ['integer'],
            [$a_user_id]
        );
        if (($user_rec = $ilDB->fetchAssoc($res))) {
            return ['user_id' => $a_user_id,
                         'firstname' => $user_rec['firstname'],
                         'lastname' => $user_rec['lastname'],
                         'title' => $user_rec['title'],
                         'login' => $user_rec['login']
            ];
        }
        return ['user_id' => 0,
                     'firstname' => '',
                     'lastname' => '',
                     'title' => '',
                     'login' => ''
        ];
    }

    public static function _lookupLanguage(int $a_usr_id): string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        $q = 'SELECT value FROM usr_pref WHERE usr_id= ' .
            $ilDB->quote($a_usr_id, 'integer') . ' AND keyword = ' .
            $ilDB->quote('language', 'text');
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
    ): void {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            'UPDATE usr_data ' .
            ' SET ext_account = %s WHERE usr_id = %s',
            ['text', 'integer'],
            [$a_ext_id, $a_usr_id]
        );
    }

    public static function _writeAuthMode(int $a_usr_id, string $a_auth_mode): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            'UPDATE usr_data ' .
            ' SET auth_mode = %s WHERE usr_id = %s',
            ['text', 'integer'],
            [$a_auth_mode, $a_usr_id]
        );
    }

    /**
     * @deprecated
     */
    public static function _lookupFields(int $a_user_id): array // Missing array type.
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT * FROM usr_data WHERE usr_id = %s',
            ['integer'],
            [$a_user_id]
        );
        $user_rec = $ilDB->fetchAssoc($res);
        return $user_rec;
    }

    public static function _lookupActive(int $a_usr_id): bool
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

    public static function _lookupLogin(int $a_user_id): string
    {
        return (string) self::_lookup($a_user_id, 'login') ?? '';
    }

    public static function _lookupExternalAccount(int $a_user_id): string
    {
        return (string) self::_lookup($a_user_id, 'ext_account') ?? '';
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
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        $q = 'SELECT login,usr_id,ext_account,auth_mode FROM usr_data ' .
            'WHERE auth_mode = %s';
        $types[] = 'text';
        $values[] = $a_auth_mode;
        if ($a_read_auth_default and ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode', ilAuthUtils::AUTH_LOCAL)) == $a_auth_mode) {
            $q .= ' OR auth_mode = %s ';
            $types[] = 'text';
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
        array $usr_ids,
        bool $a_status
    ): void {
        global $DIC;

        $db = $DIC['ilDB'];

        if ($a_status) {
            $db->manipulate(
                'UPDATE usr_data SET active = 1, inactivation_date = NULL' . PHP_EOL
                . "WHERE {$db->in('usr_id', $usr_ids, false, 'integer')}"
            );
            return;
        }

        $in_part = $db->in('usr_id', $usr_ids, false, 'integer');
        $db->manipulate(
            "UPDATE usr_data SET active = 0 WHERE {$in_part}"
        );
        $db->manipulateF(
            'UPDATE usr_data SET inactivation_date = %s' . PHP_EOL
            . "WHERE inactivation_date IS NULL AND {$in_part}",
            ['timestamp'],
            [(new \DateTimeImmutable('@' . time(), new DateTimeZone('UTC')))
                ->format(self::DATABASE_DATE_FORMAT)]
        );
    }

    public static function _lookupAuthMode(int $a_usr_id): string
    {
        return (string) self::_lookup($a_usr_id, 'auth_mode');
    }

    /**
     * check whether external account and authentication method
     * matches with a user
     */
    public static function _checkExternalAuthAccount(
        string $a_auth,
        string $a_account,
        bool $tryFallback = true
    ): ?string {
        $db = $GLOBALS['DIC']->database();
        $settings = $GLOBALS['DIC']->settings();

        // Check directly with auth_mode
        $r = $db->queryF(
            'SELECT * FROM usr_data WHERE ' .
            ' ext_account = %s AND auth_mode = %s',
            ['text', 'text'],
            [$a_account, $a_auth]
        );
        if ($usr = $db->fetchAssoc($r)) {
            return $usr['login'];
        }

        if (!$tryFallback) {
            return null;
        }

        // For compatibility, check for login (no ext_account entry given)
        $res = $db->queryF(
            'SELECT login FROM usr_data ' .
            'WHERE login = %s AND auth_mode = %s AND (ext_account IS NULL OR ext_account = "") ',
            ['text', 'text'],
            [$a_account, $a_auth]
        );
        if ($usr = $db->fetchAssoc($res)) {
            return $usr['login'];
        }

        // If auth_default == $a_auth => check for login
        if (ilAuthUtils::_getAuthModeName($settings->get('auth_mode')) == $a_auth) {
            $res = $db->queryF(
                'SELECT login FROM usr_data WHERE ' .
                ' ext_account = %s AND auth_mode = %s',
                ['text', 'text'],
                [$a_account, 'default']
            );
            if ($usr = $db->fetchAssoc($res)) {
                return $usr['login'];
            }
            // Search for login (no ext_account given)
            $res = $db->queryF(
                'SELECT login FROM usr_data ' .
                'WHERE login = %s AND (ext_account IS NULL OR ext_account = "") AND auth_mode = %s',
                ['text', 'text'],
                [$a_account, 'default']
            );
            if ($usr = $db->fetchAssoc($res)) {
                return $usr['login'];
            }
        }
        return null;
    }

    public static function getUserIdByLogin(string $a_login): int
    {
        return (int) self::_lookupId($a_login);
    }

    public static function getUserIdsByEmail(string $a_email): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT usr_id FROM usr_data ' .
            'WHERE email = %s and active = 1',
            ['text'],
            [$a_email]
        );
        $ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $ids[] = (int) $row->usr_id;
        }

        return $ids;
    }

    public static function getUserLoginsByEmail(string $a_email): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT login FROM usr_data ' .
            'WHERE email = %s and active = 1',
            ['text'],
            [$a_email]
        );
        $ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $ids[] = $row->login;
        }

        return $ids;
    }

    public static function _lookupId(
        string|array $a_user_str
    ): int|null|array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!is_array($a_user_str)) {
            $res = $ilDB->queryF(
                'SELECT usr_id FROM usr_data WHERE login = %s',
                ['text'],
                [$a_user_str]
            );

            $user_rec = $ilDB->fetchAssoc($res);
            if (is_array($user_rec)) {
                return (int) $user_rec['usr_id'];
            }

            return null;
        }

        $set = $ilDB->query(
            'SELECT usr_id FROM usr_data ' .
            ' WHERE ' . $ilDB->in('login', $a_user_str, false, 'text')
        );

        $ids = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = (int) $rec['usr_id'];
        }

        return $ids;
    }

    public static function _lookupLastLogin(int $a_user_id): string
    {
        return self::_lookup($a_user_id, 'last_login') ?? '';
    }

    public static function _lookupFirstLogin(int $a_user_id): string
    {
        return self::_lookup($a_user_id, 'first_login') ?? '';
    }

    public static function hasActiveSession(
        int $a_user_id,
        string $a_session_id
    ): bool {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $set = $ilDB->queryf(
            '
			SELECT COUNT(*) session_count
			FROM usr_session WHERE user_id = %s AND expires > %s AND session_id != %s ',
            ['integer', 'integer', 'text'],
            [$a_user_id, time(), $a_session_id]
        );
        $row = $ilDB->fetchAssoc($set);
        return (bool) $row['session_count'];
    }

    public static function _readUsersProfileData(array $a_user_ids): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->query('SELECT * FROM usr_data WHERE ' .
            $ilDB->in('usr_id', $a_user_ids, false, 'integer'));
        $user_data = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $user_data[$row['usr_id']] = $row;
        }
        return $user_data;
    }

    public static function _getNumberOfUsersForStyle(
        string $a_skin,
        string $a_style
    ): int {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = 'SELECT count(*) as cnt FROM usr_pref up1, usr_pref up2 ' .
            ' WHERE up1.keyword= ' . $ilDB->quote('style', 'text') .
            ' AND up1.value= ' . $ilDB->quote($a_style, 'text') .
            ' AND up2.keyword= ' . $ilDB->quote('skin', 'text') .
            ' AND up2.value= ' . $ilDB->quote($a_skin, 'text') .
            ' AND up1.usr_id = up2.usr_id ';

        $cnt_set = $ilDB->query($q);

        $cnt_rec = $ilDB->fetchAssoc($cnt_set);

        return (int) $cnt_rec['cnt'];
    }

    public static function _getAllUserAssignedStyles(): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = 'SELECT DISTINCT up1.value style, up2.value skin FROM usr_pref up1, usr_pref up2 ' .
            ' WHERE up1.keyword = ' . $ilDB->quote('style', 'text') .
            ' AND up2.keyword = ' . $ilDB->quote('skin', 'text') .
            ' AND up1.usr_id = up2.usr_id';

        $sty_set = $ilDB->query($q);

        $styles = [];
        while ($sty_rec = $ilDB->fetchAssoc($sty_set)) {
            $styles[] = $sty_rec['skin'] . ':' . $sty_rec['style'];
        }

        return $styles;
    }

    /**
     * get number of users per auth mode
     */
    public static function _getNumberOfUsersPerAuthMode(): array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $r = $ilDB->query('SELECT count(*) AS cnt, auth_mode FROM usr_data ' .
            'GROUP BY auth_mode');
        $cnt_arr = [];
        while ($cnt = $ilDB->fetchAssoc($r)) {
            $cnt_arr[$cnt['auth_mode']] = (int) $cnt['cnt'];
        }

        return $cnt_arr;
    }

    public static function _getLocalAccountsForEmail(string $a_email): array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        // default set to local (1)?

        $q = 'SELECT * FROM usr_data WHERE ' .
            ' email = %s AND (auth_mode = %s ';
        $types = ['text', 'text'];
        $values = [$a_email, 'local'];

        if ($ilSetting->get('auth_mode') == 1) {
            $q .= ' OR auth_mode = %s';
            $types[] = 'text';
            $values[] = 'default';
        }

        $q .= ')';

        $users = [];
        $usr_set = $ilDB->queryF($q, $types, $values);
        while ($usr_rec = $ilDB->fetchAssoc($usr_set)) {
            $users[$usr_rec['usr_id']] = $usr_rec['login'];
        }

        return $users;
    }

    public static function _moveUsersToStyle(
        string $a_from_skin,
        string $a_from_style,
        string $a_to_skin,
        string $a_to_style
    ): void {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = 'SELECT up1.usr_id usr_id FROM usr_pref up1, usr_pref up2 ' .
            ' WHERE up1.keyword= ' . $ilDB->quote('style', 'text') .
            ' AND up1.value= ' . $ilDB->quote($a_from_style, 'text') .
            ' AND up2.keyword= ' . $ilDB->quote('skin', 'text') .
            ' AND up2.value= ' . $ilDB->quote($a_from_skin, 'text') .
            ' AND up1.usr_id = up2.usr_id ';

        $usr_set = $ilDB->query($q);

        while ($usr_rec = $ilDB->fetchAssoc($usr_set)) {
            $ilDB->replace(
                'usr_pref',
                [
                    'usr_id' => [ilDBConstants::T_INTEGER, $usr_rec['usr_id']],
                    'keyword' => [ilDBConstants::T_TEXT, 'skin'],
                ],
                [
                    'value' => [ilDBConstants::T_TEXT, $a_to_skin]
                ]
            );
            $ilDB->replace(
                'usr_pref',
                [
                    'usr_id' => [ilDBConstants::T_INTEGER, $usr_rec['usr_id']],
                    'keyword' => [ilDBConstants::T_TEXT, 'style'],
                ],
                [
                    'value' => [ilDBConstants::T_TEXT, $a_to_style]
                ]
            );
        }
    }

    public static function _getUsersForClipboadObject(
        string $a_type,
        int $a_id
    ): array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = 'SELECT DISTINCT user_id FROM personal_clipboard WHERE ' .
            'item_id = ' . $ilDB->quote($a_id, 'integer') . ' AND ' .
            'type = ' . $ilDB->quote($a_type, 'text');
        $user_set = $ilDB->query($q);
        $users = [];
        while ($user_rec = $ilDB->fetchAssoc($user_set)) {
            $users[] = (int) $user_rec['user_id'];
        }

        return $users;
    }

    public static function _getImportedUserId(
        string $i2_id
    ): int {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT obj_id FROM object_data WHERE import_id = ' .
            $ilDB->quote($i2_id, 'text');

        $res = $ilDB->query($query);
        $id = 0;
        while ($row = $ilDB->fetchObject($res)) {
            $id = (int) $row->obj_id;
        }
        return $id;
    }

    public static function lookupOrgUnitsRepresentation(
        int $a_usr_id
    ): string {
        return ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($a_usr_id);
    }

    public static function _getAvatar(int $a_usr_id): Avatar
    {
        $define = new ilUserAvatarResolver($a_usr_id ?: ANONYMOUS_USER_ID);
        $define->setSize('xsmall');
        return $define->getAvatar();
    }

    public static function _getPersonalPicturePath(
        int $usr_id,
        string $size = 'small',
        bool $force_pic = false
    ): string {
        $define = new ilUserAvatarResolver($usr_id);
        $define->setForcePicture($force_pic);
        $define->setSize($size);
        return $define->getLegacyPictureURL();
    }

    public static function copyProfilePicturesToDirectory(
        int $a_user_id,
        string $a_dir
    ): void {
        global $DIC;
        $irss = $DIC->resourceStorage();

        $clean_dir = trim(str_replace('..', '', $a_dir));
        if ($clean_dir == '' || !is_dir($clean_dir)) {
            return;
        }
        $avatar_rid = (new ilObjUser($a_user_id))->getAvatarRid();
        if ($avatar_rid === null) {
            return;
        }

        file_put_contents(
            $clean_dir . '/usr_' . $a_user_id . '.jpg',
            $irss->consume()->stream($avatar_rid)->getStream()->getContents()
        );
    }

    public static function _lookupFeedHash(
        int $a_user_id,
        bool $a_create = false
    ): ?string {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($a_user_id > 0) {
            $set = $ilDB->queryF(
                'SELECT feed_hash from usr_data WHERE usr_id = %s',
                ['integer'],
                [$a_user_id]
            );
            if ($rec = $ilDB->fetchAssoc($set)) {
                if (strlen($rec['feed_hash']) == 32) {
                    return $rec['feed_hash'];
                } elseif ($a_create) {
                    $hash = md5(random_int(1, 9999999) + str_replace(' ', '', microtime()));
                    $ilDB->manipulateF(
                        'UPDATE usr_data SET feed_hash = %s' .
                        ' WHERE usr_id = %s',
                        ['text', 'integer'],
                        [$hash, $a_user_id]
                    );
                    return $hash;
                }
            }
        }
        return null;
    }

    public static function _getFeedPass(
        int $a_user_id
    ): ?string {
        if ($a_user_id > 0) {
            return self::_lookupPref($a_user_id, 'priv_feed_pass');
        }
        return null;
    }
}
