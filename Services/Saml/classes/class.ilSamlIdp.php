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
	 * @var string
	 */
	protected $auth_id = '';

	/**
	 * @var bool
	 */
	protected $is_active = false;

	/**
	 * @var string
	 */
	protected $name = '';

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
	protected $idp = '';

	/**
	 * @var bool
	 */
	protected $account_migration_status = false;

	/**
	 * @var array|null
	 */
	protected static $parsed_idps = null;

	/**
	 * @var array
	 */
	protected static $idp_as_data = array();

	/**
	 * @param int $a_idp_id
	 */
	protected function __construct($a_idp_id = 0)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$this->db     = $ilDB;
		$this->idp_id = $a_idp_id;

		if($this->idp_id > 0)
		{
			self::parseIdps();
			$this->read();
		}
	}

	/**
	 *
	 */
	private static function parseIdps()
	{
		if(self::$parsed_idps === null)
		{
			$idp_data = array();

			require_once 'Services/Saml/lib/simplesamlphp/lib/_autoload.php';
			$idp_remote_auth_sources = array();

			$sources = SimpleSAML_Auth_Source::getSources();
			$i       = -1;
			foreach($sources as $id)
			{
				$i++;
				if($i === 0)
				{
					continue;
				}

				$as = new SimpleSAML_Auth_Simple($id);
				$idp_remote_auth_sources[$as->getAuthSource()->getMetadata()->getValue('idp')] = array(
					'id'      => $id,
					'auth_id' => $as->getAuthSource()->getAuthId(),
					'idp'     => $as->getAuthSource()->getMetadata()->getValue('idp')
				);
			}

			$i        = 0;
			$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
			foreach($metadata->getList('saml20-idp-remote') as $idp)
			{
				if(isset($idp_remote_auth_sources[$idp['entityid']]))
				{
					$idp_data[$i + 1] = array(
						'idp_id'  => $i + 1,
						'name'    => $idp_remote_auth_sources[$idp['entityid']]['id'] . ' (' . $idp['entityid'] . ')',
						'idp'     => $idp_remote_auth_sources[$idp['entityid']]['idp'],
						'auth_id' => $idp_remote_auth_sources[$idp['entityid']]['auth_id']
					);
				}

				++$i;
			}

			self::$parsed_idps = $idp_data;
		}
	}

	/**
	 * @return self
	 * @throws ilException
	 */
	public static function getFirstActiveIdp()
	{
		$idps = self::getActiveIdpList();
		if(count($idps) > 0)
		{
			return current($idps);
		}

		require_once 'Services/Exceptions/classes/class.ilException.php';
		throw new ilException('No active SAML IDP found');
	}

	/**
	 * @param int $a_idp_id
	 * @return self
	 */
	public static function getInstanceByIdpId($a_idp_id)
	{
		if(!isset(self::$instances[$a_idp_id]) || !(self::$instances[$a_idp_id] instanceof self))
		{
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
		$res   = $this->db->query($query);
		while($record = $this->db->fetchAssoc($res))
		{
			$this->bindDbRecord($record);
			break;
		}

		if(isset(self::$parsed_idps[$this->getIdpId()]))
		{
			$this->setName(self::$parsed_idps[$this->getIdpId()]['name']);
			$this->setAuthId(self::$parsed_idps[$this->getIdpId()]['auth_id']);
			$this->setIdp(self::$parsed_idps[$this->getIdpId()]['idp']);
		}
	}

	/**
	 *
	 */
	public function persist()
	{
		$this->db->replace(
			'saml_idp_settings',
			array(
				'idp_id' => array('integer', $this->getIdpId())
			),
			array(
				'is_active'           => array('integer', $this->isActive()),
				'allow_local_auth'    => array('integer', $this->allowLocalAuthentication()),
				'default_role_id'     => array('integer', $this->getDefaultRoleId()),
				'uid_claim'           => array('text', $this->getUidClaim()),
				'login_claim'         => array('text', $this->getLoginClaim()),
				'sync_status'         => array('integer', $this->isSynchronizationEnabled()),
				'account_migr_status' => array('integer', $this->isAccountMigrationEnabled())
			)
		);
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'idp_id'              => $this->getIdpId(),
			'name'                => $this->getName(),
			'is_active'           => $this->isActive(),
			'allow_local_auth'    => $this->allowLocalAuthentication(),
			'default_role_id'     => $this->getDefaultRoleId(),
			'uid_claim'           => $this->getUidClaim(),
			'login_claim'         => $this->getLoginClaim(),
			'sync_status'         => $this->isSynchronizationEnabled(),
			'account_migr_status' => $this->isAccountMigrationEnabled(),
			'idp'                 => $this->getIdp(),
		);
	}

	/**
	 * @param array $record
	 */
	public function bindDbRecord(array $record)
	{
		$this->setIdpId((int)$record['idp_id']);
		$this->setActive((bool)$record['is_active']);
		$this->setLocalLocalAuthenticationStatus((bool)$record['allow_local_auth']);
		$this->setDefaultRoleId((int)$record['default_role_id']);
		$this->setUidClaim($record['uid_claim']);
		$this->setLoginClaim($record['login_claim']);
		$this->setSynchronizationStatus((bool)$record['sync_status']);
		$this->setAccountMigrationStatus((bool)$record['account_migr_status']);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function bindForm(ilPropertyFormGUI $form)
	{
		$this->setLocalLocalAuthenticationStatus((bool)$form->getInput('allow_local_auth'));
		$this->setDefaultRoleId((int)$form->getInput('default_role_id'));
		$this->setUidClaim($form->getInput('uid_claim'));
		$this->setLoginClaim($form->getInput('login_claim'));
		$this->setSynchronizationStatus((bool)$form->getInput('sync_status'));
		$this->setAccountMigrationStatus((bool)$form->getInput('account_migr_status'));
	}

	/**
	 * @param string $a_auth_mode
	 * @return bool
	 */
	public static function isAuthModeSaml($a_auth_mode)
	{
		if(!$a_auth_mode)
		{
			$GLOBALS['ilLog']->write(__METHOD__ . ': No auth mode given..............');
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
		if(self::isAuthModeSaml($a_auth_mode))
		{
			$auth_arr = explode('_', $a_auth_mode);
			return $auth_arr[1];
		}

		return NULL;
	}

	/**
	 * @return self[]
	 */
	public static function getActiveIdpList()
	{
		$idps = array();

		foreach(self::getAllIdps() as $idp)
		{
			if($idp->isActive())
			{
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
		$idps = array();

		self::parseIdps();
		foreach(self::$parsed_idps as $idp_data)
		{
			$idps[] = new self($idp_data['idp_id']);
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
		if(count((array)$auth_arr) > 1)
		{
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
		if(count((array)$auth_arr) > 1)
		{
			return AUTH_SAML . '_' . $auth_arr[1];
		}

		return AUTH_SAML;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return boolean
	 */
	public function isActive()
	{
		return (bool)$this->is_active;
	}

	/**
	 * @param boolean $is_active
	 */
	public function setActive($is_active)
	{
		$this->is_active = (bool)$is_active;
	}

	/**
	 * @return int
	 */
	public function getIdpId()
	{
		return (int)$this->idp_id;
	}

	/**
	 * @param int $idp_id
	 */
	public function setIdpId($idp_id)
	{
		$this->idp_id = (int)$idp_id;
	}

	/**
	 * @return boolean
	 */
	public function allowLocalAuthentication()
	{
		return (bool)$this->allow_local_auth;
	}

	/**
	 * @param $status boolean
	 */
	public function setLocalLocalAuthenticationStatus($status)
	{
		$this->allow_local_auth = (bool)$status;
	}

	/**
	 * @return int
	 */
	public function getDefaultRoleId()
	{
		return (int)$this->default_role_id;
	}

	/**
	 * @param int $role_id
	 */
	public function setDefaultRoleId($role_id)
	{
		$this->default_role_id = (int)$role_id;
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
		return (bool)$this->sync_status;
	}

	/**
	 * @param boolean $sync
	 */
	public function setSynchronizationStatus($sync)
	{
		$this->sync_status = (bool)$sync;
	}

	/**
	 * @return boolean
	 */
	public function isAccountMigrationEnabled()
	{
		return (bool)$this->account_migration_status;
	}

	/**
	 * @param boolean $migr
	 */
	public function setAccountMigrationStatus($migr)
	{
		$this->account_migration_status = (int)$migr;
	}

	/**
	 * @return string
	 */
	public function getAuthId()
	{
		return $this->auth_id;
	}

	/**
	 * @param string $auth_id
	 */
	public function setAuthId($auth_id)
	{
		$this->auth_id = $auth_id;
	}

	/**
	 * @return string
	 */
	public function getIdp()
	{
		return $this->idp;
	}

	/**
	 * @param string $idp
	 */
	public function setIdp($idp)
	{
		$this->idp = $idp;
	}
}