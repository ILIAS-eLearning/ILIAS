<?php

declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/vendor/composer/vendor/autoload.php");

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

/**
 * Tests working with color data object
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class PasswordTest extends TestCase
{
    protected Data\Factory $f;

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
