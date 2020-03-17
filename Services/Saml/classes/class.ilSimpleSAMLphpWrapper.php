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

        SimpleSAML_Configuration::setConfigDir($configurationPath);
        $this->config = SimpleSAML_Configuration::getInstance();

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
                require_once 'Services/Password/classes/class.ilPasswordUtils.php';
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

        $params = array(
            'ReturnStateParam' => 'LogoutState',
            'ReturnStateStage' => 'ilLogoutState'
        );

        if (strlen($returnUrl) > 0) {
            $params['ReturnTo'] = $returnUrl;
        }

        $this->authSource->logout($params);
    }

    /**
     * @inheritdoc
     */
    public function getIdpDiscovery()
    {
        return new ilSimpleSAMLphplIdpDiscovery();
    }
}
