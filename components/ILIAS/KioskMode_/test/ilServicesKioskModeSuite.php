<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesKioskModeSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        require_once 'ilKioskModeServiceTest.php';

        $suite->addTestSuite(ilKioskModeServiceTest::class);

        return $suite;
    }
}
