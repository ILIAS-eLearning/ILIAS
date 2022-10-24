<?php

declare(strict_types=1);

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

/**
 * StudyProgramme Test-Suite
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilModulesStudyProgrammeSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilModulesStudyProgrammeSuite();

        // add each test class of the component
        require_once("./Modules/StudyProgramme/test/model/Progress/ilStudyProgrammeProgressTest.php");
        $suite->addTestSuite("ilStudyProgrammeProgressTest");
        require_once("./Modules/StudyProgramme/test/model/AutoCategories/ilStudyProgrammeAutoCategoryTest.php");
        $suite->addTestSuite("ilStudyProgrammeAutoCategoryTest");
        require_once("./Modules/StudyProgramme/test/model/AutoMemberships/ilStudyProgrammeAutoMembershipsSourceTest.php");
        $suite->addTestSuite("ilStudyProgrammeAutoMembershipsSourceTest");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeAssessmentSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeAssessmentSettingsTest");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeAutoMailSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeAutoMailSettingsTest");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeValidityOfAchievedQualificationSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeValidityOfAchievedQualificationSettingsTest");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeDeadlineSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeDeadlineSettingsTest");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeTypeSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeTypeSettingsTest");
        require_once("./Modules/StudyProgramme/test/types/ilStudyProgrammeTypeInfoTest.php");
        $suite->addTestSuite("ilStudyProgrammeTypeInfoTest");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeDashGUITest.php");
        $suite->addTestSuite("ilStudyProgrammeDashGUITest");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeProgressCalculationsTest.php");
        $suite->addTestSuite("ilStudyProgrammeProgressCalculationsTest");
        require_once("./Modules/StudyProgramme/test/helpers/ilStudyProgrammeGUIMessagesTest.php");
        $suite->addTestSuite("ilStudyProgrammeGUIMessagesTest");
        require_once("./Modules/StudyProgramme/test/cron/ilStudyProgrammeCronRiskyToFailTest.php");
        $suite->addTestSuite("ilStudyProgrammeCronRiskyToFailTest");
        require_once("./Modules/StudyProgramme/test/cron/ilStudyProgrammeCronAboutToExpireTest.php");
        $suite->addTestSuite("ilStudyProgrammeCronAboutToExpireTest");
        require_once("./Modules/StudyProgramme/test/ilObjStudyProgrammeCacheTest.php");
        $suite->addTestSuite("ilObjStudyProgrammeCacheTest");

        return $suite;
    }
}
