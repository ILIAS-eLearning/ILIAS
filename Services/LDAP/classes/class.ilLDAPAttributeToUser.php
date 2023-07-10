<?php

declare(strict_types=1);

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
*
* Update/create ILIAS user account by given LDAP attributes according to user attribute mapping settings.
*
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilLDAPAttributeToUser
{
    public const MODE_INITIALIZE_ROLES = 1;

    private array $modes = [];
    private ilLDAPServer $server_settings;
    private array $user_data = [];
    private ilLDAPAttributeMapping $mapping;
    private string $new_user_auth_mode = 'ldap';
    private ilLogger $logger;
    private ilXmlWriter $writer;
    private ilUserDefinedFields $udf;

    /**
     * Construct of ilLDAPAttribute2XML
     * Defines between LDAP and ILIAS user attributes
     */
    public function __construct(ilLDAPServer $a_server)
    {
        global $DIC;

        $this->logger = $DIC->logger()->auth();

        $this->server_settings = $a_server;

        $this->initLDAPAttributeMapping();
    }

    /**
     * Get server settings
     * @return ilLDAPServer
     */
    public function getServer(): ilLDAPServer
    {
        return $this->server_settings;
    }

    /**
     * Set user data received from pear auth or by ldap_search
     *
     * @param array array of auth data. array('ilias_account1' => array(firstname => 'Stefan',...),...)
     *
     */
    public function setUserData(array $a_data): void
    {
        $this->user_data = $a_data;
    }

    /**
     * Set auth mode for new users.
     * @param string $a_authmode
     */
    public function setNewUserAuthMode(string $a_authmode): void
    {
        $this->new_user_auth_mode = $a_authmode;
    }

    /**
     * Get auth mode for new users
     */
    public function getNewUserAuthMode(): string
    {
        return $this->new_user_auth_mode;
    }

    /**
     * Add import mode
     */
    public function addMode(int $a_mode): void
    {
        //TODO check for proper value
        if (!in_array($a_mode, $this->modes, true)) {
            $this->modes[] = $a_mode;
        }
    }

    /**
     * Check if mode is active
     * @param int $a_mode
     * @return bool
     */
    public function isModeActive(int $a_mode): bool
    {
        return in_array($a_mode, $this->modes, true);
    }


    /**
     * Create/Update non existing users
     */
    public function refresh(): bool
    {
        $this->usersToXML();

        $importParser = new ilUserImportParser();
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));
        $importParser->setRoleAssignment(ilLDAPRoleAssignmentRules::getAllPossibleRoles($this->getServer()->getServerId()));
        $importParser->setFolderId(7);
        $importParser->startParsing();

        return true;
    }

    /**
     * Parse role assignments for update of user account.
     * @param int $a_usr_id
     * @param string $a_external_account
     * @param array $user
     */
    protected function parseRoleAssignmentsForUpdate(int $a_usr_id, string $a_external_account, array $user): void
    {
        foreach (ilLDAPRoleAssignmentRules::getAssignmentsForUpdate(
            $this->getServer()->getServerId(),
            $a_usr_id,
            $a_external_account,
            $user
        ) as $role_data) {
            $this->writer->xmlElement(
                'Role',
                array('Id' => $role_data['id'],
                    'Type' => $role_data['type'],
                    'Action' => $role_data['action']),
                ''
            );
        }
    }

    /**
     * Parse role assignments for update of user account.
     * @param string $a_external_account
     * @param array $a_user
     */
    protected function parseRoleAssignmentsForCreation(string $a_external_account, array $a_user): void
    {
        foreach (ilLDAPRoleAssignmentRules::getAssignmentsForCreation(
            $this->getServer()->getServerId(),
            $a_external_account,
            $a_user
        ) as $role_data) {
            $this->writer->xmlElement(
                'Role',
                array('Id' => $role_data['id'],
                    'Type' => $role_data['type'],
                    'Action' => $role_data['action']),
                ''
            );
        }
    }

    /**
     * Create xml string of user according to mapping rules
     */
    private function usersToXML(): void
    {
        $this->writer = new ilXmlWriter();
        $this->writer->xmlStartTag('Users');

        $cnt_update = 0;
        $cnt_create = 0;

        // Single users
        foreach ($this->user_data as $external_account => $user) {
            $user['ilExternalAccount'] = $external_account;

            // Required fields
            if ($user['ilInternalAccount']) {
                $usr_id = ilObjUser::_lookupId($user['ilInternalAccount']);

                ++$cnt_update;
                // User exists
                $this->writer->xmlStartTag('User', array('Id' => $usr_id,'Action' => 'Update'));
                $this->writer->xmlElement('Login', array(), $user['ilInternalAccount']);
                $this->writer->xmlElement('ExternalAccount', array(), $external_account);
                $this->writer->xmlElement('AuthMode', array('type' => $this->getNewUserAuthMode()));

                if ($this->isModeActive(self::MODE_INITIALIZE_ROLES)) {
                    $this->parseRoleAssignmentsForCreation($external_account, $user);
                } else {
                    $this->parseRoleAssignmentsForUpdate($usr_id, $external_account, $user);
                }
                $rules = $this->mapping->getRulesForUpdate();
            } else {
                ++$cnt_create;
                // Create user
                $this->writer->xmlStartTag('User', array('Action' => 'Insert'));
                $this->writer->xmlElement('Login', array(), ilAuthUtils::_generateLogin($external_account));

                $this->parseRoleAssignmentsForCreation($external_account, $user);
                $rules = $this->mapping->getRules(true);
            }

            $this->writer->xmlElement('Active', array(), "true");
            $this->writer->xmlElement('TimeLimitOwner', array(), 7);
            $this->writer->xmlElement('TimeLimitUnlimited', array(), 1);
            $this->writer->xmlElement('TimeLimitFrom', array(), time());
            $this->writer->xmlElement('TimeLimitUntil', array(), time());

            // only for new users.
            // If auth_mode is 'default' (ldap) this status should remain.
            if (!$user['ilInternalAccount']) {
                $this->writer->xmlElement(
                    'AuthMode',
                    array('type' => $this->getNewUserAuthMode()),
                    $this->getNewUserAuthMode()
                );
                $this->writer->xmlElement('ExternalAccount', array(), $external_account);
            }
            foreach ($rules as $field => $data) {
                // Do Mapping: it is possible to assign multiple ldap attribute to one user data field
                if (!($value = $this->doMapping($user, $data))) {
                    continue;
                }

                switch ($field) {
                    case 'gender':
                        switch (strtolower($value)) {
                            case 'n':
                            case 'neutral':
                                $this->writer->xmlElement('Gender', array(), 'n');
                                break;

                            case 'm':
                            case 'male':
                                $this->writer->xmlElement('Gender', array(), 'm');
                                break;

                            case 'f':
                            case 'female':
                            default:
                                $this->writer->xmlElement('Gender', array(), 'f');
                                break;
                        }
                        break;

                    case 'firstname':
                        $this->writer->xmlElement('Firstname', array(), $value);
                        break;

                    case 'lastname':
                        $this->writer->xmlElement('Lastname', array(), $value);
                        break;

                    case 'hobby':
                        $this->writer->xmlElement('Hobby', array(), $value);
                        break;

                    case 'title':
                        $this->writer->xmlElement('Title', array(), $value);
                        break;

                    case 'institution':
                        $this->writer->xmlElement('Institution', array(), $value);
                        break;

                    case 'department':
                        $this->writer->xmlElement('Department', array(), $value);
                        break;

                    case 'street':
                        $this->writer->xmlElement('Street', array(), $value);
                        break;

                    case 'city':
                        $this->writer->xmlElement('City', array(), $value);
                        break;

                    case 'zipcode':
                        $this->writer->xmlElement('PostalCode', array(), $value);
                        break;

                    case 'country':
                        $this->writer->xmlElement('Country', array(), $value);
                        break;

                    case 'phone_office':
                        $this->writer->xmlElement('PhoneOffice', array(), $value);
                        break;

                    case 'phone_home':
                        $this->writer->xmlElement('PhoneHome', array(), $value);
                        break;

                    case 'phone_mobile':
                        $this->writer->xmlElement('PhoneMobile', array(), $value);
                        break;

                    case 'fax':
                        $this->writer->xmlElement('Fax', array(), $value);
                        break;

                    case 'email':
                        $this->writer->xmlElement('Email', array(), $value);
                        break;

                    case 'second_email':
                        $this->writer->xmlElement('SecondEmail', array(), $value);
                        break;

                    case 'matriculation':
                        $this->writer->xmlElement('Matriculation', array(), $value);
                        break;

                        /*
                        case 'photo':
                            $this->writer->xmlElement('PersonalPicture',array('encoding' => 'Base64','imagetype' => 'image/jpeg'),
                                base64_encode($this->convertInput($user[$value])));
                            break;
                        */
                    default:
                        // Handle user defined fields
                        if (strpos($field, 'udf_') !== 0) {
                            continue 2;
                        }
                        $id_data = explode('_', $field);
                        if (!isset($id_data[1])) {
                            continue 2;
                        }
                        $this->initUserDefinedFields();
                        $definition = $this->udf->getDefinition((int) $id_data[1]);
                        $this->writer->xmlElement(
                            'UserDefinedField',
                            array('Id' => $definition['il_id'],
                                                                            'Name' => $definition['field_name']),
                            $value
                        );
                        break;
                }
            }
            $this->writer->xmlEndTag('User');
        }

        if ($cnt_create) {
            $this->logger->info('LDAP: Started creation of ' . $cnt_create . ' users.');
        }
        if ($cnt_update) {
            $this->logger->info('LDAP: Started update of ' . $cnt_update . ' users.');
        }
        $this->writer->xmlEndTag('Users');
    }

    /**
     * A value can be an array or a string
     * This function converts arrays to strings
     *
     * @param array|string value
     * @return string
     */
    private function convertInput($a_value): string
    {
        if (is_array($a_value)) {
            return $a_value[0];
        }

        return $a_value;
    }

    private function doMapping(array $user, array $rule): string
    {
        $mapping = strtolower(trim($rule['value']));

        if (strpos($mapping, ',') === false) {
            return $this->convertInput($user[$mapping] ?? '');
        }
        // Is multiple mapping

        $fields = explode(',', $mapping);
        $value = '';
        foreach ($fields as $field) {
            if ($value !== '') {
                $value .= ' ';
            }
            $value .= ($this->convertInput($user[trim($field)] ?? ''));
        }
        return $value;
    }

    private function initLDAPAttributeMapping(): void
    {
        $this->mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->server_settings->getServerId());
    }

    private function initUserDefinedFields(): void
    {
        $this->udf = ilUserDefinedFields::_getInstance();
    }
}
