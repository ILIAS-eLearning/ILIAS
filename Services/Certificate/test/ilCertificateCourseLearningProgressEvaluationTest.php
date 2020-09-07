<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCourseLearningProgressEvaluationTest extends PHPUnit_Framework_TestCase
{
    public function testOnlyOneCourseIsCompletedOnLPChange()
    {
        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository->method('fetchActiveTemplatesByType')
            ->willReturn(
                array(
                    new ilCertificateTemplate(
                        5,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        '1',
                        'v5.4.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        1
                    ),
                    new ilCertificateTemplate(
                        6,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        '1',
                        'v5.4.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        5
                    ),
                )
            );

        $setting = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->method('get')
            ->withConsecutive(
                array('cert_subitems_5'),
                array('cert_subitems_6')
            )
            ->willReturnOnConsecutiveCalls(
                '[10,20]',
                '[10,500]'
            );

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('lookupObjId')
            ->withConsecutive(
                array(10),
                array(20),
                array(10),
                array(500)
            )
            ->willReturnOnConsecutiveCalls(100, 200, 100, 500);

        $statusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
            ->getMock();

        $statusHelper->method('lookUpStatus')
            ->withConsecutive(
                array(100),
                array(200),
                array(100),
                array(500)
            )
            ->willReturnOnConsecutiveCalls(
                ilLPStatus::LP_STATUS_COMPLETED_NUM,
                ilLPStatus::LP_STATUS_COMPLETED_NUM,
                ilLPStatus::LP_STATUS_COMPLETED_NUM,
                ilLPStatus::LP_STATUS_IN_PROGRESS
            );

        $trackingHelper = $this->getMockBuilder('ilCertificateObjUserTrackingHelper')
            ->getMock();

        $trackingHelper->method('enabledLearningProgress')->willReturn(false);

        $evaluation = new ilCertificateCourseLearningProgressEvaluation(
            $templateRepository,
            $setting,
            $objectHelper,
            $statusHelper,
            $trackingHelper
        );

        $completedCourses = $evaluation->evaluate(10, 200);

        $this->assertEquals(5, $completedCourses[0]->getObjId());
    }

    public function testAllCoursesAreCompletedOnLPChange()
    {
        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository->method('fetchActiveTemplatesByType')
            ->willReturn(
                array(
                    new ilCertificateTemplate(
                        5,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        '1',
                        'v5.4.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        1
                    ),
                    new ilCertificateTemplate(
                        6,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        '1',
                        'v5.4.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        5
                    ),
                )
            );

        $setting = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->method('get')
            ->withConsecutive(
                array('cert_subitems_5'),
                array('cert_subitems_6')
            )
            ->willReturnOnConsecutiveCalls(
                '[10,20]',
                '[10,500]'
            );

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('lookupObjId')
            ->withConsecutive(
                array(10),
                array(20),
                array(10),
                array(500)
            )
            ->willReturnOnConsecutiveCalls(100, 200, 100, 500);

        $statusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
            ->getMock();

        $statusHelper->method('lookUpStatus')
            ->withConsecutive(
                array(100),
                array(200),
                array(100),
                array(500)
            )
            ->willReturnOnConsecutiveCalls(
                ilLPStatus::LP_STATUS_COMPLETED_NUM,
                ilLPStatus::LP_STATUS_COMPLETED_NUM,
                ilLPStatus::LP_STATUS_COMPLETED_NUM,
                ilLPStatus::LP_STATUS_COMPLETED_NUM
            );

        $trackingHelper = $this->getMockBuilder('ilCertificateObjUserTrackingHelper')
            ->getMock();

        $trackingHelper->method('enabledLearningProgress')->willReturn(false);

        $evaluation = new ilCertificateCourseLearningProgressEvaluation(
            $templateRepository,
            $setting,
            $objectHelper,
            $statusHelper,
            $trackingHelper
        );

        $completedCourses = $evaluation->evaluate(10, 200);

        $this->assertEquals(5, $completedCourses[0]->getObjId());
        $this->assertEquals(6, $completedCourses[1]->getObjId());
    }

    public function testNoSubitemDefinedForEvaluation()
    {
        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository->method('fetchActiveTemplatesByType')
            ->willReturn(
                array(
                    new ilCertificateTemplate(
                        5,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        '1',
                        'v5.4.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        1
                    ),
                    new ilCertificateTemplate(
                        6,
                        'crs',
                        '<xml>Some Content</xml>',
                        md5('<xml>Some Content</xml>'),
                        '[]',
                        '1',
                        'v5.4.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/thumbnail.svg',
                        5
                    ),
                )
            );

        $setting = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->method('get')
            ->withConsecutive(
                array('cert_subitems_5'),
                array('cert_subitems_6')
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false
            );

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $statusHelper = $this->getMockBuilder('ilCertificateLPStatusHelper')
            ->getMock();

        $trackingHelper = $this->getMockBuilder('ilCertificateObjUserTrackingHelper')
            ->getMock();

        $trackingHelper->method('enabledLearningProgress')->willReturn(false);

        $evaluation = new ilCertificateCourseLearningProgressEvaluation(
            $templateRepository,
            $setting,
            $objectHelper,
            $statusHelper,
            $trackingHelper
        );

        $completedCourses = $evaluation->evaluate(10, 200);

        $this->assertEquals(array(), $completedCourses);
    }
}
