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

use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\LanguageActivity;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Language;
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
        $repository = $this->createMock(RepositoryProvider::class);

        return match ($name) {
            'install' => new InstallLanguage($rbac, $setup_language, $folder),
            'update' => new UpdateLanguage($rbac, $setup_language, $folder),
            'uninstall' => new UninstallLanguage($rbac, $folder, $repository),
            'remove' => new RemoveLocalLanguageChanges($rbac, $folder, $repository),
            'add' => new AddLanguageEntry($language, $rbac, $folder, $repository),
            'detection' => new SetLanguageDetectionEnabled($rbac, $folder, $repository),
            'translation' => new SetLanguageTranslationEnabled($rbac, $folder, $repository),
            default => throw new \InvalidArgumentException($name),
        };
    }
}
