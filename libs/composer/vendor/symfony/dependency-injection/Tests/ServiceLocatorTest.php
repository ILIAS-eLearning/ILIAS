<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Tests;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\Test\ServiceLocatorTest as BaseServiceLocatorTest;

class ServiceLocatorTest extends BaseServiceLocatorTest
{
    public function getServiceLocator(array $factories)
    {
        return new ServiceLocator($factories);
    }

    public function testGetThrowsOnUndefinedService()
    {
        $this->expectException('Psr\Container\NotFoundExceptionInterface');
        $this->expectExceptionMessage('Service "dummy" not found: the container inside "Symfony\Component\DependencyInjection\Tests\ServiceLocatorTest" is a smaller service locator that only knows about the "foo" and "bar" services.');
        $locator = $this->getServiceLocator([
            'foo' => function () { return 'bar'; },
            'bar' => function () { return 'baz'; },
        ]);

        $locator->get('dummy');
    }

    public function testThrowsOnCircularReference()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException');
        $this->expectExceptionMessage('Circular reference detected for service "bar", path: "bar -> baz -> bar".');
        parent::testThrowsOnCircularReference();
    }

    public function testThrowsInServiceSubscriber()
    {
        $this->expectException('Psr\Container\NotFoundExceptionInterface');
        $this->expectExceptionMessage('Service "foo" not found: even though it exists in the app\'s container, the container inside "caller" is a smaller service locator that only knows about the "bar" service. Unless you need extra laziness, try using dependency injection instead. Otherwise, you need to declare it using "SomeServiceSubscriber::getSubscribedServices()".');
        $container = new Container();
        $container->set('foo', new \stdClass());
        $subscriber = new SomeServiceSubscriber();
        $subscriber->container = $this->getServiceLocator(['bar' => function () {}]);
        $subscriber->container = $subscriber->container->withContext('caller', $container);

        $subscriber->getFoo();
    }

    public function testGetThrowsServiceNotFoundException()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');
        $this->expectExceptionMessage('Service "foo" not found: even though it exists in the app\'s container, the container inside "foo" is a smaller service locator that is empty... Try using dependency injection instead.');
        $container = new Container();
        $container->set('foo', new \stdClass());

        $locator = new ServiceLocator([]);
        $locator = $locator->withContext('foo', $container);
        $locator->get('foo');
    }

    public function testInvoke()
    {
        $locator = $this->getServiceLocator([
            'foo' => function () { return 'bar'; },
            'bar' => function () { return 'baz'; },
        ]);

        $this->assertSame('bar', $locator('foo'));
        $this->assertSame('baz', $locator('bar'));
        $this->assertNull($locator('dummy'), '->__invoke() should return null on invalid service');
    }

    public function testProvidesServicesInformation()
    {
        $locator = new ServiceLocator([
            'foo' => function () { return 'bar'; },
            'bar' => function (): string { return 'baz'; },
            'baz' => function (): ?string { return 'zaz'; },
        ]);

        $this->assertSame($locator->getProvidedServices(), [
            'foo' => '?',
            'bar' => 'string',
            'baz' => '?string',
        ]);
    }
}

class SomeServiceSubscriber implements ServiceSubscriberInterface
{
    public $container;

    public function getFoo()
    {
        return $this->container->get('foo');
    }

    public static function getSubscribedServices()
    {
        return ['bar' => 'stdClass'];
    }
}
