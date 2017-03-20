<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Auth/Container.php';

/**
 * Class ilAuthContainerSAML
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAuthContainerSAML extends Auth_Container
{
	/**
	 * @var string
	 */
	protected $uid = '';

	/**
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * @var string
	 */
	protected $return_to = '';

	/**
	 * @var bool
	 */
	private $force_creation = false;

	/**
	 * @var ilSamlIdp
	 */
	protected $idp;

	/**
	 * @param string $a_idp_id
	 */
	public function __construct($a_idp_id = null)
	{
		require_once 'Services/User/classes/class.ilUserDefinedFields.php';
		require_once 'Services/Saml/classes/class.ilSamlSettings.php';
		require_once 'Services/Saml/classes/class.ilSamlAttributesHolder.php';
		require_once 'Services/Saml/classes/class.ilSamlAttributeMapping.php';
		require_once 'Services/Saml/classes/class.ilSamlIdp.php';

		if(null === $a_idp_id)
		{
			$this->idp = ilSamlIdp::getFirstActiveIdp();
		}
		else
		{
			$this->idp = ilSamlIdp::getInstanceByIdpId($a_idp_id);
		}
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param bool   $is_challenge_response
	 * @return bool
	 */
	public function fetchData($username, $password, $is_challenge_response = false)
	{
		$attributes = ilSamlAttributesHolder::getAttributes();
		ilSamlAttributesHolder::setAttributes(array());
		$return_to = ilSamlAttributesHolder::getReturnTo();
		ilSamlAttributesHolder::setReturnTo('');

		if($attributes)
		{
			$this->uid        = $attributes[$this->idp->getUidClaim()][0];
			$this->attributes = $attributes;
			$this->return_to  = $return_to;
			return true;
		}

		if(ilSession::get('tmp_external_account') && ilSession::get('tmp_attributes'))
		{
			$this->uid        = ilSession::get('tmp_external_account');
			$this->attributes = ilSession::get('tmp_attributes');
			$this->return_to  = ilSession::get('tmp_return_to');
			return true;
		}

		return false;
	}

	/**
	 * Force creation of user accounts
	 * @param bool $a_status
	 */
	public function forceCreation($a_status)
	{
		$this->force_creation = true;
	}

	/**
	 * @see ilAuthContainerBase::loginObserver()
	 * {@inheritdoc}
	 */
	public function loginObserver($a_username, $a_auth)
	{
		$update_auth_mode = false;

		ilLoggerFactory::getLogger('auth')->debug(sprintf('Login observer called for SAML authentication request of ext_account "%s" and auth_mode "%s".', $this->uid, $this->getAuthMode()));
		ilLoggerFactory::getLogger('auth')->debug(sprintf('Target set to: %s', var_export($this->return_to, 1)));
		ilLoggerFactory::getLogger('auth')->debug(sprintf('Trying to find ext_account "%s" for auth_mode "%s".', $this->uid, $this->getAuthMode()));

		$internal_account = ilObjUser::_checkExternalAuthAccount($this->getAuthMode(), $this->uid, false);

		if(strlen($internal_account) == 0)
		{
			$update_auth_mode = true;

			ilLoggerFactory::getLogger('auth')->debug(sprintf('Could not find ext_account "%s" for auth_mode "%s".', $this->uid, $this->getAuthMode()));

			$fallback_auth_mode = 'local';
			ilLoggerFactory::getLogger('auth')->debug(sprintf('Trying to find ext_account "%s" for auth_mode "%s".', $this->uid, $fallback_auth_mode));
			$internal_account = ilObjUser::_checkExternalAuthAccount($fallback_auth_mode, $this->uid, false);

			if(strlen($internal_account) == 0 && AUTH_DEFAULT == AUTH_LOCAL)
			{
				ilLoggerFactory::getLogger('auth')->debug(sprintf('Could not find ext_account "%s" for auth_mode "%s".', $this->uid, $fallback_auth_mode));

				$fallback_auth_mode = 'default';
				ilLoggerFactory::getLogger('auth')->debug(sprintf('Trying to find ext_account "%s" for auth_mode "%s".', $this->uid, $fallback_auth_mode));
				$internal_account = ilObjUser::_checkExternalAuthAccount($fallback_auth_mode, $this->uid, false);
			}
		}

		if(strlen($internal_account) > 0)
		{
			ilLoggerFactory::getLogger('auth')->debug(sprintf('Found user "%s" for ext_account "%s" in ILIAS database.', $internal_account, $this->uid));

			if($this->idp->isSynchronizationEnabled())
			{
				ilLoggerFactory::getLogger('auth')->debug(sprintf('SAML user synchronisation is enabled, so update existing user "%s" with ext_account "%s".', $internal_account, $this->uid));
				$this->importUser($internal_account, $this->uid, $this->attributes);
			}
			else if($update_auth_mode)
			{
				$usr_id = ilObjUser::_loginExists($internal_account);
				if($usr_id > 0)
				{
					ilObjUser::_writeAuthMode($usr_id, $this->getAuthMode());
					ilLoggerFactory::getLogger('auth')->debug(sprintf('SAML user synchronisation is disabled, switched auth_mode of user with login "%s" and ext_account "%s" to "%s".', $internal_account, $this->uid, $this->getAuthMode()));
				}
				else
				{
					ilLoggerFactory::getLogger('auth')->debug(sprintf('SAML user synchronisation is disabled, could not switch auth_mode of user with login "%s" and ext_account "%s" to "%s".', $internal_account, $this->uid, $this->getAuthMode()));
				}
			}

			if(strlen($this->return_to))
			{
				$_GET['target'] = $this->return_to;
			}

			ilLoggerFactory::getLogger('auth')->debug(sprintf('Authentication succeeded: Found internal login "%s for ext_account "%s" and auth_mode "%s".', $internal_account, $this->uid, $this->getAuthMode()));

			$a_auth->setAuth($internal_account);
			$this->activeUser = $internal_account;
			$this->_auth_obj->setAuth($internal_account);
			$this->_auth_obj->username = $internal_account;
			ilSession::set('used_external_auth', true);
			return true;
		}
		else
		{
			ilLoggerFactory::getLogger('auth')->debug(sprintf('Could not find an existing user for ext_account "%s" for any relevant auth_mode.', $this->uid));
			if($this->idp->isSynchronizationEnabled())
			{
				ilLoggerFactory::getLogger('auth')->debug(sprintf('SAML user synchronisation is enabled, so determine action for ext_account "%s" and auth_mode "%s".', $this->uid, $this->getAuthMode()));
				if($this->idp->isAccountMigrationEnabled() && !$this->force_creation && !$_SESSION['force_creation'])
				{
					ilSession::set('tmp_auth_mode', 'saml');
					ilSession::set('tmp_auth_mode_id', $this->idp->getIdpId());
					ilSession::set('tmp_external_account', $this->uid);
					ilSession::set('tmp_attributes', $this->attributes);
					ilSession::set('tmp_return_to', $this->return_to);

					$a_auth->logout();

					ilLoggerFactory::getLogger('auth')->debug(sprintf('Account migration is enabled, so redirecting ext_account "%s" to account migration screen.', $this->uid));

					$return_to = '';
					if(strlen($this->return_to))
					{
						$return_to = '&target=' . $this->return_to;
					}

					ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmd=showAccountMigration&cmdClass=ilstartupgui' . $return_to);
				}

				$new_name = $this->importUser(null, $this->uid, $this->attributes);
				ilLoggerFactory::getLogger('auth')->debug(sprintf('Created new user account with login "%s" and ext_account "%s".', $new_name, $this->uid));
				$a_auth->setAuth($new_name);
				ilSession::set('tmp_external_account', null);
				ilSession::set('tmp_auth_mode_id', null);
				ilSession::set('tmp_attributes', null);
				ilSession::set('tmp_return_to', null);
				return true;
			}
			else
			{
				$a_auth->status = AUTH_SAML_FAILED;
				$a_auth->logout();
				ilLoggerFactory::getLogger('auth')->debug("SAML user synchronisation is not enabled, auth failed.");
				return false;
			}
		}
	}

	/**
	 * @return string
	 */
	protected function getAuthMode()
	{
		return 'saml_' . $this->idp->getIdpId();
	}

	/**
	 * @param string|null $a_internal_login
	 * @param string      $a_external_account
	 * @param array       $a_user_data
	 * @return string
	 */
	public function importUser($a_internal_login, $a_external_account, $a_user_data = array())
	{
		require_once 'Services/Xml/classes/class.ilXmlWriter.php';

		$mapping = ilSamlAttributeMapping::getInstanceByIdpId($this->idp->getIdpId());

		$xml_writer = new ilXmlWriter();
		$xml_writer->xmlStartTag('Users');
		if(null === $a_internal_login)
		{
			$login = $a_user_data[$this->idp->getLoginClaim()][0];
			$login = ilAuthUtils::_generateLogin($login);

			$xml_writer->xmlStartTag('User', array('Action' => 'Insert'));
			$xml_writer->xmlElement('Login', array(), $login);

			$xml_writer->xmlElement('Role', array(
				'Id'     => $this->idp->getDefaultRoleId(),
				'Type'   => 'Global',
				'Action' => 'Assign'
			));

			$xml_writer->xmlElement('Active', array(), "true");
			$xml_writer->xmlElement('TimeLimitOwner', array(), USER_FOLDER_ID);
			$xml_writer->xmlElement('TimeLimitUnlimited', array(), 1);
			$xml_writer->xmlElement('TimeLimitFrom', array(), time());
			$xml_writer->xmlElement('TimeLimitUntil', array(), time());
			$xml_writer->xmlElement('AuthMode', array('type' => $this->getAuthMode()), $this->getAuthMode());
			$xml_writer->xmlElement('ExternalAccount', array(), $a_external_account);

			require_once 'Services/Saml/classes/class.ilSamlCreateUpdateAttributeMappingFilter.php';
			$mapping = new ilSamlCreateUpdateAttributeMappingFilter($mapping);
		}
		else
		{
			$login  = $a_internal_login;
			$usr_id = ilObjUser::_lookupId($a_internal_login);

			$xml_writer->xmlStartTag('User', array('Action' => 'Update', 'Id' => $usr_id));

			require_once 'Services/Saml/classes/class.ilSamlUserUpdateAttributeMappingFilter.php';
			$mapping = new ilSamlUserUpdateAttributeMappingFilter($mapping);
		}

		foreach($mapping as $rule)
		{
			$value = $a_user_data[$rule->getIdpAttribute()][0];

			switch(strtolower($rule->getAttribute()))
			{
				case 'gender':
					switch(strtolower($value))
					{
						case 'm':
						case 'male':
							$xml_writer->xmlElement('Gender', array(), 'm');
							break;

						case 'f':
						case 'female':
						default:
							$xml_writer->xmlElement('Gender', array(), 'f');
							break;
					}
					break;

				case 'firstname':
					$xml_writer->xmlElement('Firstname', array(), $value);
					break;

				case 'lastname':
					$xml_writer->xmlElement('Lastname', array(), $value);
					break;

				case 'email':
					$xml_writer->xmlElement('Email', array(), $value);
					break;

				case 'institution':
					$xml_writer->xmlElement('Institution', array(), $value);
					break;

				case 'department':
					$xml_writer->xmlElement('Department', array(), $value);
					break;

				case 'hobby':
					$xml_writer->xmlElement('Hobby', array(), $value);
					break;

				case 'title':
					$xml_writer->xmlElement('Title', array(), $value);
					break;

				case 'street':
					$xml_writer->xmlElement('Street', array(), $value);
					break;

				case 'city':
					$xml_writer->xmlElement('City', array(), $value);
					break;

				case 'zipcode':
					$xml_writer->xmlElement('PostalCode', array(), $value);
					break;

				case 'country':
					$xml_writer->xmlElement('Country', array(), $value);
					break;

				case 'phone_office':
					$xml_writer->xmlElement('PhoneOffice', array(), $value);
					break;

				case 'phone_home':
					$xml_writer->xmlElement('PhoneHome', array(), $value);
					break;

				case 'phone_mobile':
					$xml_writer->xmlElement('PhoneMobile', array(), $value);
					break;

				case 'fax':
					$xml_writer->xmlElement('Fax', array(), $value);
					break;
				
				case 'referral_comment':
					$xml_writer->xmlElement('Comment', array(), $value);
					break;

				case 'matriculation':
					$xml_writer->xmlElement('Matriculation', array(), $value);
					break;

				case 'birthday':
					$xml_writer->xmlElement('Birthday', array(), $value);
					break;

				default:
					// Handle instant messengers
					if(substr($rule->getAttribute(), 0, 3) == 'im_')
					{
						$xml_writer->xmlElement(
							'AccountInfo',
							array('Type' => substr($rule->getAttribute(), 3)),
							$value
						);
						continue;
					}
					else if('delicious' == $rule->getAttribute())
					{
						$xml_writer->xmlElement(
							'AccountInfo',
							array('Type' => 'delicious'),
							$value
						);
						continue;
					}

					// Handle user defined fields
					if(substr($rule->getAttribute(), 0, 4) != 'udf_')
					{
						continue;
					}

					$udf_data = explode('_', $rule->getAttribute());
					if(!isset($udf_data[1]))
					{
						continue;
					}

					$definition = ilUserDefinedFields::_getInstance()->getDefinition($udf_data[1]);
					$xml_writer->xmlElement(
						'UserDefinedField',
						array('Id' => $definition['il_id'], 'Name' => $definition['field_name']),
						$value
					);
					break;
			}
		}

		$xml_writer->xmlEndTag('User');
		$xml_writer->xmlEndTag('Users');

		ilLoggerFactory::getLogger('auth')->debug(sprintf('Started import of user "%s" with ext_account "%s" and auch_mode "%s".', $login, $a_external_account, $this->getAuthMode()));
		include_once './Services/User/classes/class.ilUserImportParser.php';
		$importParser = new ilUserImportParser();
		$importParser->setXMLContent($xml_writer->xmlDumpMem(false));
		$importParser->setRoleAssignment(array(
			$this->idp->getDefaultRoleId() => $this->idp->getDefaultRoleId()
		));
		$importParser->setFolderId(USER_FOLDER_ID);
		$importParser->setUserMappingMode(IL_USER_MAPPING_ID);
		$importParser->startParsing();

		return $login;
	}
}
// saml-patch: end