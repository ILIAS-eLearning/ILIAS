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

namespace ILIAS\Test\Certificate;

use ilCertificateGUI;
use ilCertificateSettingsFormRepository;
use ilPropertyFormGUI;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class CertificateSettingsTestFormRepositoryTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $certificate_settings_test_form_repository = $this->createInstanceOf(CertificateSettingsTestFormRepository::class);
        $this->assertInstanceOf(CertificateSettingsTestFormRepository::class, $certificate_settings_test_form_repository);
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testCreateForm(): void
    {
        $form_mock = $this->getMockBuilder(ilPropertyFormGUI::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settings_form_factory = $this->createMock(ilCertificateSettingsFormRepository::class);
        $settings_form_factory
            ->expects($this->once())
            ->method('createForm')
            ->willReturn($form_mock);
        $il_certificate_gui = $this->createMock(ilCertificateGUI::class);

        $certificate_settings_test_form_repository = $this->createInstanceOf(CertificateSettingsTestFormRepository::class, [
            'settings_form_repository' => $settings_form_factory
        ]);

        $this->assertEquals($form_mock, $certificate_settings_test_form_repository->createForm($il_certificate_gui));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testSave(): void
    {
        $this->assertNull($this->createInstanceOf(CertificateSettingsTestFormRepository::class)->save([]));
    }

    /**
     * @dataProvider fetchFormFieldDataProvider
     * @throws Exception|ReflectionException
     */
    public function testFetchFormFieldData(string $input, array $output): void
    {
        $settings_form_factory = $this->createMock(ilCertificateSettingsFormRepository::class);
        $settings_form_factory
            ->expects($this->once())
            ->method('fetchFormFieldData')
            ->with($input)
            ->willReturn($output);
        $certificate_settings_test_form_repository = $this->createInstanceOf(CertificateSettingsTestFormRepository::class, [
            'settings_form_repository' => $settings_form_factory
        ]);

        $this->assertEquals($output, $certificate_settings_test_form_repository->fetchFormFieldData($input));
    }

    public static function fetchFormFieldDataProvider(): array
    {
        return [
            'empty' => ['', []],
            'string' => ['string', ['string']],
            'strING' => ['strING', ['strING']]
        ];
    }
}
