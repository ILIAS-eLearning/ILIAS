<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

/**
 * Class ilCtrlSetupSuite
 *
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlSetupSuite extends TestSuite
{
    /**
     * @return self
     */
    public static function suite() : self
    {
        return new self();
    }
}