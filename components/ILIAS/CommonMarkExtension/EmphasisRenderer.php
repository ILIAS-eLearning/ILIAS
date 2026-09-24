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

namespace ILIAS\CommonMarkExtension;

use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Xml\XmlNodeRendererInterface;

/**
 * EmphasisRenderer
 *
 * Replaces the core's EmphasisRenderer
 * - removes parsing of single asterisks for emphasis
 */
final class EmphasisRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    /**
     * @param Emphasis $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        Emphasis::assertInstanceOf($node);

        $attrs = $node->data->get('attributes');

        if ($node->getOpeningDelimiter() === '_') {
            return new HtmlElement('em', $attrs, $childRenderer->renderNodes($node->children()));
        }

        $output = '*' . $childRenderer->renderNodes($node->children()) . '*';
        return new class ($output) implements \Stringable {
            protected string $string;
            public function __construct(string $string)
            {
                $this->string = $string;
            }
            public function __toString(): string
            {
                return $this->string;
            }
        };
    }

    public function getXmlTagName(Node $node): string
    {
        return 'emph';
    }

    /**
     * {@inheritDoc}
     */
    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}
