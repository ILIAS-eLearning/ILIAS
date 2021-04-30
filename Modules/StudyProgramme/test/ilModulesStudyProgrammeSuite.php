<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

use PHPUnit\Framework\TestSuite;

/**
 * StudyProgramme Test-Suite
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilModulesStudyProgrammeSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesStudyProgrammeSuite();

        // add each test class of the component
        require_once("./Modules/StudyProgramme/test/ilObjStudyProgrammeTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeEventsTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeLPTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeProgressCalculationTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeUserAssignmentTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeUserProgressTest.php");
        require_once("./Modules/StudyProgramme/test/model/Settings/ilStudyProgrammeSettingsTest.php");
        require_once("./Modules/StudyProgramme/test/model/Settings/ilStudyProgrammeSettingsRepositoryTest.php");
        require_once("./Modules/StudyProgramme/test/model/Progress/ilStudyProgrammeProgressTest.php");
        require_once("./Modules/StudyProgramme/test/model/Progress/ilStudyProgrammeProgressRepositoryTest.php");
        require_once("./Modules/StudyProgramme/test/model/Assignments/ilStudyProgrammeAssignmentTest.php");
        require_once("./Modules/StudyProgramme/test/model/Assignments/ilStudyProgrammeAssignmentRepositoryTest.php");
        require_once("./Modules/StudyProgramme/test/model/Types/ilStudyProgrammeTypeTranslationTest.php");
        require_once("./Modules/StudyProgramme/test/model/Types/ilStudyProgrammeAdvancedMetadataRecordTest.php");
        require_once("./Modules/StudyProgramme/test/model/Types/ilStudyProgrammeTypeTest.php");
        require_once("./Modules/StudyProgramme/test/model/Types/ilStudyProgrammeTypeRepositoryTest.php");
        require_once("./Modules/StudyProgramme/test/ilPrgInvalidateExpiredProgressesCronJobTest.php");
        require_once("./Modules/StudyProgramme/test/ilPrgRestartAssignmentsCronJobTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeAssessmentSettingsTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeAutoMailSettingsTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeValidityOfAchievedQualificationSettingsTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeDeadlineSettingsTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeTypeSettingsTest.php");
        require_once("./Modules/StudyProgramme/test/types/ilStudyProgrammeTypeInfoTest.php");
        require_once("./Modules/StudyProgramme/test/ilStudyProgrammeDashGUITest.php");
        $suite->addTestSuite("ilObjStudyProgrammeTest");
        $suite->addTestSuite("ilStudyProgrammeEventsTest");
        $suite->addTestSuite("ilStudyProgrammeLPTest");
        $suite->addTestSuite("ilStudyProgrammeProgressCalculationTest");
        $suite->addTestSuite("ilStudyProgrammeUserAssignmentTest");
        $suite->addTestSuite("ilStudyProgrammeUserProgressTest");
        $suite->addTestSuite("ilStudyProgrammeSettingsTest");
        $suite->addTestSuite("ilStudyProgrammeSettingsRepositoryTest");
        $suite->addTestSuite("ilStudyProgrammeProgressTest");
        $suite->addTestSuite("ilStudyProgrammeProgressRepositoryTest");
        $suite->addTestSuite("ilStudyProgrammeAssignmentTest");
        $suite->addTestSuite("ilStudyProgrammeAssignmentRepositoryTest");
        $suite->addTestSuite("ilStudyProgrammeTypeTranslationTest");
        $suite->addTestSuite("ilStudyProgrammeAdvancedMetadataRecordTest");
        $suite->addTestSuite("ilStudyProgrammeTypeTest");
        $suite->addTestSuite("ilStudyProgrammeTypeRepositoryTest");
        $suite->addTestSuite("ilPrgInvalidateExpiredProgressesCronJobTest");
        $suite->addTestSuite("ilPrgRestartAssignmentsCronJobTest");
        $suite->addTestSuite("ilStudyProgrammeAssessmentSettingsTest");
        $suite->addTestSuite("ilStudyProgrammeAutoMailSettingsTest");
        $suite->addTestSuite("ilStudyProgrammeValidityOfAchievedQualificationSettingsTest");
        $suite->addTestSuite("ilStudyProgrammeDeadlineSettingsTest");
        $suite->addTestSuite("ilStudyProgrammeTypeSettingsTest");
        $suite->addTestSuite("ilStudyProgrammeTypeInfoTest");
        $suite->addTestSuite("ilStudyProgrammeDashGUITest");
        return $suite;
    }
}
