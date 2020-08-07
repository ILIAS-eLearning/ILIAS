<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSimpleSAMLphpWrapper
 */
class ilSimpleSAMLphpWrapper implements ilSamlAuth
{
    /**
     * @var SimpleSAML\Configuration
     */
    protected $config;

    /**
     * @var SimpleSAML\Auth\Simple
     */
    protected $authSource;

    /**
     * ilSimpleSAMLphpWrapper constructor.
     * @param string $authSourceName
     * @param string $configurationPath
     * @throws Exception
     */
    public function __construct($authSourceName, $configurationPath)
    {
        $this->initConfigFiles($configurationPath);

        SimpleSAML\Configuration::setConfigDir($configurationPath);
        $this->config = SimpleSAML\Configuration::getInstance();

        $sessionHandler = $this->config->getString('session.handler', false);
        $storageType = $this->config->getString('store.type', false);

        if (
            $storageType == 'phpsession' || $sessionHandler == 'phpsession' ||
            (empty($storageType) && empty($sessionHandler))
        ) {
            throw new RuntimeException('Invalid SimpleSAMLphp session handler: Must not be phpsession');
        }

        $this->authSource = new SimpleSAML\Auth\Simple($authSourceName);
    }

    /**
     * @param string $configurationPath
     */
    protected function initConfigFiles($configurationPath)
    {
        global $DIC;

        $templateHandler = new ilSimpleSAMLphpConfigTemplateHandler($DIC->filesystem()->storage());
        $templateHandler->copy('./Services/Saml/lib/config.php.dist', 'auth/saml/config/config.php', [
            'DB_PATH' => rtrim($configurationPath, '/') . '/ssphp.sq3',
            'SQL_INITIAL_PASSWORD' => function () {
                return substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(20))), 0, 10);
            },
            'COOKIE_PATH' => IL_COOKIE_PATH,
            'LOG_DIRECTORY' => ilLoggingDBSettings::getInstance()->getLogDir()
        ]);
        $templateHandler->copy('./Services/Saml/lib/authsources.php.dist', 'auth/saml/config/authsources.php', [
            'RELAY_STATE' => rtrim(ILIAS_HTTP_PATH, '/') . '/saml.php',
            'SP_ENTITY_ID' => rtrim(ILIAS_HTTP_PATH, '/') . '/Services/Saml/lib/metadata.php'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getAuthId() : string
    {
        return $this->authSource->getAuthSource()->getAuthId();
    }

    /**
     * @inheritdoc
     */
    public function protectResource() : void
    {
        $this->authSource->requireAuth();
    }

    /**
     * @inheritdoc
     */
    public function storeParam($key, $value)
    {
        $session = SimpleSAML\Session::getSessionFromRequest();
        $session->setData('ilias', $key, $value);
    }

    /**
     * @inheritdoc
     */
    public function getParam(string $key)
    {
        $session = SimpleSAML\Session::getSessionFromRequest();

        $value = $session->getData('ilias', $key);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function popParam(string $key)
    {
        $session = SimpleSAML\Session::getSessionFromRequest();
        $value = $this->getParam($key);
        $session->deleteData('ilias', $key);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function isAuthenticated() : bool
    {
        return $this->authSource->isAuthenticated();
    }

    /**
     * @inheritdoc
     */
    public function getAttributes() : array
    {
        return $this->authSource->getAttributes();
    }

    /**
     * @inheritdoc
     */
    public function logout(string $returnUrl = '') : void
    {
        ilSession::set('used_external_auth', false);

        $params = [
            'ReturnStateParam' => 'LogoutState',
            'ReturnStateStage' => 'ilLogoutState'
        ];

        if (strlen($returnUrl) > 0) {
            $params['ReturnTo'] = $returnUrl;
        }

        $this->authSource->logout($params);
    }

    /**
     * @inheritdoc
     */
    public function getIdpDiscovery() : ilSamlIdpDiscovery
    {
        return new ilSimpleSAMLphplIdpDiscovery();
    }

    /**
     * @inheritDoc
     */
    public function getAuthDataArray() : array
    {
        return $this->authSource->getAuthDataArray();
    }
}
