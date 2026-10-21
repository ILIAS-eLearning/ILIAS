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

/**
 * @see https://mantis.ilias.de/view.php?id=47801
 */
class FileTitleSuffixTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        $this->subject = new class () {
            use ilObjFileSecureString {
                extractSuffixFromFilename as public;
                ensureSuffix as public;
                ensureSuffixForDelivery as public;
            }
        };
    }

    public static function suffixProvider(): array
    {
        return [
            'plain' => ['name.pdf', 'pdf'],
            'space in front of suffix' => ['name .pdf', 'pdf'],
            'several spaces in front of suffix' => ['Beipackzettel_10   .pdf', 'pdf'],
            'inner space' => ['a b.pdf', 'pdf'],
            'double suffix' => ['archive.tar.gz', 'gz'],
            'no suffix' => ['name', ''],
        ];
    }

    #[DataProvider('suffixProvider')]
    public function testExtractSuffixFromFilename(string $filename, string $expected): void
    {
        $this->assertSame($expected, $this->subject->extractSuffixFromFilename($filename));
    }

    public static function titleProvider(): array
    {
        return [
            'title with suffix' => ['name.pdf', 'pdf', 'name.pdf'],
            'title without suffix' => ['name', 'pdf', 'name.pdf'],
            'space in front of suffix' => ['name .pdf', 'pdf', 'name.pdf'],
            'several spaces in front of suffix' => ['Beipackzettel_10   .pdf', 'pdf', 'Beipackzettel_10.pdf'],
            'trailing spaces without suffix' => ['Beipackzettel_10   ', 'pdf', 'Beipackzettel_10.pdf'],
            'keeps inner spaces' => ['a  b.pdf', 'pdf', 'a  b.pdf'],
        ];
    }

    #[DataProvider('titleProvider')]
    public function testEnsureSuffix(string $title, string $suffix, string $expected): void
    {
        $this->assertSame($expected, $this->subject->ensureSuffix($title, $suffix));
    }

    public static function deliveryProvider(): array
    {
        return [
            'title with suffix' => ['name.pdf', 'pdf', 'name.pdf'],
            'suffix in other case' => ['name.PDF', 'pdf', 'name.PDF'],
            'title which lost its suffix' => ['name ', 'pdf', 'name.pdf'],
            'title which lost its suffix, several spaces' => ['Beipackzettel_10   ', 'pdf', 'Beipackzettel_10.pdf'],
            'file without suffix' => ['name', '', 'name'],
            'not html encoded' => ['A & B', 'pdf', 'A & B.pdf'],
        ];
    }

    #[DataProvider('deliveryProvider')]
    public function testEnsureSuffixForDelivery(string $title, string $suffix, string $expected): void
    {
        $this->assertSame($expected, $this->subject->ensureSuffixForDelivery($title, $suffix));
    }
}
