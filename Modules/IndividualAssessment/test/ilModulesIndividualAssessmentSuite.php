<?php


class ilModulesIndividualAssessmentSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesIndividualAssessmentSuite();
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentSettingsTest.php");
        require_once("./Modules/IndividualAssessment/test/ilIndividualAssessmentMembersTest.php");
        $suite->addTestSuite("ilindividualAssessmentSettingsTest");
        $suite->addTestSuite('ilIndividualAssessmentMembersTest');
        return $suite;
    }
}
