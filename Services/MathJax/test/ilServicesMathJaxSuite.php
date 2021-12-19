<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

/**
 * Test Suite for the MathJax service
 */
class ilServicesMathJaxSuite extends TestSuite
{
    const TEST_CLASSES = [
      'ilMathJaxTest'
    ];

    public static function suite() : self
    {
        $suite = new self();

        foreach (self::TEST_CLASSES as $class) {
            require_once __DIR__ . "/class.$class.php";
            $suite->addTestSuite($class);
        }

        return $suite;
    }
}
