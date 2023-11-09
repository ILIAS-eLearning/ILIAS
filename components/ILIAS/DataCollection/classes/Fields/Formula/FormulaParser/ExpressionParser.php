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

namespace ILIAS\components\DataCollection\Fields\Formula\FormulaParser;

use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Substitution\FieldSubstitution;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Token\Tokenizer;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Result\MathResultResolver;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Result\SubstitutionResultResolver;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Token\MathToken;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Result\ResultFormatter;

class ExpressionParser
{
    private static array $cached_tokens = [];
    private Tokenizer $tokenizer;
    private MathResultResolver $math_result_resolver;
    private SubstitutionResultResolver $substition_result_resolver;
    private ResultFormatter $result_formatter;

    public function __construct(
        private string $expression,
        private FieldSubstitution $substitution,
    ) {
        $this->tokenizer = new Tokenizer();

        $this->math_result_resolver = new MathResultResolver(
            $substitution,
            $this->tokenizer
        );
        $this->substition_result_resolver = new SubstitutionResultResolver($substitution);

        $this->result_formatter = new ResultFormatter();
    }

    public function parse(): string
    {
        if (!isset(self::$cached_tokens[$this->substitution->getFieldId()])) {
            self::$cached_tokens[$this->substitution->getFieldId()] = $this->tokenizer->tokenize($this->expression);
        }
        $tokens = self::$cached_tokens[$this->substitution->getFieldId()];
        $parsed = '';

        foreach ($tokens as $token) {
            if ($token->getValue() === '') {
                continue;
            }
            if ($token instanceof MathToken) {
                $result = $this->math_result_resolver->resolve($token);
            } else {
                $result = $this->substition_result_resolver->resolve($token);
            }

            $parsed .= $this->result_formatter->format($result);
        }

        return $parsed;
    }
}
