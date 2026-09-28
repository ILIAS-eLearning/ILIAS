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

namespace ILIAS\Filesystem\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use ILIAS\Filesystem\Util\Archive\Options;
use ILIAS\Filesystem\Util\Archive\PathHelper;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PathHelperTest extends TestCase
{
    #[DataProvider('getPathsForDefaultSnippets')]
    public function testDefaultSnippets(string $path, bool $is_ignored): void
    {
        $this->assertSame($is_ignored, $this->isPathIgnored($path, new UnzipOptions()));
    }

    #[DataProvider('getPathsForRegexSnippets')]
    public function testSnippetsAreMatchedLiterally(string $snippet, string $path, bool $is_ignored): void
    {
        $this->assertSame($is_ignored, $this->isPathIgnored($path, $this->optionsWithSnippets($snippet)));
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function getPathsForDefaultSnippets(): array
    {
        return [
            'macOS metadata file' => ['./dir/.DS_Store', true],
            'macOS resource fork directory' => ['./dir/__MACOSX/resource', true],
            // the dot of the '.DS_' snippet must not act as a wildcard, see https://mantis.ilias.de/view.php?id=48382
            'file name containing DS_' => ['./dir/BADS_report.pdf', false],
            'file name starting with DS_' => ['./DS_report.pdf', false],
            'unrelated file' => ['./dir/lecture.pdf', false],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function getPathsForRegexSnippets(): array
    {
        return [
            'dot is no wildcard' => ['a.c', './abc', false],
            'dot matches itself' => ['a.c', './a.c', true],
            'quantifier is no repetition' => ['a+', './aaa', false],
            'quantifier matches itself' => ['a+', './a+b', true],
            'alternation is no alternation' => ['a|b', './a', false],
            'alternation matches itself' => ['a|b', './a|b', true],
            'parentheses do not break the pattern' => ['(x)', './(x)', true],
        ];
    }

    private function isPathIgnored(string $path, Options $options): bool
    {
        $consumer = new class () {
            use PathHelper;

            public function isIgnored(string $path, Options $options): bool
            {
                return $this->isPathIgnored($path, $options);
            }
        };

        return $consumer->isIgnored($path, $options);
    }

    private function optionsWithSnippets(string ...$snippets): Options
    {
        return new class ($snippets) extends Options {
            /**
             * @param string[] $snippets
             */
            public function __construct(private array $snippets)
            {
            }

            public function getIgnoredPathSnippets(): array
            {
                return $this->snippets;
            }
        };
    }
}
