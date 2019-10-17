<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;

class FactoriesCrawlerTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var Crawler\FactoriesCrawler
     */
    protected $crawler;


    protected function setUp()
    {
        $this->crawler = new Crawler\FactoriesCrawler();
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testAccessInvalidEntry()
    {
        try {
            $entries = $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/ComponentsTreeFixture/RootFactory.php");
            $entries->getEntryById("NonExistent")->getChildren();
            $entries->getParentsOfEntry("NonExistent");
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::INVALID_ID);
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testParseValidFile()
    {
        $entries = $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/ComponentsTreeFixture/RootFactory.php");
        $this->assertEquals(6, count($entries));

        $this->assertEquals(2, count($entries->getEntryById("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")->getChildren()));
        $this->assertEquals(3, count($entries->getDescendantsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")));
        $this->assertEquals(1, count($entries->getEntryById("testsUICrawlerFixtureComponentsTreeFixtureComponent2FactoryComponent2")->getChildren()));
        $this->assertEquals(0, count($entries->getParentsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")));
        $this->assertEquals(2, count($entries->getParentsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1component12component121Component121")));
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testLoopFactory()
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/LoopFactory.php");

            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::CRAWL_MAX_NESTING_REACHED);
        }
    }

    /**
     *
     * @throws Crawler\Exception\CrawlerException
     */
    public function testIdenticalNamesFactory()
    {
        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/IdenticalNamesFactory.php");
        $this->assertTrue(true);
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testIdenticalEntriesFactory()
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/IdenticalEntriesFactory.php");
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::DUPLICATE_ENTRY);
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoNamespaceFactory()
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/NoNamespaceFactory.php");
            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT);
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoClosingDescriptionFactory()
    {
        try {
            $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/NoClosingDescriptionFactory.php");

            $this->assertFalse("This should not happen");
        } catch (Crawler\Exception\CrawlerException $e) {
            $this->assertEquals($e->getCode(), Crawler\Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION);
        }
    }
}
