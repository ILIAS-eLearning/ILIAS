<?php declare(strict_types=1);

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
class ilSamlIdp
{
    protected ilDBInterface $db;
    /** @var self[] */
    private static array $instances = [];
    protected int $idp_id;
    protected bool $is_active = false;
    protected bool $allow_local_auth = false;
    protected int $default_role_id = 0;
    protected string $uid_claim = '';
    protected string $login_claim = '';
    protected bool $sync_status = false;
    protected string $entity_id = '';
    protected bool $account_migration_status = false;
    /** @var array */
    protected static array $idp_as_data = [];

    public function __construct(int $a_idp_id = 0)
    {
        $this->db = $GLOBALS['DIC']->database();
        $this->idp_id = $a_idp_id;

        if ($this->idp_id > 0) {
            $this->read();
        }
    }

    public static function getFirstActiveIdp() : self
    {
        $idps = self::getActiveIdpList();
        if (count($idps) > 0) {
            return current($idps);
        }

        throw new ilSamlException('No active SAML IDP found');
    }

    public static function getInstanceByIdpId(int $a_idp_id) : self
    {
        if (!isset(self::$instances[$a_idp_id]) || !(self::$instances[$a_idp_id] instanceof self)) {
            self::$instances[$a_idp_id] = new self($a_idp_id);
        }

        return self::$instances[$a_idp_id];
    }

    private function read() : void
    {
        $query = 'SELECT * FROM saml_idp_settings WHERE idp_id = ' . $this->db->quote($this->getIdpId(), 'integer');
        $res = $this->db->query($query);
        while ($record = $this->db->fetchAssoc($res)) {
            $this->bindDbRecord($record);
            return;
        }

        throw new ilException('Could not find idp');
    }

    public function persist() : void
    {
        if (!$this->getIdpId()) {
            $this->setIdpId($this->db->nextId('saml_idp_settings'));
        }

        $this->db->replace(
            'saml_idp_settings',
            [
                'idp_id' => ['integer', $this->getIdpId()]
            ],
            [
                'is_active' => ['integer', (int) $this->isActive()],
                'default_role_id' => ['integer', $this->getDefaultRoleId()],
                'uid_claim' => ['text', $this->getUidClaim()],
                'login_claim' => ['text', $this->getLoginClaim()],
                'entity_id' => ['text', $this->getEntityId()],
                'sync_status' => ['integer', (int) $this->isSynchronizationEnabled()],
                'allow_local_auth' => ['integer', (int) $this->allowLocalAuthentication()],
                'account_migr_status' => ['integer', (int) $this->isAccountMigrationEnabled()]
            ]
        );
    }

    /**
     * Deletes an idp with all relevant mapping rules.
     * Furthermore, the auth_mode of the relevant user accounts will be switched to 'default'
     */
    public function delete() : void
    {
        $mapping = new ilExternalAuthUserAttributeMapping('saml', $this->getIdpId());
        $mapping->delete();

        $this->db->manipulateF(
            'UPDATE usr_data SET auth_mode = %s WHERE auth_mode = %s',
            array('text', 'text'),
            array('default', ilAuthUtils::AUTH_SAML . '_' . $this->getIdpId())
        );

        $this->db->manipulate('DELETE FROM saml_idp_settings WHERE idp_id = ' . $this->db->quote(
            $this->getIdpId(),
            'integer'
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'idp_id' => $this->getIdpId(),
            'is_active' => $this->isActive(),
            'default_role_id' => $this->getDefaultRoleId(),
            'uid_claim' => $this->getUidClaim(),
            'login_claim' => $this->getLoginClaim(),
            'sync_status' => $this->isSynchronizationEnabled(),
            'account_migr_status' => $this->isAccountMigrationEnabled(),
            'allow_local_auth' => $this->allowLocalAuthentication(),
            'entity_id' => $this->getEntityId()
        ];
    }

    /**
     * @param array<string, mixed> $record
     */
    public function bindDbRecord(array $record) : void
    {
        $this->setIdpId((int) $record['idp_id']);
        $this->setActive((bool) $record['is_active']);
        $this->setDefaultRoleId((int) $record['default_role_id']);
        $this->setUidClaim((string) $record['uid_claim']);
        $this->setLoginClaim((string) $record['login_claim']);
        $this->setSynchronizationStatus((bool) $record['sync_status']);
        $this->setAccountMigrationStatus((bool) $record['account_migr_status']);
        $this->setLocalLocalAuthenticationStatus((bool) $record['allow_local_auth']);
        $this->setEntityId((string) $record['entity_id']);
    }

    public function bindForm(ilPropertyFormGUI $form) : void
    {
        $this->setDefaultRoleId((int) $form->getInput('default_role_id'));
        $this->setUidClaim((string) $form->getInput('uid_claim'));
        $this->setLoginClaim((string) $form->getInput('login_claim'));
        $this->setSynchronizationStatus((bool) $form->getInput('sync_status'));
        $this->setLocalLocalAuthenticationStatus((bool) $form->getInput('allow_local_auth'));
        $this->setAccountMigrationStatus((bool) $form->getInput('account_migr_status'));

        /** @var ilSamlIdpMetadataInputGUI $metadata */
        $metadata = $form->getItemByPostVar('metadata');
        $this->setEntityId($metadata->getValue());
    }

    public static function isAuthModeSaml(string $a_auth_mode) : bool
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

    public static function getIdpIdByAuthMode(string $a_auth_mode) : ?int
    {
        if (self::isAuthModeSaml($a_auth_mode)) {
            $auth_arr = explode('_', $a_auth_mode);
            return (int) $auth_arr[1];
        }

        return null;
    }

    public static function geIdpIdByEntityId(string $entityId) : int
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
    public static function getActiveIdpList() : array
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
    public static function getAllIdps() : array
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

    public static function getAuthModeByKey(string $a_auth_key) : string
    {
        $auth_arr = explode('_', $a_auth_key);
        if (count((array) $auth_arr) > 1) {
            return 'saml_' . $auth_arr[1];
        }

        return 'saml';
    }

    public static function getKeyByAuthMode(string $a_auth_mode) : string
    {
        $auth_arr = explode('_', $a_auth_mode);
        if (count((array) $auth_arr) > 1) {
            return ilAuthUtils::AUTH_SAML . '_' . $auth_arr[1];
        }

        return (string) ilAuthUtils::AUTH_SAML;
    }

    public function getEntityId() : string
    {
        return $this->entity_id;
    }

    public function setEntityId(string $entity_id) : void
    {
        $this->entity_id = $entity_id;
    }

    public function isActive() : bool
    {
        return $this->is_active;
    }

    public function setActive(bool $is_active) : void
    {
        $this->is_active = $is_active;
    }

    public function getIdpId() : int
    {
        return $this->idp_id;
    }

    public function setIdpId(int $idp_id) : void
    {
        $this->idp_id = $idp_id;
    }

    public function allowLocalAuthentication() : bool
    {
        return $this->allow_local_auth;
    }

    public function setLocalLocalAuthenticationStatus(bool $status) : void
    {
        $this->allow_local_auth = $status;
    }

    public function getDefaultRoleId() : int
    {
        return $this->default_role_id;
    }

    public function setDefaultRoleId(int $role_id) : void
    {
        $this->default_role_id = $role_id;
    }

    public function setUidClaim(string $claim) : void
    {
        $this->uid_claim = $claim;
    }

    public function getUidClaim() : string
    {
        return $this->uid_claim;
    }

    public function setLoginClaim(string $claim) : void
    {
        $this->login_claim = $claim;
    }

    public function getLoginClaim() : string
    {
        return $this->login_claim;
    }

    public function isSynchronizationEnabled() : bool
    {
        return $this->sync_status;
    }

    public function setSynchronizationStatus(bool $sync) : void
    {
        $this->sync_status = $sync;
    }

    public function isAccountMigrationEnabled() : bool
    {
        return $this->account_migration_status;
    }

    public function setAccountMigrationStatus(bool $status) : void
    {
        $this->account_migration_status = $status;
    }
}
