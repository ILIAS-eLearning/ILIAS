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

use League\CommonMark as Core;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark;
use League\CommonMark\Extension\CommonMark\Delimiter\Processor\EmphasisDelimiterProcessor;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\CommonMark\Extension\InlinesOnly\ChildRenderer;
use League\CommonMark\Environment\Environment;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUIRestrictedMarkdownPreviewRendererGUI extends ilUIMarkdownPreviewGUI implements ilCtrlBaseClassInterface
{
    public function render(string $markdown_text): string
    {
        $environment = new Environment();

        // minimal inlines copied from commonmarks OnlyInlinesExtension.
        $environment->addExtension(
            new class () implements ConfigurableExtensionInterface {
                public function configureSchema(ConfigurationBuilderInterface $builder): void
                {
                    $builder->addSchema('commonmark', Expect::structure([
                        'use_asterisk' => Expect::bool(true),
                        'use_underscore' => Expect::bool(true),
                        'enable_strong' => Expect::bool(true),
                        'enable_em' => Expect::bool(true),
                    ]));
                }

                public function register(EnvironmentBuilderInterface $environment): void
                {
                    $childRenderer = new ChildRenderer();

                    $environment
                        ->addInlineParser(new Core\Parser\Inline\NewlineParser(), 200)
                        ->addInlineParser(new CommonMark\Parser\Inline\EscapableParser(), 80)
                        ->addInlineParser(new CommonMark\Parser\Inline\EntityParser(), 70)
                        ->addDelimiterProcessor(new EmphasisDelimiterProcessor('*'))
                        ->addDelimiterProcessor(new EmphasisDelimiterProcessor('_'))
                        ->addRenderer(Core\Node\Block\Document::class, $childRenderer, 0)
                        ->addRenderer(Core\Node\Block\Paragraph::class, $childRenderer, 0)
                        ->addRenderer(CommonMark\Node\Inline\Emphasis::class, new CommonMark\Renderer\Inline\EmphasisRenderer(), 0)
                        ->addRenderer(Core\Node\Inline\Newline::class, new Core\Renderer\Inline\NewlineRenderer(), 0)
                        ->addRenderer(CommonMark\Node\Inline\Strong::class, new CommonMark\Renderer\Inline\StrongRenderer(), 0)
                        ->addRenderer(Core\Node\Inline\Text::class, new Core\Renderer\Inline\TextRenderer(), 0);
                }
            }
        );

        $converter = new League\CommonMark\MarkdownConverter($environment);

        return $converter->convert($markdown_text)->getContent();
    }
}
