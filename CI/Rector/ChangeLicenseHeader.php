<?php

namespace ILIAS\CI\Rector;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PhpParser\Comment;

final class ChangeLicenseHeader extends AbstractRector
{
    const EXISTING_LICENSE_PATTERN = '(copyright|Copyright|GPL-3\.0|GPLv3|LICENSE)';
    const IGNORE_SUBPATHS = '(lib|vendor|CI|data|Customizing)';
    private $license_header_default = "/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/";

    private Comment $standard_comment;

    public function __construct()
    {
        $this->standard_comment = new Comment($this->license_header_default);
    }

    public function getNodeTypes() : array
    {
        return [
            Node\Stmt\Use_::class,
            Node\Stmt\Class_::class,
            Node\Stmt\Interface_::class,
            Node\Stmt\Trait_::class,
            Node\Expr\Include_::class
        ];
    }

    /**
     * @param Node\Stmt\Global_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (preg_match(self::IGNORE_SUBPATHS, $this->file->getSmartFileInfo()->getPathname()) > 0) {
            return $node;
        }

        switch (true) {
            case $node instanceof Node\Stmt\Use_:
            case $node instanceof Node\Expr\Include_:
                $node->setAttribute('comments', $this->filterComments($node));
                return $node;
            case $node instanceof Node\Stmt\Class_:
            case $node instanceof Node\Stmt\Interface_:
            case $node instanceof Node\Stmt\Trait_:
                $node->setAttribute('comments', $this->filterComments($node, [$this->standard_comment]));
                return $node;
            default:
                return $node;
        }

    }

    /**
     * @param Node $node
     * @return Comment[]
     */
    private function filterComments(Node $node, array $default = []) : array
    {
        foreach ($node->getComments() as $comment) {
            if (preg_match(self::EXISTING_LICENSE_PATTERN, $comment->getText()) > 0) {
                continue;
            }
            $default[] = $comment;
        }
        return $default;
    }

    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition(
            'Adds or replaces a license-header in each class-file', [
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
