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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class TagInputTest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TagInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFieldFactory();

        $f->tag(
            "label",
            ["lorem", "ipsum", "dolor"],
            "byline"
        );
    }

    public function testRender(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $tags = ["lorem", "ipsum", "dolor",];
        $tag = $f->tag($label, $tags, $byline)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'tag-field-input',
            $label,
            '
            <div class="c-field-tag__wrapper">
                <input id="id_1" name="name_0" class="c-field-tag" value=""/>
            </div>
            ',
            $byline,
            'id_1',
            'id_2'
        );
        $this->assertEquals($expected, $this->render($tag));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $tag = $f->tag('label', [], null)->withNameFrom($this->name_source);

        $this->testWithError($tag);
        $this->testWithNoByline($tag);
        $this->testWithRequired($tag);
        $this->testWithDisabled($tag);
        $this->testWithAdditionalOnloadCodeRendersId($tag);
    }

    public function testValueRequired(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var I\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $raw_value1 = "lorem,ipsum";
        $expected_result = ['lorem', 'ipsum'];
        $tag1 = $tag->withInput(new DefInputData([$name => $raw_value1]));
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isOk());
        $value = $value1->value();
        $this->assertEquals($expected_result, $value);
    }

    public function testEmptyStringAsInputLeadToException(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var I\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $tag2 = $tag->withInput(new DefInputData([$name => '']));
        $result = $tag2->getContent();
        $this->assertFalse($result->isOk());
        try {
            $result->value();
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf('ILIAS\Data\NotOKException', $e);
        }
    }

    public function testStringAsInputAsRequired(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];
        /** @var I\Input\Field\Tag $tag */
        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);

        $tag2 = $tag->withInput(new DefInputData([$name => 'test']));
        $result = $tag2->getContent();
        $this->assertTrue($result->isOk());
        $this->assertEquals(['test'], $result->value());
    }

    public function testNullValueLeadsToException(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $name = "name_0";
        $tags = ["lorem", "ipsum", "dolor",];

        $tag = $f->tag($label, $tags)->withNameFrom($this->name_source)->withRequired(true);
        $tag2 = $tag->withInput(new DefInputData([$name => null]));
        $value2 = $tag2->getContent();
        $this->assertTrue($value2->isError());
    }

    public function testUserCreatedNotAllowed(): void
    {
        $this->markTestSkipped("This is supposed to work, but currently does not.");

        $f = $this->getFieldFactory();
        $tags = ["lorem", "ipsum", "dolor",];
        $tag = $f->tag("label", $tags)->withUserCreatedTagsAllowed(false)->withNameFrom($this->name_source);

        $tag1 = $tag->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum"]
            )
        );
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isOk());
        $value = $value1->value();
        $this->assertEquals(
            ["lorem", "ipsum"],
            $value
        );

        $tag1 = $tag->withInput(
            new DefInputData(
                ["name_0" => "conseptetuer,ipsum"]
            )
        );
        $value1 = $tag1->getContent();
        $this->assertTrue($value1->isError());
    }

    public function testMaxTagsOk(): void
    {
        $f = $this->getFieldFactory();

        $tag = $f->tag("label", [])->withMaxTags(3)->withNameFrom($this->name_source)->withInput(
            new DefInputData(["name_0" => "lorem,ipsum"])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }

    public function testMaxTagsNotOk(): void
    {
        $f = $this->getFieldFactory();

        $this->expectException(InvalidArgumentException::class);
        $f->tag("label", [])->withMaxTags(2)->withNameFrom($this->name_source)->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum,dolor"]
            )
        );
    }

    public function testMaxTaglengthTagsOk(): void
    {
        $f = $this->getFieldFactory();

        $tag = $f->tag("label", [])->withTagMaxLength(10)->withNameFrom($this->name_source)->withInput(
            new DefInputData(["name_0" => "lorem,ipsum"])
        );
        $value = $tag->getContent();
        $this->assertTrue($value->isOk());
    }

    public function testMaxTaglengthTagsNotOk(): void
    {
        $f = $this->getFieldFactory();

        $this->expectException(InvalidArgumentException::class);
        $f->tag("label", [])->withTagMaxLength(2)->withNameFrom($this->name_source)->withInput(
            new DefInputData(
                ["name_0" => "lorem,ipsum,dolor"]
            )
        );
    }
}
