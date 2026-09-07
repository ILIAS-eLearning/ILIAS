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

use ILIAS\Environment\Configuration\Ini\IniFileReadRepository;
use ILIAS\Environment\Configuration\Ini\IniFileWriteRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IniFileWriteRepositoryTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'ini_write_');
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testSetThenGet(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $repo->set('server', 'absolute_path', '/var/www');
        self::assertSame('/var/www', $repo->get('server', 'absolute_path'));
    }

    public function testAddAndRemoveSection(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $repo->addSection('tools');
        self::assertTrue($repo->hasSection('tools'));
        $repo->removeSection('tools');
        self::assertFalse($repo->hasSection('tools'));
    }

    public function testRemoveKey(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $repo->set('section', 'key', 'value');
        $repo->remove('section', 'key');
        self::assertFalse($repo->has('section', 'key'));
    }

    public function testPersistRoundTrip(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $repo->set('server', 'absolute_path', '/var/www/ilias');
        $repo->set('clients', 'default', 'default');
        $repo->persist();

        $reloaded = new IniFileReadRepository($this->file);
        self::assertSame('/var/www/ilias', $reloaded->get('server', 'absolute_path'));
        self::assertSame('default', $reloaded->get('clients', 'default'));
    }

    #[DataProvider('specialValues')]
    public function testPersistRoundTripsSpecialCharacters(string $value): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $repo->set('section', 'key', $value);
        $repo->persist();

        $reloaded = new IniFileReadRepository($this->file);
        self::assertSame($value, $reloaded->get('section', 'key'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function specialValues(): array
    {
        return [
            'double quote' => ['pa"ss"word'],
            'backslash' => ['C:\\path\\to\\dir'],
            'quote after backslash' => ['a\\"b'],
            'trailing slash' => ['/var/www/'],
            'equals sign' => ['key=value'],
            'semicolon' => ['value;with;semicolons'],
            'leading and trailing spaces' => ['  spaced  '],
            'empty' => [''],
        ];
    }

    public function testSetRejectsLineFeed(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $this->expectException(InvalidArgumentException::class);
        $repo->set('section', 'key', "line1\nline2");
    }

    public function testSetRejectsCarriageReturn(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $this->expectException(InvalidArgumentException::class);
        $repo->set('section', 'key', "a\rb");
    }

    public function testSetRejectsNulByte(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $this->expectException(InvalidArgumentException::class);
        $repo->set('section', 'key', "a\x00b");
    }

    public function testSetRejectsStructurallyInvalidKey(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $this->expectException(InvalidArgumentException::class);
        $repo->set('section', 'k=bad', 'value');
    }

    public function testSetRejectsInvalidSection(): void
    {
        $repo = new IniFileWriteRepository($this->file);
        $this->expectException(InvalidArgumentException::class);
        $repo->set('bad]section', 'key', 'value');
    }
}
