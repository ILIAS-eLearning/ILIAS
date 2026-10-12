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

namespace ILIAS\Language\Setup;

use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\NativeGettext;
use ILIAS\Language\ComponentTranslation\PlainLogText;
use ILIAS\Language\ComponentTranslation\ShippedTranslations;
use ILIAS\Language\ComponentTranslation\ShippedTranslationsBuild;
use ILIAS\Setup;

/**
 * Build step (`php cli/setup.php build`) for the migrated modules: builds what native gettext serves
 * from every shipped `.po` into `artifacts/language/`, see ShippedTranslationsBuild.
 *
 * Disallowed markup, uncompilable `.po` files and identifier collisions are warnings on every build.
 * The build is aborted (Setup\UnachievableException) if it cannot be written or native gettext does
 * not serve it.
 */
final class ShippedLanguageFilesCompiledObjective implements Setup\Objective
{
    public function __construct(
        private readonly LanguageFileDirectoryManager $language_file_directory_manager,
        private readonly string $ilias_absolute_path,
        private readonly ShippedTranslations $shipped_translations = new ShippedTranslations(),
        private readonly ?string $locale_source = null
    ) {
    }

    public function getHash(): string
    {
        $directories = [];
        foreach ($this->language_file_directory_manager->getDirectories() as $directory) {
            $directories[] = $directory->getPath() . '|' . $directory->getPrefix();
        }

        return hash('sha256', self::class . '|' . $this->ilias_absolute_path . '|' . implode(',', $directories));
    }

    public function getLabel(): string
    {
        return 'Build the shipped language files (.po) of the migrated modules for native gettext to artifacts/language';
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
        $inform = static function (string $message) use ($io): void {
            if ($io instanceof Setup\AdminInteraction) {
                $io->inform(PlainLogText::of($message));
            }
        };

        try {
            $result = (new ShippedTranslationsBuild(
                $this->language_file_directory_manager,
                $this->ilias_absolute_path,
                $this->shipped_translations,
                $this->locale_source
            ))->run($inform);
        } catch (\RuntimeException $e) {
            throw new Setup\UnachievableException(
                'The language files of the migrated modules could not be built: ' . $e->getMessage(),
                0,
                $e
            );
        } finally {
            // the check of the build activated native gettext in this process
            NativeGettext::restore();
        }

        foreach ($result['warnings'] as $warning) {
            $inform('WARNING: ' . $warning);
        }
        foreach ($result['collisions'] as $collision) {
            $inform('WARNING: ' . $collision);
        }
        $inform(sprintf(
            'Shipped language files: %s, %d compiled, %d obsolete removed.',
            $result['build'] === null ? 'no migrated module' : ($result['unchanged'] ? 'build ' . $result['build'] . ' unchanged' : 'build ' . $result['build']),
            $result['compiled'],
            $result['removed']
        ));

        return $environment;
    }
}
