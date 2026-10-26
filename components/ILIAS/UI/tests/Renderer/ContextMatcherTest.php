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
 */

declare(strict_types=1);

use ILIAS\UI\Implementation\Render\ContextMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ContextMatcherTest extends TestCase
{
    private ContextMatcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new ContextMatcher();
    }

    public static function directAnyProvider(): array
    {
        return [
            'direct ancestor matches' => [['A', 'B', 'C', 'D'], ['C'], true],
            'direct ancestor among several' => [['A', 'B', 'C', 'D'], ['X', 'C'], true],
            'minimal context' => [['C', 'D'], ['C'], true],
            'indirect ancestor only' => [['A', 'B', 'C', 'D'], ['A'], false],
            'other indirect ancestor only' => [['A', 'B', 'C', 'D'], ['B'], false],
            'current component only' => [['A', 'B', 'C', 'D'], ['D'], false],
            'indirect and direct' => [['A', 'B', 'C', 'D'], ['A', 'C'], true],
            'no match' => [['A', 'B', 'C', 'D'], ['X', 'Y'], false],
            'empty search' => [['A', 'B', 'C', 'D'], [], false],
            'context too small' => [['D'], ['D'], false],
            'empty context' => [[], ['A'], false],
        ];
    }

    #[DataProvider('directAnyProvider')]
    public function testIsDirectDescendantOfAny(array $context, array $search, bool $expected): void
    {
        $this->assertSame($expected, $this->matcher->isDirectDescendantOfAny($context, $search));
    }

    public static function directExactlyProvider(): array
    {
        return [
            'single direct ancestor' => [['A', 'B', 'C', 'D'], ['C'], true],
            'two ancestors in order' => [['A', 'B', 'C', 'D'], ['B', 'C'], true],
            'all ancestors in order' => [['A', 'B', 'C', 'D'], ['A', 'B', 'C'], true],
            'minimal context' => [['C', 'D'], ['C'], true],
            'not contiguous' => [['A', 'B', 'C', 'D'], ['A', 'C'], false],
            'wrong order' => [['A', 'B', 'C', 'D'], ['C', 'B'], false],
            'indirect ancestor only' => [['A', 'B', 'C', 'D'], ['B'], false],
            'current component only' => [['A', 'B', 'C', 'D'], ['D'], false],
            'includes current component' => [['A', 'B', 'C', 'D'], ['C', 'D'], false],
            'search spans whole context' => [['A', 'B', 'C', 'D'], ['A', 'B', 'C', 'D'], false],
            'search longer than context' => [['C', 'D'], ['A', 'B', 'C'], false],
            'empty search' => [['A', 'B', 'C', 'D'], [], false],
            'context too small' => [['D'], ['C'], false],
            'empty context' => [[], ['C'], false],
            'duplicates, sequence at end' => [['A', 'B', 'A', 'B', 'D'], ['A', 'B'], true],
            'duplicates, wrong order at end' => [['A', 'B', 'A', 'B', 'D'], ['B', 'A'], false],
            'sequence only further up' => [['A', 'B', 'X', 'D'], ['A', 'B'], false],
        ];
    }

    #[DataProvider('directExactlyProvider')]
    public function testIsDirectDescendantOf(array $context, array $search, bool $expected): void
    {
        $this->assertSame($expected, $this->matcher->isDirectDescendantOf($context, $search));
    }

    public static function indirectAnyProvider(): array
    {
        return [
            'first indirect ancestor' => [['A', 'B', 'C', 'D'], ['A'], true],
            'second indirect ancestor' => [['A', 'B', 'C', 'D'], ['B'], true],
            'indirect among several' => [['A', 'B', 'C', 'D'], ['X', 'B'], true],
            'indirect and direct' => [['A', 'B', 'C', 'D'], ['A', 'C'], true],
            'direct ancestor only' => [['A', 'B', 'C', 'D'], ['C'], false],
            'current component only' => [['A', 'B', 'C', 'D'], ['D'], false],
            'no match' => [['A', 'B', 'C', 'D'], ['X', 'Y'], false],
            'empty search' => [['A', 'B', 'C', 'D'], [], false],
            'minimal context' => [['A', 'B', 'C'], ['A'], true],
            'minimal context, direct ancestor' => [['A', 'B', 'C'], ['B'], false],
            'context too small' => [['B', 'C'], ['B'], false],
            'single entry' => [['C'], ['C'], false],
            'empty context' => [[], ['A'], false],
        ];
    }

    #[DataProvider('indirectAnyProvider')]
    public function testIsIndirectDescendantOfAny(array $context, array $search, bool $expected): void
    {
        $this->assertSame($expected, $this->matcher->isIndirectDescendantOfAny($context, $search));
    }

    public static function indirectExactlyProvider(): array
    {
        return [
            'first indirect ancestor' => [['A', 'B', 'C', 'D'], ['A'], true],
            'second indirect ancestor' => [['A', 'B', 'C', 'D'], ['B'], true],
            'both indirect ancestors in order' => [['A', 'B', 'C', 'D'], ['A', 'B'], true],
            'both indirect ancestors, wrong order' => [['A', 'B', 'C', 'D'], ['B', 'A'], false],
            'reaches direct ancestor' => [['A', 'B', 'C', 'D'], ['B', 'C'], false],
            'not contiguous' => [['A', 'B', 'C', 'D'], ['A', 'C'], false],
            'direct ancestor only' => [['A', 'B', 'C', 'D'], ['C'], false],
            'current component only' => [['A', 'B', 'C', 'D'], ['D'], false],
            'search longer than indirect ancestors' => [['A', 'B', 'C', 'D'], ['A', 'B', 'C'], false],
            'empty search' => [['A', 'B', 'C', 'D'], [], false],
            'minimal context' => [['A', 'B', 'C'], ['A'], true],
            'minimal context, direct ancestor' => [['A', 'B', 'C'], ['B'], false],
            'context too small' => [['B', 'C'], ['B'], false],
            'single entry' => [['C'], ['C'], false],
            'empty context' => [[], ['A'], false],
            'sequence at start of window' => [['A', 'B', 'X', 'Y', 'D'], ['A', 'B'], true],
            'sequence in the middle' => [['X', 'A', 'B', 'Y', 'Z', 'D'], ['A', 'B'], true],
            'sequence at end of window' => [['X', 'A', 'B', 'C', 'D'], ['A', 'B'], true],
            'sequence straddles window end' => [['X', 'A', 'B', 'C', 'D'], ['B', 'C'], false],
            'duplicates do not count twice' => [['A', 'A', 'X', 'Y', 'Z'], ['A', 'B'], false],
            'duplicate sequence' => [['A', 'A', 'X', 'Y', 'Z'], ['A', 'A'], true],
            'sequence only at direct ancestor' => [['A', 'B', 'C', 'B', 'D'], ['C', 'B'], false],
            'sequence also in indirect ancestors' => [['A', 'B', 'C', 'B', 'D'], ['A', 'B'], true],
            'partial match then full match' => [['A', 'A', 'B', 'X', 'D'], ['A', 'B'], true],
        ];
    }

    #[DataProvider('indirectExactlyProvider')]
    public function testIsIndirectDescendantOf(array $context, array $search, bool $expected): void
    {
        $this->assertSame($expected, $this->matcher->isIndirectDescendantOf($context, $search));
    }
}
