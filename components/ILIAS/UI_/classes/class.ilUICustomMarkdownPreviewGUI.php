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
 */

declare(strict_types=1);

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\CommonMark\Node\Node;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\Inline\DelimitedInterface;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Util\HtmlElement;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUICustomMarkdownPreviewGUI extends ilUIMarkdownPreviewGUI implements ilCtrlBaseClassInterface
{
    public function render(string $markdown_text): string
    {
        $environment = new Environment();

        $processor = new class () implements DelimiterProcessorInterface {
            public function getOpeningCharacter(): string
            {
                return '=';
            }

            public function getClosingCharacter(): string
            {
                return '=';
            }

            public function getMinLength(): int
            {
                return 1;
            }

            public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
            {
                // don't allow =word== or ==word=.
                if ($opener->getLength() !== $closer->getLength()) {
                    return 0;
                }

                // only allow =word= and ==word==.
                if ($opener->getLength() > 2 || $closer->getLength() > 2) {
                    return 0;
                }

                return $opener->getLength();
            }

            public function process(
                AbstractStringContainer $opener,
                AbstractStringContainer $closer,
                int $delimiterUse
            ): void {
                $gap = new GapNode(\str_repeat('=', $delimiterUse));

                $tmp = $opener->next();
                while ($tmp !== null && $tmp !== $closer) {
                    $next = $tmp->next();
                    $gap->appendChild($tmp);
                    $tmp = $next;
                }

                $opener->insertAfter($gap);
            }
        };

        $renderer = new class () implements NodeRendererInterface {
            public function render(Node $node, ChildNodeRendererInterface $childRenderer)
            {
                GapNode::assertInstanceOf($node);

                $value = htmlspecialchars($childRenderer->renderNodes($node->children()));

                return "<input type=\"text\" value=\"$value\" />";
            }
        };

        $environment->addExtension(
            new class ($renderer, $processor) implements ExtensionInterface {
                public function __construct(
                    protected NodeRendererInterface $renderer,
                    protected DelimiterProcessorInterface $processor,
                ) {
                }

                public function register(EnvironmentBuilderInterface $environment): void
                {
                    $environment->addDelimiterProcessor($this->processor);
                    $environment->addRenderer(GapNode::class, $this->renderer);
                }
            }
        );

        $environment->addExtension(new CommonMarkCoreExtension());

        $converter = new League\CommonMark\MarkdownConverter($environment);

        return $converter->convert($markdown_text)->getContent();
    }
}

class GapNode extends AbstractInline implements DelimitedInterface
{
    public function __construct(
        protected string $delimiter,
    ) {
        parent::__construct();
    }

    public function getOpeningDelimiter(): string
    {
        return $this->delimiter;
    }

    public function getClosingDelimiter(): string
    {
        return $this->delimiter;
    }
}
