<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;
use ILIAS\UI\Implementation\Crawler\Entry as Entry;

class ComponentEntryRulesTest extends PHPUnit_Framework_TestCase
{
    protected $empty_rules_array = array(
        "usage" => array(),
        "composition" => array(),
        "interaction" => array(),
        "wording" => array(),
        "ordering" => array(),
        "style" => array(),
        "responsiveness" => array(),
        "accessibility" => array()
    );
    protected $invalid_categories1_array = array(
        "usage",
        "wrong"
    );
    protected $invalid_categories2_array = array(
        "usage" => array(),
        "wrong" => array()
    );
    protected $invalid_category_item_array = array(
        "usage" => array("Correct"),
        "composition" => "Wrong"
    );
    protected $invalid_category_value_array = array(
        "usage" => array("Correct"),
        "composition" => array(array("wrong"))
    );
    protected $correct_rules1_array = array(
        "usage" => array(1 => "Usage Rule 1", 2 => "Usage Rule 2"),
        "composition" => array(1 => "composition Rule 1", 2 => "composition Rule 2"),
        "interaction" => array(1 => "interaction Rule 1", 2 => "interaction Rule 2"),
        "wording" => array(1 => "wording Rule 1", 2 => "wording Rule 2"),
        "ordering" => array(1 => "ordering Rule 1", 2 => "ordering Rule 2"),
        "style" => array(1 => "style Rule 1", 2 => "style Rule 2"),
        "responsiveness" => array(1 => "responsiveness Rule 1", 2 => "responsiveness Rule 2"),
        "accessibility" => array(1 => "accessibility Rule 1", 2 => "accessibility Rule 2")
    );
    protected $correct_rules2_array = array(
        "usage" => array(1 => "Usage Rule 1", 2 => "Usage Rule 2"),
        "accessibility" => ""
    );
    protected $correct_rules2_array_return = array(
        "usage" => array(1 => "Usage Rule 1", 2 => "Usage Rule 2"),
        "composition" => array(),
        "interaction" => array(),
        "wording" => array(),
        "ordering" => array(),
        "style" => array(),
        "responsiveness" => array(),
        "accessibility" => array()
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
    public function testEmptyRules()
    {
        $rule = new Entry\ComponentEntryRules();
        $this->assertEquals($this->empty_rules_array, $rule->getRules());

        $rule = new Entry\ComponentEntryRules(array());
        $this->assertEquals($this->empty_rules_array, $rule->getRules());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testHasRules()
    {
        $rules = new Entry\ComponentEntryRules($this->empty_rules_array);
        $this->assertFalse($rules->hasRules());
        $rules = new Entry\ComponentEntryRules($this->correct_rules1_array);
        $this->assertTrue($rules->hasRules());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidRules()
    {
        try {
            new Entry\ComponentEntryRules(null);
            new Entry\ComponentEntryRules("rule1");
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
            new Entry\ComponentEntryRules($this->invalid_categories1_array);
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
            new Entry\ComponentEntryRules($this->invalid_categories2_array);
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
            new Entry\ComponentEntryRules($this->invalid_category_item_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::ARRAY_EXPECTED);
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidCategoryValue()
    {
        try {
            new Entry\ComponentEntryRules($this->invalid_category_value_array);
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::STRING_EXPECTED);
        }
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectRules1()
    {
        $rules = new Entry\ComponentEntryRules($this->correct_rules1_array);
        $this->assertEquals($this->correct_rules1_array, $rules->getRules());
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testCorrectRules2()
    {
        $rules = new Entry\ComponentEntryRules($this->correct_rules2_array);
        $this->assertEquals($this->correct_rules2_array_return, $rules->getRules());
    }

    public function testParseProperEntryToArray()
    {
        $entry = $this->parser->parseArrayFromFile("tests/UI/Crawler/Fixture/ProperEntry.php")[0];

        $entry["rules"]['composition'] = array();
        $entry["rules"]['interaction'] = array();
        $entry["rules"]['wording'] = array();
        $entry["rules"]['ordering'] = array();
        $entry["rules"]['responsiveness'] = array();
        $entry["rules"]['accessibility'] = array();

        $rules = new Entry\ComponentEntryRules($entry["rules"]);
        $this->assertEquals($rules->getRules(), $entry["rules"]);
    }
}
