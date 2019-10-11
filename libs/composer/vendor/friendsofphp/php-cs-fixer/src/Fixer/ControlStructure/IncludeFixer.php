<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class IncludeFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'Include/Require and file path should be divided with a single space. File path should not be placed under brackets.',
            [
                new CodeSample(
                    '<?php
require ("sample1.php");
require_once  "sample2.php";
include       "sample3.php";
include_once("sample4.php");
'
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens)
    {
        $this->clearIncludies($tokens, $this->findIncludies($tokens));
    }

    private function clearIncludies(Tokens $tokens, array $includies)
    {
        foreach ($includies as $includy) {
            if ($includy['end'] && !$tokens[$includy['end']]->isGivenKind(T_CLOSE_TAG)) {
                $afterEndIndex = $tokens->getNextNonWhitespace($includy['end']);
                if (null === $afterEndIndex || !$tokens[$afterEndIndex]->isComment()) {
                    $tokens->removeLeadingWhitespace($includy['end']);
                }
            }

            $braces = $includy['braces'];

            if ($braces) {
                $nextToken = $tokens[$tokens->getNextMeaningfulToken($braces['close'])];

                if ($nextToken->equalsAny([';', [T_CLOSE_TAG]])) {
                    $this->removeWhitespaceAroundIfPossible($tokens, $braces['open']);
                    $this->removeWhitespaceAroundIfPossible($tokens, $braces['close']);
                    $tokens->clearTokenAndMergeSurroundingWhitespace($braces['open']);
                    $tokens->clearTokenAndMergeSurroundingWhitespace($braces['close']);
                }
            }

            $nextIndex = $tokens->getNonEmptySibling($includy['begin'], 1);

            if ($tokens[$nextIndex]->isWhitespace()) {
                $tokens[$nextIndex] = new Token([T_WHITESPACE, ' ']);
            } elseif ($braces || $tokens[$nextIndex]->isGivenKind([T_VARIABLE, T_CONSTANT_ENCAPSED_STRING, T_COMMENT])) {
                $tokens->insertAt($includy['begin'] + 1, new Token([T_WHITESPACE, ' ']));
            }
        }
    }

    private function findIncludies(Tokens $tokens)
    {
        static $includyTokenKinds = [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE];

        $includies = [];

        foreach ($tokens->findGivenKind($includyTokenKinds) as $includyTokens) {
            foreach ($includyTokens as $index => $token) {
                $includy = [
                    'begin' => $index,
                    'braces' => null,
                    'end' => $tokens->getNextTokenOfKind($index, [';', [T_CLOSE_TAG]]),
                ];

                $nextTokenIndex = $tokens->getNextMeaningfulToken($index);
                $nextToken = $tokens[$nextTokenIndex];

                if ($nextToken->equals('(')) {
                    // Don't remove braces when the statement is wrapped.
                    // Include is also legal as function parameter or condition statement but requires being wrapped then.
                    $braceCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextTokenIndex);

                    if ($tokens[$tokens->getNextMeaningfulToken($braceCloseIndex)]->equalsAny([';', [T_CLOSE_TAG]])) {
                        $includy['braces'] = [
                            'open' => $nextTokenIndex,
                            'close' => $braceCloseIndex,
                        ];
                    }
                }

                $includies[$index] = $includy;
            }
        }

        krsort($includies);

        return $includies;
    }

    /**
     * @param Tokens $tokens
     * @param int    $index
     */
    private function removeWhitespaceAroundIfPossible(Tokens $tokens, $index)
    {
        $nextIndex = $tokens->getNextNonWhitespace($index);
        if (null === $nextIndex || !$tokens[$nextIndex]->isComment()) {
            $tokens->removeLeadingWhitespace($index);
        }

        $prevIndex = $tokens->getPrevNonWhitespace($index);
        if (null === $prevIndex || !$tokens[$prevIndex]->isComment()) {
            $tokens->removeTrailingWhitespace($index);
        }
    }
}
