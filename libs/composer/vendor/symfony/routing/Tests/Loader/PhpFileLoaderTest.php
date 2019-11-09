<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Tests\Loader;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class PhpFileLoaderTest extends TestCase
{
    public function testSupports()
    {
        $loader = new PhpFileLoader($this->getMockBuilder('Symfony\Component\Config\FileLocator')->getMock());

        $this->assertTrue($loader->supports('foo.php'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.php', 'php'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.php', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadWithRoute()
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validpattern.php');
        $routes = $routeCollection->all();

        $this->assertCount(1, $routes, 'One route is loaded');
        $this->assertContainsOnly('Symfony\Component\Routing\Route', $routes);

        foreach ($routes as $route) {
            $this->assertSame('/blog/{slug}', $route->getPath());
            $this->assertSame('MyBlogBundle:Blog:show', $route->getDefault('_controller'));
            $this->assertSame('{locale}.example.com', $route->getHost());
            $this->assertSame('RouteCompiler', $route->getOption('compiler_class'));
            $this->assertEquals(['GET', 'POST', 'PUT', 'OPTIONS'], $route->getMethods());
            $this->assertEquals(['https'], $route->getSchemes());
        }
    }

    public function testLoadWithImport()
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routeCollection = $loader->load('validresource.php');
        $routes = $routeCollection->all();

        $this->assertCount(1, $routes, 'One route is loaded');
        $this->assertContainsOnly('Symfony\Component\Routing\Route', $routes);

        foreach ($routes as $route) {
            $this->assertSame('/prefix/blog/{slug}', $route->getPath());
            $this->assertSame('MyBlogBundle:Blog:show', $route->getDefault('_controller'));
            $this->assertSame('{locale}.example.com', $route->getHost());
            $this->assertSame('RouteCompiler', $route->getOption('compiler_class'));
            $this->assertEquals(['GET', 'POST', 'PUT', 'OPTIONS'], $route->getMethods());
            $this->assertEquals(['https'], $route->getSchemes());
        }
    }

    public function testThatDefiningVariableInConfigFileHasNoSideEffects()
    {
        $locator = new FileLocator([__DIR__.'/../Fixtures']);
        $loader = new PhpFileLoader($locator);
        $routeCollection = $loader->load('with_define_path_variable.php');
        $resources = $routeCollection->getResources();
        $this->assertCount(1, $resources);
        $this->assertContainsOnly('Symfony\Component\Config\Resource\ResourceInterface', $resources);
        $fileResource = reset($resources);
        $this->assertSame(
            realpath($locator->locate('with_define_path_variable.php')),
            (string) $fileResource
        );
    }

    public function testLoadingRouteWithDefaults()
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routes = $loader->load('defaults.php');

        $this->assertCount(1, $routes);

        $defaultsRoute = $routes->get('defaults');

        $this->assertSame('/defaults', $defaultsRoute->getPath());
        $this->assertSame('en', $defaultsRoute->getDefault('_locale'));
        $this->assertSame('html', $defaultsRoute->getDefault('_format'));
    }

    public function testLoadingImportedRoutesWithDefaults()
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures']));
        $routes = $loader->load('importer-with-defaults.php');

        $this->assertCount(2, $routes);

        $expectedRoutes = new RouteCollection();
        $expectedRoutes->add('one', $localeRoute = new Route('/defaults/one'));
        $localeRoute->setDefault('_locale', 'g_locale');
        $localeRoute->setDefault('_format', 'g_format');
        $expectedRoutes->add('two', $formatRoute = new Route('/defaults/two'));
        $formatRoute->setDefault('_locale', 'g_locale');
        $formatRoute->setDefault('_format', 'g_format');
        $formatRoute->setDefault('specific', 'imported');

        $expectedRoutes->addResource(new FileResource(__DIR__.'/../Fixtures/imported-with-defaults.php'));
        $expectedRoutes->addResource(new FileResource(__DIR__.'/../Fixtures/importer-with-defaults.php'));

        $this->assertEquals($expectedRoutes, $routes);
    }

    public function testLoadingUtf8Route()
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures/localized']));
        $routes = $loader->load('utf8.php');

        $this->assertCount(2, $routes);

        $expectedRoutes = new RouteCollection();
        $expectedRoutes->add('some_route', new Route('/'));

        $expectedRoutes->add('some_utf8_route', $route = new Route('/utf8'));
        $route->setOption('utf8', true);

        $expectedRoutes->addResource(new FileResource(__DIR__.'/../Fixtures/localized/utf8.php'));

        $this->assertEquals($expectedRoutes, $routes);
    }

    public function testLoadingUtf8ImportedRoutes()
    {
        $loader = new PhpFileLoader(new FileLocator([__DIR__.'/../Fixtures/localized']));
        $routes = $loader->load('importer-with-utf8.php');

        $this->assertCount(2, $routes);

        $expectedRoutes = new RouteCollection();
        $expectedRoutes->add('utf8_one', $one = new Route('/one'));
        $one->setOption('utf8', true);

        $expectedRoutes->add('utf8_two', $two = new Route('/two'));
        $two->setOption('utf8', true);

        $expectedRoutes->addResource(new FileResource(__DIR__.'/../Fixtures/localized/imported-with-utf8.php'));
        $expectedRoutes->addResource(new FileResource(__DIR__.'/../Fixtures/localized/importer-with-utf8.php'));

        $this->assertEquals($expectedRoutes, $routes);
    }

    public function testRoutingConfigurator()
    {
        $locator = new FileLocator([__DIR__.'/../Fixtures']);
        $loader = new PhpFileLoader($locator);
        $routeCollectionClosure = $loader->load('php_dsl.php');
        $routeCollectionObject = $loader->load('php_object_dsl.php');

        $expectedCollection = new RouteCollection();

        $expectedCollection->add('foo', (new Route('/foo'))
            ->setOptions(['utf8' => true])
            ->setCondition('abc')
        );
        $expectedCollection->add('buz', (new Route('/zub'))
            ->setDefaults(['_controller' => 'foo:act'])
        );
        $expectedCollection->add('c_root', (new Route('/sub/pub/'))
            ->setRequirements(['id' => '\d+'])
        );
        $expectedCollection->add('c_bar', (new Route('/sub/pub/bar'))
            ->setRequirements(['id' => '\d+'])
        );
        $expectedCollection->add('c_pub_buz', (new Route('/sub/pub/buz'))
            ->setHost('host')
            ->setRequirements(['id' => '\d+'])
        );
        $expectedCollection->add('z_c_root', new Route('/zub/pub/'));
        $expectedCollection->add('z_c_bar', new Route('/zub/pub/bar'));
        $expectedCollection->add('z_c_pub_buz', (new Route('/zub/pub/buz'))->setHost('host'));
        $expectedCollection->add('r_root', new Route('/bus'));
        $expectedCollection->add('r_bar', new Route('/bus/bar/'));
        $expectedCollection->add('ouf', (new Route('/ouf'))
            ->setSchemes(['https'])
            ->setMethods(['GET'])
            ->setDefaults(['id' => 0])
        );

        $expectedCollection->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_dsl_sub.php')));
        $expectedCollection->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_dsl_sub_root.php')));

        $expectedCollectionClosure = $expectedCollection;
        $expectedCollectionObject = clone $expectedCollection;

        $expectedCollectionClosure->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_dsl.php')));
        $expectedCollectionObject->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_object_dsl.php')));

        $this->assertEquals($expectedCollectionClosure, $routeCollectionClosure);
        $this->assertEquals($expectedCollectionObject, $routeCollectionObject);
    }

    public function testRoutingConfiguratorCanImportGlobPatterns()
    {
        $locator = new FileLocator([__DIR__.'/../Fixtures/glob']);
        $loader = new PhpFileLoader($locator);
        $routeCollection = $loader->load('php_dsl.php');

        $route = $routeCollection->get('bar_route');
        $this->assertSame('AppBundle:Bar:view', $route->getDefault('_controller'));

        $route = $routeCollection->get('baz_route');
        $this->assertSame('AppBundle:Baz:view', $route->getDefault('_controller'));
    }

    public function testRoutingI18nConfigurator()
    {
        $locator = new FileLocator([__DIR__.'/../Fixtures']);
        $loader = new PhpFileLoader($locator);
        $routeCollection = $loader->load('php_dsl_i18n.php');

        $expectedCollection = new RouteCollection();

        $expectedCollection->add('foo.en', (new Route('/glish/foo'))->setDefaults(['_locale' => 'en', '_canonical_route' => 'foo']));
        $expectedCollection->add('bar.en', (new Route('/glish/bar'))->setDefaults(['_locale' => 'en', '_canonical_route' => 'bar']));
        $expectedCollection->add('baz.en', (new Route('/baz'))->setDefaults(['_locale' => 'en', '_canonical_route' => 'baz']));
        $expectedCollection->add('c_foo.fr', (new Route('/ench/pub/foo'))->setDefaults(['_locale' => 'fr', '_canonical_route' => 'c_foo']));
        $expectedCollection->add('c_bar.fr', (new Route('/ench/pub/bar'))->setDefaults(['_locale' => 'fr', '_canonical_route' => 'c_bar']));

        $expectedCollection->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_dsl_sub_i18n.php')));
        $expectedCollection->addResource(new FileResource(realpath(__DIR__.'/../Fixtures/php_dsl_i18n.php')));

        $this->assertEquals($expectedCollection, $routeCollection);
    }
}
