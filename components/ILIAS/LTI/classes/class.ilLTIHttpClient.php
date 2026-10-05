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
 * The HTTP client of celtic/lti, which logs how each call of ILIAS to a platform or a tool ended and how long
 * it took: the tokens, the key sets, the members and the scores ILIAS asks for or sends.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIHttpClient implements ClientInterface
{
    public function __construct(private readonly ?ClientInterface $client, private readonly ilLogger $logger)
    {
    }

    public function send(HttpMessage $message): bool
    {
        if ($this->client === null) {
            $this->logger->error('LTI call {method} {url} not sent: PHP has neither cURL nor allow_url_fopen', [
                'method' => $message->getMethod(),
                'url' => ilLTILibraryLogger::hideSecrets($message->getUrl()),
            ]);
            return false;
        }

        $start = hrtime(true);
        $ok = $this->client->send($message);
        $this->logger->debug('LTI call {method} {url}: {status} in {ms} ms{error}', [
            'method' => $message->getMethod(),
            'url' => ilLTILibraryLogger::hideSecrets($message->getUrl()),
            'status' => $message->status,
            'ms' => intdiv(hrtime(true) - $start, 1_000_000),
            'error' => $message->error !== '' ? ', ' . $message->error : '',
        ]);

        return $ok;
    }
}
