<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdp
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlIdp
{
    /**
     * @var ilDB|ilDBInterface
     */
    protected $db;

    /**
     * @var self[]
     */
    private static $instances = array();

    /**
     * @var int
     */
    protected $idp_id;

    /**
     * @var bool
     */
    protected $is_active = false;

    /**
     * @var bool
     */
    protected $allow_local_auth = false;

    /**
     * @var int
     */
    protected $default_role_id = false;

    /**
     * @var string
     */
    protected $uid_claim = '';

    /**
     * @var string
     */
    protected $login_claim = '';

    /**
     * @var bool
     */
    protected $sync_status = false;

    /**
     * @var string
     */
    protected $entity_id = '';

    /**
     * @var bool
     */
    protected $account_migration_status = false;

    /**
     * @var array
     */
    protected static $idp_as_data = array();

    /**
     * @param int $a_idp_id
     */
    public function __construct($a_idp_id = 0)
    {
        $this->db = $GLOBALS['DIC']->database();
        $this->idp_id = $a_idp_id;

        if ($this->idp_id > 0) {
            $this->read();
        }
    }

    /**
     * @return self
     * @throws \ilSamlException
     */
    public static function getFirstActiveIdp()
    {
        $idps = self::getActiveIdpList();
        if (count($idps) > 0) {
            return current($idps);
        }

        require_once 'Services/Saml/exceptions/class.ilSamlException.php';
        throw new \ilSamlException('No active SAML IDP found');
    }

    /**
     * @param int $a_idp_id
     * @return self
     */
    public static function getInstanceByIdpId($a_idp_id)
    {
        if (!isset(self::$instances[$a_idp_id]) || !(self::$instances[$a_idp_id] instanceof self)) {
            self::$instances[$a_idp_id] = new self($a_idp_id);
        }

        return self::$instances[$a_idp_id];
    }

    /**
     * @throws ilException
     */
    private function read()
    {
        $query = 'SELECT * FROM saml_idp_settings WHERE idp_id = ' . $this->db->quote($this->getIdpId(), 'integer');
        $res = $this->db->query($query);
        while ($record = $this->db->fetchAssoc($res)) {
            $this->bindDbRecord($record);
            return;
        }

        throw new \ilException('Could not find idp');
    }

    /**
     *
     */
    public function persist()
    {
        if (!$this->getIdpId()) {
            $this->setIdpId($this->db->nextId('saml_idp_settings'));
        }

        $this->db->replace(
            'saml_idp_settings',
            array(
                'idp_id' => array('integer', $this->getIdpId())
            ),
            array(
                'is_active' => array('integer', $this->isActive()),
                'default_role_id' => array('integer', $this->getDefaultRoleId()),
                'uid_claim' => array('text', $this->getUidClaim()),
                'login_claim' => array('text', $this->getLoginClaim()),
                'entity_id' => array('text', $this->getEntityId()),
                'sync_status' => array('integer', $this->isSynchronizationEnabled()),
                'allow_local_auth' => array('integer', $this->allowLocalAuthentication()),
                'account_migr_status' => array('integer', $this->isAccountMigrationEnabled())
            )
        );
    }

    /**
     * Deletes an idp with all relvant mapping rules. Furthermore the auth_mode of the relevant user accounts will be switched to 'default'
     */
    public function delete()
    {
        require_once 'Services/Authentication/classes/External/UserAttributeMapping/class.ilExternalAuthUserAttributeMapping.php';
        $mapping = new ilExternalAuthUserAttributeMapping('saml', $this->getIdpId());
        $mapping->delete();

        $this->db->manipulateF(
            'UPDATE usr_data SET auth_mode = %s WHERE auth_mode = %s',
            array('text', 'text'),
            array('default', AUTH_SAML . '_' . $this->getIdpId())
        );

        $this->db->manipulate('DELETE FROM saml_idp_settings WHERE idp_id = ' . $this->db->quote($this->getIdpId(), 'integer'));
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'idp_id' => $this->getIdpId(),
            'is_active' => $this->isActive(),
            'default_role_id' => $this->getDefaultRoleId(),
            'uid_claim' => $this->getUidClaim(),
            'login_claim' => $this->getLoginClaim(),
            'sync_status' => $this->isSynchronizationEnabled(),
            'account_migr_status' => $this->isAccountMigrationEnabled(),
            'allow_local_auth' => $this->allowLocalAuthentication(),
            'entity_id' => $this->getEntityId()
        );
    }

    /**
     * @param array $record
     */
    public function bindDbRecord(array $record)
    {
        $this->setIdpId((int) $record['idp_id']);
        $this->setActive((bool) $record['is_active']);
        $this->setDefaultRoleId((int) $record['default_role_id']);
        $this->setUidClaim($record['uid_claim']);
        $this->setLoginClaim($record['login_claim']);
        $this->setSynchronizationStatus((bool) $record['sync_status']);
        $this->setAccountMigrationStatus((bool) $record['account_migr_status']);
        $this->setLocalLocalAuthenticationStatus((bool) $record['allow_local_auth']);
        $this->setEntityId($record['entity_id']);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function bindForm(ilPropertyFormGUI $form)
    {
        $this->setDefaultRoleId((int) $form->getInput('default_role_id'));
        $this->setUidClaim($form->getInput('uid_claim'));
        $this->setLoginClaim($form->getInput('login_claim'));
        $this->setSynchronizationStatus((bool) $form->getInput('sync_status'));
        $this->setLocalLocalAuthenticationStatus((bool) $form->getInput('allow_local_auth'));
        $this->setAccountMigrationStatus((bool) $form->getInput('account_migr_status'));

        /**
         * @var $metadata ilSamlIdpMetadataInputGUI
         */
        $metadata = $form->getItemByPostVar('metadata');
        $this->setEntityId($metadata->getIdpMetadataParser()->getEntityId());
    }

    /**
     * @param string $a_auth_mode
     * @return bool
     */
    public static function isAuthModeSaml($a_auth_mode)
    {
        if (!$a_auth_mode) {
            $GLOBALS['DIC']->logger()->auth()->write(__METHOD__ . ': No auth mode given..............');
            return false;
        }

        $auth_arr = explode('_', $a_auth_mode);
        return count($auth_arr) == 2 && $auth_arr[0] == AUTH_SAML && strlen($auth_arr[1]);
    }

    /**
     * @param string $a_auth_mode
     * @return null|int
     */
    public static function getIdpIdByAuthMode($a_auth_mode)
    {
        if (self::isAuthModeSaml($a_auth_mode)) {
            $auth_arr = explode('_', $a_auth_mode);
            return $auth_arr[1];
        }

        return null;
    }

    /**
     * @return self[]
     */
    public static function getActiveIdpList()
    {
        $idps = array();

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
    public static function getAllIdps()
    {
        global $DIC;

        $res = $DIC->database()->query('SELECT * FROM saml_idp_settings');

        $idps = array();
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $idp = new self();
            $idp->bindDbRecord($row);

            $idps[] = $idp;
        }

        return $idps;
    }

    /**
     * @param string $a_auth_key
     * @return string
     */
    public static function getAuthModeByKey($a_auth_key)
    {
        $auth_arr = explode('_', $a_auth_key);
        if (count((array) $auth_arr) > 1) {
            return 'saml_' . $auth_arr[1];
        }

        return 'saml';
    }

    /**
     * @param string $a_auth_mode
     * @return int|string
     */
    public static function getKeyByAuthMode($a_auth_mode)
    {
        $auth_arr = explode('_', $a_auth_mode);
        if (count((array) $auth_arr) > 1) {
            return AUTH_SAML . '_' . $auth_arr[1];
        }

        return AUTH_SAML;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entity_id;
    }

    /**
     * @param string $entity_id
     */
    public function setEntityId($entity_id)
    {
        $this->entity_id = $entity_id;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return (bool) $this->is_active;
    }

    /**
     * @param boolean $is_active
     */
    public function setActive($is_active)
    {
        $this->is_active = (bool) $is_active;
    }

    /**
     * @return int
     */
    public function getIdpId()
    {
        return (int) $this->idp_id;
    }

    /**
     * @param int $idp_id
     */
    public function setIdpId($idp_id)
    {
        $this->idp_id = (int) $idp_id;
    }

    /**
     * @return boolean
     */
    public function allowLocalAuthentication()
    {
        return (bool) $this->allow_local_auth;
    }

    /**
     * @param $status boolean
     */
    public function setLocalLocalAuthenticationStatus($status)
    {
        $this->allow_local_auth = (bool) $status;
    }

    /**
     * @return int
     */
    public function getDefaultRoleId()
    {
        return (int) $this->default_role_id;
    }

    /**
     * @param int $role_id
     */
    public function setDefaultRoleId($role_id)
    {
        $this->default_role_id = (int) $role_id;
    }

    /**
     * @param $claim string
     */
    public function setUidClaim($claim)
    {
        $this->uid_claim = $claim;
    }

    /**
     * @return string
     */
    public function getUidClaim()
    {
        return $this->uid_claim;
    }

    /**
     * @param $claim string
     */
    public function setLoginClaim($claim)
    {
        $this->login_claim = $claim;
    }

    /**
     * @return string
     */
    public function getLoginClaim()
    {
        return $this->login_claim;
    }

    /**
     * @return boolean
     */
    public function isSynchronizationEnabled()
    {
        return (bool) $this->sync_status;
    }

    /**
     * @param boolean $sync
     */
    public function setSynchronizationStatus($sync)
    {
        $this->sync_status = (bool) $sync;
    }

    /**
     * @return boolean
     */
    public function isAccountMigrationEnabled()
    {
        return (bool) $this->account_migration_status;
    }

    /**
     * @param boolean $status
     */
    public function setAccountMigrationStatus($status)
    {
        $this->account_migration_status = (int) $status;
    }
}
