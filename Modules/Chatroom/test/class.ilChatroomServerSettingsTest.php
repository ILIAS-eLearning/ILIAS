<?php

/**
 * Class ilChatroomServerSettingsTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomServerSettingsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ilChatroomServerSettings
     */
    protected $settings;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }

        require_once './Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
        $this->settings = new ilChatroomServerSettings();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('ilChatroomServerSettings', $this->settings);
    }

    public function setterAndGettersProvider()
    {
        return array(
            array('port', 'integer', 7373),
            array('protocol', 'string', 'http://'), //@todo Remove :// from protocol
            array('domain', 'string', '127.0.0.1'),

            array('authKey', 'string', 'cfdf79fc-4133-4f3b-882f-5162a87dc465'),
            array('authSecret', 'string', 'f8072a49-0488-411f-be0a-723a762700ba'),
            array('clientUrlEnabled', 'boolean', true),
            array('clientUrl', 'string', 'http://proxy.localhost'),
            array('iliasUrlEnabled', 'boolean', true),
            array('iliasUrl', 'string', 'http://proxy.localhost'),
            array('smiliesEnabled', 'boolean', false),

            //@TODO Remove this properties
            array('instance', 'string', '123456'),
        );
    }

    /**
     * @param string $property
     * @param string $type
     * @param mixed  $value
     * @dataProvider setterAndGettersProvider
     */
    public function testSettersAndGetters($property, $type, $value)
    {
        $setter = 'set' . ucfirst($property);
        $getter = 'get' . ucfirst(($property));

        $this->assertTrue(method_exists($this->settings, $setter), sprintf('The Setter "%s" does not exist', $setter));
        $this->assertTrue(method_exists($this->settings, $getter), sprintf('The Getter "%s" does not exist', $setter));

        $this->settings->$setter($value);
        $actual = $this->settings->$getter();

        $this->assertEquals($value, $actual, sprintf('The expected value "%s" is not equals to "%s"', $value, $actual));

        if (class_exists($type)) {
            $this->assertInstanceOf(
                $type,
                $actual,
                sprintf('The actual return value is not an instance of "%s"', $type)
            );
        } else {
            $this->assertInternalType($type, $actual, sprintf('The actual return value is not a type of "%s"', $type));
        }
    }

    public function testGetBaseUrl()
    {
        $protocol = 'http://';
        $domain   = '127.0.0.1';
        $port     = '7373';
        $expected = sprintf('%s%s:%s', $protocol, $domain, $port);

        $this->settings->setProtocol($protocol);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);

        $this->assertEquals($expected, $this->settings->getBaseURL());
    }

    public function testGenerateClientUrlIfEnabled()
    {
        $protocol     = 'http://';
        $domain       = '127.0.0.1';
        $clientDomain = 'proxy.localhost';
        $port         = '7373';
        $expected     = sprintf('%s%s:%s', $protocol, $clientDomain, $port);

        $this->settings->setClientUrlEnabled(true);
        $this->settings->setProtocol($protocol);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setClientUrl(sprintf('%s:%s', $clientDomain, $port));

        $this->assertEquals($expected, $this->settings->generateClientUrl());
    }

    public function testGenerateClientUrlIfDisabled()
    {
        $protocol     = 'http://';
        $domain       = '127.0.0.1';
        $clientDomain = 'proxy.localhost';
        $port         = '7373';
        $expected     = sprintf('%s%s:%s', $protocol, $domain, $port);

        $this->settings->setClientUrlEnabled(false);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setProtocol($protocol);
        $this->settings->setClientUrl(sprintf('%s:%s', $clientDomain, $port));

        $this->assertEquals($expected, $this->settings->generateClientUrl());
    }

    public function testGenerateIliasUrlIfEnabled()
    {
        $protocol    = 'http://';
        $domain      = '127.0.0.1';
        $iliasDomain = 'proxy.localhost';
        $port        = '7373';
        $expected    = sprintf('%s%s:%s', $protocol, $iliasDomain, $port);

        $this->settings->setIliasUrlEnabled(true);
        $this->settings->setProtocol($protocol);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setIliasUrl(sprintf('%s:%s', $iliasDomain, $port));

        $this->assertEquals($expected, $this->settings->generateIliasUrl());
    }

    public function testGenerateIliasUrlIfDisabled()
    {
        $protocol    = 'http://';
        $domain      = '127.0.0.1';
        $iliasDomain = 'proxy.localhost';
        $port        = '7373';
        $expected    = sprintf('%s%s:%s', $protocol, $domain, $port);

        $this->settings->setIliasUrlEnabled(false);
        $this->settings->setDomain($domain);
        $this->settings->setPort($port);
        $this->settings->setProtocol($protocol);
        $this->settings->setIliasUrl(sprintf('%s:%s', $iliasDomain, $port));

        $this->assertEquals($expected, $this->settings->generateIliasUrl());
    }

    public function testGetUrl()
    {
        $protocol    = 'http://';
        $domain      = '127.0.0.1';
        $iliasDomain = 'proxy.localhost:8080';
        $port        = 7373;
        $action      = 'Heartbeat';
        $instance    = 'master';
        $scope       = 123;

        $this->settings->setProtocol($protocol . '');
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
        $this->assertEquals($expected, $this->settings->getURL($action));

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
        $this->assertEquals($expected, $this->settings->getURL($action, $scope));

        $this->settings->setIliasUrlEnabled(true);
        $expected = sprintf(
            '%s%s%s/%s/%s',
            $protocol,
            $iliasDomain,
            ilChatroomServerSettings::PREFIX,
            $action,
            $instance
        );
        $this->assertEquals($expected, $this->settings->getURL($action));

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
        $this->assertEquals($expected, $this->settings->getURL($action, $scope));
    }
}
