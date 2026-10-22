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

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use XapiProxy\XapiProxy;
use XapiProxy\XapiProxyResponse;

class XapiProxyResponseTest extends TestCase
{
    /**
     * @dataProvider statusCodes
     */
    public function testConditionalAnswersAreRelayedInsteadOfBeingReplacedByAProxyError(
        int $status,
        bool $expected
    ): void {
        // XapiProxy declares its own method(), so the stub is configured through expects()
        $proxy = $this->createMock(XapiProxy::class);
        $proxy->expects($this->any())->method('log')->willReturn($this->createMock(ilLogger::class));

        $this->assertSame(
            $expected,
            (new XapiProxyResponse($proxy))->checkResponse(
                ['state' => 'fulfilled', 'value' => new Response($status)],
                'https://lrs.example.org/xapi'
            )
        );
    }

    /**
     * @return array<string, array{0: int, 1: bool}>
     */
    public static function statusCodes(): array
    {
        return [
            'ok' => [200, true],
            'no content' => [204, true],
            'not modified' => [304, true],
            'document does not exist' => [404, true],
            'precondition required by the lrs' => [409, true],
            'precondition failed' => [412, true],
            'bad request' => [400, false],
            'server error' => [500, false],
        ];
    }

    public function testConnectionErrorsRemainAnError(): void
    {
        $proxy = $this->createMock(XapiProxy::class);
        $proxy->expects($this->any())->method('log')->willReturn($this->createMock(ilLogger::class));

        $this->assertFalse(
            (new XapiProxyResponse($proxy))->checkResponse(
                ['state' => 'rejected', 'reason' => new Exception('connection refused')],
                'https://lrs.example.org/xapi'
            )
        );
    }
}
