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
 * @see https://mantis.ilias.de/view.php?id=43460
 */
class CopyTitleTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        $this->subject = new class () {
            use ilObjFileSecureString {
                moveCopyInfoInFrontOfSuffix as public;
                ensureSuffix as public;
            }
        };
    }

    public static function titleProvider(): array
    {
        $kopie = '- Kopie (%1$s)';
        return [
            'first copy' => ['Bericht.pdf', 'Bericht.pdf - Kopie', 'pdf', [], $kopie, 'Bericht - Kopie.pdf'],
            'second copy' => [
                'Bericht.pdf',
                'Bericht.pdf - Kopie',
                'pdf',
                ['Bericht.pdf', 'Bericht - Kopie.pdf'],
                $kopie,
                'Bericht - Kopie (2).pdf'
            ],
            'third copy' => [
                'Bericht.pdf',
                'Bericht.pdf - Kopie',
                'pdf',
                ['Bericht.pdf', 'Bericht - Kopie.pdf', 'Bericht - Kopie (2).pdf'],
                $kopie,
                'Bericht - Kopie (3).pdf'
            ],
            'numbered by ilObject' => ['Bericht.pdf', 'Bericht.pdf - Kopie (4)', 'pdf', [], $kopie, 'Bericht - Kopie (4).pdf'],
            'dots in title' => ['v1.2.pdf', 'v1.2.pdf - Kopie', 'pdf', [], $kopie, 'v1.2 - Kopie.pdf'],
            'no suffix in title' => ['Bericht', 'Bericht - Kopie', 'pdf', [], $kopie, 'Bericht - Kopie'],
            'file without suffix, dot in title' => ['Version 1.2', 'Version 1.2 - Kopie', '', [], $kopie, 'Version 1.2 - Kopie'],
            'title suffix differs from file suffix' => ['Report.PDF', 'Report.PDF - Copy', 'pdf', [], $kopie, 'Report.PDF - Copy'],
            'no copy info' => ['Bericht.pdf', 'Bericht.pdf', 'pdf', [], $kopie, 'Bericht.pdf'],
            'translation without placeholder' => [
                'X.pdf',
                'X.pdf Nome',
                'pdf',
                ['X Nome.pdf'],
                'Nome',
                'X (2).pdf'
            ],
            'translation with broken placeholder' => [
                'X.pdf',
                'X.pdf - Kopyala',
                'pdf',
                ['X - Kopyala.pdf'],
                '- Kopyala (% 1 $ s)',
                'X (2).pdf'
            ],
            'numbering of release 11' => ['X.pdf', 'X.pdf (1)', 'pdf', ['X.pdf', 'X (1).pdf'], '(%d)', 'X (2).pdf'],
        ];
    }

    #[DataProvider('titleProvider')]
    public function testMoveCopyInfoInFrontOfSuffix(
        string $original,
        string $cloned,
        string $suffix,
        array $existing,
        string $copy_n_suffix,
        string $expected
    ): void {
        $title = $this->subject->moveCopyInfoInFrontOfSuffix($original, $cloned, $suffix, $existing, $copy_n_suffix);
        $this->assertSame($expected, $title);
    }

    public function testCopyInfoSurvivesEnsureSuffix(): void
    {
        // ilObjFile::beforeUpdate() applies ensureSuffix() to the title of the copy
        $this->assertSame('Bericht.pdf', $this->subject->ensureSuffix('Bericht.pdf - Kopie', 'pdf'));
        $this->assertSame(
            'Bericht - Kopie.pdf',
            $this->subject->ensureSuffix(
                $this->subject->moveCopyInfoInFrontOfSuffix('Bericht.pdf', 'Bericht.pdf - Kopie', 'pdf', [], '- Kopie (%1$s)'),
                'pdf'
            )
        );
    }
}
