<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsCourseFormRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testSaveSettings()
    {
        $object = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(100);

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder('ilTemplate')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder('ilCtrl')
            ->disableOriginalConstructor()
            ->getMock();

        $access = $this->getMockBuilder('ilAccess')
            ->disableOriginalConstructor()
            ->getMock();

        $toolbar = $this->getMockBuilder('ilToolbarGUI')
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderDescriptionObject = $this->getMockBuilder('ilCertificatePlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $leaningProgressObject = $this->getMockBuilder('ilObjectLP')
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory = $this->getMockBuilder('ilCertificateSettingsFormRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $trackingHelper = $this->getMockBuilder('ilCertificateObjUserTrackingHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $lpHelper = $this->getMockBuilder('ilCertificateObjectLPHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $lpMock = $this->getMockBuilder('ilObjectLP')
            ->disableOriginalConstructor()
            ->getMock();

        $lpMock->method('getCurrentMode')
            ->willReturn(100);

        $lpHelper->method('getInstance')->willReturn($lpMock);

        $tree = $this->getMockBuilder('ilTree')
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->atLeastOnce())
            ->method('set');

        $repository = new ilCertificateSettingsCourseFormRepository(
            $object,
            '/some/where',
            $language,
            $template,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $leaningProgressObject,
            $settingsFormFactory,
            $trackingHelper,
            $objectHelper,
            $lpHelper,
            $tree,
            $setting
        );

        $repository->save(array('subitems' => array(1, 2, 3)));
    }

    public function testFetchFormFieldData()
    {
        $object = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(100);

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder('ilTemplate')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder('ilCtrl')
            ->disableOriginalConstructor()
            ->getMock();

        $access = $this->getMockBuilder('ilAccess')
            ->disableOriginalConstructor()
            ->getMock();

        $toolbar = $this->getMockBuilder('ilToolbarGUI')
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderDescriptionObject = $this->getMockBuilder('ilCertificatePlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $leaningProgressObject = $this->getMockBuilder('ilObjectLP')
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory = $this->getMockBuilder('ilCertificateSettingsFormRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory
            ->expects($this->atLeastOnce())
            ->method('fetchFormFieldData')
            ->willReturn(
                array(
                    'subitems' => array(),
                    'something_else' => 'something'
                )
            );

        $trackingHelper = $this->getMockBuilder('ilCertificateObjUserTrackingHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $lpHelper = $this->getMockBuilder('ilCertificateObjectLPHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $tree = $this->getMockBuilder('ilTree')
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn('[1, 2, 3]');

        $repository = new ilCertificateSettingsCourseFormRepository(
            $object,
            '/some/where',
            $language,
            $template,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $leaningProgressObject,
            $settingsFormFactory,
            $trackingHelper,
            $objectHelper,
            $lpHelper,
            $tree,
            $setting
        );

        $result = $repository->fetchFormFieldData('Some Content');

        $this->assertEquals(
            array(
                'subitems' => array(1, 2, 3),
                'something_else' => 'something'
            ),
            $result
        );
    }
}
