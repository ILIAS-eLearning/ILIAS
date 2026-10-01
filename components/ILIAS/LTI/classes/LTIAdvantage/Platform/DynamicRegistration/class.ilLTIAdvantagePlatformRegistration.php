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

use ceLTIc\LTI\Service\Score;
use ceLTIc\LTI\Util;
use Random\RandomException;

/**
 * LTI Advantage Dynamic Registration of a tool with ILIAS as platform. celtic/lti has nothing for this role.
 *
 * The user who registers the tool opens its registration URL with the OpenID configuration of ILIAS
 * (lticonfig.php) and a registration token. The tool reads the configuration and posts its own to
 * ltiregistration.php, with the token, from its server: that request has no session of ILIAS, so the token
 * carries who registers and the client id the tool gets. The tool is stored as an own tool of that user, and
 * the token is used up with it. When the tool says it is done, the page of the user takes the tool the
 * registration left in the session and creates the object for it.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformRegistration
{
    private const string PURPOSE = 'lti_tool_registration';
    private const int TOKEN_LIFETIME = 3600;
    private const string SESSION_KEY = 'lti_advantage_tool_registrations';
    private const string TOOL_CONFIGURATION = 'https://purl.imsglobal.org/spec/lti-tool-configuration';
    private const string PLATFORM_CONFIGURATION = 'https://purl.imsglobal.org/spec/lti-platform-configuration';
    private const string MESSAGE_LAUNCH = 'LtiResourceLinkRequest';
    private const string MESSAGE_DEEP_LINKING = 'LtiDeepLinkingRequest';
    private const string ERROR_METADATA = 'invalid_client_metadata';
    private const string ERROR_TOKEN = 'invalid_token';
    private const int URL_LENGTH = 255;

    public static function getOpenidConfigurationUrl(): string
    {
        return ilObjLTITool::getIliasHttpPath() . '/lticonfig.php';
    }

    /**
     * What a tool learns about ILIAS before it registers: the endpoints and what ILIAS supports.
     */
    public static function getOpenidConfiguration(): array
    {
        $path = ilObjLTITool::getIliasHttpPath();

        return [
            'issuer' => $path,
            'authorization_endpoint' => $path . '/ltiauth.php',
            'token_endpoint' => $path . '/ltitoken.php',
            'token_endpoint_auth_methods_supported' => ['private_key_jwt'],
            'token_endpoint_auth_signing_alg_values_supported' => ['RS256'],
            'jwks_uri' => ilLTIAdvantageKeyPair::getJwksUrl(),
            'registration_endpoint' => $path . '/ltiregistration.php',
            'scopes_supported' => array_merge(['openid'], ilLTIAdvantagePlatformConnection::getAccessTokenScopes()),
            'response_types_supported' => ['id_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'claims_supported' => ['iss', 'aud', 'sub', 'name', 'given_name', 'family_name', 'email', 'picture', 'locale'],
            self::PLATFORM_CONFIGURATION => [
                'product_family_code' => 'ilias',
                'version' => ILIAS_VERSION,
                'messages_supported' => [['type' => self::MESSAGE_LAUNCH]],
            ],
        ];
    }

    /**
     * Starts a registration of the user.
     *
     * @param string $custom_params custom parameters the object created for the tool gets, in the format of
     *                              its settings
     * @return array{0: string, 1: string} the client id the tool gets, which names the registration, and the
     *                                     URL the registration page of the tool opens with
     * @throws ilException when ILIAS has no key
     * @throws RandomException
     */
    public static function start(string $tool_url, string $custom_params, int $user_id): array
    {
        $client_id = Util::getRandomString(15);
        $now = time();
        $token = ilLTIAdvantageKeyPair::sign([
            'purpose' => self::PURPOSE,
            'sub' => $user_id,
            'aud' => $client_id,
            'iat' => $now,
            'exp' => $now + self::TOKEN_LIFETIME,
        ]);

        $registrations = self::getRegistrations();
        $registrations[$client_id] = ['custom_params' => $custom_params, 'created' => $now];
        ilSession::set(self::SESSION_KEY, $registrations);

        return [$client_id, $tool_url . (str_contains($tool_url, '?') ? '&' : '?') . http_build_query([
            'openid_configuration' => self::getOpenidConfigurationUrl(),
            'registration_token' => $token,
        ])];
    }

    /**
     * The registrations the session started that may still be running, by client id.
     */
    private static function getRegistrations(): array
    {
        $registrations = ilSession::get(self::SESSION_KEY);
        if (!is_array($registrations)) {
            return [];
        }

        return array_filter(
            $registrations,
            static fn(array $registration): bool => $registration['created'] >= time() - self::TOKEN_LIFETIME
        );
    }

    /**
     * Ends a registration the session started. Returns the tool and the custom parameters of the object to
     * create for it, or null when the tool has not registered. Either way the registration is over.
     *
     * @return array{0: ilLTITool, 1: string}|null
     */
    public static function finish(string $client_id, int $user_id): ?array
    {
        $registrations = self::getRegistrations();
        $registration = $registrations[$client_id] ?? null;
        unset($registrations[$client_id]);
        ilSession::set(self::SESSION_KEY, $registrations);

        $tool_id = $registration !== null ? ilLTITool::lookupIdByClientId($client_id) : 0;
        if ($tool_id === 0) {
            return null;
        }
        $tool = new ilLTITool($tool_id);

        return $tool->getCreator() === $user_id ? [$tool, (string) $registration['custom_params']] : null;
    }

    /**
     * Handles the registration request of a tool.
     *
     * @param string $authorization the Authorization header of the request
     * @return array{0: int, 1: array} the HTTP status and the JSON answer: the configuration of the tool as
     *                                 registered, or an error of RFC 7591
     * @throws ilException when ILIAS has no key
     * @throws RandomException
     */
    public static function register(string $authorization, string $body): array
    {
        $token = preg_match('/^Bearer\s+(\S+)$/i', trim($authorization), $matches) === 1
            ? ilLTIAdvantageKeyPair::verify($matches[1])
            : null;
        if (
            $token === null
            || ($token['purpose'] ?? null) !== self::PURPOSE
            || !is_string($token['aud'] ?? null)
            || !is_int($token['sub'] ?? null)
        ) {
            return self::error(401, self::ERROR_TOKEN, 'The registration token is missing, invalid or expired.');
        }
        if (ilLTITool::lookupIdByClientId($token['aud']) > 0) {
            return self::error(401, self::ERROR_TOKEN, 'The registration token has already been used.');
        }

        $configuration = json_decode($body, true);
        $problem = is_array($configuration)
            ? self::findProblem($configuration)
            : 'The request is not a JSON object.';
        if ($problem !== null) {
            return self::error(400, self::ERROR_METADATA, $problem);
        }

        $tool_id = self::createTool($configuration, $token['aud'], $token['sub']);

        $configuration['client_id'] = $token['aud'];
        $configuration['scope'] = implode(' ', self::getGrantedScopes($configuration));
        // earlier releases gave every tool its own deployment, named by the id of the tool
        $configuration[self::TOOL_CONFIGURATION]['deployment_id'] = (string) $tool_id;

        return [200, $configuration];
    }

    /**
     * What a registration request lacks of what ILIAS needs to launch the tool, null when nothing.
     */
    private static function findProblem(array $configuration): ?string
    {
        $tool_configuration = $configuration[self::TOOL_CONFIGURATION] ?? null;
        $redirect_uris = $configuration['redirect_uris'] ?? null;

        return match (true) {
            ($configuration['application_type'] ?? 'web') !== 'web' => 'application_type must be web.',
            !self::isFilled($configuration['client_name'] ?? null, 255) => 'client_name is missing or too long.',
            !in_array('id_token', (array) ($configuration['response_types'] ?? []), true) => 'response_types must include id_token.',
            array_diff(['implicit', 'client_credentials'], (array) ($configuration['grant_types'] ?? [])) !== [] =>
                'grant_types must include implicit and client_credentials.',
            ($configuration['token_endpoint_auth_method'] ?? 'private_key_jwt') !== 'private_key_jwt' =>
                'token_endpoint_auth_method must be private_key_jwt.',
            !self::isUrl($configuration['initiate_login_uri'] ?? null) => 'initiate_login_uri is missing, invalid or too long.',
            !self::isUrl($configuration['jwks_uri'] ?? null) => 'jwks_uri is missing, invalid or too long.',
            !is_array($redirect_uris) || $redirect_uris === [] || array_filter($redirect_uris, self::isUrl(...)) !== $redirect_uris
                => 'redirect_uris is missing or holds an invalid URL.',
            strlen(implode(',', $redirect_uris)) > 510 => 'redirect_uris is too long.',
            !is_array($tool_configuration) => self::TOOL_CONFIGURATION . ' is missing.',
            !self::isFilled($tool_configuration['domain'] ?? null, self::URL_LENGTH) => 'domain is missing.',
            !self::isUrl($tool_configuration['target_link_uri'] ?? null) => 'target_link_uri is missing, invalid or too long.',
            isset($tool_configuration['custom_parameters']) && !is_array($tool_configuration['custom_parameters']) =>
                'custom_parameters must be an object.',
            strlen(self::getCustomParams($tool_configuration)) > 1020 => 'custom_parameters are too long.',
            isset($tool_configuration['messages']) && !is_array($tool_configuration['messages']) => 'messages must be a list.',
            default => null,
        };
    }

    /**
     * @return int the id of the new tool
     */
    private static function createTool(array $configuration, string $client_id, int $user_id): int
    {
        $tool_configuration = $configuration[self::TOOL_CONFIGURATION];
        $deep_linking_url = self::getDeepLinkingUrl($tool_configuration);
        $scores = in_array(Score::$SCOPE, self::getGrantedScopes($configuration), true);

        return ilLTITool::create([
            'title' => ['text', $configuration['client_name']],
            'description' => ['text', ilStr::subStr(is_string($tool_configuration['description'] ?? null) ? $tool_configuration['description'] : '', 0, 4000)],
            'lti_version' => ['text', ilLTITool::VERSION_ADVANTAGE],
            'availability' => ['integer', ilLTITool::AVAILABILITY_CREATE],
            'provider_url' => ['text', $tool_configuration['target_link_uri']],
            'initiate_login' => ['text', $configuration['initiate_login_uri']],
            'redirection_uris' => ['text', implode(',', $configuration['redirect_uris'])],
            'key_type' => ['text', ilLTITool::KEY_TYPE_JWK],
            'public_key' => ['text', ''],
            'public_keyset' => ['text', $configuration['jwks_uri']],
            'client_id' => ['text', $client_id],
            'custom_params' => ['text', self::getCustomParams($tool_configuration)],
            'content_item' => ['integer', (int) ($deep_linking_url !== null)],
            'content_item_url' => ['text', (string) $deep_linking_url],
            // a tool that asks to send scores sends the result of its users: ILIAS takes it for the learning progress
            'has_outcome' => ['integer', (int) $scores],
            'grade_synchronization' => ['integer', (int) $scores],
            // an LTI Advantage tool keeps the LTI 1.1 key empty and customizable
            'provider_key_customizable' => ['integer', 1],
            'provider_key' => ['text', ''],
            'provider_secret' => ['text', ''],
        ], $user_id, false);
    }

    /**
     * @return string[] the scopes the tool asked for that ILIAS grants
     */
    private static function getGrantedScopes(array $configuration): array
    {
        $requested = preg_split('/\s+/', trim((string) ($configuration['scope'] ?? '')), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_intersect(ilLTIAdvantagePlatformConnection::getAccessTokenScopes(), $requested));
    }

    /**
     * The custom parameters of the tool in the format of its settings.
     */
    private static function getCustomParams(array $tool_configuration): string
    {
        $params = [];
        foreach ((array) ($tool_configuration['custom_parameters'] ?? []) as $name => $value) {
            if (is_scalar($value)) {
                $params[] = $name . '=' . $value;
            }
        }

        return implode(';', $params);
    }

    /**
     * Where the tool takes a Deep Linking request, null when it takes none.
     */
    private static function getDeepLinkingUrl(array $tool_configuration): ?string
    {
        foreach ((array) ($tool_configuration['messages'] ?? []) as $message) {
            if (is_array($message) && ($message['type'] ?? null) === self::MESSAGE_DEEP_LINKING) {
                $url = $message['target_link_uri'] ?? $tool_configuration['target_link_uri'];

                return self::isUrl($url) ? $url : $tool_configuration['target_link_uri'];
            }
        }

        return null;
    }

    private static function isFilled(mixed $value, int $length): bool
    {
        return is_string($value) && trim($value) !== '' && ilStr::strLen($value) <= $length;
    }

    private static function isUrl(mixed $value): bool
    {
        return is_string($value)
            && strlen($value) <= self::URL_LENGTH
            && filter_var($value, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    /**
     * @return array{0: int, 1: array}
     */
    private static function error(int $status, string $error, string $description): array
    {
        return [$status, ['error' => $error, 'error_description' => $description]];
    }
}
