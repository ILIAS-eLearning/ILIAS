<?php

use PHPUnit\Framework\TestSuite;

class ilModulesLearningSequenceSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesLearningSequenceSuite();

        // add each test class of the component
        require_once("./Modules/LearningSequence/test/LSControlBuilderTest.php");
        require_once("./Modules/LearningSequence/test/LSLocatorBuilderTest.php");
        require_once("./Modules/LearningSequence/test/LSTOCBuilderTest.php");
        require_once("./Modules/LearningSequence/test/LSUrlBuilderTest.php");
        require_once("./Modules/LearningSequence/test/Settings/LSSettingsTest.php");
        require_once("./Modules/LearningSequence/test/LSItems/LSItemTest.php");
        require_once("./Modules/LearningSequence/test/LearnerProgress/LSLearnerItemTest.php");
        require_once("./Modules/LearningSequence/test/GlobalSettings/GlobalSettingsTest.php");

        $suite->addTestSuite("LSControlBuilderTest");
        $suite->addTestSuite("LSLocatorBuilderTest");
        $suite->addTestSuite("LSTOCBuilderTest");
        $suite->addTestSuite("LSUrlBuilderTest");
        $suite->addTestSuite("LSSettingsTest");
        $suite->addTestSuite("LSItemTest");
        $suite->addTestSuite("LSLearnerItemTest");
        $suite->addTestSuite("LSGlobalSettingsTest");

        return $suite;
    }
}
