<?php declare(strict_types=1);

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
        'objects.dtd',
        'objects.xml',
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
    ];

    public function testAppRootFolderOnlyContainsDefinedFiles() : void
    {
        $app_root_folder = getcwd();

        for ($i = 0; $i < 20 && !file_exists($app_root_folder . '/index.php'); $i++) {
            $app_root_folder = $app_root_folder . '/..';
        }

        if (!file_exists($app_root_folder . '/index.php')) {
            $this->fail('Could not determine ILIAS root folder');
        }

        $found_files = [];
        $iter = new CallbackFilterIterator(
            new DirectoryIterator($app_root_folder),
            static function (SplFileInfo $file) : bool {
                return $file->isFile();
            }
        );
        foreach ($iter as $file) {
            /** @var SplFileInfo $file */
            $found_files[] = $file->getBasename();
        }

        $unexpected_files = array_diff($found_files, self::ALLOWED_ROOT_FOLDER_FILES);

        $this->assertEmpty(
            $unexpected_files,
            sprintf(
                'The following files are not expected in the ILIAS root folder: %s',
                implode(', ', $unexpected_files)
            )
        );
    }
}
