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

namespace ILIAS\HTTP\Message;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * The currently used psr-7 implementation is created and published by guzzle under the MIT license.
 * source: https://github.com/guzzle/psr7
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class MessageFactoryImpl implements MessageFactory
{
    public function request(
        string $method,
        UriInterface|string $uri,
        array $headers = [],
        StreamInterface|string|null $body = null
    ): RequestInterface {
        return new Request($method, $uri, $headers, $body);
    }

    public function response(
        int $status = 200,
        array $headers = [],
        StreamInterface|string|null $body = null
    ): ResponseInterface {
        return new Response($status, $headers, $body);
    }

    public function uri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
