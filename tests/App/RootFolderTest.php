<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class RootFolderTest
 * @author Michael Jansen <mjansen@databay.de>
 */
final class RootFolderTest extends TestCase
{
    private const ALLOWED_ROOT_FOLDER_FILES = [
        '.eslintrc.json',
        '.gitignore',
        '.htaccess',
        '.phpunit.result.cache',
        'captainhook.local.json',
        'phpstan.local.neon',
        'phpstan-baseline.neon',
        '.php_cs.cache',
        'calendar.php',
        'captainhook.json',
        'composer.json',
        'composer.lock',
        'confirmReg.php',
        'error.php',
        'favicon.ico',
        'feed.php',
        'goto.php',
        'gs_content.php',
        'ilias.ini.php',
        'ilias.php',
        'index.php',
        'LICENSE',
        'login.php',
        'logout.php',
        'lti.php',
        'openidconnect.php',
        'package-lock.json',
        'package.json',
        'privfeed.php',
        'pwassist.php',
        'README.md',
        'register.php',
        'rootindex.php',
        'saml.php',
        'sessioncheck.php',
        'shib_login.php',
        'shib_logout.php',
        'storeScorm.php',
        'storeScorm2004.php',
        'studip_referrer.php',
        'unzip_test_file.zip',
        'webdav.php',
        '.DS_Store',
        '.buildpath',
        '.project'
    ];

    private const ALLOWED_ROOT_FOLDER_DIRS = [
        '.git',
        '.github',
        '.idea',
        'CI',
        'Customizing',
        'Modules',
        'Services',
        'cron',
        'data',
        'dicto',
        'docs',
        'extern',
        'include',
        'lang',
        'libs',
        'node_modules',
        'setup',
        'src',
        'sso',
        'templates',
        'test',
        'tests',
        'webservice',
        'xml',
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
        $app_root_folder = getcwd();

        for ($i = 0; $i < 20 && !is_file($app_root_folder . '/index.php'); $i++) {
            $app_root_folder .= '/..';
        }

        if (!is_file($app_root_folder . '/index.php')) {
            $this->fail('Could not determine ILIAS root folder');
        }

        return $app_root_folder;
    }

    public function testAppRootFolderOnlyContainsDefinedFiles(): void
    {
        $found_files = [];
        $iter = new CallbackFilterIterator(
            new DirectoryIterator($this->getAppRootFolderOrFail()),
            static function (DirectoryIterator $file): bool {
                return $file->isFile();
            }
        );
        foreach ($iter as $file) {
            /** @var DirectoryIterator $file */
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
        $iter = new CallbackFilterIterator(
            new DirectoryIterator($this->getAppRootFolderOrFail()),
            static function (DirectoryIterator $file): bool {
                return $file->isDir() && !$file->isDot();
            }
        );
        foreach ($iter as $file) {
            /** @var DirectoryIterator $file */
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
