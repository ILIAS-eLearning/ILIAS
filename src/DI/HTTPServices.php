<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ILIAS\HTTP\Cookies\CookieJarWrapper;
use ILIAS\HTTP\Factory;
use ILIAS\HTTP\Response\rendering\ResponseRenderingStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides an interface to the ILIAS HTTP services.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class HTTPServices implements Factory {

	/**
	 * @var    Container
	 */
	protected $container;
	/**
	 * @var ResponseRenderingStrategy
	 */
	private $render;


	/**
	 * HTTPServices constructor.
	 *
	 * @param \ILIAS\DI\Container $container
	 * @param ResponseRenderingStrategy $renderingStrategy
	 */
	public function __construct(Container $container, ResponseRenderingStrategy $renderingStrategy)
	{
		$this->container = $container;
		$this->render = $renderingStrategy;
	}


	/**
	 * Creates a new cookie jar from the current known request.
	 *
	 * @return \ILIAS\HTTP\Cookies\CookieJar
	 */
	public function cookieJar()
	{
		return CookieJarWrapper::fromResponse($this->response());
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
		$this->render->renderResponse($this->response());
	}
}
