<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';
require_once 'Services/Saml/interfaces/interface.ilSamlAuth.php';

/**
 * Class ilSimpleSAMLphpWrapper
 */
class ilSimpleSAMLphpWrapper implements ilSamlAuth
{
	/**
	 * @var SimpleSAML_Configuration
	 */
	protected $config;

	/**
	 * @var SimpleSAML_Auth_Simple
	 */
	protected $authSource;

	/**
	 * ilSimpleSAMLphpWrapper constructor.
	 * @param string $authSourceName
	 * @param string $configurationPath
	 */
	public function __construct($authSourceName, $configurationPath)
	{
		global $DIC;

		$fs = $DIC->filesystem()->storage();
		if(!$fs->has('auth/saml/config/config.php'))
		{
			$fs->put('auth/saml/config/config.php', file_get_contents('./Services/Saml/lib/config.php.dist'));
		}
		if(!$fs->has('auth/saml/config/authsources.php'))
		{
			$fs->put('auth/saml/config/authsources.php', file_get_contents('./Services/Saml/lib/authsources.php.dist'));
		}

		SimpleSAML_Configuration::setConfigDir($configurationPath);

		$this->config   = SimpleSAML_Configuration::getInstance();
		$sessionHandler = $this->config->getString('session.handler', false);
		$storageType    = $this->config->getString('store.type', false);
		if($storageType == 'phpsession' || $sessionHandler == 'phpsession' || (empty($storageType) && empty($sessionHandler)))
		{
			throw new RuntimeException('Invalid SimpleSAMLphp session handler: Must not be phpsession');
		}

		$this->authSource = new SimpleSAML_Auth_Simple($authSourceName);
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthId()
	{
		return $this->authSource->getAuthSource()->getAuthId();
	}

	/**
	 * @inheritdoc
	 */
	public function protectResource()
	{
		$this->authSource->requireAuth();
	}

	/**
	 * @inheritdoc
	 */
	public function storeParam($key, $value)
	{
		$session = SimpleSAML_Session::getSessionFromRequest();
		$session->setData('ilias', $key, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function getParam($key)
	{
		$session = SimpleSAML_Session::getSessionFromRequest();

		$value = $session->getData('ilias', $key);

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function popParam($key)
	{
		$session = SimpleSAML_Session::getSessionFromRequest();
		$value = $this->getParam($key);
		$session->deleteData('ilias', $key);

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function isAuthenticated()
	{
		return $this->authSource->isAuthenticated();
	}

	/**
	 * @inheritdoc
	 */
	public function getAttributes()
	{
		return $this->authSource->getAttributes();
	}

	/**
	 * @inheritdoc
	 */
	public function logout($returnUrl = '')
	{
		ilSession::set('used_external_auth', false);
		ilUtil::setCookie("SAMLSESSID","");
		ilUtil::setCookie("SimpleSAMLAuthToken","");

		$params = array(
			'ReturnStateParam' => 'LogoutState',
			'ReturnStateStage' => 'ilLogoutState'
		);

		if(strlen($returnUrl) > 0)
		{
			$params['ReturnTo']= $returnUrl;
		}

		$this->authSource->logout($params);
	}

	/**
	 * @inheritdoc
	 */
	public function getIdpDiscovery()
	{
		require_once 'Services/Saml/classes/class.ilSimpleSAMLphplIdpDiscovery.php';
		return new ilSimpleSAMLphplIdpDiscovery();
	}
}