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

namespace ILIAS\Mail\Message;

use ILIAS\Refinery\String\MakeClickable;
use ILIAS\Refinery\String\MarkdownFormattingToHTML;
use PHPUnit\Framework\Attributes\DataProvider;

class MailMessageHtmlRendererTest extends \ilMailBaseTestCase
{
    private MailMessageHtmlRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new MailMessageHtmlRenderer(
            (new MarkdownFormattingToHTML())->toHTML(),
            new MakeClickable(),
        );
    }

    /**
     * @return array<string, array{0: string, 1: bool, 2: string}>
     */
    public static function messageProvider(): array
    {
        return [
            'legacy html stays text in the detail view' => [
                'This is a <b>Test</b>',
                true,
                "<p>This is a &lt;b&gt;Test&lt;/b&gt;</p>\n",
            ],
            'legacy html stays text in the print view' => [
                'This is a <b>Test</b>',
                false,
                "<p>This is a &lt;b&gt;Test&lt;/b&gt;</p>\n",
            ],
            'legacy html and ampersand are encoded once' => [
                'This is a <b>Test</b> & more',
                true,
                "<p>This is a &lt;b&gt;Test&lt;/b&gt; &amp; more</p>\n",
            ],
            'entity-encoded markup stays escaped' => [
                'Hallo, &lt;img src=x onerror=alert(1)&gt;',
                true,
                "<p>Hallo, &lt;img src=x onerror=alert(1)&gt;</p>\n",
            ],
            'markdown formatting is rendered' => [
                'Das ist **fett** und [Link](https://www.ilias.de)',
                false,
                "<p>Das ist <strong>fett</strong> und <a href=\"https://www.ilias.de\">Link</a></p>\n",
            ],
            'bare urls are clickable in the detail view' => [
                'Siehe https://example.com/a?x=1&y=2 bitte',
                true,
                "<p>Siehe <a target=\"_blank\" rel=\"noopener\" href=\"https://example.com/a?x=1&amp;y=2\">https://example.com/a?x=1&amp;y=2</a> bitte</p>\n",
            ],
            'bare urls stay plain text in the print view' => [
                'Siehe https://example.com/a?x=1&y=2 bitte',
                false,
                "<p>Siehe https://example.com/a?x=1&amp;y=2 bitte</p>\n",
            ],
            'unresolved placeholders are not template syntax' => [
                'Hallo {USER_FULLNAME}',
                true,
                "<p>Hallo &#123;USER_FULLNAME&#125;</p>\n",
            ],
            'placeholders, bare urls and legacy html are handled together' => [
                'Hi {USER_FULLNAME}, siehe https://example.com und <b>alt</b>',
                true,
                "<p>Hi &#123;USER_FULLNAME&#125;, siehe <a target=\"_blank\" rel=\"noopener\" href=\"https://example.com\">https://example.com</a> und &lt;b&gt;alt&lt;/b&gt;</p>\n",
            ],
            'empty body stays empty' => [
                '',
                true,
                '',
            ],
        ];
    }

    #[DataProvider('messageProvider')]
    public function testRender(string $message, bool $make_urls_clickable, string $expected): void
    {
        $this->assertSame($expected, $this->renderer->render($message, $make_urls_clickable));
    }
}
