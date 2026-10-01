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

use ceLTIc\LTI\Util;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;
use Random\RandomException;

/**
 * LTI Advantage Deep Linking with ILIAS as platform: a user who creates LTI objects in a container picks the
 * content in the tool, and each resource link the tool sends back becomes an object.
 *
 * A request waits in the session of the user, for that user and container, and is used once: its state goes
 * to the tool as the data of the request and has to come back with the response. The session cookie is sent
 * as SameSite=None when the request starts, because the tool posts the response from its own site.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantagePlatformDeepLinking
{
    private const string SESSION_KEY = 'lti_advantage_deep_linking';
    private const int LIFETIME = 1800;
    private const string TYPE_RESOURCE_LINK = 'ltiResourceLink';
    private const string MEDIA_TYPE_RESOURCE_LINK = 'application/vnd.ims.lti.v1.ltilink';
    private const int MAX_TITLE_LENGTH = 128;

    /**
     * Keeps a new request for the user and the container the objects are created in.
     *
     * @param string $custom_params custom parameters every object gets, in the format of the object settings
     * @return string the state of the request
     * @throws RandomException
     */
    public static function start(ilLTITool $tool, int $ref_id, int $user_id, string $custom_params = ''): string
    {
        $state = bin2hex(random_bytes(16));
        $requests = self::getRequests();
        $requests[$state] = [
            'tool_id' => $tool->getId(),
            'ref_id' => $ref_id,
            'user_id' => $user_id,
            'custom_params' => $custom_params,
            'created' => time(),
        ];
        ilSession::set(self::SESSION_KEY, $requests);

        return $state;
    }

    /**
     * Sends the page that starts the request at the tool and ends the request of ILIAS.
     *
     * @param string $return_url where the tool posts its response
     * @throws RandomException
     * @throws ResponseSendingException
     */
    public static function sendRequestPage(string $state, int $user_id, int $ref_id, string $return_url, Container $dic): never
    {
        $request = self::getRequests()[$state] ?? null;
        $page = $dic->language()->txt('error');
        $status = 400;

        if (self::isValid($request, $user_id, $ref_id)) {
            $tool = new ilLTITool($request['tool_id']);
            try {
                $parameters = ilLTIAdvantagePlatformLaunchParameterBuilder::buildForUser(
                    $tool,
                    ilCmiXapiUser::getIdentAsId($tool->getPrivacyIdent(), $dic->user()),
                    ilCmiXapiUser::getIdent($tool->getPrivacyIdent(), $dic->user()),
                    ilLTIAdvantagePlatformLaunchParameterBuilder::ROLE_INSTRUCTOR,
                    $ref_id
                ) + [
                    'launch_presentation_document_target' => 'iframe',
                    'content_item_return_url' => $return_url,
                    'accept_types' => self::TYPE_RESOURCE_LINK,
                    'accept_media_types' => self::MEDIA_TYPE_RESOURCE_LINK,
                    'accept_presentation_document_targets' => 'iframe,window',
                    'accept_multiple' => 'true',
                    'auto_create' => 'true',
                    'data' => $state,
                ];
                $page = new ilLTIAdvantagePlatformConnection($tool)->getDeepLinkingPage(
                    $tool->getContentItemUrl(),
                    $parameters,
                    $state
                );
                $status = 200;
            } catch (ilException $e) {
                $dic->logger()->forComponent('lti')->error($e->getMessage());
                $status = 500;
            }
        }

        ilLTIAdvantagePlatformLaunchRenderer::sendPage($page, $status, $dic);
    }

    /**
     * Checks the response of the tool to the request, which is used up with it.
     *
     * @return array|null the tool, the custom parameters of the request, the objects to create (title,
     *                    description and custom parameters of each) and the message and error the tool gave
     *                    for the user; null when the response is not valid
     * @throws RandomException
     */
    public static function receive(string $state, int $user_id, int $ref_id, Container $dic): ?array
    {
        $log = $dic->logger()->forComponent('lti');
        $requests = self::getRequests();
        $request = $requests[$state] ?? null;
        unset($requests[$state]);
        ilSession::set(self::SESSION_KEY, $requests);

        if (!self::isValid($request, $user_id, $ref_id)) {
            $log->warning('LTI Deep Linking response refused: no such request of the user in this session.');
            return null;
        }

        $tool = new ilLTITool($request['tool_id']);
        $body = $dic->http()->request()->getParsedBody();
        Util::$requestParameters = is_array($body) ? $body : [];
        try {
            $connection = new ilLTIAdvantagePlatformConnection($tool);
        } catch (ilException $e) {
            $log->error($e->getMessage());
            return null;
        }
        $connection->handleRequest();
        $parameters = $connection->getMessageParameters() ?? [];

        // the library keeps the data of a response apart from the one of a request
        if (!$connection->ok || ($parameters['data.LtiDeepLinkingResponse'] ?? null) !== $state) {
            $log->warning('LTI Deep Linking response refused: ' . ($connection->reason ?? 'the data does not match the request.'));
            return null;
        }

        // what the tool gives for the log is for the administrators, not for the user
        foreach (['lti_log', 'lti_errorlog'] as $name) {
            if (trim((string) ($parameters[$name] ?? '')) !== '') {
                $log->info('LTI Deep Linking response of tool ' . $tool->getId() . ', ' . $name . ': ' . $parameters[$name]);
            }
        }

        return [
            'tool' => $tool,
            'custom_params' => (string) $request['custom_params'],
            'items' => self::getItems((string) ($parameters['content_items'] ?? '')),
            'message' => trim((string) ($parameters['lti_msg'] ?? '')),
            'error' => trim((string) ($parameters['lti_errormsg'] ?? '')),
        ];
    }

    /**
     * Ends the request with a page that opens the given URL in the whole window, as the response of the tool
     * arrives in its iframe.
     *
     * @throws ResponseSendingException
     */
    public static function sendTopRedirect(string $url, Container $dic): never
    {
        $escaped = htmlspecialchars($url, ENT_QUOTES);
        $page = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>ILIAS</title></head><body>'
            . '<script>window.top.location.replace(' . json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . ');</script>'
            . '<a href="' . $escaped . '" target="_top">' . $escaped . '</a></body></html>';

        $dic->http()->saveResponse($dic->http()->response()->withBody(Streams::ofString($page)));
        $dic->http()->sendResponse();
        $dic->http()->close();
    }

    /**
     * The resource links of the response. Other types of content cannot become an LTI object and are left out.
     * The target link of a resource link is kept as the custom parameter target_link_uri, as earlier releases did.
     *
     * @param string $content_items
     * @return array
     */
    private static function getItems(string $content_items): array
    {
        $decoded = json_decode($content_items, true);
        $items = [];
        foreach (is_array($decoded) ? $decoded : [] as $item) {
            if (!is_array($item) || ($item['type'] ?? '') !== self::TYPE_RESOURCE_LINK) {
                continue;
            }

            $custom_params = [];
            $url = (string) ($item['url'] ?? '');
            if (filter_var($url, FILTER_VALIDATE_URL) !== false && preg_match('/^https?:\/\//i', $url)) {
                $custom_params[] = 'target_link_uri=' . $url;
            }
            foreach (is_array($item['custom'] ?? null) ? $item['custom'] : [] as $name => $value) {
                if (is_scalar($value) && $name !== '') {
                    $custom_params[] = $name . '=' . $value;
                }
            }

            $items[] = [
                'title' => ilStr::subStr(trim((string) ($item['title'] ?? '')), 0, self::MAX_TITLE_LENGTH),
                'description' => trim((string) ($item['text'] ?? '')),
                'custom_params' => implode(';', $custom_params),
            ];
        }

        return $items;
    }

    /**
     * @param array|null $request
     */
    private static function isValid(?array $request, int $user_id, int $ref_id): bool
    {
        return $request !== null
            && $request['user_id'] === $user_id
            && $request['ref_id'] === $ref_id
            && $request['created'] >= time() - self::LIFETIME
            && new ilLTITool($request['tool_id'])->offersDeepLinking();
    }

    /**
     * @return array
     */
    private static function getRequests(): array
    {
        $requests = ilSession::get(self::SESSION_KEY);

        return is_array($requests) ? $requests : [];
    }
}
