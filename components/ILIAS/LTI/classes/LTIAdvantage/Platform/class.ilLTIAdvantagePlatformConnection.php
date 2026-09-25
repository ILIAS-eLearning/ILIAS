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
use ceLTIc\LTI\Platform;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\Util;

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
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIAdvantagePlatformConnection extends Platform
{
    /**
     * What an access token of ILIAS may grant: the Assignment and Grade Services.
     */
    public const array ACCESS_TOKEN_SCOPES = [
        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly',
        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
        'https://purl.imsglobal.org/spec/lti-ags/scope/score',
    ];

    private const string MESSAGE_LAUNCH = 'basic-lti-launch-request';
    private const string STORAGE_FRAME_PARENT = '_parent';
    private const string SESSION_KEY = 'lti_advantage_logins';
    private const int LOGIN_LIFETIME = 600;

    /**
     * @throws ilException when the tool is not an LTI Advantage tool or ILIAS has no key
     */
    public function __construct(ilLTITool $tool)
    {
        parent::__construct(new ilLTIDataConnector());

        if ($tool->getLtiVersion() !== ilLTITool::VERSION_ADVANTAGE || $tool->getClientId() === '') {
            throw new ilException('The LTI tool ' . $tool->getId() . ' is not an LTI Advantage tool.');
        }

        $this->platformId = ilObjLTITool::getIliasHttpPath();
        $this->clientId = $tool->getClientId();
        // earlier releases gave every tool its own deployment, named by the id of the tool
        $this->deploymentId = (string) $tool->getId();
        $this->ltiVersion = LtiVersion::V1P3;
        $this->authenticationUrl = ilObjLTITool::getIliasHttpPath() . '/ltiauth.php';
        $this->accessTokenUrl = ilObjLTITool::getIliasHttpPath() . '/ltitoken.php';
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
     * The page that starts the launch of an object: it sends the browser to the login initiation URL of
     * the tool. The launch itself waits in the session for the authentication request of the tool.
     *
     * @param array $parameters see ilLTIAdvantagePlatformLaunchParameterBuilder
     * @param bool $embedded true when the tool opens in an iframe of the page that handles the platform storage
     */
    public function getLaunchPage(int $ref_id, array $parameters, bool $embedded): string
    {
        self::$browserStorageFrame = $embedded ? self::STORAGE_FRAME_PARENT : null;

        return $this->sendMessage(
            Tool::$defaultTool->messageUrl,
            self::MESSAGE_LAUNCH,
            $parameters,
            '',
            (string) $parameters['user_id'],
            (string) $ref_id
        );
    }

    /**
     * @param array $params
     */
    protected function onInitiateLogin(string &$url, string &$loginHint, ?string &$ltiMessageHint, array $params): void
    {
        $logins = ilSession::get(self::SESSION_KEY);
        $logins = is_array($logins) ? $logins : [];
        $logins[(string) $ltiMessageHint] = [
            'client_id' => $this->clientId,
            'message_url' => $url,
            'login_hint' => $loginHint,
            'params' => $params,
            'storage_frame' => self::$browserStorageFrame,
            'created' => time(),
        ];
        ilSession::set(self::SESSION_KEY, $logins);
    }

    /**
     * Accepts the authentication request only for a login this session started for the same tool, user
     * and object in the last minutes. Each login is used once.
     */
    protected function onAuthenticate(): void
    {
        $parameters = Util::getRequestParameters();
        $hint = (string) ($parameters['lti_message_hint'] ?? '');
        $logins = ilSession::get(self::SESSION_KEY);
        $login = is_array($logins) ? ($logins[$hint] ?? null) : null;

        if (
            $login === null
            || $login['client_id'] !== $this->clientId
            || $login['login_hint'] !== ($parameters['login_hint'] ?? null)
            || $login['created'] < time() - self::LOGIN_LIFETIME
        ) {
            $this->ok = false;
            $this->messageParameters['error'] = 'access_denied';
            return;
        }

        unset($logins[$hint]);
        ilSession::set(self::SESSION_KEY, $logins);
        Tool::$defaultTool->messageUrl = $login['message_url'];
        $this->messageParameters = $login['params'];
        // the library tells the tool again where the platform storage is, with the id_token
        self::$browserStorageFrame = $login['storage_frame'] ?? null;
    }
}
