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

        $this->assertEquals(2,count($entries->getEntryById("abstractComponent1")->getChildren()));
        $this->assertEquals(3,count($entries->getDescendantsOfEntry("abstractComponent1")));
        $this->assertEquals(1,count($entries->getEntryById("abstractComponent2")->getChildren()));
        $this->assertEquals(0,count($entries->getParentsOfEntry("abstractComponent1")));
        $this->assertEquals(2,count($entries->getParentsOfEntry("non-AbstractComponent1.2.1")));

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
     * @throws Crawler\Exception\CrawlerException
     */
    public function testDuplicateFactory() {
        $this->expectException(Crawler\Exception\CrawlerException::class);
        $this->expectExceptionCode(Crawler\Exception\CrawlerException::DUPLICATE_ENTRY);

        $this->crawler->crawlFactory("tests/UI/Crawler/Fixture/DuplicateFactory.php");
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
