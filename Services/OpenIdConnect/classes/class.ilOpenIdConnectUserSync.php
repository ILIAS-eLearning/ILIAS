<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilOpenIdConnectUserSync
{
    public const AUTH_MODE = 'oidc';

    private ilOpenIdConnectSettings $settings;
    private ilLogger $logger;
    private ilXmlWriter $writer;
    private stdClass $user_info;
    private string $ext_account = '';
    private string $int_account = '';
    private int $usr_id = 0;

    public function __construct(ilOpenIdConnectSettings $settings, stdClass $user_info)
    {
        global $DIC;

        $this->settings = $settings;
        $this->user_info = $user_info;

        $this->logger = $DIC->logger()->auth();
        $this->writer = new ilXmlWriter();
    }

    public function setExternalAccount(string $ext_account): void
    {
        $this->ext_account = $ext_account;
    }

    public function setInternalAccount(string $int_account): void
    {
        $this->int_account = $int_account;
        $this->usr_id = ilObjUser::_lookupId($this->int_account);
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

    /**
     * @throws ilOpenIdConnectSyncForbiddenException
     */
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

        $int_account = ilObjUser::_checkExternalAuthAccount(
            self::AUTH_MODE,
            $this->ext_account
        );
        $this->setInternalAccount($int_account);

        return true;
    }

    protected function transformToXml(): void
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

        $this->writer->xmlElement('ExternalAccount', array(), $this->ext_account);
        $this->writer->xmlElement('AuthMode', array('type' => self::AUTH_MODE), null);

        $this->parseRoleAssignments();

        if ($this->needsCreation()) {
            $this->writer->xmlElement('Active', array(), "true");
            $this->writer->xmlElement('TimeLimitOwner', array(), 7);
            $this->writer->xmlElement('TimeLimitUnlimited', array(), 1);
            $this->writer->xmlElement('TimeLimitFrom', array(), time());
            $this->writer->xmlElement('TimeLimitUntil', array(), time());
        }

        foreach ($this->settings->getProfileMappingFields() as $field => $lng_key) {
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
                case 'firstname':
                    $this->writer->xmlElement('Firstname', [], $value);
                    break;

                case 'lastname':
                    $this->writer->xmlElement('Lastname', [], $value);
                    break;

                case 'email':
                    $this->writer->xmlElement('Email', [], $value);
                    break;

                case 'birthday':
                    $this->writer->xmlElement('Birthday', [], $value);
                    break;
            }
        }
        $this->writer->xmlEndTag('User');
        $this->writer->xmlEndTag('Users');

        $this->logger->debug($this->writer->xmlDumpMem());
    }

    /**
     * Parse role assignments
     * @return array<int, int> array of role assignments
     */
    protected function parseRoleAssignments(): array
    {
        $this->logger->debug('Parsing role assignments');

        $found_role = false;

        $roles_assignable[$this->settings->getRole()] = $this->settings->getRole();

        $this->logger->dump($this->settings->getRoleMappings(), ilLogLevel::DEBUG);

        foreach ($this->settings->getRoleMappings() as $role_id => $role_info) {
            $this->logger->dump($role_id);
            $this->logger->dump($role_info);

            [$role_attribute, $role_value] = explode('::', $role_info['value']);

            if (
                !$role_attribute ||
                !$role_value
            ) {
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
                if (!in_array($role_value, $this->user_info->{$role_attribute}, true)) {
                    $this->logger->debug('User account has no ' . $role_value);
                    continue;
                }
            } elseif (strcmp($this->user_info->{$role_attribute}, $role_value) !== 0) {
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

            // add default role
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

    protected function valueFrom(string $connect_name): string
    {
        if (!$connect_name) {
            return '';
        }
        if (!property_exists($this->user_info, $connect_name)) {
            $this->logger->debug('Cannot find property ' . $connect_name . ' in user info ');
            return '';
        }

        return $this->user_info->{$connect_name};
    }
}
