<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;
use ILIAS\UI\Implementation\Crawler\Entry as Entry;

class ComponentEntryDescriptionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $empty_description_array = array(
        "purpose" => "",
        "composition" => "",
        "effect" => "",
        "rivals" => array()
    );

    protected $invalid_categories1_array = array(
        "purpose",
        "wrong"
    );
    protected $invalid_categories2_array = array(
        "purpose" => "",
        "wrong" => ""
    );
    protected $invalid_category_item_array = array(
        "purpose" => "Correct",
        "composition" => array("Wrong")
    );
    protected $invalid_category_value_array = array(
        "purpose" => "Correct",
        "rivals" => array(array("wrong"))
    );

    protected $correct_description1_array = array(
        "purpose" => "Purpose Description",
        "composition" => "Composition Description",
        "effect" => "Effect Description",
        "rivals" => array("Element 1" => "Rival 1", "Element 2" => "Rival 2")
    );

    protected $correct_description2_array = array(
        "purpose" => "Purpose Description"
    );

    protected $correct_description2_array_return = array(
        "purpose" => "Purpose Description",
        "composition" => "",
        "effect" => "",
        "rivals" => array()
    );

    /**
     * @var Crawler\EntriesYamlParser
     */
    protected $parser;

    /**
     * @var ProperEntryFixture
     */
    protected $proper_entry;

    protected function setUp()
    {
        $this->parser = new Crawler\EntriesYamlParser();
        $this->proper_entry = new ProperEntryFixture();
    }


    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testEmptyDescription()
    {
        $description = new Entry\ComponentEntryDescription();
        $this->assertEquals($this->empty_description_array, $description->getDescription());

        $description = new Entry\ComponentEntryDescription(array());
        $this->assertEquals($this->empty_description_array, $description->getDescription());
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidDescription()
    {
        try {
            new Entry\ComponentEntryDescription(null);
            new Entry\ComponentEntryDescription("desc1");
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::ARRAY_EXPECTED);
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategories1()
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_categories1_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::INVALID_INDEX);
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategories2()
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_categories2_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::INVALID_INDEX);
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategoryItem()
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_category_item_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::STRING_EXPECTED);
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategoryValue()
    {
        try {
            new Entry\ComponentEntryDescription($this->invalid_category_value_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::STRING_EXPECTED);
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectDescription1()
    {
        $description = new Entry\ComponentEntryDescription($this->correct_description1_array);
        $this->assertEquals($this->correct_description1_array, $description->getDescription());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectDescription2()
    {
        $description = new Entry\ComponentEntryDescription($this->correct_description2_array);
        $this->assertEquals($this->correct_description2_array_return, $description->getDescription());
    }

    public function testParseProperEntryToArray()
    {
        $entry = $this->parser->parseArrayFromFile("tests/UI/Crawler/Fixture/ProperEntry.php")[0];

        $entry["description"]['composition'] = "";
        $entry["description"]['effect'] = "";

        $description = new Entry\ComponentEntryDescription($entry["description"]);

        $this->assertEquals($description->getDescription(), $entry["description"]);
    }
}
