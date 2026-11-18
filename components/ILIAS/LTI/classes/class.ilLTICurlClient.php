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

use ceLTIc\LTI\Http\ClientInterface;
use ceLTIc\LTI\Http\HttpMessage;

/**
 * Sends the calls of celtic/lti with the cURL connection of ILIAS, so that they go through the proxy
 * configured in Administration > Server > Proxy: the tokens, the key sets, the members and the scores.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final readonly class ilLTICurlClient implements ClientInterface
{
    private const int CONNECT_TIMEOUT = 30;

    public function __construct(private ?ilProxySettings $proxy_settings = null)
    {
    }

    public function send(HttpMessage $message): bool
    {
        $message->ok = false;
        try {
            $curl = new ilCurlConnection($message->getUrl(), $this->proxy_settings);
            $curl->init();
            // ILIAS tunnels every call through the proxy, but many proxies only open tunnels to port 443:
            // a call over plain HTTP is handed to the proxy as it is
            if (parse_url($message->getUrl(), PHP_URL_SCHEME) === 'http') {
                $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, false);
            }
            $curl->setOpt(CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
            $curl->setOpt(CURLOPT_HTTPHEADER, $message->requestHeaders);
            if ($message->getMethod() === 'POST') {
                $curl->setOpt(CURLOPT_POST, true);
                $curl->setOpt(CURLOPT_POSTFIELDS, $message->request);
            } elseif ($message->getMethod() !== 'GET') {
                $curl->setOpt(CURLOPT_CUSTOMREQUEST, $message->getMethod());
                if ($message->request !== null) {
                    $curl->setOpt(CURLOPT_POSTFIELDS, $message->request);
                }
            }
            $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLINFO_HEADER_OUT, true);
            $curl->setOpt(CURLOPT_HEADER, true);
            $curl->parseResponse((string) $curl->exec());
        } catch (ilCurlConnectionException $e) {
            $message->error = $e->getMessage();
            return false;
        }

        $message->requestHeaders = $this->splitHeaders((string) $curl->getInfo(CURLINFO_HEADER_OUT));
        $message->responseHeaders = $this->splitHeaders($this->getLastHeaderBlock($curl->getResponseHeader()));
        $message->response = $curl->getResponseBody();
        $message->status = (int) $curl->getInfo(CURLINFO_HTTP_CODE);
        $message->ok = $message->status >= 100 && $message->status < 400;

        return $message->ok;
    }

    /**
     * The headers cURL received hold one block per response: the answer of the proxy to CONNECT and any interim
     * response, such as "100 Continue", come before the headers of the platform or tool, which are the last ones.
     */
    private function getLastHeaderBlock(string $headers): string
    {
        $blocks = array_filter(
            explode("\n\n", str_replace("\r\n", "\n", $headers)),
            static fn(string $block): bool => trim($block) !== ''
        );

        return (string) end($blocks);
    }

    /**
     * @return string[]
     */
    private function splitHeaders(string $headers): array
    {
        return explode("\n", trim(str_replace("\r\n", "\n", $headers)));
    }
}
