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

namespace ILIAS\HTTP\Request;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestFactoryImpl
 *
 * This class creates new psr-7 compliant ServerRequests
 * and decouples the used library from ILIAS components.
 *
 * The currently used psr-7 implementation is created and published by guzzle under the MIT license.
 * source: https://github.com/guzzle/psr7
 *
 * @package ILIAS\HTTP\Request
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @author  Fabian Schmid <fabian@sr.solutions>
 */
class RequestFactoryImpl implements RequestFactory
{
    private const DEFAULT_FORWARDED_HEADER = 'X-Forwarded-Proto';
    private const DEFAULT_FORWARDED_PROTO = 'https';

    public function __construct(
        private ?string $forwarded_header = null,
        private ?string $forwarded_proto = null
    ) {
    }

    public function create(): ServerRequestInterface
    {
        $server_request = ServerRequest::fromGlobals();

        if ($this->forwarded_header !== null && $this->forwarded_proto !== null) {
            if (in_array(
                $this->forwarded_proto,
                $server_request->getHeader($this->forwarded_header),
                true
            )) {
                return $server_request->withUri($server_request->getUri()->withScheme($this->forwarded_proto));
            }

            // alternative if ini settings are used which look like X_FORWARDED_PROTO
            $header_names = array_keys($server_request->getHeaders());
            foreach ($header_names as $header_name) {
                if (str_replace("-", "_", strtoupper($header_name)) !== $this->forwarded_header) {
                    continue;
                }
                if (!in_array($this->forwarded_proto, $server_request->getHeader($header_name), true)) {
                    continue;
                }
                return $server_request->withUri($server_request->getUri()->withScheme($this->forwarded_proto));
            }
        }

        return $server_request;
    }
}
