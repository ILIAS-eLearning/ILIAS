<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;


class FactoriesCrawlerTest extends PHPUnit_Framework_TestCase {


    /**
     * @var Crawler\FactoriesCrawler
     */
    protected $crawler;


    protected function setUp(){
        $this->crawler = new Crawler\FactoriesCrawler();
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testAccessInvalidEntry() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::INVALID_ID);

        $entries = $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/ComponentsTreeFixture/RootFactory.php");
        $entries->getEntryById("NonExistent")->getChildren();
        $entries->getParentsOfEntry("NonExistent");


    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testParseValidFile() {
        $entries = $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/ComponentsTreeFixture/RootFactory.php");
        $this->assertEquals(6,count($entries));

        $this->assertEquals(2,count($entries->getEntryById("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")->getChildren()));
        $this->assertEquals(3,count($entries->getDescendantsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")));
        $this->assertEquals(1,count($entries->getEntryById("testsUICrawlerFixtureComponentsTreeFixtureComponent2FactoryComponent2")->getChildren()));
        $this->assertEquals(0,count($entries->getParentsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1FactoryComponent1")));
        $this->assertEquals(2,count($entries->getParentsOfEntry("testsUICrawlerFixtureComponentsTreeFixtureComponent1component12component121Component121")));

    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testLoopFactory() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::CRAWL_MAX_NESTING_REACHED);

        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/LoopFactory.php");
    }

    /**
     *
     * @throws Crawler\Exception\CrawlerException
     */
    public function testIdenticalNamesFactory() {
        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/IdenticalNamesFactory.php");
        $this->assertTrue(true);
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testIdenticalEntriesFactory() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::DUPLICATE_ENTRY);
        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/IdenticalEntriesFactory.php");
        $this->assertTrue(true);
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoNamespaceFactory() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT);

        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/NoNamespaceFactory.php");
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testNoClosingDescriptionFactory() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION);

        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/NoClosingDescriptionFactory.php");
    }
}
