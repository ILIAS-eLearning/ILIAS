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

        require_once("./components/ILIAS/StudyProgramme/test/model/Assignments/ilStudyProgrammeProgressTest.php");
        $suite->addTestSuite("ilStudyProgrammeProgressTest");
        require_once("./components/ILIAS/StudyProgramme/test/model/Assignments/ilStudyProgrammeProgressIdTest.php");
        $suite->addTestSuite("ilStudyProgrammeProgressIdTest");
        require_once("./components/ILIAS/StudyProgramme/test/model/Assignments/ilStudyProgrammeProgressTreeTest.php");
        $suite->addTestSuite("ilStudyProgrammeProgressTreeTest");
        require_once("./components/ILIAS/StudyProgramme/test/model/Assignments/ilStudyProgrammeAssignmentTest.php");
        $suite->addTestSuite("ilStudyProgrammeAssignmentTest");
        require_once("./components/ILIAS/StudyProgramme/test/model/Assignments/ilStudyProgrammeAssignmentActionsTest.php");
        $suite->addTestSuite("ilStudyProgrammeAssignmentActionsTest");
        require_once("./components/ILIAS/StudyProgramme/test/model/AutoCategories/ilStudyProgrammeAutoCategoryTest.php");
        $suite->addTestSuite("ilStudyProgrammeAutoCategoryTest");
        require_once("./components/ILIAS/StudyProgramme/test/model/AutoMemberships/ilStudyProgrammeAutoMembershipsSourceTest.php");
        $suite->addTestSuite("ilStudyProgrammeAutoMembershipsSourceTest");
        require_once("./components/ILIAS/StudyProgramme/test/ilStudyProgrammeAssessmentSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeAssessmentSettingsTest");
        require_once("./components/ILIAS/StudyProgramme/test/ilStudyProgrammeAutoMailSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeAutoMailSettingsTest");
        require_once("./components/ILIAS/StudyProgramme/test/ilStudyProgrammeValidityOfAchievedQualificationSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeValidityOfAchievedQualificationSettingsTest");
        require_once("./components/ILIAS/StudyProgramme/test/ilStudyProgrammeDeadlineSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeDeadlineSettingsTest");
        require_once("./components/ILIAS/StudyProgramme/test/ilStudyProgrammeTypeSettingsTest.php");
        $suite->addTestSuite("ilStudyProgrammeTypeSettingsTest");
        require_once("./components/ILIAS/StudyProgramme/test/types/ilStudyProgrammeTypeInfoTest.php");
        $suite->addTestSuite("ilStudyProgrammeTypeInfoTest");
        require_once("./components/ILIAS/StudyProgramme/test/helpers/ilStudyProgrammeGUIMessagesTest.php");
        $suite->addTestSuite("ilStudyProgrammeGUIMessagesTest");
        require_once("./components/ILIAS/StudyProgramme/test/cron/ilStudyProgrammeCronRiskyToFailTest.php");
        $suite->addTestSuite("ilStudyProgrammeCronRiskyToFailTest");
        require_once("./components/ILIAS/StudyProgramme/test/cron/ilStudyProgrammeCronAboutToExpireTest.php");
        $suite->addTestSuite("ilStudyProgrammeCronAboutToExpireTest");
        require_once("./components/ILIAS/StudyProgramme/test/cron/ilPrgRestartAssignmentsCronJobTest.php");
        $suite->addTestSuite("ilPrgRestartAssignmentsCronJobTest");
        require_once("./components/ILIAS/StudyProgramme/test/ilObjStudyProgrammeCacheTest.php");
        $suite->addTestSuite("ilObjStudyProgrammeCacheTest");
        require_once("./components/ILIAS/StudyProgramme/test/ilObjStudyProgrammeCertificateTest.php");
        $suite->addTestSuite("ilObjStudyProgrammeCertificateTest");

        return $suite;
    }
}
