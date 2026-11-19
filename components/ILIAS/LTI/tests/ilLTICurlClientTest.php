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

use ceLTIc\LTI\Http\HttpMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTICurlClientTest extends TestCase
{
    public function testCallFailsThroughAnUnreachableProxy(): void
    {
        if (!ilCurlConnection::_isCurlExtensionLoaded()) {
            $this->markTestSkipped('PHP has no cURL.');
        }

        // nothing listens on port 1 of the loopback, so the call fails without leaving the machine
        $setting = $this->createStub(ilSetting::class);
        $setting->method('get')->willReturnMap([
            ['proxy_host', null, '127.0.0.1'],
            ['proxy_port', null, '1'],
            ['proxy_status', null, '1'],
        ]);
        $client = new ilLTICurlClient(new ilProxySettings($setting));
        $message = new HttpMessage('https://platform.example.org/jwks');

        $this->assertFalse($client->send($message));
        $this->assertFalse($message->ok);
        $this->assertSame(0, $message->status);
        $this->assertNotSame('', $message->error);
    }
}
