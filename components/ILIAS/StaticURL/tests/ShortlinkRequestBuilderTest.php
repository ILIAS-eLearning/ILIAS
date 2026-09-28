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

namespace ILIAS\StaticURL\Tests;

use PHPUnit\Framework\MockObject\Stub;
use ILIAS\HTTP\Services;
use PHPUnit\Framework\Attributes\DataProvider;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Handler\LegacyGotoHandler;
use ILIAS\StaticURL\Request\BundledRequestBuilder;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Shortlinks\Handler as ShortlinkHandler;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use ILIAS\Data\URI;

require_once "Base.php";

/**
 * Guards the precedence of the request builders bundled in the BundledRequestBuilder.
 *
 * Old permanent links of the form /goto_<client_id>_<type>_<id>.html are rewritten to
 * goto.php?client_id=...&target=... by the ILIAS rewrite rules. That rewrite is
 * server-internal, so REQUEST_URI keeps the original .html path. The
 * ShortlinkRequestBuilder must not capture those requests - otherwise they end up in the
 * shortlink handler, which does not know the alias and answers with a 404.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ShortlinkRequestBuilderTest extends Base
{
    private Stub $request_stub;
    private Stub $http_stub;
    private Factory $refinery;
    private BundledRequestBuilder $subject;

    protected function setUp(): void
    {
        $this->http_stub = $this->createStub(Services::class);
        $this->request_stub = $this->createStub(ServerRequestInterface::class);
        $this->http_stub->method('request')->willReturn($this->request_stub);

        $this->refinery = new Factory(
            new \ILIAS\Data\Factory(),
            $this->createStub(\ilLanguage::class),
        );

        $this->subject = new BundledRequestBuilder();
    }

    private function prepareRequest(string $called_url): void
    {
        $uri = new URI($called_url);
        $this->request_stub->method('getUri')->willReturn(new \GuzzleHttp\Psr7\Uri($called_url));
        $this->request_stub->method('getQueryParams')->willReturn($uri->getParameters());
        $this->http_stub->method('wrapper')->willReturn(new WrapperFactory($this->request_stub));
    }

    /**
     * The URLs below are the ones the browser sends; the query string is what the rewrite
     * rules in components/ILIAS/Init/resources/.htaccess produce for them. The path is left
     * untouched by the rewrite, which is exactly what made these links fail.
     */
    public static function legacyGotoProvider(): \Iterator
    {
        yield 'rewritten .html link' => [
            'https://ilias.domain/goto_docu_cat_993241.html?client_id=docu&target=cat_993241',
            'cat_993241',
        ];
        yield 'rewritten .html link with lang' => [
            'https://ilias.domain/goto_docu_crs_256.html?client_id=docu&target=crs_256&lang=de',
            'crs_256',
        ];
        yield 'rewritten .html link in sub directory' => [
            'https://ilias.domain/sub/goto_docu_cat_993241.html?client_id=docu&target=cat_993241',
            'cat_993241',
        ];
        yield 'rewritten .html user link' => [
            'https://ilias.domain/goto_docu_usr_root.html?client_id=docu&target=usr_root',
            'usr_root',
        ];
        yield 'rewritten .html wiki page link' => [
            'https://ilias.domain/goto_docu_wiki_wpage_6314_1357.html?client_id=docu&target=wiki_wpage_6314_1357',
            'wiki_wpage_6314_1357',
        ];
        yield 'direct goto.php' => [
            'https://ilias.domain/goto.php?client_id=docu&target=root_1',
            'root_1',
        ];
        yield 'direct goto.php in sub directory' => [
            'https://ilias.domain/sub/goto.php?client_id=docu&target=cat_993241',
            'cat_993241',
        ];
    }

    #[DataProvider('legacyGotoProvider')]
    public function testLegacyGotoLinksAreNotCapturedByShortlinkBuilder(
        string $called_url,
        string $expected_target
    ): void {
        $this->prepareRequest($called_url);

        $request = $this->subject->buildRequest($this->http_stub, $this->refinery, []);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame(LegacyGotoHandler::NAMESPACE, $request->getNamespace());
        $this->assertSame(
            $expected_target,
            $request->getAdditionalParameters()[LegacyGotoHandler::TARGET] ?? null
        );
    }

    /**
     * If a StaticURL handler is registered for the type of an old permanent link, the request
     * must be routed to that handler instead of the deprecated LegacyGotoHandler.
     */
    public function testRewrittenLegacyLinkReachesRegisteredHandler(): void
    {
        $this->prepareRequest(
            'https://ilias.domain/goto_docu_wiki_1357.html?client_id=docu&target=wiki_1357'
        );

        $request = $this->subject->buildRequest(
            $this->http_stub,
            $this->refinery,
            ['wiki' => $this->createStub(Handler::class)]
        );

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('wiki', $request->getNamespace());
        $this->assertSame(1357, $request->getReferenceId()?->toInt());
    }

    public static function shortlinkProvider(): \Iterator
    {
        // A shortlink alias is rewritten to goto.php/shortlink/<alias>, but REQUEST_URI keeps
        // the alias path and no target query parameter is added.
        yield 'alias' => ['https://ilias.domain/MyShortlink', 'MyShortlink'];
        yield 'alias with trailing slash' => ['https://ilias.domain/MyShortlink/', 'MyShortlink'];
        yield 'alias in sub directory' => ['https://ilias.domain/sub/My-Short_link1', 'My-Short_link1'];
    }

    #[DataProvider('shortlinkProvider')]
    public function testShortlinksAreStillCapturedByShortlinkBuilder(
        string $called_url,
        string $expected_alias
    ): void {
        $this->prepareRequest($called_url);

        $request = $this->subject->buildRequest($this->http_stub, $this->refinery, []);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame(ShortlinkHandler::SHORTLINK_NAMESPACE, $request->getNamespace());
        $this->assertSame($expected_alias, $request->getAdditionalParameters()[0] ?? null);
    }
}
