<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

/**
 * Class ilChatroomServerSettingsTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomServerSettingsTest extends TestCase
{
    protected ilChatroomServerSettings $settings;

    public function setterAndGettersProvider(): array
    {
        $assertIsString = function ($actual): void {
            $this->assertIsString($actual, 'The actual return value is not a type of "string"');
        };

        $assertIsInteger = function ($actual): void {
            $this->assertIsInt($actual, 'The actual return value is not a type of "int"');
        };

        $assertIsBool = function ($actual): void {
            $this->assertIsBool($actual, 'The actual return value is not a type of "bool"');
        };

        return [
            ['port', $assertIsInteger, 7373],
            ['protocol', $assertIsString, 'http://'],
            ['domain', $assertIsString, '127.0.0.1'],

            ['authKey', $assertIsString, 'cfdf79fc-4133-4f3b-882f-5162a87dc465'],
            ['authSecret', $assertIsString, 'f8072a49-0488-411f-be0a-723a762700ba'],
            ['clientUrlEnabled', $assertIsBool, true],
            ['clientUrl', $assertIsString, 'http://proxy.localhost'],
            ['iliasUrlEnabled', $assertIsBool, true],
            ['iliasUrl', $assertIsString, 'http://proxy.localhost'],
            ['smiliesEnabled', $assertIsBool, false],

            //@TODO Remove this properties
            ['instance', $assertIsString, '123456'],
        ];
    }

    /**
     * @param string $property
     * @param callable $assertionCallback
     * @param mixed $value
     * @dataProvider setterAndGettersProvider
     */
    public function testSettersAndGetters(string $property, callable $assertionCallback, $value): void
    {
        $setter = 'set' . ucfirst($property);
        $getter = 'get' . ucfirst(($property));

        $this->assertTrue(method_exists($this->settings, $setter), sprintf('The Setter "%s" does not exist', $setter));
        $this->assertTrue(method_exists($this->settings, $getter), sprintf('The Getter "%s" does not exist', $setter));

        $this->settings->$setter($value);
        $actual = $this->settings->$getter();

        $this->assertSame($value, $actual, sprintf('The expected value "%s" is not equals to "%s"', $value, $actual));

        $assertionCallback($actual);
    }

    public function testGetBaseUrl(): void
    {
        $protocol = 'http://';
        $domain = '127.0.0.1';
        $port = 7373;
        $expected = sprintf('%s%s:%s', $protocol, $domain, $port);

        $this->settings->setProtocol($protocol);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);

        $this->assertSame($expected, $this->settings->getBaseURL());
    }

    public function testGenerateClientUrlIfEnabled(): void
    {
        $protocol = 'http://';
        $domain = '127.0.0.1';
        $clientDomain = 'proxy.localhost';
        $port = 7373;
        $expected = sprintf('%s%s:%s', $protocol, $clientDomain, $port);

        $this->settings->setClientUrlEnabled(true);
        $this->settings->setProtocol($protocol);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setClientUrl(sprintf('%s:%s', $clientDomain, $port));

        $this->assertSame($expected, $this->settings->generateClientUrl());
    }

    public function testGenerateClientUrlIfDisabled(): void
    {
        $protocol = 'http://';
        $domain = '127.0.0.1';
        $clientDomain = 'proxy.localhost';
        $port = 7373;
        $expected = sprintf('%s%s:%s', $protocol, $domain, $port);

        $this->settings->setClientUrlEnabled(false);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setProtocol($protocol);
        $this->settings->setClientUrl(sprintf('%s:%s', $clientDomain, $port));

        $this->assertSame($expected, $this->settings->generateClientUrl());
    }

    public function testGenerateIliasUrlIfEnabled(): void
    {
        $protocol = 'http://';
        $domain = '127.0.0.1';
        $iliasDomain = 'proxy.localhost';
        $port = 7373;
        $expected = sprintf('%s%s:%s', $protocol, $iliasDomain, $port);

        $this->settings->setIliasUrlEnabled(true);
        $this->settings->setProtocol($protocol);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setIliasUrl(sprintf('%s:%s', $iliasDomain, $port));

        $this->assertSame($expected, $this->settings->generateIliasUrl());
    }

    public function testGenerateIliasUrlIfDisabled(): void
    {
        $protocol = 'http://';
        $domain = '127.0.0.1';
        $iliasDomain = 'proxy.localhost';
        $port = 7373;
        $expected = sprintf('%s%s:%s', $protocol, $domain, $port);

        $this->settings->setIliasUrlEnabled(false);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setProtocol($protocol);
        $this->settings->setIliasUrl(sprintf('%s:%s', $iliasDomain, $port));

        $this->assertSame($expected, $this->settings->generateIliasUrl());
    }

    public function testGetUrl(): void
    {
        $protocol = 'http://';
        $domain = '127.0.0.1';
        $iliasDomain = 'proxy.localhost:8080';
        $port = 7373;
        $action = 'Heartbeat';
        $instance = 'master';
        $scope = 123;

        $this->settings->setProtocol($protocol);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setIliasUrl($iliasDomain);
        $this->settings->setInstance($instance);

        $this->settings->setIliasUrlEnabled(false);
        $expected = sprintf(
            '%s%s:%s%s/%s/%s',
            $protocol,
            $domain,
            $port,
            ilChatroomServerSettings::PREFIX,
            $action,
            $instance
        );
        $this->assertSame($expected, $this->settings->getURL($action));

        $this->settings->setIliasUrlEnabled(false);
        $expected = sprintf(
            '%s%s:%s%s/%s/%s/%s',
            $protocol,
            $domain,
            $port,
            ilChatroomServerSettings::PREFIX,
            $action,
            $instance,
            $scope
        );
        $this->assertSame($expected, $this->settings->getURL($action, $scope));

        $this->settings->setIliasUrlEnabled(true);
        $expected = sprintf(
            '%s%s%s/%s/%s',
            $protocol,
            $iliasDomain,
            ilChatroomServerSettings::PREFIX,
            $action,
            $instance
        );
        $this->assertSame($expected, $this->settings->getURL($action));

        $this->settings->setIliasUrlEnabled(true);
        $expected = sprintf(
            '%s%s%s/%s/%s/%s',
            $protocol,
            $iliasDomain,
            ilChatroomServerSettings::PREFIX,
            $action,
            $instance,
            $scope
        );
        $this->assertSame($expected, $this->settings->getURL($action, $scope));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = new ilChatroomServerSettings();
    }
}
