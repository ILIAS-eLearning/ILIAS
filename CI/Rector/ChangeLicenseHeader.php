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

namespace ILIAS\CI\Rector;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PhpParser\Comment;
use Rector\NodeTypeResolver\Node\AttributeKey as AttributeKeys;

final class ChangeLicenseHeader extends AbstractRector
{
    public const EXISTING_LICENSE_PATTERN = '(copyright|Copyright|GPL-3\.0|GPLv3|LICENSE)';
    public const IGNORE_SUBPATHS = '(lib|vendor|data|Customizing)';
    private string $license_header_default = "/**
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
";

    private Comment $standard_comment;
    private array $previous_search = [
        Node\Expr\Include_::class,
        Node\Stmt\Use_::class,
        Node\Stmt\Namespace_::class,
        Node\Name::class,
        Node\Stmt\Class_::class,
        Node\Stmt\Expression::class,
        Node\Stmt\Declare_::class
    ];

    public function __construct()
    {
        $this->standard_comment = new Comment($this->license_header_default);
    }

    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\Class_::class,
            Node\Stmt\Interface_::class,
            Node\Stmt\Trait_::class
        ];
    }

    /**
     * @param Node\Stmt\Global_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (preg_match(self::IGNORE_SUBPATHS, $this->file->getSmartFileInfo()->getPathname()) > 0) {
            return $node;
        }
        $node->setAttribute('comments', $this->filterComments($node));
        $current = $node;
        $previous = $node->getAttribute(AttributeKeys::PREVIOUS_NODE);
        while (is_object($previous) && in_array(get_class($previous), $this->previous_search)) {
            if (get_class($previous) === Node\Name::class) {
                $previous = $previous->getAttribute(AttributeKeys::PARENT_NODE);
            }
            $current = $previous;
            $current->setAttribute(
                AttributeKeys::COMMENTS,
                $this->filterComments($current)
            );
            $previous = $current->getAttribute(AttributeKeys::PREVIOUS_NODE);
        }

        $current->setAttribute(AttributeKeys::COMMENTS, $this->filterComments($current, [$this->standard_comment]));

        return $node;
    }

    /**
     * @param Node $node
     * @return Comment[]
     */
    private function filterComments(Node $node, array $default = []): array
    {
        foreach ($node->getComments() as $comment) {
            if (preg_match(self::EXISTING_LICENSE_PATTERN, $comment->getText()) > 0) {
                continue;
            }
            $default[] = $comment;
        }
        return $default;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds or replaces a license-header in each class-file',
            [
                new CodeSample(
                    // code before
                    '',
                    // code after
                    ''
                ),
            ]
        );
    }
}
