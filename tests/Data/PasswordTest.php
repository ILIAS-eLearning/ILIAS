<?php

declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

/**
 * Tests working with color data object
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class PasswordTest extends TestCase
{
    protected function setUp(): void
    {
        $this->f = new Data\Factory();
    }

    public function testValue(): void
    {
        $pass = 'secret';
        $pwd = $this->f->password($pass);
        $this->assertEquals($pass, $pwd->toString());
    }

    public function testWrongParam(): void
    {
        $this->expectException(TypeError::class);
        $pwd = $this->f->password(123);
    }
}
