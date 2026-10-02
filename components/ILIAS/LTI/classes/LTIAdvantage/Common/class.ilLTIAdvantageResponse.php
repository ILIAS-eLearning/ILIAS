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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;

/**
 * The answer to a request of LTI Advantage that ends the request: the endpoints of ILIAS and the pages that
 * post a message to the other side.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantageResponse
{
    public const string CONTENT_TYPE_JSON = 'application/json; charset=utf-8';
    public const string CONTENT_TYPE_HTML = 'text/html; charset=utf-8';

    /**
     * @param string $content_type none when empty
     * @param array $headers further headers by name
     */
    public static function send(int $status, string $content_type, string $body, array $headers = []): never
    {
        global $DIC;

        $http = $DIC->http();
        $response = $http->response()->withStatus($status);
        if ($content_type !== '') {
            $response = $response->withHeader(ResponseHeader::CONTENT_TYPE, $content_type);
        }
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $http->saveResponse($response->withBody(Streams::ofString($body)));
        $http->sendResponse();
        $http->close();
    }

    /**
     * @param array $data
     * @param array $headers further headers by name
     */
    public static function json(int $status, array $data, array $headers = []): never
    {
        self::send($status, self::CONTENT_TYPE_JSON, (string) json_encode($data, JSON_UNESCAPED_SLASHES), $headers);
    }

    public static function html(string $page, int $status = 200): never
    {
        self::send($status, self::CONTENT_TYPE_HTML, $page);
    }
}
