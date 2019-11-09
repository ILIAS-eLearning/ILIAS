<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Tests\Generator\Dumper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\CompiledUrlGenerator;
use Symfony\Component\Routing\Generator\Dumper\CompiledUrlGeneratorDumper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CompiledUrlGeneratorDumperTest extends TestCase
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var CompiledUrlGeneratorDumper
     */
    private $generatorDumper;

    /**
     * @var string
     */
    private $testTmpFilepath;

    /**
     * @var string
     */
    private $largeTestTmpFilepath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeCollection = new RouteCollection();
        $this->generatorDumper = new CompiledUrlGeneratorDumper($this->routeCollection);
        $this->testTmpFilepath = sys_get_temp_dir().'/php_generator.'.$this->getName().'.php';
        $this->largeTestTmpFilepath = sys_get_temp_dir().'/php_generator.'.$this->getName().'.large.php';
        @unlink($this->testTmpFilepath);
        @unlink($this->largeTestTmpFilepath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->testTmpFilepath);

        $this->routeCollection = null;
        $this->generatorDumper = null;
        $this->testTmpFilepath = null;
    }

    public function testDumpWithRoutes()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}'));
        $this->routeCollection->add('Test2', new Route('/testing2'));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext('/app.php'));

        $absoluteUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_URL);
        $absoluteUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $relativeUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_PATH);

        $this->assertEquals('http://localhost/app.php/testing/bar', $absoluteUrlWithParameter);
        $this->assertEquals('http://localhost/app.php/testing2', $absoluteUrlWithoutParameter);
        $this->assertEquals('/app.php/testing/bar', $relativeUrlWithParameter);
        $this->assertEquals('/app.php/testing2', $relativeUrlWithoutParameter);
    }

    public function testDumpWithSimpleLocalizedRoutes()
    {
        $this->routeCollection->add('test', (new Route('/foo')));
        $this->routeCollection->add('test.en', (new Route('/testing/is/fun'))->setDefault('_locale', 'en')->setDefault('_canonical_route', 'test'));
        $this->routeCollection->add('test.nl', (new Route('/testen/is/leuk'))->setDefault('_locale', 'nl')->setDefault('_canonical_route', 'test'));

        $code = $this->generatorDumper->dump();
        file_put_contents($this->testTmpFilepath, $code);

        $context = new RequestContext('/app.php');
        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, $context, null, 'en');

        $urlWithDefaultLocale = $projectUrlGenerator->generate('test');
        $urlWithSpecifiedLocale = $projectUrlGenerator->generate('test', ['_locale' => 'nl']);
        $context->setParameter('_locale', 'en');
        $urlWithEnglishContext = $projectUrlGenerator->generate('test');
        $context->setParameter('_locale', 'nl');
        $urlWithDutchContext = $projectUrlGenerator->generate('test');

        $this->assertEquals('/app.php/testing/is/fun', $urlWithDefaultLocale);
        $this->assertEquals('/app.php/testen/is/leuk', $urlWithSpecifiedLocale);
        $this->assertEquals('/app.php/testing/is/fun', $urlWithEnglishContext);
        $this->assertEquals('/app.php/testen/is/leuk', $urlWithDutchContext);

        // test with full route name
        $this->assertEquals('/app.php/testing/is/fun', $projectUrlGenerator->generate('test.en'));

        $context->setParameter('_locale', 'de_DE');
        // test that it fall backs to another route when there is no matching localized route
        $this->assertEquals('/app.php/foo', $projectUrlGenerator->generate('test'));
    }

    public function testDumpWithRouteNotFoundLocalizedRoutes()
    {
        $this->expectException('Symfony\Component\Routing\Exception\RouteNotFoundException');
        $this->expectExceptionMessage('Unable to generate a URL for the named route "test" as such route does not exist.');
        $this->routeCollection->add('test.en', (new Route('/testing/is/fun'))->setDefault('_locale', 'en')->setDefault('_canonical_route', 'test'));

        $code = $this->generatorDumper->dump();
        file_put_contents($this->testTmpFilepath, $code);

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext('/app.php'), null, 'pl_PL');
        $projectUrlGenerator->generate('test');
    }

    public function testDumpWithFallbackLocaleLocalizedRoutes()
    {
        $this->routeCollection->add('test.en', (new Route('/testing/is/fun'))->setDefault('_canonical_route', 'test'));
        $this->routeCollection->add('test.nl', (new Route('/testen/is/leuk'))->setDefault('_canonical_route', 'test'));
        $this->routeCollection->add('test.fr', (new Route('/tester/est/amusant'))->setDefault('_canonical_route', 'test'));

        $code = $this->generatorDumper->dump();
        file_put_contents($this->testTmpFilepath, $code);

        $context = new RequestContext('/app.php');
        $context->setParameter('_locale', 'en_GB');
        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, $context, null, null);

        // test with context _locale
        $this->assertEquals('/app.php/testing/is/fun', $projectUrlGenerator->generate('test'));
        // test with parameters _locale
        $this->assertEquals('/app.php/testen/is/leuk', $projectUrlGenerator->generate('test', ['_locale' => 'nl_BE']));

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext('/app.php'), null, 'fr_CA');
        // test with default locale
        $this->assertEquals('/app.php/tester/est/amusant', $projectUrlGenerator->generate('test'));
    }

    public function testDumpWithTooManyRoutes()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}'));
        for ($i = 0; $i < 32769; ++$i) {
            $this->routeCollection->add('route_'.$i, new Route('/route_'.$i));
        }
        $this->routeCollection->add('Test2', new Route('/testing2'));

        file_put_contents($this->largeTestTmpFilepath, $this->generatorDumper->dump());
        $this->routeCollection = $this->generatorDumper = null;

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->largeTestTmpFilepath, new RequestContext('/app.php'));

        $absoluteUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_URL);
        $absoluteUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $relativeUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_PATH);

        $this->assertEquals('http://localhost/app.php/testing/bar', $absoluteUrlWithParameter);
        $this->assertEquals('http://localhost/app.php/testing2', $absoluteUrlWithoutParameter);
        $this->assertEquals('/app.php/testing/bar', $relativeUrlWithParameter);
        $this->assertEquals('/app.php/testing2', $relativeUrlWithoutParameter);
    }

    public function testDumpWithoutRoutes()
    {
        $this->expectException('InvalidArgumentException');
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext('/app.php'));

        $projectUrlGenerator->generate('Test', []);
    }

    public function testGenerateNonExistingRoute()
    {
        $this->expectException('Symfony\Component\Routing\Exception\RouteNotFoundException');
        $this->routeCollection->add('Test', new Route('/test'));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext());
        $projectUrlGenerator->generate('NonExisting', []);
    }

    public function testDumpForRouteWithDefaults()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}', ['foo' => 'bar']));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext());
        $url = $projectUrlGenerator->generate('Test', []);

        $this->assertEquals('/testing', $url);
    }

    public function testDumpWithSchemeRequirement()
    {
        $this->routeCollection->add('Test1', new Route('/testing', [], [], [], '', ['ftp', 'https']));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext('/app.php'));

        $absoluteUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_PATH);

        $this->assertEquals('ftp://localhost/app.php/testing', $absoluteUrl);
        $this->assertEquals('ftp://localhost/app.php/testing', $relativeUrl);

        $projectUrlGenerator = new CompiledUrlGenerator(require $this->testTmpFilepath, new RequestContext('/app.php', 'GET', 'localhost', 'https'));

        $absoluteUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_PATH);

        $this->assertEquals('https://localhost/app.php/testing', $absoluteUrl);
        $this->assertEquals('/app.php/testing', $relativeUrl);
    }
}
