<?php
namespace ILIAS\UI\Implementation\Crawler;
use  ILIAS\UI\Implementation\Crawler\Exception as Ex;
use ILIAS\UI\Implementation\Crawler\Entry as En;
/**
 * This is a wrapper around the actual FactoriesCrawler created in order
 * to make it more suitable for abstract factory testing. Instead of simply 
 * dying with an exception at an invalid factory branch, it will be skipped,
 * after logging the exception. Note that this may also be tested using Crawler
 * 	tests by including an appropriate exception handler.
 */
class FactoriesCrawlerCrashtestdummy extends FactoriesCrawler {
	protected $exception_handler;
	public function __construct(Ex\CrawlerExceptionHandler $exception_handler) {
		$this->exception_handler = $exception_handler;
		parent::__construct();
	}

	/**
	 * @inheritdoc
	 */
	public function crawlFactory($factoryPath, En\ComponentEntry $parent = null,$depth=0) {
		try{
			return parent::crawlFactory($factoryPath,$parent,$depth);
		} catch (Ex\CrawlerException $ex) {
			$this->exception_handler->handleException($ex);
			return new En\ComponentEntries();
		}
	} 
}