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

use ILIAS\Environment\Configuration\Instance\DefaultClientIdProvider;
use ILIAS\Environment\Configuration\Instance\IliasIni;
use ILIAS\HTTP\GlobalHttpState;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class DefaultClientIdProviderTest extends TestCase
{
    /**
     * @param array<string, string> $query
     * @param array<string, string> $cookies
     */
    private function provider(array $query, array $cookies, string $ini_default = 'default'): DefaultClientIdProvider
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn($query);
        $request->method('getCookieParams')->willReturn($cookies);

        $http = $this->createStub(GlobalHttpState::class);
        $http->method('request')->willReturn($request);

        $ini = $this->createStub(IliasIni::class);
        $ini->method('getDefaultClientId')->willReturn($ini_default);

        return new DefaultClientIdProvider($ini, $http);
    }

    public function testQueryParameterTakesPrecedence(): void
    {
        $id = $this->provider(['client_id' => 'from_query'], ['ilClientId' => 'from_cookie'], 'from_ini')->getClientId();
        self::assertSame('from_query', $id->toString());
    }

    public function testCookieIsUsedWhenNoQueryParameter(): void
    {
        $id = $this->provider([], ['ilClientId' => 'from_cookie'], 'from_ini')->getClientId();
        self::assertSame('from_cookie', $id->toString());
    }

    public function testFallsBackToIniDefault(): void
    {
        $id = $this->provider([], [], 'from_ini')->getClientId();
        self::assertSame('from_ini', $id->toString());
    }

    public function testMarkupValueIsRejectedRatherThanSanitised(): void
    {
        // strip_tags() was removed: ClientId is now the sole validation point
        // and rejects anything outside [A-Za-z0-9#_.-] instead of silently
        // stripping it.
        $this->expectException(InvalidArgumentException::class);
        $this->provider(['client_id' => '<script>alert</script>'], [], 'from_ini')->getClientId();
    }
}
