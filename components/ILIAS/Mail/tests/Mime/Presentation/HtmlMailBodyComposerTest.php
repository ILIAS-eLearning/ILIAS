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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Mail\Mime\Presentation\Asset\AssetFile;
use ILIAS\Mail\Mime\Presentation\Asset\InlineImages;
use ILIAS\Mail\Mime\Presentation\Asset\MailAssets;
use ILIAS\Mail\Mime\Presentation\HtmlMailBodyComposer;
use ILIAS\Mail\Mime\Presentation\MailBodySource;
use ILIAS\Mail\Mime\Presentation\PlainTextMailBodyComposer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\String\Group as StringGroup;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Layout\Factory as LayoutFactory;
use ILIAS\UI\Component\Layout\Page\Factory as PageFactory;
use ILIAS\UI\Component\Layout\Page\Mail as MailPage;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Component\Legacy\Factory as LegacyFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HtmlMailBodyComposerTest extends TestCase
{
    private const string STYLE_SHEET = 'mail.css';
    private const string INSTALLATION_TITLE = 'ILIAS';
    private const string INSTALLATION_URL = 'https://example.ilias.de';
    private const string RENDERED_BODY = '<html>rendered-mail</html>';

    private string $style_sheet_path;

    protected function setUp(): void
    {
        $this->skipIfVfsStreamNotAvailable();

        vfsStream::setup();
        vfsStream::create([
            self::STYLE_SHEET => 'body{}',
        ]);

        $this->style_sheet_path = vfsStream::url('root/' . self::STYLE_SHEET);
    }

    /**
     * @return Generator<string, array{0: string, 1: string}>
     */
    public static function composeTransformsTheBodyAccordingToHtmlDetectionProvider(): Generator
    {
        $plain_text = "Hello\nMail";
        $paragraph = "<p>Hello</p>\nMail";
        $line_break = "Hello<br>Mail\nForum";
        $allowed_inline = "Hello <b>Mail</b>\nForum";
        $other_markup = "Hello <em>Mail</em>\nForum";

        yield from [
            'plain text is converted with nl2br' => [
                $plain_text,
                nl2br($plain_text),
            ],
            'paragraph block html is kept without nl2br' => [
                $paragraph,
                $paragraph,
            ],
            'br block html is kept without nl2br' => [
                $line_break,
                $line_break,
            ],
            'allowed inline tags still receive nl2br' => [
                $allowed_inline,
                nl2br($allowed_inline),
            ],
            'other markup tags skip nl2br' => [
                $other_markup,
                $other_markup,
            ],
        ];
    }

    #[DataProvider('composeTransformsTheBodyAccordingToHtmlDetectionProvider')]
    public function testComposeTransformsTheBodyAccordingToHtmlDetection(
        string $body,
        string $expected_html
    ): void {
        $clickable = $this->createMock(Transformation::class);
        $clickable->expects($this->once())
            ->method('transform')
            ->with($expected_html)
            ->willReturnArgument(0);

        $this->sut($clickable)->compose(new MailBodySource($body));
    }

    public function testComposeUsesTheRendererOutputAsBodyAndPlainTextAsAlternative(): void
    {
        $clickable_html = 'Hello <b>Mail</b> and more';
        $clickable = $this->createStub(Transformation::class);
        $clickable->method('transform')->willReturn($clickable_html);

        $composed = $this->sut($clickable)->compose(
            new MailBodySource('ignored source body')
        );

        $this->assertSame(self::RENDERED_BODY, $composed->body());
        $this->assertSame('Hello Mail and more', $composed->alternativeBody());
    }

    private function sut(Transformation $clickable): HtmlMailBodyComposer
    {
        $assets = $this->createStub(MailAssets::class);
        $assets->method('styleSheet')->willReturn(new AssetFile($this->style_sheet_path));
        $assets->method('inlineImages')->willReturn(InlineImages::none());

        $legacy = $this->createStub(LegacyFactory::class);
        $legacy->method('content')->willReturn($this->createStub(Content::class));

        $page = $this->createStub(PageFactory::class);
        $page->method('mail')->willReturn($this->createStub(MailPage::class));

        $layout = $this->createStub(LayoutFactory::class);
        $layout->method('page')->willReturn($page);

        $ui = $this->createStub(UIFactory::class);
        $ui->method('layout')->willReturn($layout);
        $ui->method('legacy')->willReturn($legacy);

        $string_group = $this->createStub(StringGroup::class);
        $string_group->method('makeClickable')->willReturn($clickable);

        $refinery = $this->createStub(Refinery::class);
        $refinery->method('string')->willReturn($string_group);

        return new HtmlMailBodyComposer(
            $assets,
            new PlainTextMailBodyComposer(),
            $ui,
            $this->rendererReturning(self::RENDERED_BODY),
            $refinery,
            new DataFactory(),
            self::INSTALLATION_TITLE,
            self::INSTALLATION_URL
        );
    }

    private function rendererReturning(string $body): Renderer
    {
        $renderer = $this->createStub(Renderer::class);
        $renderer->method('render')->willReturn($body);

        return $renderer;
    }

    private function skipIfVfsStreamNotAvailable(): void
    {
        if (!class_exists(vfsStreamWrapper::class)) {
            $this->markTestSkipped(
                'vfsStream (https://github.com/bovigo/vfsStream) is required for virtual filesystem tests.'
            );
        }
    }
}
