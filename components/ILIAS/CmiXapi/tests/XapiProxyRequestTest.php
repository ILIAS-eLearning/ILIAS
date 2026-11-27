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

use GuzzleHttp\Psr7\Request;
use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use XapiProxy\XapiProxy;
use XapiProxy\XapiProxyRequest;

class XapiProxyRequestTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['DIC'] = new Container();
    }

    /**
     * @param array<string, string> $headers
     * @dataProvider preconditionCases
     */
    public function testPreconditionIsOnlySynthesizedForUnconditionalDocumentWrites(
        string $method,
        string $resource,
        array $headers,
        bool $expected
    ): void {
        // XapiProxy declares its own method(), so the stub is configured through expects()
        $proxy = $this->createMock(XapiProxy::class);
        $proxy->expects($this->any())->method('cmdParts')->willReturn(['', '', '', $resource, '']);

        $method_under_test = new ReflectionMethod(XapiProxyRequest::class, 'requiresSynthesizedPrecondition');
        $method_under_test->setAccessible(true);

        $this->assertSame(
            $expected,
            $method_under_test->invoke(
                new XapiProxyRequest($proxy),
                new Request($method, 'https://ilias.example.org/xapiproxy.php/' . $resource, $headers)
            )
        );
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: array<string, string>, 3: bool}>
     */
    public static function preconditionCases(): array
    {
        return [
            'unconditional state write' => ['PUT', 'activities/state', [], true],
            'unconditional activity profile write' => ['PUT', 'activities/profile', [], true],
            'unconditional agent profile write' => ['PUT', 'agents/profile', [], true],
            'client sends If-Match' => ['PUT', 'activities/state', ['If-Match' => '"abc"'], false],
            'client sends If-None-Match' => ['PUT', 'activities/state', ['If-None-Match' => '*'], false],
            'merging POST is not guarded' => ['POST', 'activities/state', [], false],
            'document read is not guarded' => ['GET', 'activities/state', [], false],
            'document delete is not guarded' => ['DELETE', 'activities/state', [], false],
            'statements are immutable' => ['PUT', 'statements', [], false],
        ];
    }
}
