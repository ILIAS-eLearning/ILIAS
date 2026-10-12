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

use ILIAS\Environment\Configuration\Installation\ClientIniFile;
use ILIAS\Environment\Configuration\Installation\IliasIniFile;
use PHPUnit\Framework\TestCase;

/**
 * Ini files of older installations lack keys added later; the typed accessors
 * read them as empty, as \ilIniFile::readVariable() did.
 */
final class TypedIniFileTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'ini_typed_');
        file_put_contents(
            $this->file,
            "; <?php exit; ?>\n"
            . "[server]\n"
            . "absolute_path = \"/var/www/ilias\"\n"
            . "\n"
            . "[db]\n"
            . "type = \"innodb\"\n"
        );
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testIliasIniWithoutHttpsSectionReadsAsDisabled(): void
    {
        $ini = new IliasIniFile($this->file);

        self::assertSame('/var/www/ilias', $ini->getAbsolutePath());
        self::assertFalse($ini->isAutoHttpsDetectEnabled());
        self::assertSame('', $ini->getAutoHttpsDetectHeaderName());
        self::assertSame('', $ini->getAutoHttpsDetectHeaderValue());
    }

    public function testClientIniWithoutKeyReadsAsEmpty(): void
    {
        $ini = new ClientIniFile($this->file);

        self::assertSame('innodb', $ini->getDatabaseType());
        self::assertSame('', $ini->getClientName());
        self::assertSame(0, $ini->getRootFolderId());
    }
}
