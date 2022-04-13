<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsScormFormRepositoryTest extends ilCertificateBaseTestCase
{
    public function testSave() : void
    {
        $object = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $access = $this->getMockBuilder(ilAccess::class)
            ->disableOriginalConstructor()
            ->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingFormRepository = $this->getMockBuilder(ilCertificateSettingsFormRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->exactly(2))
            ->method('set');

        $repository = new ilCertificateSettingsScormFormRepository(
            $object,
            '/some/where/',
            false,
            $language,
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

    public function testFetchFormFieldData() : void
    {
        $object = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $access = $this->getMockBuilder(ilAccess::class)
            ->disableOriginalConstructor()
            ->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingFormRepository = $this->getMockBuilder(ilCertificateSettingsFormRepository::class)
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

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls('something', 'somethingelse');

        $repository = new ilCertificateSettingsScormFormRepository(
            $object,
            '/some/where/',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingFormRepository,
            $setting
        );

        $result = $repository->fetchFormFieldData('Some Content');

        $this->assertSame(
            array(
                'certificate_enabled_scorm' => 'something',
                'short_name' => 'somethingelse'
            ),
            $result
        );
    }
}
