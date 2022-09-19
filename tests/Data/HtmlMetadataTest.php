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

use PHPUnit\Framework\TestCase;
use ILIAS\Data\Meta\Html\Factory;
use ILIAS\Data\Meta\Html\TagCollection;
use ILIAS\Data\Meta\Html\UserDefined;
use ILIAS\Data\Meta\Html\NullTag;
use ILIAS\Data\Meta\Html\Tag;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class HtmlMetadataTest extends TestCase
{
    protected Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Factory();
    }

    public function testNullTag(): void
    {
        $null_tag = $this->factory->nullTag();

        $this->assertInstanceOf(NullTag::class, $null_tag);
        $this->assertEmpty($null_tag->toHtml());
    }

    public function testTagCollection(): void
    {
        $test_tag_1_html = 'test_tag_1_html';
        $test_tag_1 = $this->getMockedTag($test_tag_1_html);

        $test_tag_2_html = 'test_tag_2_html';
        $test_tag_2 = $this->getMockedTag($test_tag_2_html);

        $expected_html = $test_tag_1_html . PHP_EOL . $test_tag_2_html . PHP_EOL;

        $tag_collection = $this->factory->collection([$test_tag_1, $test_tag_2]);

        $this->assertCount(2, iterator_to_array($tag_collection->getTags()));
        $this->assertEquals($expected_html, $tag_collection->toHtml());
    }

    public function testEmptyTagCollection(): void
    {
        $tag_collection = $this->factory->collection([]);

        $this->assertEmpty(iterator_to_array($tag_collection->getTags()));
        $this->assertEmpty($tag_collection->toHtml());
    }

    public function testNestedTagCollection(): void
    {
        $test_tag_1_html = 'test_tag_1_html';
        $test_tag_1 = $this->getMockedTag($test_tag_1_html);

        $test_tag_2_html = 'test_tag_2_html';
        $test_tag_2 = $this->getMockedTag($test_tag_2_html);

        $test_tag_3_html = 'test_tag_3_html';
        $test_tag_3 = $this->getMockedTag($test_tag_3_html);

        $expected_html = $test_tag_1_html . PHP_EOL . $test_tag_2_html . PHP_EOL . $test_tag_3_html . PHP_EOL;

        $tag_collection = $this->factory->collection([
            $this->factory->collection([$test_tag_1, $test_tag_2]),
            $test_tag_3,
        ]);

        $this->assertCount(3, iterator_to_array($tag_collection->getTags()));
        $this->assertEquals($expected_html, $tag_collection->toHtml());
    }

    public function testUserDefinedTag(): void
    {
        $key = 'expected_key';
        $val = 'expected_value';

        $user_defined_tag = $this->factory->userDefined($key, $val);

        $this->assertInstanceOf(UserDefined::class, $user_defined_tag);
        $this->assertEquals(
            "<meta name=\"$key\" content=\"$val\" />",
            $user_defined_tag->toHtml()
        );
    }

    public function getMockedTag(string $html): Tag
    {
        $tag_mock = $this->createMock(Tag::class);
        $tag_mock->method('toHtml')->willReturn($html);

        return $tag_mock;
    }
}
