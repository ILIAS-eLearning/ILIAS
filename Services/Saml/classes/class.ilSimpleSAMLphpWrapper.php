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
 * Class ilSimpleSAMLphpWrapper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSimpleSAMLphpWrapper implements ilSamlAuth
{
    protected SimpleSAML\Configuration $config;
    protected SimpleSAML\Auth\Simple $authSource;

    public function __construct(string $authSourceName, string $configurationPath)
    {
        $this->initConfigFiles($configurationPath);

        SimpleSAML\Configuration::setConfigDir($configurationPath);
        $this->config = SimpleSAML\Configuration::getInstance();

        $sessionHandler = $this->config->getString('session.handler', false);
        $storageType = $this->config->getString('store.type', false);

        if (
            $storageType === 'phpsession' || $sessionHandler === 'phpsession' ||
            (empty($storageType) && empty($sessionHandler))
        ) {
            throw new RuntimeException('Invalid SimpleSAMLphp session handler: Must not be phpsession or empty');
        }

        $this->authSource = new SimpleSAML\Auth\Simple($authSourceName);
    }

    protected function initConfigFiles(string $configurationPath) : void
    {
        global $DIC;

        $templateHandler = new ilSimpleSAMLphpConfigTemplateHandler($DIC->filesystem()->storage());
        $templateHandler->copy('./Services/Saml/lib/config.php.dist', 'auth/saml/config/config.php', [
            'DB_PATH' => rtrim($configurationPath, '/') . '/ssphp.sq3',
            'SQL_INITIAL_PASSWORD' => static function () : string {
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
    public function storeParam(string $key, $value) : void
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

        return $session->getData('ilias', $key);
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

        if ($returnUrl !== '') {
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
