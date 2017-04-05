<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides an interface to the ILIAS HTTP services.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class HTTPServices implements GlobalHttpState {

	/**
	 * @var    Container
	 */
	protected $container;
	/**
	 * @var ResponseSenderStrategy
	 */
	private $sender;

    /**
     * @var CookieJarFactory $cookieJarFactory
     */
	private $cookieJarFactory;


    /**
     * HTTPServices constructor.
     *
     * @param \ILIAS\DI\Container    $container         The ILIAS DIC.
     * @param ResponseSenderStrategy $senderStrategy    A response sender strategy.
     * @param CookieJarFactory       $cookieJarFactory  Cookie Jar implementation.
     */
	public function __construct(Container $container, ResponseSenderStrategy $senderStrategy, CookieJarFactory $cookieJarFactory)
	{
		$this->container = $container;
		$this->sender = $senderStrategy;
		$this->$cookieJarFactory = $cookieJarFactory;
	}


	/**
	 * Creates a new cookie jar from the current known request.
	 *
	 * @return \ILIAS\HTTP\Cookies\CookieJar
	 */
	public function cookieJar()
	{
		return $this->cookieJarFactory->fromResponse($this->response());
	}


	/**
	 * @inheritDoc
	 */
	public function request()
	{
		return $this->container["http.request"];
	}


	/**
	 * @inheritDoc
	 */
	public function response()
	{
		return $this->container["http.response"];
	}


	/**
	 * @inheritDoc
	 */
	public function saveRequest(ServerRequestInterface $request)
	{
		$this->container["http.request"] = $request;
	}


	/**
	 * @inheritDoc
	 */
	public function saveResponse(ResponseInterface $response)
	{
		$this->container["http.response"] = $response;
	}


	/**
	 * @inheritDoc
	 */
	public function renderResponse()
	{
		$this->sender->sendResponse($this->response());
	}
}
