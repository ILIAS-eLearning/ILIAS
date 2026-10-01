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

namespace ILIAS\Tests\HTTP\Path;

use ILIAS\Data\URI;
use ILIAS\HTTP\Path\ArtifactHttpPathProvider;
use ILIAS\HTTP\Path\CascadingHttpPathProvider;
use ILIAS\HTTP\Path\HttpPathProvider;
use ILIAS\HTTP\Path\IniHttpPathProvider;
use PHPUnit\Framework\TestCase;

class HttpPathProviderTest extends TestCase
{
    private ?string $artifact = null;

    protected function tearDown(): void
    {
        if ($this->artifact !== null && is_file($this->artifact)) {
            unlink($this->artifact);
        }
    }

    public function testCascadeReturnsTheFirstKnownPath(): void
    {
        $provider = new CascadingHttpPathProvider(
            $this->providerFor(null),
            $this->providerFor('https://second.example.org/ilias'),
            $this->providerFor('https://third.example.org/ilias')
        );

        $this->assertSame('https://second.example.org/ilias', $provider->getHttpPath()?->getBaseURI());
    }

    public function testCascadeReturnsNullIfNoProviderKnowsThePath(): void
    {
        $this->assertNull((new CascadingHttpPathProvider($this->providerFor(null)))->getHttpPath());
        $this->assertNull((new CascadingHttpPathProvider())->getHttpPath());
    }

    public function testArtifactProvidesTheStoredPath(): void
    {
        $provider = new ArtifactHttpPathProvider(
            $this->artifactWith("<?php return ['http_path' => 'https://ilias.example.org/ilias/'];")
        );

        $this->assertSame('https://ilias.example.org/ilias', $provider->getHttpPath()?->getBaseURI());
    }

    public function testArtifactWithoutPathProvidesNothing(): void
    {
        $this->assertNull((new ArtifactHttpPathProvider($this->artifactWith('<?php return [];')))->getHttpPath());
    }

    public function testMissingArtifactProvidesNothing(): void
    {
        $this->assertNull((new ArtifactHttpPathProvider('/does/not/exist/http_path.php'))->getHttpPath());
    }

    public function testIniProvidesTheConfiguredPath(): void
    {
        $this->assertSame(
            'https://ilias.example.org/ilias',
            (new IniHttpPathProvider($this->iniWith('https://ilias.example.org/ilias/')))->getHttpPath()?->getBaseURI()
        );
    }

    public function testIniWithoutPathProvidesNothing(): void
    {
        $this->assertNull((new IniHttpPathProvider($this->iniWith('')))->getHttpPath());
    }

    public function testIniWithInvalidPathProvidesNothing(): void
    {
        $this->assertNull((new IniHttpPathProvider($this->iniWith('not a url')))->getHttpPath());
    }

    private function providerFor(?string $http_path): HttpPathProvider
    {
        $provider = $this->createStub(HttpPathProvider::class);
        $provider->method('getHttpPath')->willReturn($http_path === null ? null : new URI($http_path));
        return $provider;
    }

    private function artifactWith(string $content): string
    {
        $this->artifact = tempnam(sys_get_temp_dir(), 'http_path_');
        file_put_contents($this->artifact, $content);
        return $this->artifact;
    }

    private function iniWith(string $http_path): \ilIniFile
    {
        $ini = $this->createStub(\ilIniFile::class);
        $ini->method('readVariable')->willReturnMap([['server', 'http_path', $http_path]]);
        return $ini;
    }
}
