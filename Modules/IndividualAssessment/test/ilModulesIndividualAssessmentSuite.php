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

class ilModulesIndividualAssessmentSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesIndividualAssessmentSuite();

        require_once("./Modules/IndividualAssessment/test/AccessControl/ilIndividualAssessmentAccessHandlerTest.php");
        require_once("./Modules/IndividualAssessment/test/Members/ilIndividualAssessmentMemberTest.php");
        require_once("./Modules/IndividualAssessment/test/Members/ilIndividualAssessmentMembersTest.php");
        require_once("./Modules/IndividualAssessment/test/Members/ilIndividualAssessmentMembersStorageDBTest.php");
        require_once("./Modules/IndividualAssessment/test/Settings/ilIndividualAssessmentSettingsTest.php");
        require_once("./Modules/IndividualAssessment/test/Settings/ilIndividualAssessmentInfoSettingsTest.php");
        require_once("./Modules/IndividualAssessment/test/Settings/ilIndividualAssessmentCommonSettingsGUITest.php");
        require_once("./Modules/IndividualAssessment/test/Settings/ilIndividualAssessmentSettingsStorageDBTest.php");
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentDataSetTest.php");
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentExporterTest.php");
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentUserGradingTest.php");

        $suite->addTestSuite('ilIndividualAssessmentAccessHandlerTest');
        $suite->addTestSuite('ilIndividualAssessmentMemberTest');
        $suite->addTestSuite('ilIndividualAssessmentMembersTest');
        $suite->addTestSuite('ilIndividualAssessmentMembersStorageDBTest');
        $suite->addTestSuite('ilIndividualAssessmentSettingsTest');
        $suite->addTestSuite('ilIndividualAssessmentInfoSettingsTest');
        $suite->addTestSuite('ilIndividualAssessmentCommonSettingsGUITest');
        $suite->addTestSuite('ilIndividualAssessmentSettingsStorageDBTest');
        $suite->addTestSuite('ilIndividualAssessmentDataSetTest');
        $suite->addTestSuite('ilIndividualAssessmentExporterTest');
        $suite->addTestSuite('ilIndividualAssessmentUserGradingTest');

        return $suite;
    }
}
