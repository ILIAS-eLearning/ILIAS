<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

use ILIAS\Data;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ClientIdTest extends PHPUnit_Framework_TestCase
{
    /** @var Data\Factory */
    private $f;

    /**
     *
     */
    protected function setUp()
    {
        $this->f = new Data\Factory();
    }

    /**
     * @return array[]
     */
    public function clientIdProvider() : array
    {
        return [
            ['default'],
            ['default_with_underscore'],
            ['ilias_with_12345'],
        ];
    }

    /**
     * @return array[]
     */
    public function invalidClientIdProvider() : array
    {
        return [
            ['../../some/obscure/path'],
        ];
    }

    /**
     * @param string $value
     * @dataProvider clientIdProvider
     */
    public function testValidArguments(string $value)
    {
        $clientId = $this->f->clientId($value);
        $this->assertEquals($value, $clientId->toString());
    }

    /**
     * @param string $value
     * @dataProvider invalidClientIdProvider
     */
    public function tesInvalidArguments(string $value)
    {
        try {
            $clientId = $this->f->clientId($value);
            $this->fail('This should not happen');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
