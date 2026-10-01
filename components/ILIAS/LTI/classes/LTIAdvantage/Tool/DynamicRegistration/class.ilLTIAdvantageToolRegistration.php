<?php

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

declare(strict_types=1);

use ceLTIc\LTI\Platform;
use ceLTIc\LTI\Profile\Item;
use ceLTIc\LTI\Profile\Message;
use ceLTIc\LTI\Profile\ResourceHandler;
use ceLTIc\LTI\Service\LineItem;
use ceLTIc\LTI\Service\Result;
use ceLTIc\LTI\Service\Score;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\Util;
use Random\RandomException;

/**
 * LTI Advantage Dynamic Registration of ILIAS as tool with a platform of the administration.
 *
 * The administration gives out a registration URL for the platform (ltitoolregistration.php) with a token
 * that names the platform and can be used once. The platform opens it with its OpenID configuration and its
 * own registration token. celtic/lti reads the configuration, checks it and posts the configuration of ILIAS
 * to the platform; the answer, with the client id and the deployment id of ILIAS, becomes the registration of
 * the platform. The registration takes no session of ILIAS: the platform usually opens the URL in an iframe
 * of its own site, where a session cookie of ILIAS is not sent.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantageToolRegistration extends Tool
{
    /**
     * The setting of the registration of the platform that keeps the id of the token it was made with.
     */
    public const string SETTING_USED_TOKEN = 'ilias_dynamic_registration';

    private const string PURPOSE = 'lti_platform_registration';
    private const int TOKEN_LIFETIME = 86400;
    private const string TOKEN_PARAM = 'registration';
    private const string TOOL_CONFIGURATION = 'https://purl.imsglobal.org/spec/lti-tool-configuration';
    private const string MESSAGE_LAUNCH = 'basic-lti-launch-request';
    private const string MESSAGE_DEEP_LINKING = 'ContentItemSelectionRequest';

    /**
     * What ILIAS takes from a launch to create the user.
     */
    private const array CAPABILITIES = [
        'User.id',
        'Person.name.full',
        'Person.name.given',
        'Person.name.family',
        'Person.email.primary',
    ];

    private int $platform_id = 0;
    private string $token_id = '';

    public function __construct()
    {
        parent::__construct(new ilLTIDataConnector());
    }

    /**
     * A new registration URL for the platform of the administration, valid for a day and for one registration.
     *
     * @throws ilException when ILIAS has no key
     * @throws RandomException
     */
    public static function getRegistrationUrl(int $platform_id): string
    {
        $now = time();

        return ilObjLTITool::getIliasHttpPath() . '/ltitoolregistration.php?' . http_build_query([
            self::TOKEN_PARAM => ilLTIAdvantageKeyPair::sign([
                'purpose' => self::PURPOSE,
                'sub' => $platform_id,
                'jti' => bin2hex(random_bytes(16)),
                'iat' => $now,
                'exp' => $now + self::TOKEN_LIFETIME,
            ]),
        ]);
    }

    /**
     * Registers ILIAS with the platform that opened the registration URL.
     *
     * @param array $parameters the parameters of the request
     * @return array{0: bool, 1: string} whether ILIAS is registered, and why not
     * @throws RandomException
     */
    public function register(array $parameters): array
    {
        Util::$requestParameters = $parameters;
        $this->ok = $this->acceptToken((string) ($parameters[self::TOKEN_PARAM] ?? ''));
        if (!$this->ok) {
            $this->reason = 'The registration URL is invalid, expired or already used.';
        } elseif (!isset($parameters['openid_configuration']) || !is_string($parameters['openid_configuration'])) {
            $this->ok = false;
            $this->reason = 'The platform did not send its OpenID configuration.';
        } else {
            try {
                ilLTIAdvantageKeyPair::applyTo($this);
                $this->onRegistration();
            } catch (ilException $e) {
                $this->ok = false;
                $this->reason = $e->getMessage();
            } catch (Throwable $e) {
                // what the platform sent broke the library: the page only says that the registration failed
                global $DIC;

                $DIC->logger()->forComponent('lti')->error($e->getMessage());
                $this->ok = false;
                $this->reason = 'The answer of the platform could not be processed.';
            }
        }

        return [$this->ok, (string) $this->reason];
    }

    /**
     * The flow of celtic/lti, without its page: the endpoint sends one of ILIAS.
     */
    protected function onRegistration(): void
    {
        $platform_configuration = $this->getPlatformConfiguration();
        if ($this->ok && !$this->isValidIssuer($platform_configuration['issuer'] ?? null)) {
            $this->ok = false;
            $this->setReason('The platform configuration has no valid issuer.');
        }
        if (!$this->ok) {
            return;
        }

        $registration = $this->sendRegistration($platform_configuration, $this->getConfiguration($platform_configuration));
        if ($this->ok) {
            $this->getPlatformToRegister($platform_configuration, $registration);
        }
    }

    /**
     * ILIAS is launched at lti.php, which also takes the OpenID Connect login and the Deep Linking requests.
     * What it asks for of the user and of the services is what a launch uses.
     */
    protected function getConfiguration(array $platformConfig): array
    {
        $title = ilObjSystemFolder::_getHeaderTitle();
        $this->product = new Item(null, $title !== '' ? $title : 'ILIAS', 'ILIAS');
        $this->baseUrl = ilObjLTITool::getIliasHttpPath();
        $this->resourceHandlers = [
            new ResourceHandler(
                new Item(),
                '',
                [new Message(self::MESSAGE_LAUNCH, '/lti.php', self::CAPABILITIES)],
                [new Message(self::MESSAGE_DEEP_LINKING, '/lti.php', self::CAPABILITIES)]
            ),
        ];
        // the Assignment and Grade Services ILIAS sends the learning progress with
        $this->requiredScopes = [LineItem::$SCOPE, Result::$SCOPE, Score::$SCOPE];

        return parent::getConfiguration($platformConfig);
    }

    /**
     * Stores the platform as the registration of the platform of the administration, enabled as that is.
     */
    protected function getPlatformToRegister(array $platformConfig, array $registrationConfig, bool $doSave = true): Platform
    {
        // Some platforms send the ids as numbers, such as ILIAS before this release its deployment id. The library
        // only takes strings.
        $client_id = $registrationConfig['client_id'] ?? null;
        $deployment_id = $registrationConfig[self::TOOL_CONFIGURATION]['deployment_id'] ?? null;
        if (!is_scalar($client_id) || (string) $client_id === '' || !is_scalar($deployment_id) || (string) $deployment_id === '') {
            $this->ok = false;
            $this->setReason('The platform did not give ILIAS a client id and a deployment id.');
            return new Platform($this->dataConnector);
        }
        $registrationConfig['client_id'] = (string) $client_id;
        $registrationConfig[self::TOOL_CONFIGURATION]['deployment_id'] = (string) $deployment_id;

        $platform = parent::getPlatformToRegister($platformConfig, $registrationConfig, false);
        $registered_id = ilLTIPlatform::lookupIdByRegistration(
            (string) $platform->platformId,
            (string) $platform->clientId,
            (string) $platform->deploymentId
        );
        if ($registered_id > 0 && $registered_id !== $this->platform_id) {
            $this->ok = false;
            $this->setReason('Another platform of ILIAS is already registered with this issuer, client id and deployment id.');
            return $platform;
        }

        $row = ilLTIPlatform::lookupAdministrationRow($this->platform_id);
        ilLTIPlatform::saveRegistration(
            $this->platform_id,
            (string) ($row['title'] ?? $platform->name),
            (bool) ($row['active'] ?? false),
            [
                'platform_id' => $platform->platformId,
                'client_id' => $platform->clientId,
                'deployment_id' => $platform->deploymentId,
                'keyset_url' => (string) $platform->jku,
                'token_url' => (string) $platform->accessTokenUrl,
                'authentication_url' => (string) $platform->authenticationUrl,
            ],
            [self::SETTING_USED_TOKEN => $this->token_id]
        );

        return $platform;
    }

    /**
     * The token of the registration URL: signed by ILIAS for a registration, for an LTI Advantage platform of the
     * administration, and not used yet.
     *
     * @throws RandomException
     */
    private function acceptToken(string $token): bool
    {
        try {
            $payload = $token !== '' ? ilLTIAdvantageKeyPair::verify($token) : null;
        } catch (ilException) {
            $payload = null;
        }
        if (
            $payload === null
            || ($payload['purpose'] ?? null) !== self::PURPOSE
            || !is_int($payload['sub'] ?? null)
            || !is_string($payload['jti'] ?? null)
        ) {
            return false;
        }

        global $DIC;

        $platform_id = $payload['sub'];
        if (
            ilLTIPlatform::lookupAdministrationRow($platform_id) === []
            || ilLTIAdministrationPlatformForm::lookupVersion($DIC->database(), $platform_id) !== ilLTIAdministrationPlatformForm::VERSION_ADVANTAGE
            || (ilLTIPlatform::lookupRegistrationSettings($platform_id)[self::SETTING_USED_TOKEN] ?? null) === $payload['jti']
        ) {
            return false;
        }

        $this->platform_id = $platform_id;
        $this->token_id = $payload['jti'];

        return true;
    }

    /**
     * The issuer names the platform in every launch, in a column of 255 characters.
     */
    private function isValidIssuer(mixed $issuer): bool
    {
        return is_string($issuer) && $issuer !== '' && strlen($issuer) <= 255;
    }
}
