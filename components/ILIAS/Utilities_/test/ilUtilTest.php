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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\String\Group as StringGroup;
use ILIAS\Refinery\Transformation;

class ilUtilTest extends TestCase
{
    /**
     * ilUtil::makeClickable must call the refinery transformation make clickable.
     */
    public function testMakeClickableWithoutGotoLinks(): void
    {
        $input = 'Small things make base men proud.';
        $expected = 'I do desire we may be better strangers.';

        $GLOBALS['DIC'] = $this->mockClickableCall($input, $expected);

        $this->assertSame($expected, ilUtil::makeClickable($input));

        unset($GLOBALS['DIC']);
    }

    /**
     * @dataProvider provideGotoLinkData
     */
    public function testMakeClickableWithGotoLinksAndInvalidRefId(string $expected, string $input, array $ref_to_obj, array $obj_to_title): void
    {
        $wrap_array = static fn (array $array): array => (
            array_map(static fn (int $x): array => [$x], $array)
        );

        $container = $this->mockClickableCall($input, $expected);

        $cache = $this->getMockBuilder(ilObjectDataCache::class)->disableOriginalConstructor()->getMock();

        $cache->expects(self::exactly(count($ref_to_obj)))
              ->method('lookupObjId')
              ->withConsecutive(...$wrap_array(array_keys($ref_to_obj)))
              ->willReturnOnConsecutiveCalls(...array_values($ref_to_obj));

        $cache->expects(self::exactly(count($obj_to_title)))
              ->method('lookupTitle')
              ->withConsecutive(...$wrap_array(array_keys($obj_to_title)))
              ->willReturnOnConsecutiveCalls(...array_values($obj_to_title));

        $container->expects(self::exactly(count($ref_to_obj)))->method('offsetGet')->with('ilObjDataCache')->willReturn($cache);

        $GLOBALS['DIC'] = $container;

        $this->assertSame($expected, ilUtil::makeClickable($input, true));

        unset($GLOBALS['DIC']);
    }

    public function provideGotoLinkData(): array
    {
        // Please note that these test cases represent the current state, not necessarily the correct state.
        // For example all anchor attributes are REMOVED and the target is ALWAYS set to target="_self".

        $tests = [
            'Test with empty string.' => ['', '', [], []],
            'Test with correct link and target = _self is added.' => [
                'A link to <a href="%scrs_345%s" target="_self">a course</a>.',
                'A link to <a href="%scrs_345%s">somewhere</a>.',
                [345 => 5],
                [5 => 'a course'],
            ],
            'Test with multiple correct links.' => [
                'A link to <a href="%scrs_345%s" target="_self">a course</a> and to <a href="%scrs_87%s" target="_self">another course</a>.',
                'A link to <a href="%scrs_345%s">somewhere</a> and to <a href="%scrs_87%s">somewhere else</a>.',
                [345 => 5, 87 => 45],
                [5 => 'a course', 45 => 'another course'],
            ],
            'Test links with invalid ref id.' => [
                'A link to <a href="%scrs_345%s">somewhere</a>.',
                'A link to <a href="%scrs_345%s">somewhere</a>.',
                [345 => 0],
                [],
            ],
            'The target attribute is REPLACED with _self.' => [
                'A link to <a href="%scrs_345%s" target="_self">some course</a>.',
                'A link to <a target="bogus target" href="%scrs_345%s">somewhere</a>.',
                [345 => 8],
                [8 => 'some course'],
            ],
            'The attributes position does not matter, it is always replaced with target="_self".' => [
                'A link to <a href="%scrs_345%s" target="_self">some course</a>.',
                'A link to <a href="%scrs_345%s" target="bogus target">somewhere</a>.',
                [345 => 8],
                [8 => 'some course'],
            ],
            'All attributes are removed from the link.' => [
                'A link to <a href="%scrs_345%s" target="_self">some course</a>.',
                'A link to <a href="%scrs_345%s" class="very-important-css-class">somewhere</a>.',
                [345 => 8],
                [8 => 'some course'],
            ],
        ];

        $linkFormats = [
            'With goto.php: ' => ['http://localhost/goto.php?target=', ''],
            'With goto_*.html: ' => ['http://localhost/goto_', '.html'],
        ];

        $allTests = [];
        foreach ($linkFormats as $name => $args) {
            foreach ($tests as $key => $array) {
                $allTests[$name . $key] = array_merge([
                    sprintf($array[0], ...$this->repeatForFormat($array[0], $args)),
                    sprintf($array[1], ...$this->repeatForFormat($array[1], $args)),
                ], array_slice($array, 2));
            }
        }

        return $allTests;
    }

    private function mockClickableCall(string $input, string $transformed): Container
    {
        $transformation = $this->getMockBuilder(Transformation::class)->getMock();
        $transformation->expects(self::once())->method('transform')->with($input)->willReturn($transformed);

        $string_group = $this->getMockBuilder(StringGroup::class)->disableOriginalConstructor()->getMock();
        $string_group->expects(self::once())->method('makeClickable')->willReturn($transformation);

        $refinery = $this->getMockBuilder(Refinery::class)->disableOriginalConstructor()->getMock();
        $refinery->expects(self::once())->method('string')->willReturn($string_group);

        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('refinery')->willReturn($refinery);

        return $container;
    }

    private function repeatForFormat(string $format, array $values): array
    {
        return array_merge(
            ...array_fill(0, (count(explode('%s', $format)) - 1) / 2, $values)
        );
    }
}
