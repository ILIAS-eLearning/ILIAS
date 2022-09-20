<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;

/**
 * Test Suite for the MathJax service
 */
class ilServicesMathJaxSuite extends TestSuite
{
    private const TEST_CLASSES = [
        'ilMathJaxTest'
    ];

    public static function suite(): self
    {
        $suite = new self();

        foreach (self::TEST_CLASSES as $class) {
            require_once __DIR__ . "/class.$class.php";
            $suite->addTestSuite($class);
        }

        return $suite;
    }
}
