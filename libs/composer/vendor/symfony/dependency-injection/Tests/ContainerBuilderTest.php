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

require_once __DIR__.'/Fixtures/includes/autowiring_classes.php';
require_once __DIR__.'/Fixtures/includes/classes.php';
require_once __DIR__.'/Fixtures/includes/ProjectExtension.php';

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\Config\Resource\ComposerResource;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\DependencyInjection\Tests\Compiler\Foo;
use Symfony\Component\DependencyInjection\Tests\Compiler\Wither;
use Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
use Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition;
use Symfony\Component\DependencyInjection\Tests\Fixtures\ScalarFactory;
use Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy;
use Symfony\Component\DependencyInjection\TypedReference;
use Symfony\Component\ExpressionLanguage\Expression;

class ContainerBuilderTest extends TestCase
{
    public function testDefaultRegisteredDefinitions()
    {
        $builder = new ContainerBuilder();

        $this->assertCount(1, $builder->getDefinitions());
        $this->assertTrue($builder->hasDefinition('service_container'));

        $definition = $builder->getDefinition('service_container');
        $this->assertInstanceOf(Definition::class, $definition);
        $this->assertTrue($definition->isSynthetic());
        $this->assertSame(ContainerInterface::class, $definition->getClass());
        $this->assertTrue($builder->hasAlias(PsrContainerInterface::class));
        $this->assertTrue($builder->hasAlias(ContainerInterface::class));
    }

    public function testDefinitions()
    {
        $builder = new ContainerBuilder();
        $definitions = [
            'foo' => new Definition('Bar\FooClass'),
            'bar' => new Definition('BarClass'),
        ];
        $builder->setDefinitions($definitions);
        $this->assertEquals($definitions, $builder->getDefinitions(), '->setDefinitions() sets the service definitions');
        $this->assertTrue($builder->hasDefinition('foo'), '->hasDefinition() returns true if a service definition exists');
        $this->assertFalse($builder->hasDefinition('foobar'), '->hasDefinition() returns false if a service definition does not exist');

        $builder->setDefinition('foobar', $foo = new Definition('FooBarClass'));
        $this->assertEquals($foo, $builder->getDefinition('foobar'), '->getDefinition() returns a service definition if defined');
        $this->assertSame($builder->setDefinition('foobar', $foo = new Definition('FooBarClass')), $foo, '->setDefinition() implements a fluid interface by returning the service reference');

        $builder->addDefinitions($defs = ['foobar' => new Definition('FooBarClass')]);
        $this->assertEquals(array_merge($definitions, $defs), $builder->getDefinitions(), '->addDefinitions() adds the service definitions');

        try {
            $builder->getDefinition('baz');
            $this->fail('->getDefinition() throws a ServiceNotFoundException if the service definition does not exist');
        } catch (ServiceNotFoundException $e) {
            $this->assertEquals('You have requested a non-existent service "baz".', $e->getMessage(), '->getDefinition() throws a ServiceNotFoundException if the service definition does not exist');
        }
    }

    /**
     * @group legacy
     * @expectedDeprecation The "deprecated_foo" service is deprecated. You should stop using it, as it will be removed in the future.
     */
    public function testCreateDeprecatedService()
    {
        $definition = new Definition('stdClass');
        $definition->setDeprecated(true);

        $builder = new ContainerBuilder();
        $builder->setDefinition('deprecated_foo', $definition);
        $builder->get('deprecated_foo');
    }

    public function testRegister()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', 'Bar\FooClass');
        $this->assertTrue($builder->hasDefinition('foo'), '->register() registers a new service definition');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $builder->getDefinition('foo'), '->register() returns the newly created Definition instance');
    }

    public function testAutowire()
    {
        $builder = new ContainerBuilder();
        $builder->autowire('foo', 'Bar\FooClass');

        $this->assertTrue($builder->hasDefinition('foo'), '->autowire() registers a new service definition');
        $this->assertTrue($builder->getDefinition('foo')->isAutowired(), '->autowire() creates autowired definitions');
    }

    public function testHas()
    {
        $builder = new ContainerBuilder();
        $this->assertFalse($builder->has('foo'), '->has() returns false if the service does not exist');
        $builder->register('foo', 'Bar\FooClass');
        $this->assertTrue($builder->has('foo'), '->has() returns true if a service definition exists');
        $builder->set('bar', new \stdClass());
        $this->assertTrue($builder->has('bar'), '->has() returns true if a service exists');
    }

    public function testGetThrowsExceptionIfServiceDoesNotExist()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');
        $this->expectExceptionMessage('You have requested a non-existent service "foo".');
        $builder = new ContainerBuilder();
        $builder->get('foo');
    }

    public function testGetReturnsNullIfServiceDoesNotExistAndInvalidReferenceIsUsed()
    {
        $builder = new ContainerBuilder();

        $this->assertNull($builder->get('foo', ContainerInterface::NULL_ON_INVALID_REFERENCE), '->get() returns null if the service does not exist and NULL_ON_INVALID_REFERENCE is passed as a second argument');
    }

    public function testGetThrowsCircularReferenceExceptionIfServiceHasReferenceToItself()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException');
        $builder = new ContainerBuilder();
        $builder->register('baz', 'stdClass')->setArguments([new Reference('baz')]);
        $builder->get('baz');
    }

    public function testGetReturnsSameInstanceWhenServiceIsShared()
    {
        $builder = new ContainerBuilder();
        $builder->register('bar', 'stdClass');

        $this->assertTrue($builder->get('bar') === $builder->get('bar'), '->get() always returns the same instance if the service is shared');
    }

    public function testGetCreatesServiceBasedOnDefinition()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', 'stdClass');

        $this->assertIsObject($builder->get('foo'), '->get() returns the service definition associated with the id');
    }

    public function testGetReturnsRegisteredService()
    {
        $builder = new ContainerBuilder();
        $builder->set('bar', $bar = new \stdClass());

        $this->assertSame($bar, $builder->get('bar'), '->get() returns the service associated with the id');
    }

    public function testRegisterDoesNotOverrideExistingService()
    {
        $builder = new ContainerBuilder();
        $builder->set('bar', $bar = new \stdClass());
        $builder->register('bar', 'stdClass');

        $this->assertSame($bar, $builder->get('bar'), '->get() returns the service associated with the id even if a definition has been defined');
    }

    public function testNonSharedServicesReturnsDifferentInstances()
    {
        $builder = new ContainerBuilder();
        $builder->register('bar', 'stdClass')->setShared(false);

        $this->assertNotSame($builder->get('bar'), $builder->get('bar'));
    }

    /**
     * @dataProvider provideBadId
     */
    public function testBadAliasId($id)
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\InvalidArgumentException');
        $builder = new ContainerBuilder();
        $builder->setAlias($id, 'foo');
    }

    /**
     * @dataProvider provideBadId
     */
    public function testBadDefinitionId($id)
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\InvalidArgumentException');
        $builder = new ContainerBuilder();
        $builder->setDefinition($id, new Definition('Foo'));
    }

    public function provideBadId()
    {
        return [
            [''],
            ["\0"],
            ["\r"],
            ["\n"],
            ["'"],
            ['ab\\'],
        ];
    }

    public function testGetUnsetLoadingServiceWhenCreateServiceThrowsAnException()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('You have requested a synthetic service ("foo"). The DIC does not know how to construct this service.');
        $builder = new ContainerBuilder();
        $builder->register('foo', 'stdClass')->setSynthetic(true);

        // we expect a RuntimeException here as foo is synthetic
        try {
            $builder->get('foo');
        } catch (RuntimeException $e) {
        }

        // we must also have the same RuntimeException here
        $builder->get('foo');
    }

    public function testGetServiceIds()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->bar = $bar = new \stdClass();
        $builder->register('bar', 'stdClass');
        $this->assertEquals(
            [
                'service_container',
                'foo',
                'bar',
                'Psr\Container\ContainerInterface',
                'Symfony\Component\DependencyInjection\ContainerInterface',
            ],
            $builder->getServiceIds(),
            '->getServiceIds() returns all defined service ids'
        );
    }

    public function testAliases()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', 'stdClass');
        $builder->setAlias('bar', 'foo');
        $this->assertTrue($builder->hasAlias('bar'), '->hasAlias() returns true if the alias exists');
        $this->assertFalse($builder->hasAlias('foobar'), '->hasAlias() returns false if the alias does not exist');
        $this->assertEquals('foo', (string) $builder->getAlias('bar'), '->getAlias() returns the aliased service');
        $this->assertTrue($builder->has('bar'), '->setAlias() defines a new service');
        $this->assertSame($builder->get('bar'), $builder->get('foo'), '->setAlias() creates a service that is an alias to another one');

        try {
            $builder->setAlias('foobar', 'foobar');
            $this->fail('->setAlias() throws an InvalidArgumentException if the alias references itself');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('An alias can not reference itself, got a circular reference on "foobar".', $e->getMessage(), '->setAlias() throws an InvalidArgumentException if the alias references itself');
        }

        try {
            $builder->getAlias('foobar');
            $this->fail('->getAlias() throws an InvalidArgumentException if the alias does not exist');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The service alias "foobar" does not exist.', $e->getMessage(), '->getAlias() throws an InvalidArgumentException if the alias does not exist');
        }
    }

    /**
     * @group legacy
     * @expectedDeprecation The "foobar" service alias is deprecated. You should stop using it, as it will be removed in the future.
     */
    public function testDeprecatedAlias()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', 'stdClass');

        $alias = new Alias('foo');
        $alias->setDeprecated();
        $builder->setAlias('foobar', $alias);

        $builder->get('foobar');
    }

    public function testGetAliases()
    {
        $builder = new ContainerBuilder();
        $builder->setAlias('bar', 'foo');
        $builder->setAlias('foobar', 'foo');
        $builder->setAlias('moo', new Alias('foo', false));

        $aliases = $builder->getAliases();
        $this->assertEquals('foo', (string) $aliases['bar']);
        $this->assertTrue($aliases['bar']->isPublic());
        $this->assertEquals('foo', (string) $aliases['foobar']);
        $this->assertEquals('foo', (string) $aliases['moo']);
        $this->assertFalse($aliases['moo']->isPublic());

        $builder->register('bar', 'stdClass');
        $this->assertFalse($builder->hasAlias('bar'));

        $builder->set('foobar', 'stdClass');
        $builder->set('moo', 'stdClass');
        $this->assertCount(2, $builder->getAliases(), '->getAliases() does not return aliased services that have been overridden');
    }

    public function testSetAliases()
    {
        $builder = new ContainerBuilder();
        $builder->setAliases(['bar' => 'foo', 'foobar' => 'foo']);

        $aliases = $builder->getAliases();
        $this->assertArrayHasKey('bar', $aliases);
        $this->assertArrayHasKey('foobar', $aliases);
    }

    public function testAddAliases()
    {
        $builder = new ContainerBuilder();
        $builder->setAliases(['bar' => 'foo']);
        $builder->addAliases(['foobar' => 'foo']);

        $aliases = $builder->getAliases();
        $this->assertArrayHasKey('bar', $aliases);
        $this->assertArrayHasKey('foobar', $aliases);
    }

    public function testSetReplacesAlias()
    {
        $builder = new ContainerBuilder();
        $builder->setAlias('alias', 'aliased');
        $builder->set('aliased', new \stdClass());

        $builder->set('alias', $foo = new \stdClass());
        $this->assertSame($foo, $builder->get('alias'), '->set() replaces an existing alias');
    }

    public function testAliasesKeepInvalidBehavior()
    {
        $builder = new ContainerBuilder();

        $aliased = new Definition('stdClass');
        $aliased->addMethodCall('setBar', [new Reference('bar', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]);
        $builder->setDefinition('aliased', $aliased);
        $builder->setAlias('alias', 'aliased');

        $this->assertEquals(new \stdClass(), $builder->get('alias'));
    }

    public function testAddGetCompilerPass()
    {
        $builder = new ContainerBuilder();
        $builder->setResourceTracking(false);
        $defaultPasses = $builder->getCompiler()->getPassConfig()->getPasses();
        $builder->addCompilerPass($pass1 = $this->getMockBuilder('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface')->getMock(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -5);
        $builder->addCompilerPass($pass2 = $this->getMockBuilder('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface')->getMock(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);

        $passes = $builder->getCompiler()->getPassConfig()->getPasses();
        $this->assertCount(\count($passes) - 2, $defaultPasses);
        // Pass 1 is executed later
        $this->assertTrue(array_search($pass1, $passes, true) > array_search($pass2, $passes, true));
    }

    public function testCreateService()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo1', 'Bar\FooClass')->setFile(__DIR__.'/Fixtures/includes/foo.php');
        $builder->register('foo2', 'Bar\FooClass')->setFile(__DIR__.'/Fixtures/includes/%file%.php');
        $builder->setParameter('file', 'foo');
        $this->assertInstanceOf('\Bar\FooClass', $builder->get('foo1'), '->createService() requires the file defined by the service definition');
        $this->assertInstanceOf('\Bar\FooClass', $builder->get('foo2'), '->createService() replaces parameters in the file provided by the service definition');
    }

    public function testCreateProxyWithRealServiceInstantiator()
    {
        $builder = new ContainerBuilder();

        $builder->register('foo1', 'Bar\FooClass')->setFile(__DIR__.'/Fixtures/includes/foo.php');
        $builder->getDefinition('foo1')->setLazy(true);

        $foo1 = $builder->get('foo1');

        $this->assertSame($foo1, $builder->get('foo1'), 'The same proxy is retrieved on multiple subsequent calls');
        $this->assertSame('Bar\FooClass', \get_class($foo1));
    }

    public function testCreateServiceClass()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo1', '%class%');
        $builder->setParameter('class', 'stdClass');
        $this->assertInstanceOf('\stdClass', $builder->get('foo1'), '->createService() replaces parameters in the class provided by the service definition');
    }

    public function testCreateServiceArguments()
    {
        $builder = new ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'Bar\FooClass')->addArgument(['foo' => '%value%', '%value%' => 'foo', new Reference('bar'), '%%unescape_it%%']);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo', $builder->get('bar'), '%unescape_it%'], $builder->get('foo1')->arguments, '->createService() replaces parameters and service references in the arguments provided by the service definition');
    }

    public function testCreateServiceFactory()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', 'Bar\FooClass')->setFactory('Bar\FooClass::getInstance');
        $builder->register('qux', 'Bar\FooClass')->setFactory(['Bar\FooClass', 'getInstance']);
        $builder->register('bar', 'Bar\FooClass')->setFactory([new Definition('Bar\FooClass'), 'getInstance']);
        $builder->register('baz', 'Bar\FooClass')->setFactory([new Reference('bar'), 'getInstance']);

        $this->assertTrue($builder->get('foo')->called, '->createService() calls the factory method to create the service instance');
        $this->assertTrue($builder->get('qux')->called, '->createService() calls the factory method to create the service instance');
        $this->assertTrue($builder->get('bar')->called, '->createService() uses anonymous service as factory');
        $this->assertTrue($builder->get('baz')->called, '->createService() uses another service as factory');
    }

    public function testCreateServiceMethodCalls()
    {
        $builder = new ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'Bar\FooClass')->addMethodCall('setBar', [['%value%', new Reference('bar')]]);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['bar', $builder->get('bar')], $builder->get('foo1')->bar, '->createService() replaces the values in the method calls arguments');
    }

    public function testCreateServiceMethodCallsWithEscapedParam()
    {
        $builder = new ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'Bar\FooClass')->addMethodCall('setBar', [['%%unescape_it%%']]);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['%unescape_it%'], $builder->get('foo1')->bar, '->createService() replaces the values in the method calls arguments');
    }

    public function testCreateServiceProperties()
    {
        $builder = new ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder->register('foo1', 'Bar\FooClass')->setProperty('bar', ['%value%', new Reference('bar'), '%%unescape_it%%']);
        $builder->setParameter('value', 'bar');
        $this->assertEquals(['bar', $builder->get('bar'), '%unescape_it%'], $builder->get('foo1')->bar, '->createService() replaces the values in the properties');
    }

    public function testCreateServiceConfigurator()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo1', 'Bar\FooClass')->setConfigurator('sc_configure');
        $builder->register('foo2', 'Bar\FooClass')->setConfigurator(['%class%', 'configureStatic']);
        $builder->setParameter('class', 'BazClass');
        $builder->register('baz', 'BazClass');
        $builder->register('foo3', 'Bar\FooClass')->setConfigurator([new Reference('baz'), 'configure']);
        $builder->register('foo4', 'Bar\FooClass')->setConfigurator([$builder->getDefinition('baz'), 'configure']);
        $builder->register('foo5', 'Bar\FooClass')->setConfigurator('foo');

        $this->assertTrue($builder->get('foo1')->configured, '->createService() calls the configurator');
        $this->assertTrue($builder->get('foo2')->configured, '->createService() calls the configurator');
        $this->assertTrue($builder->get('foo3')->configured, '->createService() calls the configurator');
        $this->assertTrue($builder->get('foo4')->configured, '->createService() calls the configurator');

        try {
            $builder->get('foo5');
            $this->fail('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('The configure callable for class "Bar\FooClass" is not a callable.', $e->getMessage(), '->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
        }
    }

    public function testCreateServiceWithIteratorArgument()
    {
        $builder = new ContainerBuilder();
        $builder->register('bar', 'stdClass');
        $builder
            ->register('lazy_context', 'LazyContext')
            ->setArguments([
                new IteratorArgument(['k1' => new Reference('bar'), new Reference('invalid', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]),
                new IteratorArgument([]),
            ])
        ;

        $lazyContext = $builder->get('lazy_context');
        $this->assertInstanceOf(RewindableGenerator::class, $lazyContext->lazyValues);
        $this->assertInstanceOf(RewindableGenerator::class, $lazyContext->lazyEmptyValues);
        $this->assertCount(1, $lazyContext->lazyValues);
        $this->assertCount(0, $lazyContext->lazyEmptyValues);

        $i = 0;
        foreach ($lazyContext->lazyValues as $k => $v) {
            ++$i;
            $this->assertEquals('k1', $k);
            $this->assertInstanceOf('\stdClass', $v);
        }

        // The second argument should have been ignored.
        $this->assertEquals(1, $i);

        $i = 0;
        foreach ($lazyContext->lazyEmptyValues as $k => $v) {
            ++$i;
        }

        $this->assertEquals(0, $i);
    }

    public function testCreateSyntheticService()
    {
        $this->expectException('RuntimeException');
        $builder = new ContainerBuilder();
        $builder->register('foo', 'Bar\FooClass')->setSynthetic(true);
        $builder->get('foo');
    }

    public function testCreateServiceWithExpression()
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('bar', 'bar');
        $builder->register('bar', 'BarClass');
        $builder->register('foo', 'Bar\FooClass')->addArgument(['foo' => new Expression('service("bar").foo ~ parameter("bar")')]);
        $this->assertEquals('foobar', $builder->get('foo')->arguments['foo']);
    }

    public function testResolveServices()
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', 'Bar\FooClass');
        $this->assertEquals($builder->get('foo'), $builder->resolveServices(new Reference('foo')), '->resolveServices() resolves service references to service instances');
        $this->assertEquals(['foo' => ['foo', $builder->get('foo')]], $builder->resolveServices(['foo' => ['foo', new Reference('foo')]]), '->resolveServices() resolves service references to service instances in nested arrays');
        $this->assertEquals($builder->get('foo'), $builder->resolveServices(new Expression('service("foo")')), '->resolveServices() resolves expressions');
    }

    public function testResolveServicesWithDecoratedDefinition()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('Constructing service "foo" from a parent definition is not supported at build time.');
        $builder = new ContainerBuilder();
        $builder->setDefinition('grandpa', new Definition('stdClass'));
        $builder->setDefinition('parent', new ChildDefinition('grandpa'));
        $builder->setDefinition('foo', new ChildDefinition('parent'));

        $builder->get('foo');
    }

    public function testResolveServicesWithCustomDefinitionClass()
    {
        $builder = new ContainerBuilder();
        $builder->setDefinition('foo', new CustomDefinition('stdClass'));

        $this->assertInstanceOf('stdClass', $builder->get('foo'));
    }

    public function testMerge()
    {
        $container = new ContainerBuilder(new ParameterBag(['bar' => 'foo']));
        $container->setResourceTracking(false);
        $config = new ContainerBuilder(new ParameterBag(['foo' => 'bar']));
        $container->merge($config);
        $this->assertEquals(['bar' => 'foo', 'foo' => 'bar'], $container->getParameterBag()->all(), '->merge() merges current parameters with the loaded ones');

        $container = new ContainerBuilder(new ParameterBag(['bar' => 'foo']));
        $container->setResourceTracking(false);
        $config = new ContainerBuilder(new ParameterBag(['foo' => '%bar%']));
        $container->merge($config);
        $container->compile();
        $this->assertEquals(['bar' => 'foo', 'foo' => 'foo'], $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');

        $container = new ContainerBuilder(new ParameterBag(['bar' => 'foo']));
        $container->setResourceTracking(false);
        $config = new ContainerBuilder(new ParameterBag(['foo' => '%bar%', 'baz' => '%foo%']));
        $container->merge($config);
        $container->compile();
        $this->assertEquals(['bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'], $container->getParameterBag()->all(), '->merge() evaluates the values of the parameters towards already defined ones');

        $container = new ContainerBuilder();
        $container->setResourceTracking(false);
        $container->register('foo', 'Bar\FooClass');
        $container->register('bar', 'BarClass');
        $config = new ContainerBuilder();
        $config->setDefinition('baz', new Definition('BazClass'));
        $config->setAlias('alias_for_foo', 'foo');
        $container->merge($config);
        $this->assertEquals(['service_container', 'foo', 'bar', 'baz'], array_keys($container->getDefinitions()), '->merge() merges definitions already defined ones');

        $aliases = $container->getAliases();
        $this->assertArrayHasKey('alias_for_foo', $aliases);
        $this->assertEquals('foo', (string) $aliases['alias_for_foo']);

        $container = new ContainerBuilder();
        $container->setResourceTracking(false);
        $container->register('foo', 'Bar\FooClass');
        $config->setDefinition('foo', new Definition('BazClass'));
        $container->merge($config);
        $this->assertEquals('BazClass', $container->getDefinition('foo')->getClass(), '->merge() overrides already defined services');

        $container = new ContainerBuilder();
        $bag = new EnvPlaceholderParameterBag();
        $bag->get('env(Foo)');
        $config = new ContainerBuilder($bag);
        $this->assertSame(['%env(Bar)%'], $config->resolveEnvPlaceholders([$bag->get('env(Bar)')]));
        $container->merge($config);
        $this->assertEquals(['Foo' => 0, 'Bar' => 1], $container->getEnvCounters());

        $container = new ContainerBuilder();
        $config = new ContainerBuilder();
        $childDefA = $container->registerForAutoconfiguration('AInterface');
        $childDefB = $config->registerForAutoconfiguration('BInterface');
        $container->merge($config);
        $this->assertSame(['AInterface' => $childDefA, 'BInterface' => $childDefB], $container->getAutoconfiguredInstanceof());
    }

    public function testMergeThrowsExceptionForDuplicateAutomaticInstanceofDefinitions()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('"AInterface" has already been autoconfigured and merge() does not support merging autoconfiguration for the same class/interface.');
        $container = new ContainerBuilder();
        $config = new ContainerBuilder();
        $container->registerForAutoconfiguration('AInterface');
        $config->registerForAutoconfiguration('AInterface');
        $container->merge($config);
    }

    public function testResolveEnvValues()
    {
        $_ENV['DUMMY_ENV_VAR'] = 'du%%y';
        $_SERVER['DUMMY_SERVER_VAR'] = 'ABC';
        $_SERVER['HTTP_DUMMY_VAR'] = 'DEF';

        $container = new ContainerBuilder();
        $container->setParameter('bar', '%% %env(DUMMY_ENV_VAR)% %env(DUMMY_SERVER_VAR)% %env(HTTP_DUMMY_VAR)%');
        $container->setParameter('env(HTTP_DUMMY_VAR)', '123');

        $this->assertSame('%% du%%%%y ABC 123', $container->resolveEnvPlaceholders('%bar%', true));

        unset($_ENV['DUMMY_ENV_VAR'], $_SERVER['DUMMY_SERVER_VAR'], $_SERVER['HTTP_DUMMY_VAR']);
    }

    public function testResolveEnvValuesWithArray()
    {
        $_ENV['ANOTHER_DUMMY_ENV_VAR'] = 'dummy';

        $dummyArray = ['1' => 'one', '2' => 'two'];

        $container = new ContainerBuilder();
        $container->setParameter('dummy', '%env(ANOTHER_DUMMY_ENV_VAR)%');
        $container->setParameter('dummy2', $dummyArray);

        $container->resolveEnvPlaceholders('%dummy%', true);
        $container->resolveEnvPlaceholders('%dummy2%', true);

        $this->assertIsArray($container->resolveEnvPlaceholders('%dummy2%', true));

        foreach ($dummyArray as $key => $value) {
            $this->assertArrayHasKey($key, $container->resolveEnvPlaceholders('%dummy2%', true));
        }

        unset($_ENV['ANOTHER_DUMMY_ENV_VAR']);
    }

    public function testCompileWithResolveEnv()
    {
        putenv('DUMMY_ENV_VAR=du%%y');
        $_SERVER['DUMMY_SERVER_VAR'] = 'ABC';
        $_SERVER['HTTP_DUMMY_VAR'] = 'DEF';

        $container = new ContainerBuilder();
        $container->setParameter('env(FOO)', 'Foo');
        $container->setParameter('env(DUMMY_ENV_VAR)', 'GHI');
        $container->setParameter('bar', '%% %env(DUMMY_ENV_VAR)% %env(DUMMY_SERVER_VAR)% %env(HTTP_DUMMY_VAR)%');
        $container->setParameter('foo', '%env(FOO)%');
        $container->setParameter('baz', '%foo%');
        $container->setParameter('env(HTTP_DUMMY_VAR)', '123');
        $container->register('teatime', 'stdClass')
            ->setProperty('foo', '%env(DUMMY_ENV_VAR)%')
            ->setPublic(true)
        ;
        $container->compile(true);

        $this->assertSame('% du%%y ABC 123', $container->getParameter('bar'));
        $this->assertSame('Foo', $container->getParameter('baz'));
        $this->assertSame('du%%y', $container->get('teatime')->foo);

        unset($_SERVER['DUMMY_SERVER_VAR'], $_SERVER['HTTP_DUMMY_VAR']);
        putenv('DUMMY_ENV_VAR');
    }

    public function testCompileWithArrayResolveEnv()
    {
        putenv('ARRAY={"foo":"bar"}');

        $container = new ContainerBuilder();
        $container->setParameter('foo', '%env(json:ARRAY)%');
        $container->compile(true);

        $this->assertSame(['foo' => 'bar'], $container->getParameter('foo'));

        putenv('ARRAY');
    }

    public function testCompileWithArrayAndAnotherResolveEnv()
    {
        putenv('DUMMY_ENV_VAR=abc');
        putenv('ARRAY={"foo":"bar"}');

        $container = new ContainerBuilder();
        $container->setParameter('foo', '%env(json:ARRAY)%');
        $container->setParameter('bar', '%env(DUMMY_ENV_VAR)%');
        $container->compile(true);

        $this->assertSame(['foo' => 'bar'], $container->getParameter('foo'));
        $this->assertSame('abc', $container->getParameter('bar'));

        putenv('DUMMY_ENV_VAR');
        putenv('ARRAY');
    }

    public function testCompileWithArrayInStringResolveEnv()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('A string value must be composed of strings and/or numbers, but found parameter "env(json:ARRAY)" of type array inside string value "ABC %env(json:ARRAY)%".');
        putenv('ARRAY={"foo":"bar"}');

        $container = new ContainerBuilder();
        $container->setParameter('foo', 'ABC %env(json:ARRAY)%');
        $container->compile(true);

        putenv('ARRAY');
    }

    public function testCompileWithResolveMissingEnv()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\EnvNotFoundException');
        $this->expectExceptionMessage('Environment variable not found: "FOO".');
        $container = new ContainerBuilder();
        $container->setParameter('foo', '%env(FOO)%');
        $container->compile(true);
    }

    public function testDynamicEnv()
    {
        putenv('DUMMY_FOO=some%foo%');
        putenv('DUMMY_BAR=%bar%');

        $container = new ContainerBuilder();
        $container->setParameter('foo', 'Foo%env(resolve:DUMMY_BAR)%');
        $container->setParameter('bar', 'Bar');
        $container->setParameter('baz', '%env(resolve:DUMMY_FOO)%');

        $container->compile(true);
        putenv('DUMMY_FOO');
        putenv('DUMMY_BAR');

        $this->assertSame('someFooBar', $container->getParameter('baz'));
    }

    public function testFallbackEnv()
    {
        putenv('DUMMY_FOO=foo');

        $container = new ContainerBuilder();
        $container->setParameter('foo', '%env(DUMMY_FOO)%');
        $container->setParameter('bar', 'bar%env(default:foo:DUMMY_BAR)%');

        $container->compile(true);
        putenv('DUMMY_FOO');

        $this->assertSame('barfoo', $container->getParameter('bar'));
    }

    public function testCastEnv()
    {
        $container = new ContainerBuilder();
        $container->setParameter('env(FAKE)', '123');

        $container->register('foo', 'stdClass')
            ->setPublic(true)
            ->setProperties([
                'fake' => '%env(int:FAKE)%',
            ]);

        $container->compile(true);

        $this->assertSame(123, $container->get('foo')->fake);
    }

    public function testEnvAreNullable()
    {
        $container = new ContainerBuilder();
        $container->setParameter('env(FAKE)', null);

        $container->register('foo', 'stdClass')
            ->setPublic(true)
            ->setProperties([
            'fake' => '%env(int:FAKE)%',
        ]);

        $container->compile(true);

        $this->assertNull($container->get('foo')->fake);
    }

    public function testEnvInId()
    {
        $container = include __DIR__.'/Fixtures/containers/container_env_in_id.php';
        $container->compile(true);

        $expected = [
            'service_container',
            'foo',
            'bar',
            'bar_%env(BAR)%',
        ];
        $this->assertSame($expected, array_keys($container->getDefinitions()));

        $expected = [
            PsrContainerInterface::class => true,
            ContainerInterface::class => true,
            'baz_%env(BAR)%' => true,
            'bar_%env(BAR)%' => true,
        ];
        $this->assertSame($expected, $container->getRemovedIds());

        $this->assertSame(['baz_bar'], array_keys($container->getDefinition('foo')->getArgument(1)));
    }

    public function testCircularDynamicEnv()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\ParameterCircularReferenceException');
        $this->expectExceptionMessage('Circular reference detected for parameter "env(resolve:DUMMY_ENV_VAR)" ("env(resolve:DUMMY_ENV_VAR)" > "env(resolve:DUMMY_ENV_VAR)").');
        putenv('DUMMY_ENV_VAR=some%foo%');

        $container = new ContainerBuilder();
        $container->setParameter('foo', '%bar%');
        $container->setParameter('bar', '%env(resolve:DUMMY_ENV_VAR)%');

        try {
            $container->compile(true);
        } finally {
            putenv('DUMMY_ENV_VAR');
        }
    }

    public function testMergeLogicException()
    {
        $this->expectException('LogicException');
        $container = new ContainerBuilder();
        $container->setResourceTracking(false);
        $container->compile();
        $container->merge(new ContainerBuilder());
    }

    public function testfindTaggedServiceIds()
    {
        $builder = new ContainerBuilder();
        $builder
            ->register('foo', 'Bar\FooClass')
            ->addTag('foo', ['foo' => 'foo'])
            ->addTag('bar', ['bar' => 'bar'])
            ->addTag('foo', ['foofoo' => 'foofoo'])
        ;
        $this->assertEquals($builder->findTaggedServiceIds('foo'), [
            'foo' => [
                ['foo' => 'foo'],
                ['foofoo' => 'foofoo'],
            ],
        ], '->findTaggedServiceIds() returns an array of service ids and its tag attributes');
        $this->assertEquals([], $builder->findTaggedServiceIds('foobar'), '->findTaggedServiceIds() returns an empty array if there is annotated services');
    }

    public function testFindUnusedTags()
    {
        $builder = new ContainerBuilder();
        $builder
            ->register('foo', 'Bar\FooClass')
            ->addTag('kernel.event_listener', ['foo' => 'foo'])
            ->addTag('kenrel.event_listener', ['bar' => 'bar'])
        ;
        $builder->findTaggedServiceIds('kernel.event_listener');
        $this->assertEquals(['kenrel.event_listener'], $builder->findUnusedTags(), '->findUnusedTags() returns an array with unused tags');
    }

    public function testFindDefinition()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('foo', $definition = new Definition('Bar\FooClass'));
        $container->setAlias('bar', 'foo');
        $container->setAlias('foobar', 'bar');
        $this->assertEquals($definition, $container->findDefinition('foobar'), '->findDefinition() returns a Definition');
    }

    public function testAddObjectResource()
    {
        $container = new ContainerBuilder();

        $container->setResourceTracking(false);
        $container->addObjectResource(new \BarClass());

        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');

        $container->setResourceTracking(true);
        $container->addObjectResource(new \BarClass());

        $resources = $container->getResources();

        $this->assertCount(2, $resources, '2 resources were registered');

        /* @var $resource \Symfony\Component\Config\Resource\FileResource */
        $resource = end($resources);

        $this->assertInstanceOf('Symfony\Component\Config\Resource\FileResource', $resource);
        $this->assertSame(realpath(__DIR__.'/Fixtures/includes/classes.php'), realpath($resource->getResource()));
    }

    public function testGetReflectionClass()
    {
        $container = new ContainerBuilder();

        $container->setResourceTracking(false);
        $r1 = $container->getReflectionClass('BarClass');

        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');

        $container->setResourceTracking(true);
        $r2 = $container->getReflectionClass('BarClass');
        $r3 = $container->getReflectionClass('BarClass');

        $this->assertNull($container->getReflectionClass('BarMissingClass'));

        $this->assertEquals($r1, $r2);
        $this->assertSame($r2, $r3);

        $resources = $container->getResources();

        $this->assertCount(3, $resources, '3 resources were registered');

        $this->assertSame('reflection.BarClass', (string) $resources[1]);
        $this->assertSame('BarMissingClass', (string) end($resources));
    }

    public function testGetReflectionClassOnInternalTypes()
    {
        $container = new ContainerBuilder();

        $this->assertNull($container->getReflectionClass('int'));
        $this->assertNull($container->getReflectionClass('float'));
        $this->assertNull($container->getReflectionClass('string'));
        $this->assertNull($container->getReflectionClass('bool'));
        $this->assertNull($container->getReflectionClass('resource'));
        $this->assertNull($container->getReflectionClass('object'));
        $this->assertNull($container->getReflectionClass('array'));
        $this->assertNull($container->getReflectionClass('null'));
        $this->assertNull($container->getReflectionClass('callable'));
        $this->assertNull($container->getReflectionClass('iterable'));
        $this->assertNull($container->getReflectionClass('mixed'));
    }

    public function testCompilesClassDefinitionsOfLazyServices()
    {
        $container = new ContainerBuilder();

        $this->assertEmpty($container->getResources(), 'No resources get registered without resource tracking');

        $container->register('foo', 'BarClass')->setPublic(true);
        $container->getDefinition('foo')->setLazy(true);

        $container->compile();

        $matchingResources = array_filter(
            $container->getResources(),
            function (ResourceInterface $resource) {
                return 'reflection.BarClass' === (string) $resource;
            }
        );

        $this->assertNotEmpty($matchingResources);
    }

    public function testResources()
    {
        $container = new ContainerBuilder();
        $container->addResource($a = new FileResource(__DIR__.'/Fixtures/xml/services1.xml'));
        $container->addResource($b = new FileResource(__DIR__.'/Fixtures/xml/services2.xml'));
        $resources = [];
        foreach ($container->getResources() as $resource) {
            if (false === strpos($resource, '.php')) {
                $resources[] = $resource;
            }
        }
        $this->assertEquals([$a, $b], $resources, '->getResources() returns an array of resources read for the current configuration');
        $this->assertSame($container, $container->setResources([]));
        $this->assertEquals([], $container->getResources());
    }

    public function testFileExists()
    {
        $container = new ContainerBuilder();
        $A = new ComposerResource();
        $a = new FileResource(__DIR__.'/Fixtures/xml/services1.xml');
        $b = new FileResource(__DIR__.'/Fixtures/xml/services2.xml');
        $c = new DirectoryResource($dir = \dirname($b));

        $this->assertTrue($container->fileExists((string) $a) && $container->fileExists((string) $b) && $container->fileExists($dir));

        $resources = [];
        foreach ($container->getResources() as $resource) {
            if (false === strpos($resource, '.php')) {
                $resources[] = $resource;
            }
        }

        $this->assertEquals([$A, $a, $b, $c], $resources, '->getResources() returns an array of resources read for the current configuration');
    }

    public function testExtension()
    {
        $container = new ContainerBuilder();
        $container->setResourceTracking(false);

        $container->registerExtension($extension = new \ProjectExtension());
        $this->assertSame($container->getExtension('project'), $extension, '->registerExtension() registers an extension');

        $this->expectException('LogicException');
        $container->getExtension('no_registered');
    }

    public function testRegisteredButNotLoadedExtension()
    {
        $extension = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface')->getMock();
        $extension->expects($this->once())->method('getAlias')->willReturn('project');
        $extension->expects($this->never())->method('load');

        $container = new ContainerBuilder();
        $container->setResourceTracking(false);
        $container->registerExtension($extension);
        $container->compile();
    }

    public function testRegisteredAndLoadedExtension()
    {
        $extension = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface')->getMock();
        $extension->expects($this->exactly(2))->method('getAlias')->willReturn('project');
        $extension->expects($this->once())->method('load')->with([['foo' => 'bar']]);

        $container = new ContainerBuilder();
        $container->setResourceTracking(false);
        $container->registerExtension($extension);
        $container->loadFromExtension('project', ['foo' => 'bar']);
        $container->compile();
    }

    public function testPrivateServiceUser()
    {
        $fooDefinition = new Definition('BarClass');
        $fooUserDefinition = new Definition('BarUserClass', [new Reference('bar')]);
        $container = new ContainerBuilder();
        $container->setResourceTracking(false);

        $fooDefinition->setPublic(false);

        $container->addDefinitions([
            'bar' => $fooDefinition,
            'bar_user' => $fooUserDefinition->setPublic(true),
        ]);

        $container->compile();
        $this->assertInstanceOf('BarClass', $container->get('bar_user')->bar);
    }

    public function testThrowsExceptionWhenSetServiceOnACompiledContainer()
    {
        $this->expectException('BadMethodCallException');
        $container = new ContainerBuilder();
        $container->setResourceTracking(false);
        $container->register('a', 'stdClass')->setPublic(true);
        $container->compile();
        $container->set('a', new \stdClass());
    }

    public function testThrowsExceptionWhenAddServiceOnACompiledContainer()
    {
        $container = new ContainerBuilder();
        $container->compile();
        $container->set('a', $foo = new \stdClass());
        $this->assertSame($foo, $container->get('a'));
    }

    public function testNoExceptionWhenSetSyntheticServiceOnACompiledContainer()
    {
        $container = new ContainerBuilder();
        $def = new Definition('stdClass');
        $def->setSynthetic(true)->setPublic(true);
        $container->setDefinition('a', $def);
        $container->compile();
        $container->set('a', $a = new \stdClass());
        $this->assertEquals($a, $container->get('a'));
    }

    public function testThrowsExceptionWhenSetDefinitionOnACompiledContainer()
    {
        $this->expectException('BadMethodCallException');
        $container = new ContainerBuilder();
        $container->setResourceTracking(false);
        $container->compile();
        $container->setDefinition('a', new Definition());
    }

    public function testExtensionConfig()
    {
        $container = new ContainerBuilder();

        $configs = $container->getExtensionConfig('foo');
        $this->assertEmpty($configs);

        $first = ['foo' => 'bar'];
        $container->prependExtensionConfig('foo', $first);
        $configs = $container->getExtensionConfig('foo');
        $this->assertEquals([$first], $configs);

        $second = ['ding' => 'dong'];
        $container->prependExtensionConfig('foo', $second);
        $configs = $container->getExtensionConfig('foo');
        $this->assertEquals([$second, $first], $configs);
    }

    public function testAbstractAlias()
    {
        $container = new ContainerBuilder();

        $abstract = new Definition('AbstractClass');
        $abstract->setAbstract(true)->setPublic(true);

        $container->setDefinition('abstract_service', $abstract);
        $container->setAlias('abstract_alias', 'abstract_service')->setPublic(true);

        $container->compile();

        $this->assertSame('abstract_service', (string) $container->getAlias('abstract_alias'));
    }

    public function testLazyLoadedService()
    {
        $loader = new ClosureLoader($container = new ContainerBuilder());
        $loader->load(function (ContainerBuilder $container) {
            $container->set('a', new \BazClass());
            $definition = new Definition('BazClass');
            $definition->setLazy(true);
            $definition->setPublic(true);
            $container->setDefinition('a', $definition);
        });

        $container->setResourceTracking(true);

        $container->compile();

        $r = new \ReflectionProperty($container, 'resources');
        $r->setAccessible(true);
        $resources = $r->getValue($container);

        $classInList = false;
        foreach ($resources as $resource) {
            if ('reflection.BazClass' === (string) $resource) {
                $classInList = true;
                break;
            }
        }

        $this->assertTrue($classInList);
    }

    public function testInlinedDefinitions()
    {
        $container = new ContainerBuilder();

        $definition = new Definition('BarClass');

        $container->register('bar_user', 'BarUserClass')
            ->addArgument($definition)
            ->setProperty('foo', $definition);

        $container->register('bar', 'BarClass')
            ->setProperty('foo', $definition)
            ->addMethodCall('setBaz', [$definition]);

        $barUser = $container->get('bar_user');
        $bar = $container->get('bar');

        $this->assertSame($barUser->foo, $barUser->bar);
        $this->assertSame($bar->foo, $bar->getBaz());
        $this->assertNotSame($bar->foo, $barUser->foo);
    }

    public function testThrowsCircularExceptionForCircularAliases()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException');
        $this->expectExceptionMessage('Circular reference detected for service "app.test_class", path: "app.test_class -> App\TestClass -> app.test_class".');
        $builder = new ContainerBuilder();

        $builder->setAliases([
            'foo' => new Alias('app.test_class'),
            'app.test_class' => new Alias('App\\TestClass'),
            'App\\TestClass' => new Alias('app.test_class'),
        ]);

        $builder->findDefinition('foo');
    }

    public function testInitializePropertiesBeforeMethodCalls()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass');
        $container->register('bar', 'MethodCallClass')
            ->setPublic(true)
            ->setProperty('simple', 'bar')
            ->setProperty('complex', new Reference('foo'))
            ->addMethodCall('callMe');

        $container->compile();

        $this->assertTrue($container->get('bar')->callPassed(), '->compile() initializes properties before method calls');
    }

    public function testAutowiring()
    {
        $container = new ContainerBuilder();

        $container->register(A::class)->setPublic(true);
        $bDefinition = $container->register('b', __NAMESPACE__.'\B');
        $bDefinition->setAutowired(true);
        $bDefinition->setPublic(true);

        $container->compile();

        $this->assertEquals(A::class, (string) $container->getDefinition('b')->getArgument(0));
    }

    public function testClassFromId()
    {
        $container = new ContainerBuilder();

        $unknown = $container->register('Acme\UnknownClass');
        $autoloadClass = $container->register(CaseSensitiveClass::class);
        $container->compile();

        $this->assertSame('Acme\UnknownClass', $unknown->getClass());
        $this->assertEquals(CaseSensitiveClass::class, $autoloadClass->getClass());
    }

    public function testNoClassFromGlobalNamespaceClassId()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('The definition for "DateTime" has no class attribute, and appears to reference a class or interface in the global namespace.');
        $container = new ContainerBuilder();

        $container->register(\DateTime::class);
        $container->compile();
    }

    public function testNoClassFromGlobalNamespaceClassIdWithLeadingSlash()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('The definition for "\DateTime" has no class attribute, and appears to reference a class or interface in the global namespace.');
        $container = new ContainerBuilder();

        $container->register('\\'.\DateTime::class);
        $container->compile();
    }

    public function testNoClassFromNamespaceClassIdWithLeadingSlash()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('The definition for "\Symfony\Component\DependencyInjection\Tests\FooClass" has no class attribute, and appears to reference a class or interface. Please specify the class attribute explicitly or remove the leading backslash by renaming the service to "Symfony\Component\DependencyInjection\Tests\FooClass" to get rid of this error.');
        $container = new ContainerBuilder();

        $container->register('\\'.FooClass::class);
        $container->compile();
    }

    public function testNoClassFromNonClassId()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('The definition for "123_abc" has no class.');
        $container = new ContainerBuilder();

        $container->register('123_abc');
        $container->compile();
    }

    public function testNoClassFromNsSeparatorId()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('The definition for "\foo" has no class.');
        $container = new ContainerBuilder();

        $container->register('\\foo');
        $container->compile();
    }

    public function testServiceLocator()
    {
        $container = new ContainerBuilder();
        $container->register('foo_service', ServiceLocator::class)
            ->setPublic(true)
            ->addArgument([
                'bar' => new ServiceClosureArgument(new Reference('bar_service')),
                'baz' => new ServiceClosureArgument(new TypedReference('baz_service', 'stdClass')),
            ])
        ;
        $container->register('bar_service', 'stdClass')->setArguments([new Reference('baz_service')])->setPublic(true);
        $container->register('baz_service', 'stdClass')->setPublic(false);
        $container->compile();

        $this->assertInstanceOf(ServiceLocator::class, $foo = $container->get('foo_service'));
        $this->assertSame($container->get('bar_service'), $foo->get('bar'));
    }

    public function testUninitializedReference()
    {
        $container = include __DIR__.'/Fixtures/containers/container_uninitialized_ref.php';
        $container->compile();

        $bar = $container->get('bar');

        $this->assertNull($bar->foo1);
        $this->assertNull($bar->foo2);
        $this->assertNull($bar->foo3);
        $this->assertNull($bar->closures[0]());
        $this->assertNull($bar->closures[1]());
        $this->assertNull($bar->closures[2]());
        $this->assertSame([], iterator_to_array($bar->iter));

        $container = include __DIR__.'/Fixtures/containers/container_uninitialized_ref.php';
        $container->compile();

        $container->get('foo1');
        $container->get('baz');

        $bar = $container->get('bar');

        $this->assertEquals(new \stdClass(), $bar->foo1);
        $this->assertNull($bar->foo2);
        $this->assertEquals(new \stdClass(), $bar->foo3);
        $this->assertEquals(new \stdClass(), $bar->closures[0]());
        $this->assertNull($bar->closures[1]());
        $this->assertEquals(new \stdClass(), $bar->closures[2]());
        $this->assertEquals(['foo1' => new \stdClass(), 'foo3' => new \stdClass()], iterator_to_array($bar->iter));
    }

    /**
     * @dataProvider provideAlmostCircular
     */
    public function testAlmostCircular($visibility)
    {
        $container = include __DIR__.'/Fixtures/containers/container_almost_circular.php';

        $foo = $container->get('foo');
        $this->assertSame($foo, $foo->bar->foobar->foo);

        $foo2 = $container->get('foo2');
        $this->assertSame($foo2, $foo2->bar->foobar->foo);

        $this->assertSame([], (array) $container->get('foobar4'));

        $foo5 = $container->get('foo5');
        $this->assertSame($foo5, $foo5->bar->foo);

        $manager = $container->get('manager');
        $this->assertEquals(new \stdClass(), $manager);

        $manager = $container->get('manager2');
        $this->assertEquals(new \stdClass(), $manager);

        $foo6 = $container->get('foo6');
        $this->assertEquals((object) ['bar6' => (object) []], $foo6);

        $this->assertInstanceOf(\stdClass::class, $container->get('root'));

        $manager3 = $container->get('manager3');
        $listener3 = $container->get('listener3');
        $this->assertSame($manager3, $listener3->manager, 'Both should identically be the manager3 service');

        $listener4 = $container->get('listener4');
        $this->assertInstanceOf('stdClass', $listener4);
    }

    public function provideAlmostCircular()
    {
        yield ['public'];
        yield ['private'];
    }

    public function testRegisterForAutoconfiguration()
    {
        $container = new ContainerBuilder();
        $childDefA = $container->registerForAutoconfiguration('AInterface');
        $childDefB = $container->registerForAutoconfiguration('BInterface');
        $this->assertSame(['AInterface' => $childDefA, 'BInterface' => $childDefB], $container->getAutoconfiguredInstanceof());

        // when called multiple times, the same instance is returned
        $this->assertSame($childDefA, $container->registerForAutoconfiguration('AInterface'));
    }

    public function testRegisterAliasForArgument()
    {
        $container = new ContainerBuilder();

        $container->registerAliasForArgument('Foo.bar_baz', 'Some\FooInterface');
        $this->assertEquals(new Alias('Foo.bar_baz'), $container->getAlias('Some\FooInterface $fooBarBaz'));

        $container->registerAliasForArgument('Foo.bar_baz', 'Some\FooInterface', 'Bar_baz.foo');
        $this->assertEquals(new Alias('Foo.bar_baz'), $container->getAlias('Some\FooInterface $barBazFoo'));
    }

    public function testCaseSensitivity()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(true);
        $container->register('Foo', 'stdClass')->setProperty('foo', new Reference('foo'))->setPublic(false);
        $container->register('fOO', 'stdClass')->setProperty('Foo', new Reference('Foo'))->setPublic(true);

        $this->assertSame(['service_container', 'foo', 'Foo', 'fOO', 'Psr\Container\ContainerInterface', 'Symfony\Component\DependencyInjection\ContainerInterface'], $container->getServiceIds());

        $container->compile();

        $this->assertNotSame($container->get('foo'), $container->get('fOO'), '->get() returns the service for the given id, case sensitively');
        $this->assertSame($container->get('fOO')->Foo->foo, $container->get('foo'), '->get() returns the service for the given id, case sensitively');
    }

    public function testParameterWithMixedCase()
    {
        $container = new ContainerBuilder(new ParameterBag(['foo' => 'bar', 'FOO' => 'BAR']));
        $container->register('foo', 'stdClass')
            ->setPublic(true)
            ->setProperty('foo', '%FOO%');

        $container->compile();

        $this->assertSame('BAR', $container->get('foo')->foo);
    }

    public function testArgumentsHaveHigherPriorityThanBindings()
    {
        $container = new ContainerBuilder();
        $container->register('class.via.bindings', CaseSensitiveClass::class)->setArguments([
            'via-bindings',
        ]);
        $container->register('class.via.argument', CaseSensitiveClass::class)->setArguments([
            'via-argument',
        ]);
        $container->register('foo', SimilarArgumentsDummy::class)->setPublic(true)->setBindings([
            CaseSensitiveClass::class => new Reference('class.via.bindings'),
            '$token' => '1234',
        ])->setArguments([
            '$class1' => new Reference('class.via.argument'),
        ]);

        $this->assertSame(['service_container', 'class.via.bindings', 'class.via.argument', 'foo', 'Psr\Container\ContainerInterface', 'Symfony\Component\DependencyInjection\ContainerInterface'], $container->getServiceIds());

        $container->compile();

        $this->assertSame('via-argument', $container->get('foo')->class1->identifier);
        $this->assertSame('via-bindings', $container->get('foo')->class2->identifier);
    }

    public function testUninitializedSyntheticReference()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->setPublic(true)->setSynthetic(true);
        $container->register('bar', 'stdClass')->setPublic(true)->setShared(false)
            ->setProperty('foo', new Reference('foo', ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE));

        $container->compile();

        $this->assertEquals((object) ['foo' => null], $container->get('bar'));

        $container->set('foo', (object) [123]);
        $this->assertEquals((object) ['foo' => (object) [123]], $container->get('bar'));
    }

    public function testIdCanBeAnObjectAsLongAsItCanBeCastToString()
    {
        $id = new Reference('another_service');
        $aliasId = new Reference('alias_id');

        $container = new ContainerBuilder();
        $container->set($id, new \stdClass());
        $container->setAlias($aliasId, 'another_service');

        $this->assertTrue($container->has('another_service'));
        $this->assertTrue($container->has($id));
        $this->assertTrue($container->hasAlias('alias_id'));
        $this->assertTrue($container->hasAlias($aliasId));

        $container->removeAlias($aliasId);
        $container->removeDefinition($id);
    }

    public function testErroredDefinition()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\RuntimeException');
        $this->expectExceptionMessage('Service "errored_definition" is broken.');
        $container = new ContainerBuilder();

        $container->register('errored_definition', 'stdClass')
            ->addError('Service "errored_definition" is broken.')
            ->setPublic(true);

        $container->get('errored_definition');
    }

    public function testServiceLocatorArgument()
    {
        $container = include __DIR__.'/Fixtures/containers/container_service_locator_argument.php';
        $container->compile();

        $locator = $container->get('bar')->locator;

        $this->assertInstanceOf(ServiceLocator::class, $locator);
        $this->assertSame($container->get('foo1'), $locator->get('foo1'));
        $this->assertEquals(new \stdClass(), $locator->get('foo2'));
        $this->assertSame($locator->get('foo2'), $locator->get('foo2'));
        $this->assertEquals(new \stdClass(), $locator->get('foo3'));
        $this->assertNotSame($locator->get('foo3'), $locator->get('foo3'));

        try {
            $locator->get('foo4');
            $this->fail('RuntimeException expected.');
        } catch (RuntimeException $e) {
            $this->assertSame('BOOM', $e->getMessage());
        }

        $this->assertNull($locator->get('foo5'));

        $container->set('foo5', $foo5 = new \stdClass());
        $this->assertSame($foo5, $locator->get('foo5'));
    }

    public function testDecoratedSelfReferenceInvolvingPrivateServices()
    {
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')
            ->setPublic(false)
            ->setProperty('bar', new Reference('foo'));
        $container->register('baz', 'stdClass')
            ->setPublic(false)
            ->setProperty('inner', new Reference('baz.inner'))
            ->setDecoratedService('foo');

        $container->compile();

        $this->assertSame(['service_container'], array_keys($container->getDefinitions()));
    }

    public function testScalarService()
    {
        $c = new ContainerBuilder();
        $c->register('foo', 'string')
            ->setPublic(true)
            ->setFactory([ScalarFactory::class, 'getSomeValue'])
        ;

        $c->compile();

        $this->assertTrue($c->has('foo'));
        $this->assertSame('some value', $c->get('foo'));
    }

    public function testWither()
    {
        $container = new ContainerBuilder();
        $container->register(Foo::class);

        $container
            ->register('wither', Wither::class)
            ->setPublic(true)
            ->setAutowired(true);

        $container->compile();

        $wither = $container->get('wither');
        $this->assertInstanceOf(Foo::class, $wither->foo);
    }
}

class FooClass
{
}

class A
{
}

class B
{
    public function __construct(A $a)
    {
    }
}
