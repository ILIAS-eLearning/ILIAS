<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsExerciseRepositoryTest extends ilCertificateBaseTestCase
{
    public function testCreate() : void
    {
        $formMock = $this->getMockBuilder(ilPropertyFormGUI::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $settingsFormFactory = $this->getMockBuilder(ilCertificateSettingsFormRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory
            ->expects($this->once())
            ->method('createForm')
            ->willReturn($formMock);

        $repository = new ilCertificateSettingsExerciseRepository(
            $object,
            '/some/where/',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory
        );

        $guiMock = $this->getMockBuilder(ilCertificateGUI::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $repository->createForm($guiMock);

        $this->assertEquals($formMock, $result);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSave() : void
    {
        $object = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->method('getId')
            ->willReturn(100);

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

        $settingsFormFactory = $this->getMockBuilder(ilCertificateSettingsFormRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new ilCertificateSettingsExerciseRepository(
            $object,
            '/some/where/',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory
        );

        $repository->save(array(1, 2, 3));
    }

    public function testFormFieldData() : void
    {
        $object = $this->getMockBuilder(ilObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->method('getId')
            ->willReturn(100);

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

        $settingsFormFactory = $this->getMockBuilder(ilCertificateSettingsFormRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory
            ->expects($this->once())
            ->method('fetchFormFieldData')
            ->willReturn(array('something' => 'value'));

        $repository = new ilCertificateSettingsExerciseRepository(
            $object,
            '/some/where/',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory
        );

        $result = $repository->fetchFormFieldData('SomeContent');

        $this->assertEquals(array('something' => 'value'), $result);
    }
}
