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

class ilOpenIdConnectUserSync
{
    public const AUTH_MODE = 'oidc';
    private const UDF_STRING = 'udf_';

    private readonly ilLogger $logger;
    private readonly ilXmlWriter $writer;
    private string $ext_account = '';
    private string $int_account = '';
    private int $usr_id = 0;
    private ilUserDefinedFields $udf;

    public function __construct(
        private readonly ilOpenIdConnectSettings $settings,
        private readonly stdClass $user_info
    ) {
        global $DIC;

        $this->logger = $DIC->logger()->auth();
        $this->writer = new ilXmlWriter();
        $this->udf = ilUserDefinedFields::_getInstance();
    }

    public function setExternalAccount(string $ext_account): void
    {
        $this->ext_account = $ext_account;
    }

    public function setInternalAccount(string $int_account): void
    {
        $this->int_account = $int_account;
        $this->usr_id = (int) ilObjUser::_lookupId($this->int_account);
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function needsCreation(): bool
    {
        $this->logger->dump($this->int_account, ilLogLevel::DEBUG);

        return $this->int_account === '';
    }

    public function updateUser(): bool
    {
        if ($this->needsCreation() && !$this->settings->isSyncAllowed()) {
            throw new ilOpenIdConnectSyncForbiddenException('No internal account given.');
        }

        $this->transformToXml();

        $importParser = new ilUserImportParser();
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));

        $roles = $this->parseRoleAssignments();
        $importParser->setRoleAssignment($roles);

        $importParser->setFolderId(USER_FOLDER_ID);
        $importParser->startParsing();
        $debug = $importParser->getProtocol();
        $this->logger->debug(json_encode($debug, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        $int_account = ilObjUser::_checkExternalAuthAccount(
            self::AUTH_MODE,
            $this->ext_account
        );
        $this->setInternalAccount($int_account);

        return true;
    }

    private function transformToXml(): void
    {
        $this->writer->xmlStartTag('Users');

        if ($this->needsCreation()) {
            $this->writer->xmlStartTag('User', ['Action' => 'Insert']);
            $this->writer->xmlElement('Login', [], ilAuthUtils::_generateLogin($this->ext_account));
        } else {
            $this->writer->xmlStartTag(
                'User',
                [
                    'Id' => $this->getUserId(),
                    'Action' => 'Update'
                ]
            );
            $this->writer->xmlElement('Login', [], $this->int_account);
        }

        $this->writer->xmlElement('ExternalAccount', [], $this->ext_account);
        $this->writer->xmlElement('AuthMode', ['type' => self::AUTH_MODE], null);

        $this->parseRoleAssignments();

        if ($this->needsCreation()) {
            $this->writer->xmlElement('Active', [], 'true');
            $this->writer->xmlElement('TimeLimitOwner', [], 7);
            $this->writer->xmlElement('TimeLimitUnlimited', [], 1);
            $this->writer->xmlElement('TimeLimitFrom', [], time());
            $this->writer->xmlElement('TimeLimitUntil', [], time());
        }

        $profile_fields = $this->settings->getProfileMappingFields();

        $udf_fields = [];
        foreach ($this->udf->getDefinitions() as $definition) {
            $field = self::UDF_STRING . $definition['field_id'];
            $udf_fields[$field] = $field;
        }

        $profile_and_udf_fields = $profile_fields + $udf_fields;
        foreach ($profile_and_udf_fields as $field => $lng_key) {
            $connect_name = $this->settings->getProfileMappingFieldValue($field);
            if (!$connect_name) {
                $this->logger->debug('Ignoring unconfigured field: ' . $field);
                continue;
            }
            if (!$this->needsCreation() && !$this->settings->getProfileMappingFieldUpdate($field)) {
                $this->logger->debug('Ignoring ' . $field . ' for update.');
                continue;
            }

            $value = $this->valueFrom($connect_name);
            if ($value === '') {
                $this->logger->debug('Cannot find user data in ' . $connect_name);
                continue;
            }

            switch ($field) {
                case 'gender':
                    switch (strtolower($value)) {
                        case 'm':
                        case 'male':
                            $this->writer->xmlElement('Gender', [], 'm');
                            break;

                        case 'f':
                        case 'female':
                            $this->writer->xmlElement('Gender', [], 'f');
                            break;

                        default:
                            // use the default for anything that is not clearly m or f
                            $this->writer->xmlElement('Gender', [], 'n');
                            break;
                    }
                    break;

                case 'firstname':
                    $this->writer->xmlElement('Firstname', [], $value);
                    break;

                case 'lastname':
                    $this->writer->xmlElement('Lastname', [], $value);
                    break;

                case 'hobby':
                    $this->writer->xmlElement('Hobby', [], $value);
                    break;

                case 'title':
                    $this->writer->xmlElement('Title', [], $value);
                    break;

                case 'institution':
                    $this->writer->xmlElement('Institution', [], $value);
                    break;

                case 'department':
                    $this->writer->xmlElement('Department', [], $value);
                    break;

                case 'street':
                    $this->writer->xmlElement('Street', [], $value);
                    break;

                case 'city':
                    $this->writer->xmlElement('City', [], $value);
                    break;

                case 'zipcode':
                    $this->writer->xmlElement('PostalCode', [], $value);
                    break;

                case 'country':
                    $this->writer->xmlElement('Country', [], $value);
                    break;

                case 'phone_office':
                    $this->writer->xmlElement('PhoneOffice', [], $value);
                    break;

                case 'phone_home':
                    $this->writer->xmlElement('PhoneHome', [], $value);
                    break;

                case 'phone_mobile':
                    $this->writer->xmlElement('PhoneMobile', [], $value);
                    break;

                case 'fax':
                    $this->writer->xmlElement('Fax', [], $value);
                    break;

                case 'email':
                    $this->writer->xmlElement('Email', [], $value);
                    break;

                case 'second_email':
                    $this->writer->xmlElement('SecondEmail', [], $value);
                    break;

                case 'matriculation':
                    $this->writer->xmlElement('Matriculation', [], $value);
                    break;

                default:
                    if (!str_starts_with($field, 'udf_')) {
                        continue 2;
                    }

                    $id_data = explode('_', $field);
                    if (!isset($id_data[1])) {
                        continue 2;
                    }

                    $definition = $this->udf->getDefinition((int) $id_data[1]);
                    if (empty($definition)) {
                        $this->logger->warning(
                            sprintf(
                                'Invalid/Orphaned UD field mapping detected: %s',
                                $field
                            )
                        );
                        break;
                    }

                    $this->writer->xmlElement(
                        'UserDefinedField',
                        [
                            'Id' => $definition['il_id'],
                            'Name' => $definition['field_name']
                        ],
                        $value
                    );
                    break;
            }
        }
        $this->writer->xmlEndTag('User');
        $this->writer->xmlEndTag('Users');

        $this->logger->debug($this->writer->xmlDumpMem());
    }

    /**
     * @return array<int, int> Map of role assignments, where the key equals the respetive value
     */
    protected function parseRoleAssignments(): array
    {
        $this->logger->debug('Parsing role assignments');

        $found_role = false;

        $roles_assignable[$this->settings->getRole()] = $this->settings->getRole();

        $this->logger->dump($this->settings->getRoleMappings(), ilLogLevel::DEBUG);

        foreach ($this->settings->getRoleMappings() as $role_id => $role_info) {
            $this->logger->dump($role_id, ilLogLevel::DEBUG);
            $this->logger->dump($role_info, ilLogLevel::DEBUG);

            if ($role_info['value'] === '') {
                $this->logger->debug('No role mapping for role: ' . $role_id);
                continue;
            }

            [$role_attribute, $role_value] = array_map(trim(...), explode('::', $role_info['value']));

            if (!$role_attribute || !$role_value) {
                $this->logger->debug('No valid role mapping configuration for: ' . $role_id);
                continue;
            }

            if (!isset($this->user_info->{$role_attribute})) {
                $this->logger->debug('No user info passed');
                continue;
            }

            if (!$role_info['update'] && !$this->needsCreation()) {
                $this->logger->debug('No user role update for role: ' . $role_id);
                continue;
            }

            if (is_array($this->user_info->{$role_attribute})) {
                $roles_claim = array_map(trim(...), $this->user_info->{$role_attribute});
                if (!in_array($role_value, $roles_claim, true)) {
                    $this->logger->debug('User account has no ' . $role_value);
                    continue;
                }
            } elseif (strcmp(trim((string) $this->user_info->{$role_attribute}), $role_value) !== 0) {
                $this->logger->debug('User account has no ' . $role_value);
                continue;
            }

            $this->logger->debug('Matching role mapping for role_id: ' . $role_id);

            $found_role = true;
            $roles_assignable[(int) $role_id] = (int) $role_id;
            $long_role_id = ('il_' . IL_INST_ID . '_role_' . $role_id);

            $this->writer->xmlElement(
                'Role',
                [
                    'Id' => $long_role_id,
                    'Type' => 'Global',
                    'Action' => 'Assign'
                ],
                null
            );
        }

        if (!$found_role && $this->needsCreation()) {
            $long_role_id = ('il_' . IL_INST_ID . '_role_' . $this->settings->getRole());

            $this->writer->xmlElement(
                'Role',
                [
                    'Id' => $long_role_id,
                    'Type' => 'Global',
                    'Action' => 'Assign'
                ],
                null
            );
        }

        return $roles_assignable;
    }

    private function valueFrom(string $connect_name): string
    {
        if (!$connect_name) {
            return '';
        }

        if (!property_exists($this->user_info, $connect_name)) {
            $this->logger->debug('Cannot find property ' . $connect_name . ' in user info ');
            return '';
        }

        return (string) $this->user_info->{$connect_name};
    }
}
