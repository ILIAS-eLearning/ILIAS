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

namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class NoSuperfluousPhpdocTagsFixer extends AbstractFixer implements ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'Removes `@param` and `@return` tags that don\'t provide any useful information.',
            [
                new CodeSample('<?php
class Foo {
    /**
     * @param Bar $bar
     * @param mixed $baz
     */
    public function doFoo(Bar $bar, $baz) {}
}
'),
                new CodeSample('<?php
class Foo {
    /**
     * @param Bar $bar
     * @param mixed $baz
     */
    public function doFoo(Bar $bar, $baz) {}
}
', ['allow_mixed' => true]),
                new VersionSpecificCodeSample('<?php
class Foo {
    /**
     * @param Bar $bar
     * @param mixed $baz
     *
     * @return Baz
     */
    public function doFoo(Bar $bar, $baz): Baz {}
}
', new VersionSpecification(70000)),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // should run before NoEmptyPhpdocFixer
        return 6;
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens)
    {
        $namespaceUseAnalyzer = new NamespaceUsesAnalyzer();

        $shortNames = [];
        foreach ($namespaceUseAnalyzer->getDeclarationsFromTokens($tokens) as $namespaceUseAnalysis) {
            $shortNames[strtolower($namespaceUseAnalysis->getShortName())] = '\\'.strtolower($namespaceUseAnalysis->getFullName());
        }

        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_DOC_COMMENT)) {
                continue;
            }

            $functionIndex = $this->findDocumentedFunction($tokens, $index);
            if (null === $functionIndex) {
                continue;
            }

            $docBlock = new DocBlock($token->getContent());

            $openingParenthesisIndex = $tokens->getNextTokenOfKind($functionIndex, ['(']);
            $closingParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openingParenthesisIndex);

            $argumentsInfo = $this->getArgumentsInfo(
                $tokens,
                $openingParenthesisIndex + 1,
                $closingParenthesisIndex - 1
            );

            foreach ($docBlock->getAnnotationsOfType('param') as $annotation) {
                if (0 === Preg::match('/@param(?:\s+[^\$]\S+)?\s+(\$\S+)/', $annotation->getContent(), $matches)) {
                    continue;
                }

                $argumentName = $matches[1];

                if (
                    !isset($argumentsInfo[$argumentName])
                    || $this->annotationIsSuperfluous($annotation, $argumentsInfo[$argumentName], $shortNames)
                ) {
                    $annotation->remove();
                }
            }

            $returnTypeInfo = $this->getReturnTypeInfo($tokens, $closingParenthesisIndex);

            foreach ($docBlock->getAnnotationsOfType('return') as $annotation) {
                if ($this->annotationIsSuperfluous($annotation, $returnTypeInfo, $shortNames)) {
                    $annotation->remove();
                }
            }

            $tokens[$index] = new Token([T_DOC_COMMENT, $docBlock->getContent()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('allow_mixed', 'Whether type `mixed` without description is allowed (`true`) or considered superfluous (`false`)'))
                ->setAllowedTypes(['bool'])
                ->setDefault(false)
                ->getOption(),
        ]);
    }

    private function findDocumentedFunction(Tokens $tokens, $index)
    {
        do {
            $index = $tokens->getNextMeaningfulToken($index);

            if (null === $index || $tokens[$index]->isGivenKind(T_FUNCTION)) {
                return $index;
            }
        } while ($tokens[$index]->isGivenKind([T_ABSTRACT, T_FINAL, T_STATIC, T_PRIVATE, T_PROTECTED, T_PUBLIC]));

        return null;
    }

    /**
     * @param Tokens $tokens
     * @param int    $start
     * @param int    $end
     *
     * @return array<string, array>
     */
    private function getArgumentsInfo(Tokens $tokens, $start, $end)
    {
        $argumentsInfo = [];

        for ($index = $start; $index <= $end; ++$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(T_VARIABLE)) {
                continue;
            }

            $beforeArgumentIndex = $tokens->getPrevTokenOfKind($index, ['(', ',']);
            $typeIndex = $tokens->getNextMeaningfulToken($beforeArgumentIndex);

            if ($typeIndex !== $index) {
                $info = $this->parseTypeHint($tokens, $typeIndex);
            } else {
                $info = [
                    'type' => null,
                    'allows_null' => true,
                ];
            }

            if (!$info['allows_null']) {
                $nextIndex = $tokens->getNextMeaningfulToken($index);
                if (
                    $tokens[$nextIndex]->equals('=')
                    && $tokens[$tokens->getNextMeaningfulToken($nextIndex)]->equals([T_STRING, 'null'])
                ) {
                    $info['allows_null'] = true;
                }
            }

            $argumentsInfo[$token->getContent()] = $info;
        }

        return $argumentsInfo;
    }

    private function getReturnTypeInfo(Tokens $tokens, $closingParenthesisIndex)
    {
        $colonIndex = $tokens->getNextMeaningfulToken($closingParenthesisIndex);
        if ($tokens[$colonIndex]->isGivenKind(CT::T_TYPE_COLON)) {
            return $this->parseTypeHint($tokens, $tokens->getNextMeaningfulToken($colonIndex));
        }

        return [
            'type' => null,
            'allows_null' => true,
        ];
    }

    /**
     * @param Tokens $tokens
     * @param int    $index  The index of the first token of the type hint
     *
     * @return array
     */
    private function parseTypeHint(Tokens $tokens, $index)
    {
        $allowsNull = false;
        if ($tokens[$index]->isGivenKind(CT::T_NULLABLE_TYPE)) {
            $allowsNull = true;
            $index = $tokens->getNextMeaningfulToken($index);
        }

        $type = '';
        while ($tokens[$index]->isGivenKind([T_NS_SEPARATOR, T_STRING, CT::T_ARRAY_TYPEHINT, T_CALLABLE])) {
            $type .= $tokens[$index]->getContent();

            $index = $tokens->getNextMeaningfulToken($index);
        }

        return [
            'type' => $type,
            'allows_null' => $allowsNull,
        ];
    }

    /**
     * @param Annotation            $annotation
     * @param array                 $info
     * @param array<string, string> $symbolShortNames
     *
     * @return bool
     */
    private function annotationIsSuperfluous(Annotation $annotation, array $info, array $symbolShortNames)
    {
        if ('param' === $annotation->getTag()->getName()) {
            $regex = '/@param\s+(?:\S|\s(?!\$))+\s\$\S+\s+\S/';
        } else {
            $regex = '/@return\s+\S+\s+\S/';
        }

        if (Preg::match($regex, $annotation->getContent())) {
            return false;
        }

        $annotationTypes = $this->toComparableNames($annotation->getTypes(), $symbolShortNames);

        if (['null'] === $annotationTypes) {
            return false;
        }

        if (['mixed'] === $annotationTypes && null === $info['type']) {
            return !$this->configuration['allow_mixed'];
        }

        $actualTypes = null === $info['type'] ? [] : [$info['type']];
        if ($info['allows_null']) {
            $actualTypes[] = 'null';
        }

        return $annotationTypes === $this->toComparableNames($actualTypes, $symbolShortNames);
    }

    /**
     * Normalizes types to make them comparable.
     *
     * Converts given types to lowercase, replaces imports aliases with
     * their matching FQCN, and finally sorts the result.
     *
     * @param string[]              $types            The types to normalize
     * @param array<string, string> $symbolShortNames The imports aliases
     *
     * @return array The normalized types
     */
    private function toComparableNames(array $types, array $symbolShortNames)
    {
        $normalized = array_map(
            function ($type) use ($symbolShortNames) {
                $type = strtolower($type);

                if (isset($symbolShortNames[$type])) {
                    return $symbolShortNames[$type];
                }

                return $type;
            },
            $types
        );

        sort($normalized);

        return $normalized;
    }
}
