<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsDidacticTemplateSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilComponentsDidacticTemplateSuite();

        include_once("./components/ILIAS/DidacticTemplate_/test/ilDidacticTemplatePatternTest.php");
        $suite->addTestSuite(ilDidacticTemplatePatternTest::class);
        return $suite;
    }
}
