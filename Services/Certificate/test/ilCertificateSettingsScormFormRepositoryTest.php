<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsScormFormRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testSave()
    {
        $object = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

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

        $settingFormRepository = $this->getMockBuilder('ilCertificateSettingsFormRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->exactly(2))
            ->method('set');

        $repository = new ilCertificateSettingsScormFormRepository(
            $object,
            '/some/where/',
            $language,
            $template,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingFormRepository,
            $setting
        );

        $repository->save(
            array(
                'certificate_enabled_scorm' => true,
                'short_name' => 'something'
            )
        );
    }

    public function testFetchFormFieldData()
    {
        $object = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

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

        $settingFormRepository = $this->getMockBuilder('ilCertificateSettingsFormRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $settingFormRepository
            ->expects($this->once())
            ->method('fetchFormFieldData')
            ->willReturn(
                array(
                    'certificate_enabled_scorm' => '',
                    'short_name' => ''
                )
            );

        $setting = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls('something', 'somethingelse');

        $repository = new ilCertificateSettingsScormFormRepository(
            $object,
            '/some/where/',
            $language,
            $template,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingFormRepository,
            $setting
        );

        $result = $repository->fetchFormFieldData('Some Content');

        $this->assertEquals(
            array(
                'certificate_enabled_scorm' => 'something',
                'short_name' => 'somethingelse'
            ),
            $result
        );
    }
}
