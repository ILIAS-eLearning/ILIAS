<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;


class CrawlerTest extends PHPUnit_Framework_TestCase {


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
    public function testParseInvalidFile() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::INVALID_FILE_PATH);

        $this->parser->parseYamlStringArrayFromFile("Invalid Path");
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testParseProperEntryToYamlEntries() {
        $yaml_entries = $this->parser->parseYamlStringArrayFromFile("tests/UI/Crawler/Fixture/ProperEntry.php");

        $this->assertEquals($this->proper_entry->properEntryYamlString,$yaml_entries[0]);
        $this->assertEquals($this->proper_entry->properEntryYamlString,$yaml_entries[0]);
    }

    public function testParseProperEntryToArray() {
        $entries = $this->parser->parseArrayFromFile("tests/UI/Crawler/Fixture/ProperEntry.php");
        $this->assertEquals($this->proper_entry->properEntryYamlArray,$entries);
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoDescriptionEntry() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION);

        $this->parser->parseYamlStringArrayFromFile("tests/UI/Crawler/Fixture/NoDescriptionEntry.php");
    }
    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoReturnValueEntry() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT);

        $this->parser->parseYamlStringArrayFromFile("tests/UI/Crawler/Fixture/NoReturnValueEntry.php");
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testInvalidYamlEntry() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::PARSING_YAML_ENTRY_FAILED);

        $this->parser->parseArrayFromFile("tests/UI/Crawler/Fixture/InvalidYamlEntry.php");
    }

    public function testCamelCase() {
        $test_string = "Hello Camel Case";

        $this->assertEquals("helloCamelCase",Crawler\EntriesYamlParser::toLowerCamelCase($test_string, ' '));
        $this->assertEquals("HelloCamelCase",Crawler\EntriesYamlParser::toUpperCamelCase($test_string, ' '));

    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testGenerateEntry() {
        $entries = $this->parser->parseEntriesFromFile("tests/UI/Crawler/Fixture/ProperEntry.php");

        $this->assertEquals(1,count($entries));
        $this->assertEquals("CrawlerFixtureProperEntryProperEntry",$entries->getEntryById("CrawlerFixtureProperEntryProperEntry")->getId());
        $this->assertEquals("src/UI/Crawler/Fixture/ProperEntry",$entries->getEntryById("CrawlerFixtureProperEntryProperEntry")->getPath());
    }
}
