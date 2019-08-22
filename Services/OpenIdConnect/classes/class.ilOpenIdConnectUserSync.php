<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOpenIdConnectSettingsGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilOpenIdConnectUserSync
{
	const AUTH_MODE = 'oidc';

	/**
	 * @var ilOpenIdConnectSettings
	 */
	protected $settings;

	/**
	 * @var \ilLogger
	 */
	protected $logger;

	/**
	 * @var \ilXmlWriter
	 */
	private $writer;
	/**
	 * @var array
	 */
	private $user_info = [];

	/**
	 * @var string
	 */
	private $ext_account = '';


	/**
	 * @var string
	 */
	private $int_account = '';

	/**
	 * @var int
	 */
	private $usr_id = 0;


	/**
	 * ilOpenIdConnectUserSync constructor.
	 * @param ilOpenIdConnectSettings $settings
	 */
	public function __construct(\ilOpenIdConnectSettings $settings, $user_info)
	{
		global $DIC;

		$this->settings = $settings;
		$this->logger = $DIC->logger()->auth();

		$this->writer = new ilXmlWriter();

		$this->user_info = $user_info;
	}

	/**
	 * @param string $ext_account
	 */
	public function setExternalAccount(string $ext_account)
	{
		$this->ext_account = $ext_account;
	}

	/**
	 * @param string $int_account
	 */
	public function setInternalAccount(string $int_account)
	{
		$this->int_account = $int_account;
		$this->usr_id = ilObjUser::_lookupId($this->int_account);
	}

	/**
	 * @return int
	 */
	public function getUserId() : int
	{
		return $this->usr_id;
	}

	/**
	 * @return bool
	 */
	public function needsCreation() : bool
	{
		$this->logger->dump($this->int_account, \ilLogLevel::DEBUG);
		return strlen($this->int_account) == 0;
	}

	/**
	 * @return bool
	 * @throws ilOpenIdConnectSyncForbiddenException
	 */
	public function updateUser()
	{
		if($this->needsCreation() && !$this->settings->isSyncAllowed())
		{
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


		// lookup internal account
		$int_account = ilObjUser::_checkExternalAuthAccount(
			self::AUTH_MODE,
			$this->ext_account
		);
		$this->setInternalAccount($int_account);
		return true;
	}

	/**
	 * transform user data to xml
	 */
	protected function transformToXml()
	{
		$this->writer->xmlStartTag('Users');

		if($this->needsCreation())
		{
			$this->writer->xmlStartTag('User',['Action' => 'Insert']);
			$this->writer->xmlElement('Login',[],ilAuthUtils::_generateLogin($this->ext_account));
		}
		else
		{
			$this->writer->xmlStartTag(
				'User',
				[
					'Id' => $this->getUserId(),
					'Action' => 'Update'
				]);
			$this->writer->xmlElement('Login',[],$this->int_account);
		}

		$this->writer->xmlElement('ExternalAccount',array(),$this->ext_account);
		$this->writer->xmlElement('AuthMode',array('type' => self::AUTH_MODE),null);

		$this->parseRoleAssignments();

		$this->writer->xmlElement('Active',array(),"true");
		$this->writer->xmlElement('TimeLimitOwner',array(),7);
		$this->writer->xmlElement('TimeLimitUnlimited',array(),1);
		$this->writer->xmlElement('TimeLimitFrom',array(),time());
		$this->writer->xmlElement('TimeLimitUntil',array(),time());

		foreach($this->settings->getProfileMappingFields() as $field => $lng_key)
		{
			$connect_name = $this->settings->getProfileMappingFieldValue($field);
			if(!$connect_name)
			{
				$this->logger->debug('Ignoring unconfigured field: ' . $field);
				continue;

			}
			if(!$this->needsCreation() && !$this->settings->getProfileMappingFieldUpdate($field))
			{
				$this->logger->debug('Ignoring '. $field . ' for update.');
				continue;
			}

			$value = $this->valueFrom($connect_name);
			if(!strlen($value))
			{
				$this->logger->debug('Cannot find user data in '. $connect_name);
				continue;
			}

			switch($field)
			{
				case 'firstname':
					$this->writer->xmlElement('Firstname',[], $value);
					break;

				case 'lastname':
					$this->writer->xmlElement('Lastname',[], $value);
					break;

				case 'email':
					$this->writer->xmlElement('Email',[], $value);
					break;

				case 'birthday':
					$this->writer->xmlElement('Birthday',[], $value);
					break;
			}
		}
		$this->writer->xmlEndTag('User');
		$this->writer->xmlEndTag('Users');

		$this->logger->debug($this->writer->xmlDumpMem());
	}

	/**
	 * Parse role assignments
	 * @return array array of role assignments
	 */
	protected function parseRoleAssignments() : array
	{
		$this->logger->debug('Parsing role assignments');

		$found_role = false;

		$roles_assignable[$this->settings->getRole()] = $this->settings->getRole();


		$this->logger->dump($this->settings->getRoleMappings(),\ilLogLevel::DEBUG);

		foreach($this->settings->getRoleMappings() as $role_id => $role_info) {

			$this->logger->dump($role_id);
			$this->logger->dump($role_info);

			if(
				!isset($role_info['value']) ||
				!strlen($role_info['value'])
			) {
				$this->logger->debug('No role mapping configuration for: ' . $role_id);
				continue;
			}

			$value = trim($role_info['value']);

			if(
				!isset($this->user_info->groups) ||
				!is_array($this->user_info->groups)
			) {
				$this->logger->debug('No user info passed');
				continue;
			}

			if(!in_array($value, $this->user_info->groups)) {
				$this->logger->debug('User account groups have no: ' . $value);
				continue;
			}

			if(
				!$this->needsCreation() &&
				!$role_info['update']
			) {
				$this->logger->debug('No user role update for role: ' . $role_id);
				continue;
			}

			$this->logger->debug('Matching role mapping for role_id: ' . $role_id);

			$found_role = true;
			$roles_assignable[$role_id] = $role_id;
			$long_role_id = ('il_' . IL_INST_ID . '_role_'.$role_id);

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

		if($this->needsCreation() && !$found_role)
		{
			$long_role_id = ('il_' . IL_INST_ID . '_role_'.$this->settings->getRole());

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


	/**
	 * @param string $connect_name
	 */
	protected function valueFrom(string $connect_name) : string
	{
		if(!$connect_name)
		{
			return '';
		}
		if(!property_exists($this->user_info,$connect_name))
		{
			$this->logger->debug('Cannot find property ' . $connect_name .' in user info ');
			return '';
		}
		$val = $this->user_info->$connect_name;
		return $val;
	}
}