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

namespace ILIAS\App\tests;

use PHPUnit\Framework\TestCase;

/**
 * Class RootFolderTest
 * @author Michael Jansen <mjansen@databay.de>
 */
final class RootFolderTest extends TestCase
{
    private const ALLOWED_ROOT_FOLDER_FILES = [
        '.babelrc.json',
        '.eslintrc.json',
        '.gitignore',
        '.htaccess',
        '.mocharc.json',
        '.phpunit.result.cache',
        'captainhook.local.json',
        'phpstan.local.neon',
        'phpstan-baseline.neon',
        '.php_cs.cache',
        '.php-cs-fixer.cache',
        'captainhook.json',
        'composer.json',
        'composer_new.json',
        'composer.lock',
        'dependency_resolution.php',
        'ilias.ini.php',
        'ilias_version.php',
        'LICENSE',
        'package-lock.json',
        'package.json',
        'README.md',
        'unzip_test_file.zip',
        '.DS_Store',
        '.buildpath',
        '.project'
    ];

    private const ALLOWED_ROOT_FOLDER_DIRS = [
        '.git',
        '.github',
        '.idea',
        'artifacts',
        'cli',
        'components',
        'Customizing',
        'docs',
        'extern',
        'lang',
        'node_modules',
        'public',
        'scripts',
        'templates',
        'vendor',
        '.settings'
    ];

    protected array $ALLOWED_ROOT_FOLDER_DIRS = [];
    protected array $ALLOWED_ROOT_FOLDER_FILES = [];

    protected function setUp(): void
    {
        $this->ALLOWED_ROOT_FOLDER_DIRS = array_merge(
            self::ALLOWED_ROOT_FOLDER_DIRS,
            explode(",", (string) getenv('ALLOWED_ROOT_FOLDER_DIRS'))
        );
        $this->ALLOWED_ROOT_FOLDER_FILES = array_merge(
            self::ALLOWED_ROOT_FOLDER_FILES,
            explode(",", (string) getenv('ALLOWED_ROOT_FOLDER_FILES'))
        );
    }

    private function getAppRootFolderOrFail(): string
    {
        $app_root_folder = __DIR__ . "/../../../../";

        if (!is_file($app_root_folder . '/ilias_version.php')) {
            $this->fail('Could not determine ILIAS root folder');
        }

        return $app_root_folder;
    }

    public function testAppRootFolderOnlyContainsDefinedFiles(): void
    {
        $found_files = [];
        $iter = new \CallbackFilterIterator(
            new \DirectoryIterator($this->getAppRootFolderOrFail()),
            static function (\DirectoryIterator $file): bool {
                return $file->isFile();
            }
        );
        foreach ($iter as $file) {
            /** @var \DirectoryIterator $file */
            $found_files[] = $file->getBasename();
        }
        sort($found_files);

        $unexpected_files = array_diff($found_files, $this->ALLOWED_ROOT_FOLDER_FILES);

        $this->assertEmpty(
            $unexpected_files,
            sprintf(
                'The following files are not expected in the ILIAS root folder: %s',
                implode(', ', $unexpected_files)
            )
        );
    }

    public function testAppRootFolderOnlyContainsDefinedFolders(): void
    {
        $found_directories = [];
        $iter = new \CallbackFilterIterator(
            new \DirectoryIterator($this->getAppRootFolderOrFail()),
            static function (\DirectoryIterator $file): bool {
                return $file->isDir() && !$file->isDot();
            }
        );
        foreach ($iter as $file) {
            /** @var \DirectoryIterator $file */
            $found_directories[] = $file->getBasename();
        }

        $unexpected_directories = array_diff($found_directories, $this->ALLOWED_ROOT_FOLDER_DIRS);
        sort($unexpected_directories);

        $this->assertEmpty(
            $unexpected_directories,
            sprintf(
                'The following directories are not expected in the ILIAS root folder: %s',
                implode(', ', $unexpected_directories)
            )
        );
    }
}
