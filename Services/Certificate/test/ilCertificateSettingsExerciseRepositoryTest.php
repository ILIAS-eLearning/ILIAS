<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsExerciseRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $formMock = $this->getMockBuilder('ilPropertyFormGUI')
            ->disableOriginalConstructor()
            ->getMock();

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

        $settingsFormFactory = $this->getMockBuilder('ilCertificateSettingsFormRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory
            ->expects($this->once())
            ->method('createForm')
            ->willReturn($formMock);

        $repository = new ilCertificateSettingsExerciseRepository(
            $object,
            '/some/where/',
            $language,
            $template,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory
        );

        $guiMock = $this->getMockBuilder('ilCertificateGUI')
            ->disableOriginalConstructor()
            ->getMock();

        $certificateMock = $this->getMockBuilder('ilCertificate')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $repository->createForm($guiMock, $certificateMock);

        $this->assertEquals($formMock, $result);
    }

    public function testSave()
    {
        $object = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $object
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

        $settingsFormFactory = $this->getMockBuilder('ilCertificateSettingsFormRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new ilCertificateSettingsExerciseRepository(
            $object,
            '/some/where/',
            $language,
            $template,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory
        );

        $repository->save(array(1, 2, 3));
    }

    public function testFormFieldData()
    {
        $object = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $object
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

        $settingsFormFactory = $this->getMockBuilder('ilCertificateSettingsFormRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory
            ->expects($this->once())
            ->method('fetchFormFieldData')
            ->willReturn(array('something' => 'value'));

        $repository = new ilCertificateSettingsExerciseRepository(
            $object,
            '/some/where/',
            $language,
            $template,
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
