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
 * Class ilSamlIdp
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilSamlIdp
{
    private const PROP_IDP_ID = 'idp_id';
    private const PROP_IS_ACTIVE = 'is_active';
    private const PROP_DEFAULT_ROLE_ID = 'default_role_id';
    private const PROP_UID_CLAIM = 'uid_claim';
    private const PROP_LOGIN_CLAIM = 'login_claim';
    private const PROP_ENTITY_ID = 'entity_id';
    private const PROP_SYNC_STATUS = 'sync_status';
    private const PROP_ALLOW_LOCAL_AUTH = 'allow_local_auth';
    private const PROP_ACCOUNT_MIGR_STATUS = 'account_migr_status';

    private ilDBInterface $db;
    /** @var self[] */
    private static array $instances = [];
    private bool $is_active = false;
    private bool $allow_local_auth = false;
    private int $default_role_id = 0;
    private string $uid_claim = '';
    private string $login_claim = '';
    private bool $sync_status = false;
    private string $entity_id = '';
    private bool $account_migration_status = false;

    public function __construct(protected int $idp_id = 0)
    {
        $this->db = $GLOBALS['DIC']->database();

        if ($this->idp_id > 0) {
            $this->read();
        }
    }

    public static function getFirstActiveIdp(): self
    {
        $idps = self::getActiveIdpList();
        if ($idps !== []) {
            return current($idps);
        }

        throw new ilSamlException('No active SAML IDP found');
    }

    public static function getInstanceByIdpId(int $a_idp_id): self
    {
        if (!isset(self::$instances[$a_idp_id]) || !(self::$instances[$a_idp_id] instanceof self)) {
            self::$instances[$a_idp_id] = new self($a_idp_id);
        }

        return self::$instances[$a_idp_id];
    }

    private function read(): void
    {
        $query = 'SELECT * FROM saml_idp_settings WHERE idp_id = ' . $this->db->quote($this->idp_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($record = $this->db->fetchAssoc($res)) {
            $this->bindDbRecord($record);
            return;
        }

        throw new ilException('Could not find idp');
    }

    public function persist(): void
    {
        if ($this->idp_id === 0) {
            $this->setIdpId($this->db->nextId('saml_idp_settings'));
        }

        $this->db->replace(
            'saml_idp_settings',
            [
                self::PROP_IDP_ID => [ilDBConstants::T_INTEGER, $this->idp_id]
            ],
            [
                self::PROP_IS_ACTIVE => [ilDBConstants::T_INTEGER, (int) $this->is_active],
                self::PROP_DEFAULT_ROLE_ID => [ilDBConstants::T_INTEGER, $this->default_role_id],
                self::PROP_UID_CLAIM => [ilDBConstants::T_TEXT, $this->uid_claim],
                self::PROP_LOGIN_CLAIM => [ilDBConstants::T_TEXT, $this->login_claim],
                self::PROP_ENTITY_ID => [ilDBConstants::T_TEXT, $this->entity_id],
                self::PROP_SYNC_STATUS => [ilDBConstants::T_INTEGER, (int) $this->sync_status],
                self::PROP_ALLOW_LOCAL_AUTH => [ilDBConstants::T_INTEGER, (int) $this->allow_local_auth],
                self::PROP_ACCOUNT_MIGR_STATUS => [ilDBConstants::T_INTEGER, (int) $this->account_migration_status]
            ]
        );
    }

    /**
     * Deletes an idp with all relevant mapping rules.
     * Furthermore, the auth_mode of the relevant user accounts will be switched to 'default'
     */
    public function delete(): void
    {
        $mapping = new ilExternalAuthUserAttributeMapping('saml', $this->idp_id);
        $mapping->delete();

        $this->db->manipulateF(
            'UPDATE usr_data SET auth_mode = %s WHERE auth_mode = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['default', ilAuthUtils::AUTH_SAML . '_' . $this->idp_id]
        );

        $this->db->manipulate('DELETE FROM saml_idp_settings WHERE idp_id = ' . $this->db->quote(
            $this->idp_id,
            ilDBConstants::T_INTEGER
        ));
    }

    /**
     * @return array{idp_id: int, is_active: bool, default_role_id: int, uid_claim: string, login_claim: string, sync_status: bool, account_migr_status: bool, allow_local_auth: bool, entity_id: string}
     */
    public function toArray(): array
    {
        return [
            self::PROP_IDP_ID => $this->idp_id,
            self::PROP_IS_ACTIVE => $this->is_active,
            self::PROP_DEFAULT_ROLE_ID => $this->default_role_id,
            self::PROP_UID_CLAIM => $this->uid_claim,
            self::PROP_LOGIN_CLAIM => $this->login_claim,
            self::PROP_SYNC_STATUS => $this->sync_status,
            self::PROP_ACCOUNT_MIGR_STATUS => $this->account_migration_status,
            self::PROP_ALLOW_LOCAL_AUTH => $this->allow_local_auth,
            self::PROP_ENTITY_ID => $this->entity_id
        ];
    }

    /**
     * @param array<string, mixed> $record
     */
    public function bindDbRecord(array $record): void
    {
        $this->setIdpId((int) $record[self::PROP_IDP_ID]);
        $this->setActive((bool) $record[self::PROP_IS_ACTIVE]);
        $this->setDefaultRoleId((int) $record[self::PROP_DEFAULT_ROLE_ID]);
        $this->setUidClaim((string) $record[self::PROP_UID_CLAIM]);
        $this->setLoginClaim((string) $record[self::PROP_LOGIN_CLAIM]);
        $this->setSynchronizationStatus((bool) $record[self::PROP_SYNC_STATUS]);
        $this->setAccountMigrationStatus((bool) $record[self::PROP_ACCOUNT_MIGR_STATUS]);
        $this->setLocalLocalAuthenticationStatus((bool) $record[self::PROP_ALLOW_LOCAL_AUTH]);
        $this->setEntityId((string) $record[self::PROP_ENTITY_ID]);
    }

    public function bindForm(ilPropertyFormGUI $form): void
    {
        $this->setDefaultRoleId((int) $form->getInput(self::PROP_DEFAULT_ROLE_ID));
        $this->setUidClaim((string) $form->getInput(self::PROP_UID_CLAIM));
        $this->setLoginClaim((string) $form->getInput(self::PROP_LOGIN_CLAIM));
        $this->setSynchronizationStatus((bool) $form->getInput(self::PROP_SYNC_STATUS));
        $this->setLocalLocalAuthenticationStatus((bool) $form->getInput(self::PROP_ALLOW_LOCAL_AUTH));
        $this->setAccountMigrationStatus((bool) $form->getInput(self::PROP_ACCOUNT_MIGR_STATUS));

        /** @var ilSamlIdpMetadataInputGUI $metadata */
        $metadata = $form->getItemByPostVar('metadata');
        $this->setEntityId($metadata->getValue());
    }

    public static function isAuthModeSaml(string $a_auth_mode): bool
    {
        if ('' === $a_auth_mode) {
            return false;
        }

        $auth_arr = explode('_', $a_auth_mode);
        return (
            count($auth_arr) === 2 &&
            (int) $auth_arr[0] === ilAuthUtils::AUTH_SAML &&
            is_string($auth_arr[1]) && $auth_arr[1] !== ''
        );
    }

    public static function getIdpIdByAuthMode(string $a_auth_mode): ?int
    {
        if (self::isAuthModeSaml($a_auth_mode)) {
            $auth_arr = explode('_', $a_auth_mode);
            return (int) $auth_arr[1];
        }

        return null;
    }

    public static function geIdpIdByEntityId(string $entityId): int
    {
        foreach (self::getAllIdps() as $idp) {
            if ($idp->isActive() && $idp->getEntityId() === $entityId) {
                return $idp->getIdpId();
            }
        }

        return 0;
    }

    /**
     * @return self[]
     */
    public static function getActiveIdpList(): array
    {
        $idps = [];
        foreach (self::getAllIdps() as $idp) {
            if ($idp->isActive()) {
                $idps[] = $idp;
            }
        }

        return $idps;
    }

    /**
     * @return self[]
     */
    public static function getAllIdps(): array
    {
        global $DIC;

        $res = $DIC->database()->query('SELECT * FROM saml_idp_settings');

        $idps = [];
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $idp = new self();
            $idp->bindDbRecord($row);

            $idps[] = $idp;
        }

        return $idps;
    }

    public static function getAuthModeByKey(string $a_auth_key): string
    {
        $auth_arr = explode('_', $a_auth_key);
        if (count($auth_arr) > 1) {
            return 'saml_' . $auth_arr[1];
        }

        return 'saml';
    }

    public static function getKeyByAuthMode(string $a_auth_mode): string
    {
        $auth_arr = explode('_', $a_auth_mode);
        if (count($auth_arr) > 1) {
            return ilAuthUtils::AUTH_SAML . '_' . $auth_arr[1];
        }

        return (string) ilAuthUtils::AUTH_SAML;
    }

    public function getEntityId(): string
    {
        return $this->entity_id;
    }

    public function setEntityId(string $entity_id): void
    {
        $this->entity_id = $entity_id;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function setActive(bool $is_active): void
    {
        $this->is_active = $is_active;
    }

    public function getIdpId(): int
    {
        return $this->idp_id;
    }

    public function setIdpId(int $idp_id): void
    {
        $this->idp_id = $idp_id;
    }

    public function allowLocalAuthentication(): bool
    {
        return $this->allow_local_auth;
    }

    public function setLocalLocalAuthenticationStatus(bool $status): void
    {
        $this->allow_local_auth = $status;
    }

    public function getDefaultRoleId(): int
    {
        return $this->default_role_id;
    }

    public function setDefaultRoleId(int $role_id): void
    {
        $this->default_role_id = $role_id;
    }

    public function setUidClaim(string $claim): void
    {
        $this->uid_claim = $claim;
    }

    public function getUidClaim(): string
    {
        return $this->uid_claim;
    }

    public function setLoginClaim(string $claim): void
    {
        $this->login_claim = $claim;
    }

    public function getLoginClaim(): string
    {
        return $this->login_claim;
    }

    public function isSynchronizationEnabled(): bool
    {
        return $this->sync_status;
    }

    public function setSynchronizationStatus(bool $sync): void
    {
        $this->sync_status = $sync;
    }

    public function isAccountMigrationEnabled(): bool
    {
        return $this->account_migration_status;
    }

    public function setAccountMigrationStatus(bool $status): void
    {
        $this->account_migration_status = $status;
    }
}
