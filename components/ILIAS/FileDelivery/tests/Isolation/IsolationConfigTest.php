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

namespace ILIAS\Tests\FileDelivery\Isolation;

use ILIAS\FileDelivery\Isolation\IsolationConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class IsolationConfigTest extends TestCase
{
    /** @var list<string> */
    private array $artefacts = [];

    public function testDisabledFactory(): void
    {
        $config = IsolationConfig::disabled();

        $this->assertFalse($config->isActivated());
        $this->assertNull($config->getContentDomain());
        $this->assertNull($config->getIliasDomain());
        $this->assertNull($config->getContentHost());
        $this->assertNull($config->getIliasHost());
    }

    public function testFromEmptyArrayIsDisabled(): void
    {
        $config = IsolationConfig::fromArray([]);

        $this->assertFalse($config->isActivated());
        $this->assertNull($config->getContentDomain());
        $this->assertNull($config->getIliasDomain());
    }

    public function testFromArrayWithFullSpecExample(): void
    {
        // exact artefact shape from the feature spec
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://test11.iliascontent.de',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://test11.ilias.de',
        ]);

        $this->assertTrue($config->isActivated());
        $this->assertSame('https://test11.iliascontent.de', $config->getContentDomain());
        $this->assertSame('https://test11.ilias.de', $config->getIliasDomain());
        $this->assertSame('test11.iliascontent.de', $config->getContentHost());
        $this->assertSame('test11.ilias.de', $config->getIliasHost());
    }

    public function testBareHostnamesGetHttpsScheme(): void
    {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'app.example.org',
        ]);

        $this->assertSame('https://content.example.org', $config->getContentDomain());
        $this->assertSame('https://app.example.org', $config->getIliasDomain());
        $this->assertSame('content.example.org', $config->getContentHost());
        $this->assertSame('app.example.org', $config->getIliasHost());
    }

    public function testTrailingSlashIsStripped(): void
    {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org/',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org/',
        ]);

        $this->assertSame('https://content.example.org', $config->getContentDomain());
        $this->assertSame('https://app.example.org', $config->getIliasDomain());
    }

    public function testActivatedWithoutContentDomainIsForcedOff(): void
    {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);

        $this->assertFalse($config->isActivated());
    }

    public function testActivatedWithoutIliasDomainStaysActiveWithNullIliasDomain(): void
    {
        // The ILIAS domain is optional in the artefact: when http_path could not
        // be resolved at build time it is simply null (the CORS allow-origin
        // header is then omitted), isolation stays on.
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
        ]);

        $this->assertTrue($config->isActivated());
        $this->assertSame('https://content.example.org', $config->getContentDomain());
        $this->assertNull($config->getIliasDomain());
    }

    public function testInactiveStillParsesDomains(): void
    {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => false,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);

        $this->assertFalse($config->isActivated());
        $this->assertSame('https://content.example.org', $config->getContentDomain());
        $this->assertSame('https://app.example.org', $config->getIliasDomain());
    }

    #[DataProvider('unusableDomainProvider')]
    public function testUnusableDomainsNormalizeToNull(mixed $value): void
    {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => $value,
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);

        // unusable content domain -> null -> isolation forced off
        $this->assertNull($config->getContentDomain());
        $this->assertFalse($config->isActivated());
    }

    public static function unusableDomainProvider(): \Iterator
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
        yield 'scheme without host' => ['https://'];
        yield 'non-string int' => [123];
        yield 'non-string bool' => [true];
        yield 'null' => [null];
        yield 'array' => [['x']];
    }

    public function testToArrayUsesSpecKeys(): void
    {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);

        $this->assertSame([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ], $config->toArray());
    }

    public function testFromArrayToArrayRoundtripIsStable(): void
    {
        $source = [
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'app.example.org',
        ];

        $once = IsolationConfig::fromArray($source)->toArray();
        $twice = IsolationConfig::fromArray($once)->toArray();

        $this->assertSame($once, $twice);
    }

    #[DataProvider('originNormalizationProvider')]
    public function testValidValuesNormalizeToBareOrigin(
        string $input,
        string $expected_domain,
        string $expected_host
    ): void {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => false,
            IsolationConfig::KEY_CONTENT_DOMAIN => $input,
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);

        $this->assertSame($expected_domain, $config->getContentDomain());
        $this->assertSame($expected_host, $config->getContentHost());
    }

    public static function originNormalizationProvider(): \Iterator
    {
        yield 'bare host gets https' => ['content.example.org', 'https://content.example.org', 'content.example.org'];
        yield 'https kept' => ['https://content.example.org', 'https://content.example.org', 'content.example.org'];
        yield 'http kept' => ['http://localhost', 'http://localhost', 'localhost'];
        yield 'port preserved' => ['https://content.example.org:8443', 'https://content.example.org:8443', 'content.example.org'];
        yield 'host lowercased' => ['https://Content.Example.ORG', 'https://content.example.org', 'content.example.org'];
        yield 'root slash stripped' => ['https://content.example.org/', 'https://content.example.org', 'content.example.org'];
    }

    #[DataProvider('rejectedOriginProvider')]
    public function testNonBareOriginsAreRejected(string $input): void
    {
        $config = IsolationConfig::fromArray([
            IsolationConfig::KEY_ACTIVATED => false,
            IsolationConfig::KEY_CONTENT_DOMAIN => $input,
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ]);

        $this->assertNull($config->getContentDomain());
        $this->assertNull($config->getContentHost());
    }

    public static function rejectedOriginProvider(): \Iterator
    {
        yield 'with path' => ['https://content.example.org/foo'];
        yield 'with userinfo' => ['https://user:pass@content.example.org'];
        yield 'with query' => ['https://content.example.org/?a=b'];
        yield 'with fragment' => ['https://content.example.org/#x'];
        yield 'ftp scheme' => ['ftp://content.example.org'];
        yield 'file scheme' => ['file:///etc/passwd'];
        yield 'javascript scheme' => ['javascript:alert(1)'];
    }

    #[DataProvider('trustedOriginProvider')]
    public function testOriginFromUrlReducesTrustedAppUrlToBareOrigin(?string $input, ?string $expected): void
    {
        // originFromUrl is used at build time to derive the ILIAS domain from
        // http_path, which may carry a sub-directory path that must be discarded.
        $this->assertSame($expected, IsolationConfig::originFromUrl($input));
    }

    public static function trustedOriginProvider(): \Iterator
    {
        yield 'bare origin' => ['https://app.example.org', 'https://app.example.org'];
        yield 'path discarded' => ['https://app.example.org/ilias/index.php', 'https://app.example.org'];
        yield 'query discarded' => ['https://app.example.org/?a=b', 'https://app.example.org'];
        yield 'bare host gets https' => ['app.example.org', 'https://app.example.org'];
        yield 'http kept' => ['http://localhost/sub', 'http://localhost'];
        yield 'port preserved' => ['https://app.example.org:8443/sub', 'https://app.example.org:8443'];
        yield 'empty stays null' => ['', null];
        yield 'null stays null' => [null, null];
        yield 'non-http scheme rejected' => ['ftp://app.example.org', null];
    }

    private function artefact(string $php): string
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'iso_artefact_') . '.php';
        file_put_contents($path, $php);
        $this->artefacts[] = $path;
        return $path;
    }

    protected function tearDown(): void
    {
        foreach ($this->artefacts as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->artefacts = [];
    }

    public function testFromArtefactWithoutFileIsDisabled(): void
    {
        $config = IsolationConfig::fromArtefact(sys_get_temp_dir() . '/does_not_exist_isolation.php');

        $this->assertFalse($config->isActivated());
        $this->assertNull($config->getContentDomain());
        $this->assertNull($config->getIliasDomain());
    }

    public function testFromArtefactReadsTheStoredSettings(): void
    {
        $path = $this->artefact(
            "<?php return ['activated' => true, 'content_domain' => 'content.example.org',"
            . " 'ilias_domain' => 'https://app.example.org'];"
        );
        $config = IsolationConfig::fromArtefact($path);

        $this->assertTrue($config->isActivated());
        $this->assertSame('https://content.example.org', $config->getContentDomain());
        $this->assertSame('https://app.example.org', $config->getIliasDomain());
    }

    public function testFromArtefactWithNonArrayContentIsDisabled(): void
    {
        $config = IsolationConfig::fromArtefact($this->artefact('<?php return "nonsense";'));

        $this->assertFalse($config->isActivated());
        $this->assertNull($config->getContentDomain());
    }

    public function testFromArtefactWithoutReturnValueIsDisabled(): void
    {
        $config = IsolationConfig::fromArtefact($this->artefact('<?php // nothing returned'));

        $this->assertFalse($config->isActivated());
        $this->assertNull($config->getContentDomain());
    }
}
