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

use ILIAS\Administration\Setting;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\LanguageActivity;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Language;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\UI\Component\Input\Field\Section;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The outer container of every Activity's input description is a titled Section. Grinding through it is
 * covered by the Activity tests, which all run maybePerformAs() against the real fields.
 */
class ActivityInputSectionTest extends \ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;

    /** @return array<string, array{0: string, 1: string}> */
    public static function activityTitleProvider(): array
    {
        return [
            'install' => ['install', 'Install languages'],
            'update' => ['update', 'Update languages'],
            'uninstall' => ['uninstall', 'Uninstall languages'],
            'remove local changes' => ['remove', 'Remove local language changes'],
            'add entry' => ['add', 'Add a language entry'],
            'detection' => ['detection', 'Enable or disable language detection'],
            'translation' => ['translation', 'Enable or disable page translation'],
        ];
    }

    #[DataProvider('activityTitleProvider')]
    public function testGetInputDescriptionIsASectionWithTheExpectedTitle(string $activity, string $title): void
    {
        $description = $this->activity($activity)
            ->getInputDescription($this->createRealFieldsUiFactory()->input()->field());

        $this->assertInstanceOf(Section::class, $description);
        $this->assertSame($title, $description->getLabel());
    }

    private function activity(string $name): LanguageActivity
    {
        $language = $this->createMock(Language::class);
        $language->method('txt')->willReturnArgument(0);
        $rbac = $this->rbacOf($this->createMock(\ilRbacSystem::class));
        $folder = $this->languageFolder();
        $setup_language = $this->createMock(\ilSetupLanguage::class);
        $settings = $this->createMock(Setting::class);
        $repository = $this->createMock(InstalledLanguageRepository::class);

        return match ($name) {
            'install' => new InstallLanguage($language, $rbac, $setup_language, $folder),
            'update' => new UpdateLanguage($language, $rbac, $setup_language, $folder),
            'uninstall' => new UninstallLanguage($language, $rbac, $folder),
            'remove' => new RemoveLocalLanguageChanges($language, $rbac, $folder),
            'add' => new AddLanguageEntry($language, $rbac, $repository, $this->createMock(\ilDBInterface::class), $folder),
            'detection' => new SetLanguageDetectionEnabled($language, $rbac, $settings, $folder),
            'translation' => new SetLanguageTranslationEnabled($language, $rbac, $settings, $repository, $folder),
            default => throw new \InvalidArgumentException($name),
        };
    }
}
