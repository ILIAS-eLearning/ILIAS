<?php

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

declare(strict_types=1);

namespace ILIAS\Course\Certificate;

use ilTree;
use ilAccess;
use ilSetting;
use ilLanguage;
use ilObjectLP;
use ilObjCourse;
use ilToolbarGUI;
use ilCtrlInterface;
use ilCertificateObjectHelper;
use PHPUnit\Framework\TestCase;
use ilCertificateObjectLPHelper;
use ilCertificateObjUserTrackingHelper;
use ilCertificatePlaceholderDescription;
use ilCertificateSettingsFormRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsCourseFormRepositoryTest extends TestCase
{
    public function testSaveSettings(): void
    {
        $object = $this->createMock(ilObjCourse::class);

        $object
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(100);

        $language = $this->createStub(ilLanguage::class);

        $controller = $this->createStub(ilCtrlInterface::class);

        $access = $this->createStub(ilAccess::class);

        $toolbar = $this->createStub(ilToolbarGUI::class);

        $placeholderDescriptionObject = $this->createStub(ilCertificatePlaceholderDescription::class);

        $settingsFormFactory = $this->createStub(ilCertificateSettingsFormRepository::class);

        $trackingHelper = $this->createStub(ilCertificateObjUserTrackingHelper::class);

        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $lpHelper = $this->createStub(ilCertificateObjectLPHelper::class);

        $lpMock = $this->createStub(ilObjectLP::class);

        $lpMock->method('getCurrentMode')
            ->willReturn(100);

        $lpHelper->method('getInstance')->willReturn($lpMock);

        $tree = $this->createStub(ilTree::class);

        $setting = $this->createMock(ilSetting::class);

        $setting
            ->expects($this->atLeastOnce())
            ->method('set');

        $repository = new CertificateSettingsCourseFormRepository(
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
        $object = $this->createMock(ilObjCourse::class);

        $object
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(100);

        $language = $this->createStub(ilLanguage::class);

        $controller = $this->createStub(ilCtrlInterface::class);

        $access = $this->createStub(ilAccess::class);

        $toolbar = $this->createStub(ilToolbarGUI::class);

        $placeholderDescriptionObject = $this->createStub(ilCertificatePlaceholderDescription::class);

        $settingsFormFactory = $this->createMock(ilCertificateSettingsFormRepository::class);

        $settingsFormFactory
            ->expects($this->atLeastOnce())
            ->method('fetchFormFieldData')
            ->willReturn(
                [
                    'subitems' => [],
                    'something_else' => 'something'
                ]
            );

        $trackingHelper = $this->createStub(ilCertificateObjUserTrackingHelper::class);

        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $lpHelper = $this->createStub(ilCertificateObjectLPHelper::class);

        $tree = $this->createStub(ilTree::class);

        $setting = $this->createMock(ilSetting::class);

        $setting
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn('[1, 2, 3]');

        $repository = new CertificateSettingsCourseFormRepository(
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
