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

namespace ILIAS\WOPI\Discovery;

use PHPUnit\Framework\TestCase;

final class CreatableSuffixesTest extends TestCase
{
    /**
     * @param list<array{ext: string, target_ext: ?string, app?: string}> $editnew_rows
     */
    private function repositoryWith(array $editnew_rows): ActionDBRepository
    {
        $rows = array_map(
            static fn(array $row): array => [
                'ext' => $row['ext'],
                'target_ext' => $row['target_ext'],
                'app_name' => $row['app'] ?? 'Word',
            ],
            $editnew_rows
        );

        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->once())
           ->method('queryF')
           ->with($this->anything(), ['text'], [ActionTarget::EDIT_NEW->value])
           ->willReturn($this->createStub(\ilDBStatement::class));
        $db->method('fetchAssoc')->willReturnOnConsecutiveCalls(...[...$rows, null]);

        return new ActionDBRepository($db);
    }

    public function testTemplatesCreatingAnotherFileTypeAreLeftOut(): void
    {
        $repository = $this->repositoryWith([
            ['ext' => 'docx', 'target_ext' => null],
            ['ext' => 'dotx', 'target_ext' => 'docx'],
            ['ext' => 'xlsx', 'target_ext' => null],
            ['ext' => 'xltx', 'target_ext' => 'xlsx'],
            ['ext' => 'pdf', 'target_ext' => null],
        ]);

        $this->assertSame(['docx', 'pdf', 'xlsx'], $repository->getCreatableSuffixes());
    }

    public function testSuffixesAreLowercasedSortedAndUnique(): void
    {
        $repository = $this->repositoryWith([
            ['ext' => 'PPTX', 'target_ext' => null],
            ['ext' => 'docx', 'target_ext' => 'DOCX'],
            ['ext' => 'pptx', 'target_ext' => null],
        ]);

        $this->assertSame(['docx', 'pptx'], $repository->getCreatableSuffixes());
    }

    public function testNoEditnewActionsMeansNothingCanBeCreated(): void
    {
        $this->assertSame([], $this->repositoryWith([])->getCreatableSuffixes());
    }

    public function testTemplatesAreOfferedOnlyWhenAskedFor(): void
    {
        $rows = [
            ['ext' => 'docx', 'target_ext' => null],
            ['ext' => 'dotx', 'target_ext' => 'docx'],
            ['ext' => 'xltx', 'target_ext' => 'xlsx', 'app' => 'Excel'],
        ];

        $this->assertSame(['docx', 'dotx', 'xltx'], $this->repositoryWith($rows)->getCreatableSuffixes(true));
        $this->assertSame(['dotx', 'xltx'], $this->repositoryWith($rows)->getTemplateSuffixes());
    }

    public function testAnExtensionOfferedAsRegularFormatIsNoTemplate(): void
    {
        $rows = [
            ['ext' => 'odt', 'target_ext' => 'docx'],
            ['ext' => 'odt', 'target_ext' => null],
        ];

        $this->assertSame(['odt'], $this->repositoryWith($rows)->getCreatableSuffixes());
        $this->assertSame([], $this->repositoryWith($rows)->getTemplateSuffixes());
    }

    public function testFormatsCarryTheAppNameAndAreSortedByIt(): void
    {
        $repository = $this->repositoryWith([
            ['ext' => 'xlsx', 'target_ext' => null, 'app' => 'Excel'],
            ['ext' => 'pptx', 'target_ext' => null, 'app' => 'PowerPoint'],
            ['ext' => 'docx', 'target_ext' => null, 'app' => 'Word'],
            ['ext' => 'xltx', 'target_ext' => 'xlsx', 'app' => 'Excel'],
            ['ext' => 'ods', 'target_ext' => null, 'app' => 'Excel'],
        ]);

        $this->assertSame(
            ['ods' => 'Excel', 'xlsx' => 'Excel', 'pptx' => 'PowerPoint', 'docx' => 'Word'],
            $repository->getCreatableFormats()
        );
    }
}
