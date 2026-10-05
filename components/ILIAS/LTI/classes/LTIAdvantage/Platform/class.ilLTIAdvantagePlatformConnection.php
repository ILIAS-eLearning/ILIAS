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

use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Jwt\Jwt;
use ceLTIc\LTI\Platform;
use ceLTIc\LTI\PlatformNonce;
use ceLTIc\LTI\Service\LineItem;
use ceLTIc\LTI\Service\Membership;
use ceLTIc\LTI\Service\Result;
use ceLTIc\LTI\Service\Score;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\Util;
use Random\RandomException;

/**
 * ILIAS as the LTI Advantage platform of one tool, in the terms of celtic/lti: the platform is ILIAS with
 * the client id and deployment id it gave the tool and the key of ILIAS; the tool is the default tool of
 * the library, with its key and URLs. The library does the protocol: the OpenID Connect login, the
 * id_token, the check of the client assertion of a token request and the access token.
 *
 * The login a launch starts is kept in the session of the user until the tool asks for the id_token at
 * ltiauth.php. Unlike the library, an authentication request without such a login is refused.
 *
 * An embedded launch offers the tool the platform storage of LTI (postMessage to the page around the
 * iframe, see getStorageJS()), for the tools that cannot keep their state in a cookie inside an iframe.
 *
 * The answer of a Deep Linking request comes back here as well: the library checks the signature, the expiry
 * and the nonce of the JWT, this class that it is a Deep Linking response of the tool for ILIAS.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIAdvantagePlatformConnection extends Platform
{
    /**
     * What an access token of ILIAS may grant: the Assignment and Grade Services and the Names and Role
     * Provisioning Services.
     *
     * @return string[]
     */
    public static function getAccessTokenScopes(): array
    {
        return [LineItem::$SCOPE, LineItem::$SCOPE_READONLY, Result::$SCOPE, Score::$SCOPE, Membership::$SCOPE];
    }

    private const string MESSAGE_LAUNCH = 'basic-lti-launch-request';
    private const string MESSAGE_DEEP_LINKING_REQUEST = 'ContentItemSelectionRequest';
    private const string MESSAGE_DEEP_LINKING_RESPONSE = 'ContentItemSelection';
    private const string STORAGE_FRAME_PARENT = '_parent';
    private const string SESSION_KEY = 'lti_advantage_logins';
    private const int LOGIN_LIFETIME = 600;

    /**
     * @throws ilException when the tool is not an LTI Advantage tool or ILIAS has no key
     * @throws RandomException
     */
    public function __construct(ilLTITool $tool)
    {
        parent::__construct(new ilLTIDataConnector());

        if ($tool->getLtiVersion() !== ilLTITool::VERSION_ADVANTAGE || $tool->getClientId() === '') {
            throw new ilException('The LTI tool ' . $tool->getId() . ' is not an LTI Advantage tool.');
        }

        $this->platformId = ilLTIEndpoint::getBaseUrl();
        $this->clientId = $tool->getClientId();
        // earlier releases gave every tool its own deployment, named by the id of the tool
        $this->deploymentId = (string) $tool->getId();
        $this->ltiVersion = LtiVersion::V1P3;
        $this->authenticationUrl = ilLTIEndpoint::getUrl(ilLTIEndpoint::AUTHENTICATION);
        $this->accessTokenUrl = ilLTIEndpoint::getUrl(ilLTIEndpoint::TOKEN);
        ilLTIAdvantageKeyPair::applyTo($this);

        $counterpart = new Tool(new ilLTIDataConnector());
        $counterpart->ltiVersion = LtiVersion::V1P3;
        $counterpart->messageUrl = $tool->getUrl();
        $counterpart->initiateLoginUrl = $tool->getInitiateLoginUrl();
        $counterpart->redirectionUris = $tool->getRedirectionUris();
        $counterpart->rsaKey = $tool->getPublicKey() !== '' ? $tool->getPublicKey() : null;
        $counterpart->jku = $tool->getPublicKeysetUrl() !== '' ? $tool->getPublicKeysetUrl() : null;
        Tool::$defaultTool = $counterpart;

        // the key the library fetches from the key set of the tool is not kept: the tool is not stored in
        // the tables of the library, and the key set is asked for every time
        Util::$disableFetchedPublicKeysSave = true;
    }

    /**
     * The connection of the LTI Advantage tool ILIAS gave the client id, null when there is none.
     *
     * @throws ilException when ILIAS has no key
     * @throws RandomException
     */
    public static function forClientId(string $client_id): ?self
    {
        $tool_id = ilLTITool::lookupIdByClientId($client_id);

        return $tool_id > 0 ? new self(new ilLTITool($tool_id)) : null;
    }

    public function isRedirectionUri(string $uri): bool
    {
        return in_array($uri, Tool::$defaultTool->redirectionUris, true);
    }

    /**
     * Checks the client assertion of a token request: it is signed with the key of the tool and names the
     * tool as issuer and subject. The library also requires jti, iat, exp and the token URL as audience,
     * which tools set up for earlier releases of ILIAS may leave out, so they are only checked when they
     * are there: a jti is used once, the times are checked with the signature and the audience must be an
     * address of this ILIAS.
     */
    public function verifyClientAssertion(string $assertion): bool
    {
        $jwt = Jwt::getJwtClient();
        if (!$jwt->load($assertion)) {
            return $this->setReason('The client assertion is not a JWT.');
        }
        if ($jwt->getClaim('iss') !== $this->clientId || $jwt->getClaim('sub') !== $this->clientId) {
            return $this->setReason('The issuer and subject of the client assertion are not the client id.');
        }
        if ($jwt->hasClaim('aud')) {
            $audience = $jwt->getClaim('aud');
            $own_host = parse_url($this->platformId, PHP_URL_HOST);
            $hosts = array_map(
                static fn($uri): ?string => is_string($uri) ? parse_url($uri, PHP_URL_HOST) : null,
                is_array($audience) ? $audience : [$audience]
            );
            if (!in_array($own_host, $hosts, true)) {
                return $this->setReason('The audience of the client assertion is not this platform.');
            }
        }
        if ($jwt->hasClaim('jti')) {
            $nonce = new PlatformNonce($this, (string) $jwt->getClaim('jti'));
            if ($nonce->load() || !$nonce->save()) {
                return $this->setReason('The client assertion was used before.');
            }
        }
        $public_key = Tool::$defaultTool->rsaKey;
        if (!$jwt->verifySignature($public_key, Tool::$defaultTool->jku)) {
            return $this->setReason('The signature or the times of the client assertion are not valid.');
        }

        return true;
    }

    /**
     * The page that starts the launch of an object: it sends the browser to the login initiation URL of
     * the tool. The launch itself waits in the session for the authentication request of the tool.
     *
     * @param array $parameters see ilLTIAdvantagePlatformLaunchParameterBuilder
     * @param bool $embedded true when the tool opens in an iframe of the page that handles the platform storage
     * @param string $url the target link of the object, when it has its own instead of the one of the tool
     */
    public function getLaunchPage(int $ref_id, array $parameters, bool $embedded, string $url = ''): string
    {
        return $this->getMessagePage(
            $url !== '' ? $url : Tool::$defaultTool->messageUrl,
            self::MESSAGE_LAUNCH,
            $parameters,
            (string) $ref_id,
            $embedded
        );
    }

    /**
     * The page that starts a Deep Linking request, which the tool opens in an iframe of ILIAS.
     *
     * @param array $parameters see ilLTIAdvantagePlatformDeepLinking
     * @param string $state the state of the request, which also tells its login apart from the others of the session
     */
    public function getDeepLinkingPage(string $url, array $parameters, string $state): string
    {
        return $this->getMessagePage($url, self::MESSAGE_DEEP_LINKING_REQUEST, $parameters, $state, true);
    }

    /**
     * @param array $parameters
     */
    private function getMessagePage(string $url, string $type, array $parameters, string $hint, bool $embedded): string
    {
        self::$browserStorageFrame = $embedded ? self::STORAGE_FRAME_PARENT : null;

        return $this->sendMessage($url, $type, $parameters, '', (string) $parameters['user_id'], $hint);
    }

    /**
     * @param string $url
     * @param string $loginHint
     * @param string|null $ltiMessageHint
     * @param array $params
     */
    protected function onInitiateLogin(string &$url, string &$loginHint, ?string &$ltiMessageHint, array $params): void
    {
        self::getLogins()->add((string) $ltiMessageHint, [
            'client_id' => $this->clientId,
            'message_url' => $url,
            'login_hint' => $loginHint,
            'params' => $params,
            'storage_frame' => self::$browserStorageFrame,
        ]);
    }

    private static function getLogins(): ilLTIAdvantagePendingRequests
    {
        return new ilLTIAdvantagePendingRequests(self::SESSION_KEY, self::LOGIN_LIFETIME);
    }

    /**
     * Accepts the authentication request only for a login this session started for the same tool, user
     * and object in the last minutes. Each login is used once.
     */
    protected function onAuthenticate(): void
    {
        $parameters = Util::getRequestParameters();
        $hint = (string) ($parameters['lti_message_hint'] ?? '');
        $login = self::getLogins()->get($hint);

        if (
            $login === null
            || $login['client_id'] !== $this->clientId
            || $login['login_hint'] !== ($parameters['login_hint'] ?? null)
        ) {
            $this->setReason($login === null
                ? 'No login of this session waits for it, or it expired.'
                : 'It is not the login of this session for the tool and user.');
            $this->messageParameters['error'] = 'access_denied';
            return;
        }

        self::getLogins()->take($hint);
        Tool::$defaultTool->messageUrl = $login['message_url'];
        $this->messageParameters = $login['params'];
        // the library tells the tool again where the platform storage is, with the id_token
        self::$browserStorageFrame = $login['storage_frame'] ?? null;
    }

    /**
     * Logs how the authentication request of a launch was answered: with the id_token, or with the error the
     * tool gets. The library ends the request when it answers, so this runs when the request ends.
     */
    public function logAuthenticationAnswer(): void
    {
        global $DIC;

        $parameters = Util::getRequestParameters();
        $request = ilLTILibraryLogger::describe([
            'client_id' => $this->clientId,
            'deployment_id' => $this->deploymentId,
            'message' => $this->messageParameters['lti_message_type'] ?? null,
            'hint' => $parameters['lti_message_hint'] ?? null,
            'user_id' => $parameters['login_hint'] ?? null,
            'nonce' => $parameters['nonce'] ?? null,
            'state' => $parameters['state'] ?? null,
            'redirect_uri' => $parameters['redirect_uri'] ?? null,
        ]);
        $log = $DIC->logger()->forComponent('lti');
        if ($this->ok) {
            $log->info('LTI Advantage id_token sent to the tool: {request}', ['request' => $request]);
            return;
        }
        $log->warning('LTI Advantage authentication request refused: {error} {reason} ({request})', [
            'error' => $this->messageParameters['error'] ?? 'server_error',
            'reason' => $this->reason ?? $this->messageParameters['error_description'] ?? '',
            'request' => $request,
        ]);
    }

    /**
     * A Deep Linking response is only taken from the tool of this connection, for ILIAS and its deployment. The
     * library has checked the signature with the key of the tool by then.
     */
    protected function onContentItem(): void
    {
        $audience = $this->jwt?->getClaim('aud');
        if (
            ($this->messageParameters['lti_message_type'] ?? '') !== self::MESSAGE_DEEP_LINKING_RESPONSE
            || $this->jwt?->getClaim('iss') !== $this->clientId
            || !in_array($this->platformId, is_array($audience) ? $audience : [$audience], true)
            || (string) $this->jwt->getClaim(Util::JWT_CLAIM_PREFIX . '/claim/deployment_id') !== $this->deploymentId
        ) {
            $this->setReason('The message is not a Deep Linking response of the tool for this platform.');
        }
    }
}
