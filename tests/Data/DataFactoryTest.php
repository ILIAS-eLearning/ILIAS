<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;

/**
 * Testing the faytory of result objects
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class DataFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->f = new Data\Factory();
    }

    protected function tearDown()
    {
        $this->f = null;
    }

    public function testOk()
    {
        $result = $this->f->ok(3.154);
        $this->assertInstanceOf(Data\Result::class, $result);
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isError());
    }

    public function testError()
    {
        $result = $this->f->error("Something went wrong");
        $this->assertInstanceOf(Data\Result::class, $result);
        $this->assertTrue($result->isError());
        $this->assertFalse($result->isOk());
    }

    public function testPassword()
    {
        $pwd = $this->f->password("secret");
        $this->assertInstanceOf(Data\Password::class, $pwd);
    }
}
