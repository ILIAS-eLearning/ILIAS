<?php
require_once("libs/composer/vendor/autoload.php");
require_once("tests/UI/Crawler/FactoriesCrawlerTest.php");
use ILIAS\UI\Implementation\Crawler\Exception as Ex;
use ILIAS\UI\Implementation\Crawler as Crawler;

class FactoriesCrawlerCrashtestdummyTest extends FactoriesCrawlerTest {
	protected function setUp(){
        $this->crawler = new Crawler\FactoriesCrawlerCrashtestdummy(new Ex\CrawlerExceptionThrower());
    }
}