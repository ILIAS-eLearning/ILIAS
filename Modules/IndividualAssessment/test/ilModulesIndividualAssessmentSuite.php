<?php declare(strict_types=1);

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
