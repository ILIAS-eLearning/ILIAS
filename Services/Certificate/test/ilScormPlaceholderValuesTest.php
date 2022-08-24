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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilScormPlaceholderValuesTest extends ilCertificateBaseTestCase
{
    public function testGetPlaceholderValues(): void
    {
        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturnCallback(function ($variableValue) {
                if ($variableValue === 'lang_sep_decimal') {
                    return ',';
                } elseif ($variableValue === 'lang_sep_thousand') {
                    return '.';
                }

                return 'Some Translation: ' . $variableValue;
            });

        $language->expects($this->once())
            ->method('loadLanguageModule');

        $dateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $objectMock = $this->getMockBuilder(ilObjSAHSLearningModule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPointsInPercent', 'getMaxPoints', 'getTitle', 'getId'])
            ->getMock();

        $objectMock->method('getPointsInPercent')
            ->willReturn(100.0);

        $objectMock->method('getMaxPoints')
            ->willReturn(100.0);

        $objectMock->method('getTitle')
            ->willReturn('SomeTitle');

        $objectMock->method('getId')
            ->willReturn(500);

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturn('Formatted String');

        $objectLPHelper = $this->getMockBuilder(ilCertificateObjectLPHelper::class)
            ->getMock();

        $lpCollection = $this->getMockBuilder(ilLPCollectionOfSCOs::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPossibleItems', 'getScoresForUserAndCP_Node_Id', 'isAssignedEntry'])
            ->getMock();

        $lpCollection->method('getPossibleItems')
            ->willReturn([100 => ['title' => 'Some Title']]);

        $lpCollection->method('getScoresForUserAndCP_Node_Id')
            ->willReturn(
                [
                    'raw' => 100,
                    'max' => 300,
                    'scaled' => 2
                ]
            );

        $lpCollection->method('isAssignedEntry')
            ->willReturn(true);

        $olp = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollectionInstance'])
            ->getMock();

        $olp->method('getCollectionInstance')
            ->willReturn($lpCollection);

        $objectLPHelper->method('getInstance')
            ->willReturn($olp);

        $lpStatusHelper = $this->getMockBuilder(ilCertificateLPStatusHelper::class)
            ->getMock();

        $lpStatusHelper->method('lookupStatusChanged')
            ->willReturn('2018-12-01 13:00:11');

        $scormPlaceholderValues = new ilScormPlaceholderValues(
            $defaultPlaceholderValues,
            $language,
            $dateHelper,
            $objectHelper,
            $utilHelper,
            $objectLPHelper,
            $lpStatusHelper
        );

        $result = $scormPlaceholderValues->getPlaceholderValues(10, 200);

        $this->assertEquals(
            [
                'SCORM_TITLE' => 'Formatted String',
                'SCORM_POINTS' => '100,0 %',
                'SCORM_POINTS_MAX' => 100,
                'SCO_T_0' => 'Some Title',
                'SCO_P_0' => '100,0',
                'SCO_PM_0' => '300,0',
                'SCO_PP_0' => '200,0 %',
                'DATE_COMPLETED' => '',
                'DATETIME_COMPLETED' => ''
            ],
            $result
        );
    }

    public function testGetPlaceholderValuesForPreview(): void
    {
        $defaultPlaceholderValues = $this->getMockBuilder(ilDefaultPlaceholderValues::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholderValues->method('getPlaceholderValuesForPreview')
            ->willReturn(
                [
                    'SOME_PLACEHOLDER' => 'aaa',
                    'SOME_OTHER_PLACEHOLDER' => 'bbb'
                ]
            );

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language->method('txt')
            ->willReturnCallback(function ($variableValue) {
                if ($variableValue === 'lang_sep_decimal') {
                    return ',';
                } elseif ($variableValue === 'lang_sep_thousand') {
                    return '.';
                }

                return 'Some Translation: ' . $variableValue;
            });

        $dateHelper = $this->getMockBuilder(ilCertificateDateHelper::class)
            ->getMock();

        $objectMock = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock->method('getTitle')
            ->willReturn('Some Title');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($objectMock);

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->method('prepareFormOutput')
            ->willReturnCallback(function ($input) {
                return $input;
            });

        $objectLPHelper = $this->getMockBuilder(ilCertificateObjectLPHelper::class)
            ->getMock();

        $lpCollection = $this->getMockBuilder(ilLPCollectionOfSCOs::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPossibleItems', 'isAssignedEntry'])
            ->getMock();

        $lpCollection->method('getPossibleItems')
            ->willReturn([
                [
                    'title' => 'Some Title'
                ],
                [
                    'title' => 'Some Other Title'
                ]
            ]);

        $lpCollection->method('isAssignedEntry')
            ->willReturn(true);

        $objectLPMock = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectLPMock->method('getCollectionInstance')
            ->willReturn($lpCollection);

        $objectLPHelper->method('getInstance')
            ->willReturn($objectLPMock);

        $lpStatusHelper = $this->getMockBuilder(ilCertificateLPStatusHelper::class)
            ->getMock();

        $scormPlaceholderValues = new ilScormPlaceholderValues(
            $defaultPlaceholderValues,
            $language,
            $dateHelper,
            $objectHelper,
            $utilHelper,
            $objectLPHelper,
            $lpStatusHelper
        );

        $result = $scormPlaceholderValues->getPlaceholderValuesForPreview(100, 10);

        $this->assertEquals(
            [
                'SCORM_TITLE' => 'Some Title',
                'SCORM_POINTS' => '80,7 %',
                'SCORM_POINTS_MAX' => '90',
                'SCO_T_0' => 'Some Title',
                'SCO_P_0' => '30,3',
                'SCO_PM_0' => '90,9',
                'SCO_PP_0' => '33,3 %',
                'SCO_T_1' => 'Some Other Title',
                'SCO_P_1' => '30,3',
                'SCO_PM_1' => '90,9',
                'SCO_PP_1' => '33,3 %',
                'SOME_PLACEHOLDER' => 'aaa',
                'SOME_OTHER_PLACEHOLDER' => 'bbb'
            ],
            $result
        );
    }
}
