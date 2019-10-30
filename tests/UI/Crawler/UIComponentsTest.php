<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
include_once("tests/UI/Crawler/Fixture/Fixture.php");

use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Tests the actual UI components from src/UI. If no error is thrown, everything should be fine.
 */
class UIComponentsTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var Crawler\FactoriesCrawler
     */
    protected $crawler;

    /**
     * @var Crawler\FactoriesCrawler
     */
    protected $path_to_base_factory = "src/UI/Factory.php";


    protected function setUp()
    {
        $this->crawler = new Crawler\FactoriesCrawler();
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testAllUIComponentsFactoriesForNotThrowingErrors()
    {
        $this->crawler->crawlFactory($this->path_to_base_factory);
        /**
         * This assertion is only reached if all entries have been successfully parsed (no error was thrown)
         */
        $this->assertTrue(true);
    }
}
