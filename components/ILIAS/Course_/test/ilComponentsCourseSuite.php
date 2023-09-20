<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestSuite;

class ilComponentsCourseSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        /** @noRector  */
        include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Course_/test/ilCourseMailTemplateTutorContextTest.php';
        $suite->addTestSuite("ilCourseMailTemplateTutorContextTest");

        include_once substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . '/components/ILIAS/Course_/test/Timings/ilTimingAcceptedTest.php';
        $suite->addTestSuite(ilTimingAcceptedTest::class);

        return $suite;
    }
}
