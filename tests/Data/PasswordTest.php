<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;

/**
 * Tests working with color data object
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class PasswordTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->f = new Data\Factory();
    }

    public function testValue()
    {
        $pass = 'secret';
        $pwd = $this->f->password($pass);
        $this->assertEquals($pass, $pwd->toString());
    }

    public function testWrongParam()
    {
        try {
            $pwd = $this->f->password(123);
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
