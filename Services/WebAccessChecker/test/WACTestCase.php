<?php

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class WACTestCase
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
abstract class WACTestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		parent::setUp();

		//set remote address to localhost
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		//init request and response handling
		$container = new \ILIAS\DI\Container();
		$container["http"] = new \ILIAS\DI\HTTPServices($container, new \ILIAS\HTTP\Response\rendering\NullResponseRenderingStrategy());
		$container["http.request"] = \ILIAS\HTTP\Request\RequestFactory::create();
		$container["http.response"] = \ILIAS\HTTP\Response\ResponseFactory::create();

		$GLOBALS["DIC"] = $container;
	}


	/**
	 * @return \ILIAS\HTTP\Factory
	 */
	public function http()
	{
		return $GLOBALS["DIC"]->http();
	}


	/**
	 * Current request.
	 *
	 * @return \Psr\Http\Message\ServerRequestInterface
	 */
	public function request()
	{
		return $GLOBALS["DIC"]->http()->request();
	}


	/**
	 * Current response.
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function response()
	{
		return $GLOBALS["DIC"]->http()->response();
	}


	/**
	 * Cookie jar with the cookies of the current response.
	 *
	 * @return \ILIAS\HTTP\Cookies\CookieJar
	 */
	public function cookieJar()
	{
		return $GLOBALS["DIC"]->http()->cookieJar();
	}
}