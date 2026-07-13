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

namespace Component\Layout\Page;

require_once('vendor/composer/vendor/autoload.php');
require_once(__DIR__ . '/../../../Base.php');

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Legacy\Content;
use ILIAS\Data\Link as DataLink;
use ILIAS\UI\Implementation\Component\Link;
use ILIAS_UI_TestBase;
use ILIAS\UI\Implementation\Component\Layout\Page;
use NoUIFactory;
use PHPUnit\Framework\Attributes\DataProvider;

class MailPageTest extends ILIAS_UI_TestBase
{
    protected Page\Mail $mailpage;
    protected Page\Factory $factory;
    protected string $logo;
    protected string $installation_title;
    protected string $stylesheet_path;
    protected URI $uri;
    protected Content $html_content;
    protected DataLink $footer_url;

    public function setUp(): void
    {
        $this->html_content = $this->createMock(Content::class);
        $this->html_content->method('getContent')->willReturn('ILIAS HTML Content');

        $this->logo = 'cid:ILIAS Logo';
        $this->installation_title = 'ILIAS Installation Title';
        $this->stylesheet_path = __FILE__;
        $this->uri = $this->createMock(URI::class);
        $this->uri->method('getBaseURI')->willReturn('ILIAS Link URI');
        $this->footer_url = $this->createMock(DataLink::class);
        $this->footer_url->method('getLabel')->willReturn('ILIAS Link Label');
        $this->footer_url->method('getURL')->willReturn($this->uri);

        $this->factory = new Page\Factory();
        $this->mailpage = $this->factory->mail(
            $this->stylesheet_path,
            $this->logo,
            $this->installation_title,
            $this->html_content,
            $this->footer_url,
        );
    }

    public function testConstruction(): void
    {
        static::assertInstanceOf(
            Page\Mail::class,
            $this->mailpage,
        );
    }

    public static function provideGetData(): array
    {
        return [
            ['html_content', 'getContent', 'assertContains'],
            ['logo', 'getLogoURL', 'assertEquals'],
            ['installation_title', 'getInstallationTitle', 'assertEquals'],
            ['stylesheet_path', 'getStyleSheetPath', 'assertEquals'],
            ['footer_url', 'getFooterURL', 'assertEquals'],
        ];
    }

    #[DataProvider('provideGetData')]
    public function testGet(mixed $expected, string $method_to_call, string $assert_method): void
    {
        static::$assert_method(
            $this->$expected,
            $this->mailpage->$method_to_call(),
        );
    }

    public function testRenderMailPage(): void
    {
        $renderer = $this->getDefaultRenderer(null, [$this->html_content]);
        $mailpage = $this->factory->mail(
            $this->stylesheet_path,
            $this->logo,
            $this->installation_title,
            $this->html_content,
            $this->footer_url,
        );

        $html = $this->brutallyTrimHTML($renderer->render($mailpage));
        $expected = $this->brutallyTrimHTML('
<body>
<table class="table">
	<tr class="header">
		<td class="spacing">
			<table class="w-750 center">
				<tr class="rowheight"></tr>
				<tr>
					<td class="image-data">
						<img class="logo" src="' . $this->logo . '" alt="Logo">
					</td>
					<td class="installation-text">
						<span>
							<strong>' . $this->installation_title . '</strong>
						</span>
					</td>
				</tr>
				<tr class="rowheight"></tr>
			</table>
		</td>
	</tr>
	<tr class="content">
		<td class="spacing">
			<table class="w-750 center">
				<tr class="headerheight"></tr>
				<tr>
					<td class="font-definition">
						' . $renderer->render($this->html_content) . '
					</td>
				</tr>
				<tr class="headerheight"></tr>
			</table>
		</td>
	</tr>
	<tr class="footer">
		<td class="spacing">
			<table class="w-750 center">
				<tr>
					<td class="font-definition">
						<span>' . $this->installation_title . '</span>
						<br>
						<a href="ILIAS Link URI">ILIAS Link Label</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>');

        static::assertStringContainsString($expected, $html);
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function link(): Link\Factory
            {
                return new Link\Factory();
            }
        };
    }
}
