<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Tests\FileDelivery\Services;

use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\FileDelivery\Delivery\LegacyDelivery;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
use ILIAS\FileDelivery\Services;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\Data\URI;
use ILIAS\HTTP\Path\HttpPathProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class BaseURITest extends TestCase
{
    public function testHostIsTakenFromTheRequestAndPathFromTheHttpPath(): void
    {
        $services = $this->servicesFor(
            new ServerRequest('GET', 'https://alias.example.org/ilias/index.php'),
            'https://ilias.example.org/ilias'
        );

        $this->assertSame('https://alias.example.org/ilias', $this->baseURIOf($services));
    }

    public function testPortOfTheRequestIsKept(): void
    {
        $services = $this->servicesFor(
            new ServerRequest('GET', 'http://localhost:8080/ilias/index.php'),
            'http://localhost/ilias'
        );

        $this->assertSame('http://localhost:8080/ilias', $this->baseURIOf($services));
    }

    /**
     * Cron jobs run without a request, see https://mantis.ilias.de/view.php?id=47807
     */
    public function testHttpPathIsUsedWithoutARequest(): void
    {
        $services = $this->servicesFor(
            new ServerRequest('GET', ''),
            'https://ilias.example.org/ilias/'
        );

        $this->assertSame('https://ilias.example.org/ilias', $this->baseURIOf($services));
    }

    /**
     * The login page is requested as "/ilias/", whose dirname drops the installation
     * path, see https://mantis.ilias.de/view.php?id=48124
     */
    public function testPathIsTakenFromTheHttpPathOnARequestWithoutScript(): void
    {
        $services = $this->servicesFor(
            new ServerRequest('GET', 'https://ilias.example.org/ilias/'),
            'https://ilias.example.org/ilias'
        );

        $this->assertSame('https://ilias.example.org/ilias', $this->baseURIOf($services));
    }

    public function testPathIsTakenFromTheHttpPathOnARequestToASubdirectory(): void
    {
        $services = $this->servicesFor(
            new ServerRequest('GET', 'https://ilias.example.org/ilias/Customizing/global/plugins/run.php'),
            'https://ilias.example.org/ilias'
        );

        $this->assertSame('https://ilias.example.org/ilias', $this->baseURIOf($services));
    }

    public function testHttpPathWithoutPathPointsToTheRoot(): void
    {
        $services = $this->servicesFor(
            new ServerRequest('GET', 'https://ilias.example.org/'),
            'https://ilias.example.org'
        );

        $this->assertSame('https://ilias.example.org', $this->baseURIOf($services));
    }

    /**
     * Without any http path, the request is all there is.
     */
    public function testPathIsTakenFromTheRequestWithoutHttpPath(): void
    {
        $services = $this->servicesFor(
            new ServerRequest('GET', 'https://ilias.example.org/ilias/index.php'),
            null
        );

        $this->assertSame('https://ilias.example.org/ilias', $this->baseURIOf($services));
    }

    private function servicesFor(ServerRequestInterface $request, ?string $http_path): Services
    {
        $http_path_provider = $this->createStub(HttpPathProvider::class);
        $http_path_provider->method('getHttpPath')->willReturn(
            $http_path === null ? null : new URI(rtrim($http_path, '/'))
        );

        $http = $this->createMock(\ILIAS\HTTP\Services::class);
        $http->method('request')->willReturn($request);

        // the delivery classes are final, so they are built for real. Only the
        // http services and the http path matter for the base URI.
        $data_signer = new DataSigner(
            new SecretKeyRotation(new SecretKey(str_repeat('a', 32)))
        );
        $response_builder = $this->createMock(ResponseBuilder::class);

        return new Services(
            new StreamDelivery($data_signer, $http, $response_builder, $response_builder),
            new LegacyDelivery($http, $response_builder, $response_builder),
            $data_signer,
            $http,
            $http_path_provider
        );
    }

    private function baseURIOf(Services $services): string
    {
        return (new \ReflectionMethod($services, 'getBaseURI'))->invoke($services);
    }
}
