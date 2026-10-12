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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\LanguageActivity;
use ILIAS\Language\Activities\UpdateLanguage;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The RBAC-free Setup variants and the lazy RBAC access of LanguageActivity.
 */
class LanguageActivitySetupVariantTest extends \ilLanguageBaseTestCase
{
    /**
     * @return array<string, array{0: class-string<LanguageActivity>}>
     */
    public static function setupActivities(): array
    {
        return [
            'install' => [InstallLanguage::class],
            'update' => [UpdateLanguage::class],
        ];
    }

    /**
     * @param class-string<InstallLanguage|UpdateLanguage> $class
     */
    #[DataProvider('setupActivities')]
    public function testForSetupActivityRefusesIsAllowedToPerform(string $class): void
    {
        $activity = $class::forSetup($this->createStub(\ilSetupLanguage::class));

        $this->expectException(\LogicException::class);
        $activity->isAllowedToPerform(6, []);
    }

    public function testWebActivityAsksRbacSystemOnlyInIsAllowedToPerform(): void
    {
        $system = $this->createMock(\ilRbacSystem::class);
        $system->expects(self::once())->method('checkAccessOfUser')
            ->with(6, 'write', 77)->willReturn(true);
        $rbac = $this->createMock(\ILIAS\AccessControl\PublicInterface\RBAC::class);
        $rbac->expects(self::once())->method('system')->willReturn($system);

        $activity = new InstallLanguage(
            $rbac,
            $this->createStub(\ilSetupLanguage::class),
            $this->languageFolder(77)
        );

        self::assertTrue($activity->isAllowedToPerform(6, []));
    }

    public function testForSetupActivityStillPerformsWithoutRbac(): void
    {
        $setup_language = $this->createStub(\ilSetupLanguage::class);
        $setup_language->method('getInstalledLanguages')->willReturn(['de']);

        $result = UpdateLanguage::forSetup($setup_language)->perform(['language_keys' => ['fr']]);

        self::assertSame(
            [
                'updated_language_keys' => [],
                'not_installed_language_keys' => ['fr'],
                'overlay_write_failed_language_keys' => [],
                'invalid_markup_customizing_entries' => [],
            ],
            $result
        );
    }
}
