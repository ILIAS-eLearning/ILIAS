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

class ilComponentsIndividualAssessmentSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilComponentsIndividualAssessmentSuite();

        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/AccessControl/ilIndividualAssessmentAccessHandlerTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/Members/ilIndividualAssessmentMemberTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/Members/ilIndividualAssessmentMembersTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/Members/ilIndividualAssessmentMembersStorageDBTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/Settings/ilIndividualAssessmentSettingsTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/Settings/ilIndividualAssessmentInfoSettingsTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/Settings/ilIndividualAssessmentCommonSettingsGUITest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/Settings/ilIndividualAssessmentSettingsStorageDBTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/ilIndividualAssessmentDataSetTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/ilIndividualAssessmentExporterTest.php");
        require_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/IndividualAssessment_/test/ilIndividualAssessmentUserGradingTest.php");

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
