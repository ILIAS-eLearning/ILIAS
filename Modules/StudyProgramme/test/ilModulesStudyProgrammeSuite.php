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
