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

declare(strict_types=1);

use ILIAS\User\LocalDIC;
use ILIAS\User\Context;
use ILIAS\User\BuildExportFieldArray;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Settings\DataRepository as UserSettingsDataRepository;
use ILIAS\User\Settings\Settings;
use ILIAS\User\Settings\SettingsImplementation;
use ILIAS\Language\Language;

/**
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserXMLWriter extends ilXmlWriter
{
    use BuildExportFieldArray;

    private readonly ILIAS $ilias;
    private readonly ilDBInterface $db;
    private readonly Language $lng;
    private readonly Profile $user_profile;
    private readonly SettingsImplementation $user_settings;
    private readonly UserSettingsDataRepository $user_settings_data_repo;
    private array $users; // Missing array type.
    private int $user_id = 0;
    private bool $attach_roles = false;
    private bool $attach_preferences = false;

    /**
     * fields to be exported
     *
     */
    private array $fields_to_export = [];

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ilias = $DIC['ilias'];
        $this->db = $DIC['ilDB'];
        $this->lng = $DIC['lng'];
        $this->user_id = $DIC['ilUser']->getId();

        $local_dic = LocalDIC::dic();
        $this->user_profile = $local_dic[Profile::class];
        $this->user_settings = LocalDIC::dic()[Settings::class];
        $this->user_settings_data_repo = $local_dic[UserSettingsDataRepository::class];


        $this->attach_roles = false;

        parent::__construct();
    }

    public function setAttachRoles(bool $value): void
    {
        $this->attach_roles = $value;
    }

    public function setObjects(array $users): void // Missing array type.
    {
        $this->users = $users;
    }


    public function start(): bool
    {
        if (!is_array($this->users)) {
            return false;
        }

        $this->__buildHeader();

        foreach ($this->users as $user) {
            $this->__handleUser($user);
        }

        $this->__buildFooter();

        return true;
    }

    public function getXML(): string
    {
        return $this->xmlDumpMem(false);
    }


    public function __buildHeader(): void
    {
        $this->xmlSetDtdDef('<!DOCTYPE Users PUBLIC "-//ILIAS//DTD UserImport//EN" "' . ILIAS_HTTP_PATH . '/components/ILIAS/Export/xml/ilias_user_5_1.dtd">');
        $this->xmlSetGenCmt('User of ilias system');
        $this->xmlHeader();

        $this->xmlStartTag('Users');
    }

    public function __buildFooter(): void
    {
        $this->xmlEndTag('Users');
    }

    public function __handleUser(array $row): void // Missing array type.
    {
        if ($this->fields_to_export === []) {
            $this->setFieldsToExport(
                array_merge(
                    $this->getExportFieldArray(
                        $this->lng,
                        $this->user_profile,
                        $this->user_settings
                    ),
                    ['time_limit_owner']
                )
            );
        }

        $settings = $this->user_settings_data_repo->getFor($row['usr_id']);

        if ($row['language'] === null
            || $row['language'] === '') {
            $row['language'] = $this->lng->getDefaultLanguage();
        }

        $attrs = [
            'Id' => 'il_' . IL_INST_ID . '_usr_' . $row['usr_id'],
            'Language' => $row['language'],
            'Action' => 'Update'
        ];

        $this->xmlStartTag('User', $attrs);

        $this->xmlElement('Login', null, $row['login']);

        if ($this->attach_roles == true) {
            $query = sprintf(
                'SELECT object_data.title, object_data.description,  rbac_fa.* ' .
                            'FROM object_data, rbac_ua, rbac_fa WHERE rbac_ua.usr_id = %s ' .
                            'AND rbac_ua.rol_id = rbac_fa.rol_id AND object_data.obj_id = rbac_fa.rol_id',
                $this->db->quote($row['usr_id'], 'integer')
            );
            $rbacresult = $this->db->query($query);

            while ($rbacrow = $rbacresult->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
                if ($rbacrow['assign'] != 'y') {
                    continue;
                }

                $type = '';

                if ($rbacrow['parent'] == ROLE_FOLDER_ID) {
                    $type = 'Global';
                } else {
                    $type = 'Local';
                }
                if ($type !== '') {
                    $this->xmlElement(
                        'Role',
                        ['Id' => 'il_' . IL_INST_ID . '_role_' . $rbacrow['rol_id'], 'Type' => $type],
                        $rbacrow['title']
                    );
                }
            }
        }

        $this->__addElement('Firstname', $row['firstname']);
        $this->__addElement('Lastname', $row['lastname']);
        $this->__addElement('Title', $row['title']);

        if ($this->canExport('PersonalPicture', 'upload')) {
            $imageData = $this->getPictureValue($row['usr_id']);
            if ($imageData) {
                $value = array_shift($imageData); //$imageData['value'];
                $this->__addElement('PersonalPicture', $value, $imageData, 'upload');
            }
        }


        $this->__addElement('Gender', $row['gender']);
        $this->__addElement('Email', $row['email']);
        $this->__addElement('SecondEmail', $row['second_email'], null, 'second_email');
        $this->__addElement('Birthday', $row['birthday']);
        $this->__addElement('Institution', $row['institution']);
        $this->__addElement('Street', $row['street']);
        $this->__addElement('City', $row['city']);
        $this->__addElement('PostalCode', $row['zipcode'], null, 'zipcode');
        $this->__addElement('Country', $row['country']);
        $this->__addElement('PhoneOffice', $row['phone_office'], null, 'phone_office');
        $this->__addElement('PhoneHome', $row['phone_home'], null, 'phone_home');
        $this->__addElement('PhoneMobile', $row['phone_mobile'], null, 'phone_mobile');
        $this->__addElement('Fax', $row['fax']);
        $this->__addElement('Hobby', $row['hobby']);

        $this->__addElementMulti('GeneralInterest', $row['interests_general'] ?? [], null, 'interests_general');
        $this->__addElementMulti('OfferingHelp', $row['interests_help_offered'] ?? [], null, 'interests_help_offered');
        $this->__addElementMulti('LookingForHelp', $row['interests_help_looking'] ?? [], null, 'interests_help_looking');

        $this->__addElement('Department', $row['department']);
        $this->__addElement('Comment', $row['referral_comment'], null, 'referral_comment');
        $this->__addElement('Matriculation', $row['matriculation']);
        $this->__addElement('Active', $row['active'] ? 'true' : 'false');
        $this->__addElement('ClientIP', $row['client_ip'], null, 'client_ip');
        $this->__addElement('TimeLimitOwner', (string) $row['time_limit_owner'], null, 'time_limit_owner');
        $this->__addElement('TimeLimitUnlimited', (string) $row['time_limit_unlimited'], null, 'time_limit_unlimited');

        if ($row['time_limit_from'] !== null) {
            $this->__addElement('TimeLimitFrom', (string) $row['time_limit_from'], null, 'time_limit_from');
        }

        if ($row['time_limit_from'] !== null) {
            $this->__addElement('TimeLimitUntil', (string) $row['time_limit_until'], null, 'time_limit_until');
        }

        $this->__addElement('ApproveDate', $row['approve_date'], null, 'approve_date');
        $this->__addElement('AgreeDate', $row['agree_date'], null, 'agree_date');

        if ($row['auth_mode'] !== null
            && $row['auth_mode'] !== '') {
            $this->__addElement('AuthMode', null, ['type' => $row['auth_mode']], 'auth_mode', true);
        }

        if ($row['ext_account'] !== null
            && $row['ext_account'] !== '') {
            $this->__addElement('ExternalAccount', $row['ext_account'], null, 'ext_account', true);
        }

        if (isset($settings['skin'])
            && isset($settings['style'])
            && $this->canExport('Look', 'skin_style')) {
            $this->__addElement(
                'Look',
                null,
                [
                    'Skin' => $settings['skin'], 'Style' => $settings['style']
                ],
                'skin_style',
                true
            );
        }


        $this->__addElement('LastUpdate', $row['last_update'], null, 'last_update');
        $this->__addElement('LastLogin', $row['last_login'], null, 'last_login');

        $this->addUDFsToXML($row);

        $this->__addElement('AccountInfo', $row['ext_account'], ['Type' => 'external']);

        $this->__addElement('GMapsInfo', null, [
            'longitude' => $row['longitude'],
            'latitude' => $row['latitude'],
            'zoom' => $row['loc_zoom']]);

        $this->__addElement('Feedhash', $row['feed_hash']);

        if ($this->attach_preferences || $this->canExport('prefs', 'preferences')) {
            $this->__handlePreferences($settings, $row);
        }

        $this->xmlEndTag('User');
    }


    private function __handlePreferences(array $prefs, array $row): void // Missing array type.
    {
        //todo nadia: test mail_address_option
        $mailOptions = new ilMailOptions($row['usr_id']);
        $prefs['mail_incoming_type'] = $mailOptions->getIncomingType();
        $prefs['mail_address_option'] = $mailOptions->getEmailAddressMode();
        $prefs['mail_signature'] = $mailOptions->getSignature();
        if ($prefs !== []) {
            $this->xmlStartTag('Prefs');
            foreach ($prefs as $key => $value) {
                if (self::isPrefExportable($key)) {
                    $this->xmlElement('Pref', ['key' => $key], $value);
                }
            }
            $this->xmlEndTag('Prefs');
        }
    }

    public function __addElementMulti(
        string $tagname,
        array $value,
        ?array $attrs = null,
        ?string $settingsname = null,
        bool $requiredTag = false
    ): void {
        foreach ($value as $item) {
            $this->__addElement($tagname, $item, $attrs, $settingsname, $requiredTag);
        }
    }

    public function __addElement(
        string $tagname,
        ?string $value,
        ?array $attrs = null,
        ?string $settingsname = null,
        bool $requiredTag = false
    ): void {
        if ($this->canExport($tagname, $settingsname)
            && ($value !== null
                || $requiredTag
                || is_array($attrs) && count($attrs) > 0)) {
            $this->xmlElement($tagname, $attrs, (string) $value);
        }
    }

    private function canExport(
        string $tagname,
        ?string $settingsname = null
    ): bool {
        return $this->fields_to_export === []
            || in_array(strtolower($tagname), $this->fields_to_export) !== false
            || in_array($settingsname, $this->fields_to_export) !== false;
    }

    public function setFieldsToExport(array $fields_to_export): void // Missing array type.
    {
        $this->fields_to_export = $fields_to_export;
    }

    /**
     * if set to true, all preferences of a user will be set
     */
    public function setAttachPreferences(bool $attach_preferences): void
    {
        $this->attach_preferences = $attach_preferences;
    }

    /**
     * return array with base-encoded picture data as key value,
     * encoding type as encoding, and image type as key type.
     */
    private function getPictureValue(int $usr_id): ?array
    {
        $avatar_resolver = new ilUserAvatarResolver($usr_id);
        $avatar_resolver->setForcePicture(true);
        if (!$avatar_resolver->hasProfilePicture()) {
            return null;
        }

        [$image_data, $image_type] = $avatar_resolver->getUserPictureForVCard();

        if ($image_data === null || $image_type === null) {
            return null;
        }

        return [
            'value' => base64_encode($image_data),
            'encoding' => 'Base64',
            'imagetype' => $image_type
        ];
    }

    /**
     * add user defined field data to xml (using usr dtd)
     */
    private function addUDFsToXML(
        array $row
    ): void {
        foreach ($this->user_profile->getVisibleFields(Context::Export) as $field) {
            if (!$field->isCustom()
                || !array_key_exists($field->getIdentifier(), $row)) {
                continue;
            }
            $this->xmlElement(
                'UserDefinedField',
                [
                    'Id' => $field->getIdentifier(),
                    'Name' => $field->getLabel($this->lng)
                ],
                json_encode($row[$field->getIdentifier()])
            );
        }
    }

    /**
     * return exportable preference keys as found in db
     */
    public static function getExportablePreferences(): array // Missing array type.
    {
        return [
                'public_city',
                'public_country',
                'public_department',
                'public_email',
                'public_second_email',
                'public_fax',
                'public_hobby',
                'public_institution',
                'public_matriculation',
                'public_phone',
                'public_phone_home',
                'public_phone_mobile',
                'public_phone_office',
                'public_profile',
                'public_street',
                'public_avatar',
                'public_zip',
                'send_info_mails',
                /*'show_users_online',*/
                'hide_own_online_status',
                'bs_allow_to_contact_me',
                'chat_osc_accept_msg',
                'chat_broadcast_typing',
                'user_tz',
                'weekstart',
                'mail_incoming_type',
                'mail_signature',
                'mail_linebreak',
                'public_interests_general',
                'public_interests_help_offered',
                'public_interests_help_looking'
        ];
    }

    /**
     * returns wether a key from db is exportable or not
     */
    public static function isPrefExportable(string $key): bool
    {
        return in_array($key, self::getExportablePreferences());
    }
}
