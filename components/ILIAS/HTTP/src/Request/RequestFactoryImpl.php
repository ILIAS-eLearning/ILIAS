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
    /**
     * @var string
     */
    private const DEFAULT_FORWARDED_PROTO = 'https';

    public function __construct(
        private HeaderSettings $header_settings
    ) {
    }

    public function create(): ServerRequestInterface
    {
        $server_request = ServerRequest::fromGlobals();

        $is_enabled = $this->header_settings->isHTTPSDetectionEnabled();
        if (!$is_enabled) {
            return $server_request;
        }
        $header_name = $this->header_settings->getHTTPDetectionHeaderName();
        $header_value = $this->header_settings->getHTTPDetectionHeaderValue();
        if ($header_name !== null && $header_value !== null) {
            if (in_array(
                $header_value,
                $server_request->getHeader($header_name),
                true
            )) {
                return $server_request->withUri(
                    $server_request->getUri()->withScheme(self::DEFAULT_FORWARDED_PROTO)
                );
            }

            // alternative if ini settings are used which look like X_FORWARDED_PROTO
            $request_header_names = array_keys($server_request->getHeaders());
            foreach ($request_header_names as $request_header_name) {
                if (str_replace("-", "_", strtoupper((string) $request_header_name)) !== $header_name) {
                    continue;
                }
                if (!in_array($header_value, $server_request->getHeader($request_header_name), true)) {
                    continue;
                }
                return $server_request->withUri(
                    $server_request->getUri()->withScheme(self::DEFAULT_FORWARDED_PROTO)
                );
            }
        }

        return $server_request;
    }
}
