<?php declare(strict_types=1);

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
class ilCertificateSettingsTestFormRepositoryTest extends ilCertificateBaseTestCase
{
    public function testCreate() : void
    {
        $object = $this->getMockBuilder(ilObjTest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formMock = $this->getMockBuilder(ilPropertyFormGUI::class)
            ->disableOriginalConstructor()
            ->getMock();

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
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

        $repository = new ilCertificateSettingsTestFormRepository(
            100,
            '/some/where/',
            false,
            $object,
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

        $this->assertSame($formMock, $result);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSave() : void
    {
        $object = $this->getMockBuilder(ilObjTest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->method('getId')
            ->willReturn(100);

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
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

        $repository = new ilCertificateSettingsTestFormRepository(
            100,
            '/some/where/',
            false,
            $object,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory
        );

        $repository->save([1, 2, 3]);
    }

    public function testFormFieldData() : void
    {
        $object = $this->getMockBuilder(ilObjTest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->method('getId')
            ->willReturn(100);

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrlInterface::class)
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
            ->willReturn(['something' => 'value']);

        $repository = new ilCertificateSettingsTestFormRepository(
            100,
            '/some/where/',
            false,
            $object,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory
        );

        $result = $repository->fetchFormFieldData('SomeContent');

        $this->assertSame(['something' => 'value'], $result);
    }
}
