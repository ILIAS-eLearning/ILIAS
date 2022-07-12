<?php declare(strict_types=1);

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
 
require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;
use ILIAS\UI\Implementation\Crawler\Entry as Entry;
use PHPUnit\Framework\TestCase;

class ComponentEntryDescriptionTest extends TestCase
{
    protected array $empty_description_array = [
        "purpose" => "",
        "composition" => "",
        "effect" => "",
        "rivals" => []
    ];

    protected array $invalid_categories1_array = [
        "purpose",
        "wrong"
    ];

    protected array $invalid_categories2_array = [
        "purpose" => "",
        "wrong" => ""
    ];

    protected array $invalid_category_item_array = [
        "purpose" => "Correct",
        "composition" => ["Wrong"]
    ];

    protected array $invalid_category_value_array = [
        "purpose" => "Correct",
        "rivals" => [["wrong"]]
    ];

    protected array $correct_description1_array = [
        "purpose" => "Purpose Description",
        "composition" => "Composition Description",
        "effect" => "Effect Description",
        "rivals" => ["Element 1" => "Rival 1", "Element 2" => "Rival 2"]
    ];

    protected array $correct_description2_array = [
        "purpose" => "Purpose Description"
    ];

    protected array $correct_description2_array_return = [
        "purpose" => "Purpose Description",
        "composition" => "",
        "effect" => "",
        "rivals" => []
    ];

    protected Crawler\EntriesYamlParser $parser;
    protected ProperEntryFixture $proper_entry;

    protected function setUp() : void
    {
        $this->parser = new Crawler\EntriesYamlParser();
        $this->proper_entry = new ProperEntryFixture();
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testEmptyDescription() : void
    {
        $description = new Entry\ComponentEntryDescription();
        $this->assertEquals($this->empty_description_array, $description->getDescription());

        $description = new Entry\ComponentEntryDescription(array());
        $this->assertEquals($this->empty_description_array, $description->getDescription());
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidDescription() : void
    {
        $this->expectException(TypeError::class);
        new Entry\ComponentEntryDescription(null);

        $this->expectException(TypeError::class);
        new Entry\ComponentEntryDescription('desc1');
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategories1() : void
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_categories1_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::INVALID_INDEX, $e->getCode());
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategories2() : void
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_categories2_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::INVALID_INDEX, $e->getCode());
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategoryItem() : void
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_category_item_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::STRING_EXPECTED, $e->getCode());
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategoryValue() : void
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_category_value_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals(Crawler\Exception\CrawlerException::STRING_EXPECTED, $e->getCode());
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectDescription1() : void
    {
        $description = new Entry\ComponentEntryDescription($this->correct_description1_array);
        $this->assertEquals($this->correct_description1_array, $description->getDescription());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectDescription2() : void
    {
        $description = new Entry\ComponentEntryDescription($this->correct_description2_array);
        $this->assertEquals($this->correct_description2_array_return, $description->getDescription());
    }

    public function testParseProperEntryToArray() : void
    {
        $entry = $this->parser->parseArrayFromFile("tests/UI/Crawler/Fixture/ProperEntry.php")[0];

        $entry["description"]['composition'] = "";
        $entry["description"]['effect'] = "";

        $description = new Entry\ComponentEntryDescription($entry["description"]);

        $this->assertEquals($description->getDescription(), $entry["description"]);
    }
}
