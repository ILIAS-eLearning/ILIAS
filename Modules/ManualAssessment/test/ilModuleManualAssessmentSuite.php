<?php


class ilModulesManualAssessmentSuite extends PHPUnit_Framework_TestSuite {
	public static function suite() {
		$suite = new ilModulesManualAssessmentSuite();
	//	require_once("./Modules/ManualAssessment/test/ilManualAssessmentSettingsTest.php");

    //    $suite->addTestSuite("ilManualAssessmentSettingsTest");
		return $suite;
	}
}