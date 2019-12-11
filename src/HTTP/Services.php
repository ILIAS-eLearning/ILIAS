<?php declare(strict_types=1);

namespace ILIAS\HTTP;

use ILIAS\HTTP\Cookies\CookieJar;
use ILIAS\HTTP\Cookies\CookieJarFactoryImpl;
use ILIAS\HTTP\Request\RequestFactoryImpl;
use ILIAS\HTTP\Response\ResponseFactoryImpl;
use ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Services
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services implements GlobalHttpState
{

    /**
     * @var array
     */
    private static $services = [];


    public function wrapper() : WrapperFactory
    {
        return $this->getWithArgument(WrapperFactory::class, $this->raw()->request());
    }


    public function raw() : GlobalHttpState
    {
        if (!$this->has(HTTPServices::class)) {
            $request_factory = new RequestFactoryImpl();
            $response_factory = new ResponseFactoryImpl();
            $cookie_jar_factory = new CookieJarFactoryImpl();
            $response_sender_strategy = new DefaultResponseSenderStrategy();

            return $this->getWithMultipleArguments(HTTPServices::class, [
                $response_sender_strategy,
                $cookie_jar_factory,
                $request_factory,
                $response_factory,
            ]);
        }

        return $this->get(HTTPServices::class);
    }


    /**
     * @inheritDoc
     */
    public function request() : RequestInterface
    {
        // throw new \LogicException("No longer allowed to use RequestInterface directly, please use wrapper() instead.");
        return $this->raw()->request();
    }


    /**
     * @inheritDoc
     */
    public function response() : ResponseInterface
    {
        return $this->raw()->response();
    }


    /**
     * @inheritDoc
     */
    public function cookieJar() : CookieJar
    {
        return $this->raw()->cookieJar();
    }


    /**
     * @inheritDoc
     */
    public function saveRequest(ServerRequestInterface $request) : void
    {
        $this->raw()->saveRequest($request);
    }


    /**
     * @inheritDoc
     */
    public function saveResponse(ResponseInterface $response) : void
    {
        $this->raw()->saveResponse($response);
    }


    /**
     * @inheritDoc
     */
    public function sendResponse() : void
    {
        $this->raw()->sendResponse();
    }


    public function close() : void
    {
        $this->raw()->close();
    }


    /**
     * @param string $class_name
     *
     * @return mixed
     */
    private function get(string $class_name)
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name();
        }

        return self::$services[$class_name];
    }


    /**
     * @param string $class_name
     *
     * @return mixed
     */
    private function getWithArgument(string $class_name, $argument)
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name($argument);
        }

        return self::$services[$class_name];
    }


    /**
     * @param string $class_name
     * @param array  $arguments
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function getWithMultipleArguments(string $class_name, array $arguments)
    {
        if (!$this->has($class_name)) {
            $i = new \ReflectionClass($class_name);

            self::$services[$class_name] = $i->newInstanceArgs($arguments);
        }

        return self::$services[$class_name];
    }


    /**
     * @param string $class_name
     *
     * @return bool
     */
    private function has(string $class_name) : bool
    {
        return isset(self::$services[$class_name]);
    }
}
