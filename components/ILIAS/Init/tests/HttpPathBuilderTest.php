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

use PHPUnit\Framework\TestCase;

class HttpPathBuilderTest extends TestCase
{
    /**
     * @return Generator<string, array{
     *     server_data: array<string, mixed>|ArrayAccess<string, mixed>,
     *     http_path: string,
     *     wsdl_path: string,
     *     allowed_hosts: string
     * }>
     */
    public static function environmentProvider(): Generator
    {
        yield 'Host matches configured HTTP path (no `allowed_hosts` configuration)' => [
            'server_data' => [
                'HTTP_HOST' => 'localhost',
                'REQUEST_URI' => '/login.php',
            ],
            'http_path' => 'http://localhost',
            'wsdl_path' => 'https://localhost/soap/server.php?wsdl=1',
            'allowed_hosts' => '',
        ];

        yield 'Host matches configured WSDL path (no `allowed_hosts` configuration)' => [
            'server_data' => [
                'HTTP_HOST' => 'soap.ilias.de',
                'REQUEST_URI' => '/login.php',
            ],
            'http_path' => 'https://test.ilias.de',
            'wsdl_path' => 'https://soap.ilias.de/soap/server.php?wsdl=1',
            'allowed_hosts' => '',
        ];

        yield 'Localhost is always allowed (no `allowed_hosts` configuration)' => [
            'server_data' => [
                'HTTP_HOST' => 'localhost',
                'REQUEST_URI' => '/login.php',
            ],
            'http_path' => 'https://test.ilias.de',
            'wsdl_path' => 'https://test.ilias.de/soap/server.php?wsdl=1',
            'allowed_hosts' => '',
        ];

        yield 'Host is in `allowed_hosts` list' => [
            'server_data' => [
                'HTTP_HOST' => 'test2.ilias.de',
                'REQUEST_URI' => '/login.php',
            ],
            'http_path' => 'https://test.ilias.de',
            'wsdl_path' => 'https://test.ilias.de/soap/server.php?wsdl=1',
            'allowed_hosts' => 'test2.ilias.de',
        ];
    }

    /**
     * @param array<string, mixed>|ArrayAccess<string, mixed> $server_data
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('environmentProvider')]
    public function testValidHostsTriggerNoExceptions(
        array|ArrayAccess $server_data,
        string $http_path,
        string $wsdl_path,
        string $allowed_hosts
    ): void {
        $path_builder = new \ILIAS\Init\Environment\HttpPathBuilder(
            new \ILIAS\Data\Factory(),
            $this->getSettingsMock($wsdl_path, $allowed_hosts),
            $this->getHttpsMock(),
            $this->getIniMock($http_path),
            $server_data
        );

        $this->assertNotEmpty((string) $path_builder->build());
    }

    public function testUnknownHostWillRaiseException(): void
    {
        $this->expectException(RuntimeException::class);

        $path_builder = new \ILIAS\Init\Environment\HttpPathBuilder(
            new \ILIAS\Data\Factory(),
            $this->getSettingsMock('https://test.ilias.de/soap/server.php?wsdl=1', 'test.ilias.de'),
            $this->getHttpsMock(),
            $this->getIniMock('https://test.ilias.de'),
            [
                'HTTP_HOST' => 'phishing.ilias.de',
                'REQUEST_URI' => '/login.php',
            ]
        );

        $path_builder->build();
    }

    private function getSettingsMock(
        string $wsdl_path,
        string $allowed_hosts
    ): ilSetting&\PHPUnit\Framework\MockObject\MockObject {
        $settings = $this
            ->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $settings->method('get')->willReturnCallback(
            static fn(string $key, ?string $default = null): ?string => match ($key) {
                'soap_wsdl_path' => $wsdl_path,
                'allowed_hosts' => $allowed_hosts,
                default => $default
            }
        );

        return $settings;
    }

    private function getHttpsMock(): ilHttps&\PHPUnit\Framework\MockObject\MockObject
    {
        $https = $this
            ->getMockBuilder(ilHTTPS::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isDetected'])
            ->getMock();
        $https->method('isDetected')->willReturn(true);

        return $https;
    }

    private function getIniMock(string $http_path): ilIniFile&\PHPUnit\Framework\MockObject\MockObject
    {
        $ini = $this
            ->getMockBuilder(ilIniFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['readVariable'])
            ->getMock();
        $ini->method('readVariable')->willReturnCallback(
            static fn(string $group, string $variable): string => match ($variable) {
                'http_path' => $http_path,
                default => ''
            }
        );

        return $ini;
    }
}
