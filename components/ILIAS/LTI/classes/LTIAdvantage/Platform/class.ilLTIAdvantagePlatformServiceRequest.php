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

/**
 * What the services of ILIAS as LTI Advantage platform (ltiservices.php) share: their URLs, the tool an
 * access token of ltitoken.php was given to and the object of that tool a request is about. A refused
 * request throws a DomainException whose code is the HTTP status.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformServiceRequest
{
    private const string CONTENT_TYPE_ERROR = 'application/json; charset=utf-8';

    /**
     * Answers one request to the services, which the first segment of the path names.
     *
     * @param string $path the path after ltiservices.php
     * @param array $query the query parameters
     * @return array the HTTP status, the headers and the body of the response
     */
    public static function handle(
        string $method,
        string $path,
        string $authorization,
        string $content_type,
        array $query,
        string $body
    ): array {
        return match (explode('/', ltrim($path, '/'))[0]) {
            'membership' => new ilLTIAdvantagePlatformMembershipService()->handle($method, $path, $authorization, $query),
            default => new ilLTIAdvantagePlatformGradeService()->handle($method, $path, $authorization, $content_type, $query, $body),
        };
    }

    /**
     * The URL of a service path. It is built from the configured HTTP path: under ltiservices.php,
     * ILIAS_HTTP_PATH ends in the script, and a tool reads back the URLs of its launch.
     */
    public static function getUrl(string $path): string
    {
        global $DIC;

        $http_path = ilContext::modifyHttpPath((string) $DIC->iliasIni()->readVariable('server', 'http_path'));

        return rtrim($http_path, '/') . '/ltiservices.php' . $path;
    }

    /**
     * The tool an access token of ILIAS was given to, when the token grants one of the scopes and the tool is
     * still available. Of the tokens ILIAS signs, only access tokens carry a scope.
     *
     * @param array $scopes
     * @throws ilException when ILIAS has no key
     * @throws \Random\RandomException
     */
    public static function authorize(string $authorization, array $scopes): ilLTITool
    {
        $token = ilLTIAdvantageKeyPair::bearerToken($authorization);
        if ($token === '') {
            throw new DomainException('No access token', 401);
        }
        $payload = ilLTIAdvantageKeyPair::verify($token);
        $granted = $payload['imsglobal.org.security.scope'] ?? null;
        $client_id = $payload['sub'] ?? null;
        if (!is_string($granted) || !is_string($client_id)) {
            throw new DomainException('Invalid access token', 401);
        }

        $tool_id = ilLTITool::lookupIdByClientId($client_id);
        if ($tool_id === 0) {
            throw new DomainException('Unknown client ' . $client_id, 401);
        }
        if (array_intersect($scopes, explode(' ', $granted)) === []) {
            throw new DomainException('The access token does not grant ' . implode(' or ', $scopes), 403);
        }

        $tool = new ilLTITool($tool_id);
        if ($tool->getAvailability() === ilLTITool::AVAILABILITY_NONE) {
            throw new DomainException('The tool ' . $tool_id . ' is not available', 403);
        }

        return $tool;
    }

    /**
     * The object of the tool in the context, at a reference that is in the repository and inside the context.
     */
    public static function getObject(int $context_ref_id, int $obj_id, ilLTITool $tool): ilObjLTITool
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        if (ilObject::_lookupType($obj_id) === 'lti') {
            foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                if ($tree->isInTree($ref_id) && in_array($context_ref_id, $tree->getPathId($ref_id), true)) {
                    $object = new ilObjLTITool($ref_id);
                    if ($object->getToolId() === $tool->getId()) {
                        return $object;
                    }
                }
            }
        }

        throw new DomainException('No object ' . $obj_id . ' of the tool ' . $tool->getId() . ' in ' . $context_ref_id, 404);
    }

    /**
     * @param array $data
     * @return array the HTTP status, the headers and the body of the response
     */
    public static function respond(string $media_type, array $data): array
    {
        return [200, ['Content-Type' => $media_type], json_encode($data, JSON_UNESCAPED_SLASHES)];
    }

    /**
     * @return array the HTTP status, the headers and the body of the response
     */
    public static function refuseMethod(string $allowed): array
    {
        return [405, ['Allow' => $allowed, 'Content-Type' => self::CONTENT_TYPE_ERROR], json_encode([
            'error' => 'The resource only accepts ' . $allowed,
        ])];
    }

    /**
     * Logs why a request was refused and answers it with the status of the exception.
     *
     * @return array the HTTP status, the headers and the body of the response
     */
    public static function refuse(string $service, DomainException $e): array
    {
        global $DIC;

        $DIC->logger()->forComponent('lti')->warning('LTI Advantage ' . $service . ' request refused: ' . $e->getMessage());
        $headers = $e->getCode() === 401 ? ['WWW-Authenticate' => 'Bearer error="invalid_token"'] : [];

        return [$e->getCode(), $headers + ['Content-Type' => self::CONTENT_TYPE_ERROR], json_encode([
            'error' => $e->getMessage(),
        ], JSON_UNESCAPED_SLASHES)];
    }
}
