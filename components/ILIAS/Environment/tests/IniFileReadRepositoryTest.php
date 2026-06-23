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

use ILIAS\Environment\Configuration\Instance\IniFileReadRepository;
use PHPUnit\Framework\TestCase;

final class IniFileReadRepositoryTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'ini_read_');
        file_put_contents(
            $this->file,
            "; <?php exit; ?>\n"
            . "[server]\n"
            . "absolute_path = \"/var/www/ilias\"\n"
            . "timezone = \"Europe/Zurich\"\n"
            . "\n"
            . "[clients]\n"
            . "default = \"default\"\n"
        );
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testGetSectionsReturnsAllSectionNamesInOrder(): void
    {
        $repo = new IniFileReadRepository($this->file);
        self::assertSame(['server', 'clients'], $repo->getSections());
    }

    public function testHasSection(): void
    {
        $repo = new IniFileReadRepository($this->file);
        self::assertTrue($repo->hasSection('server'));
        self::assertFalse($repo->hasSection('missing'));
    }

    public function testGetReturnsTheStoredValue(): void
    {
        $repo = new IniFileReadRepository($this->file);
        self::assertSame('/var/www/ilias', $repo->get('server', 'absolute_path'));
        self::assertSame('Europe/Zurich', $repo->get('server', 'timezone'));
    }

    public function testGetSectionReturnsAllPairs(): void
    {
        $repo = new IniFileReadRepository($this->file);
        self::assertSame(
            ['absolute_path' => '/var/www/ilias', 'timezone' => 'Europe/Zurich'],
            $repo->getSection('server')
        );
    }

    public function testHas(): void
    {
        $repo = new IniFileReadRepository($this->file);
        self::assertTrue($repo->has('clients', 'default'));
        self::assertFalse($repo->has('clients', 'missing'));
        self::assertFalse($repo->has('missing', 'default'));
    }

    public function testGetOnUnknownKeyThrows(): void
    {
        $repo = new IniFileReadRepository($this->file);
        $this->expectException(InvalidArgumentException::class);
        $repo->get('server', 'does_not_exist');
    }

    public function testGetSectionOnUnknownSectionThrows(): void
    {
        $repo = new IniFileReadRepository($this->file);
        $this->expectException(InvalidArgumentException::class);
        $repo->getSection('does_not_exist');
    }

    public function testMissingFileResultsInEmptyRepository(): void
    {
        $repo = new IniFileReadRepository('/this/path/does/not/exist.ini');
        self::assertSame([], $repo->getSections());
    }
}
