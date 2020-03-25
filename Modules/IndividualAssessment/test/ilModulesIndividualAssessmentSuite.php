<?php

use PHPUnit\Framework\TestSuite;

class ilModulesIndividualAssessmentSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesIndividualAssessmentSuite();
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentSettingsTest.php");
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentMembersTest.php");
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentUserGradingTest.php");
        $suite->addTestSuite('ilindividualAssessmentSettingsTest');
        $suite->addTestSuite('ilIndividualAssessmentMembersTest');
        $suite->addTestSuite('ilIndividualAssessmentUserGradingTest');
        return $suite;
    }
}
