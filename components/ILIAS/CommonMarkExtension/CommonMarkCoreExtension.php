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

namespace ILIAS\CommonMarkExtension\CommonMarkExtension;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Delimiter\Processor\EmphasisDelimiterProcessor;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\CommonMark\Node as CoreNode;
use League\CommonMark\Parser as CoreParser;
use League\CommonMark\Renderer as CoreRenderer;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;
use League\CommonMark\Extension\CommonMark as CM;

/**
 * CommonMarkCoreExtension
 *
 * Replaces the core's CommonMarkCoreExtension and
 * removes parsing of setext headings via CommonMarkHeadingStartParser
 */
final class CommonMarkCoreExtension implements ConfigurableExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('commonmark', Expect::structure([
            'use_asterisk' => Expect::bool(true),
            'use_underscore' => Expect::bool(true),
            'enable_strong' => Expect::bool(true),
            'enable_em' => Expect::bool(true),
            'unordered_list_markers' => Expect::listOf('string')->min(1)->default(['*', '+', '-'])->mergeDefaults(false),
        ]));
    }

    // phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma,Squiz.WhiteSpace.SemicolonSpacing.Incorrect
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(new CM\Parser\Block\BlockQuoteStartParser(), 70)
            ->addBlockStartParser(new CommonMarkHeadingStartParser(), 60)
            ->addBlockStartParser(new CM\Parser\Block\FencedCodeStartParser(), 50)
            ->addBlockStartParser(new CM\Parser\Block\HtmlBlockStartParser(), 40)
            ->addBlockStartParser(new CM\Parser\Block\ThematicBreakStartParser(), 20)
            ->addBlockStartParser(new CM\Parser\Block\ListBlockStartParser(), 10)
            ->addBlockStartParser(new CM\Parser\Block\IndentedCodeStartParser(), -100)

            ->addInlineParser(new CoreParser\Inline\NewlineParser(), 200)
            ->addInlineParser(new CM\Parser\Inline\BacktickParser(), 150)
            ->addInlineParser(new CM\Parser\Inline\EscapableParser(), 80)
            ->addInlineParser(new CM\Parser\Inline\EntityParser(), 70)
            ->addInlineParser(new CM\Parser\Inline\AutolinkParser(), 50)
            ->addInlineParser(new CM\Parser\Inline\HtmlInlineParser(), 40)
            ->addInlineParser(new CM\Parser\Inline\CloseBracketParser(), 30)
            ->addInlineParser(new CM\Parser\Inline\OpenBracketParser(), 20)
            ->addInlineParser(new CM\Parser\Inline\BangParser(), 10)

            ->addRenderer(CM\Node\Block\BlockQuote::class, new CM\Renderer\Block\BlockQuoteRenderer(), 0)
            ->addRenderer(CoreNode\Block\Document::class, new CoreRenderer\Block\DocumentRenderer(), 0)
            ->addRenderer(CM\Node\Block\FencedCode::class, new CM\Renderer\Block\FencedCodeRenderer(), 0)
            ->addRenderer(CM\Node\Block\Heading::class, new CM\Renderer\Block\HeadingRenderer(), 0)
            ->addRenderer(CM\Node\Block\HtmlBlock::class, new CM\Renderer\Block\HtmlBlockRenderer(), 0)
            ->addRenderer(CM\Node\Block\IndentedCode::class, new CM\Renderer\Block\IndentedCodeRenderer(), 0)
            ->addRenderer(CM\Node\Block\ListBlock::class, new CM\Renderer\Block\ListBlockRenderer(), 0)
            ->addRenderer(CM\Node\Block\ListItem::class, new CM\Renderer\Block\ListItemRenderer(), 0)
            ->addRenderer(CoreNode\Block\Paragraph::class, new CoreRenderer\Block\ParagraphRenderer(), 0)
            ->addRenderer(CM\Node\Block\ThematicBreak::class, new CM\Renderer\Block\ThematicBreakRenderer(), 0)

            ->addRenderer(CM\Node\Inline\Code::class, new CM\Renderer\Inline\CodeRenderer(), 0)
            ->addRenderer(CM\Node\Inline\Emphasis::class, new CM\Renderer\Inline\EmphasisRenderer(), 0)
            ->addRenderer(CM\Node\Inline\HtmlInline::class, new CM\Renderer\Inline\HtmlInlineRenderer(), 0)
            ->addRenderer(CM\Node\Inline\Image::class, new CM\Renderer\Inline\ImageRenderer(), 0)
            ->addRenderer(CM\Node\Inline\Link::class, new CM\Renderer\Inline\LinkRenderer(), 0)
            ->addRenderer(CoreNode\Inline\Newline::class, new CoreRenderer\Inline\NewlineRenderer(), 0)
            ->addRenderer(CM\Node\Inline\Strong::class, new CM\Renderer\Inline\StrongRenderer(), 0)
            ->addRenderer(CoreNode\Inline\Text::class, new CoreRenderer\Inline\TextRenderer(), 0)
        ;

        if ($environment->getConfiguration()->get('commonmark/use_asterisk')) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('*'));
        }

        if ($environment->getConfiguration()->get('commonmark/use_underscore')) {
            $environment->addDelimiterProcessor(new EmphasisDelimiterProcessor('_'));
        }
    }
}
