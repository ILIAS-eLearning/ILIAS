<?php

declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("vendor/composer/vendor/autoload.php");

use PHPUnit\Framework\TestCase;

/**
 * Testing the faytory of result objects
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class DataReadmeTest extends TestCase
{
    public function testReadme(): void
    {
        ob_start();
        require_once("./components/ILIAS/Data/README.md");
        ob_end_clean();
        $this->assertTrue(true);
    }
}
