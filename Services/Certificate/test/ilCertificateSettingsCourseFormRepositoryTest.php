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
class ilCertificateSettingsCourseFormRepositoryTest extends ilCertificateBaseTestCase
{
    public function testSaveSettings(): void
    {
        $object = $this->getMockBuilder(ilObjCourse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
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

        $trackingHelper = $this->getMockBuilder(ilCertificateObjUserTrackingHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpHelper = $this->getMockBuilder(ilCertificateObjectLPHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpMock = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpMock->method('getCurrentMode')
            ->willReturn(100);

        $lpHelper->method('getInstance')->willReturn($lpMock);

        $tree = $this->getMockBuilder(ilTree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->atLeastOnce())
            ->method('set');

        $repository = new ilCertificateSettingsCourseFormRepository(
            $object,
            '/some/where',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory,
            $trackingHelper,
            $objectHelper,
            $lpHelper,
            $tree,
            $setting
        );

        $repository->save(['subitems' => [1, 2, 3]]);
    }

    public function testFetchFormFieldData(): void
    {
        $object = $this->getMockBuilder(ilObjCourse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
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
            ->expects($this->atLeastOnce())
            ->method('fetchFormFieldData')
            ->willReturn(
                [
                    'subitems' => [],
                    'something_else' => 'something'
                ]
            );

        $trackingHelper = $this->getMockBuilder(ilCertificateObjUserTrackingHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpHelper = $this->getMockBuilder(ilCertificateObjectLPHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tree = $this->getMockBuilder(ilTree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn('[1, 2, 3]');

        $repository = new ilCertificateSettingsCourseFormRepository(
            $object,
            '/some/where',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $settingsFormFactory,
            $trackingHelper,
            $objectHelper,
            $lpHelper,
            $tree,
            $setting
        );

        $result = $repository->fetchFormFieldData('Some Content');

        $this->assertSame(
            [
                'subitems' => [1, 2, 3],
                'something_else' => 'something'
            ],
            $result
        );
    }
}
