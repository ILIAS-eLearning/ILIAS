<?php

use PHPUnit\Framework\TestSuite;

/**
 * Context Test-Suite
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version 1.0.0
 */
class ilComponentsContextSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilComponentsContextSuite();

        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/Context_/test/ilContextTest.php");

        $suite->addTestSuite("ilContextTest");

        return $suite;
    }
}
