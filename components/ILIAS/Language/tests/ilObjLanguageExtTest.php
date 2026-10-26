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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ilObjLanguageExtTest extends TestCase
{
    private string $file = '';

    protected function tearDown(): void
    {
        unset($GLOBALS['DIC']);
        if ($this->file !== '' && is_file($this->file)) {
            unlink($this->file);
        }
    }

    /** @return array<string, array{string}> */
    public static function invalidLanguageFiles(): array
    {
        return [
            'header never ends' => ["<!-- language file start -->x\n* @author nobody\n"],
            'entry with too few parts' => ["<!-- language file start -->\nmod#:#only_two\n"],
        ];
    }

    /** raiseError() would exit and skip the clean-up of the caller, so an invalid file has to throw before the DB is touched. */
    #[DataProvider('invalidLanguageFiles')]
    public function testImportLanguageFileThrowsAnIlExceptionForAnInvalidFileBeforeTouchingTheDatabase(string $content): void
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->separator = '#:#';
        $lng->comment_separator = '###';
        $lng->method('txt')->willReturnCallback(static fn(string $topic): string => $topic);
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->never())->method('manipulate');
        $db->expects($this->never())->method('insert');
        $db->expects($this->never())->method('update');
        $db->expects($this->never())->method('query');
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['lng'] = $lng;
        $GLOBALS['DIC']['ilDB'] = $db;
        defined('ILIAS_HTTP_PATH') || define('ILIAS_HTTP_PATH', 'http://ilias.de/');
        defined('ILIAS_VERSION') || define('ILIAS_VERSION', '10.0');
        $this->file = sys_get_temp_dir() . '/lng_ext_test_' . bin2hex(random_bytes(6)) . '.lang';
        file_put_contents($this->file, $content);
        $object = (new ReflectionClass(ilObjLanguageExt::class))->newInstanceWithoutConstructor();

        try {
            $object->importLanguageFile($this->file, 'replace');
            self::fail('an invalid file must not be imported');
        } catch (ilException $e) {
            self::assertStringContainsString('file_not_valid', $e->getMessage());
        }
    }
}
