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

namespace ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Result;

use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Substitution\FieldSubstitution;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Token\Token;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Token\Tokenizer;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Result\Result\Result;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Result\Result\StringResult;

class SubstitutionResultResolver implements ResultResolver
{
    private \ilLanguage $lng;

    public function __construct(
        private FieldSubstitution $substitution,
    ) {
        global $DIC;
        $this->lng = $DIC->language();
    }

    public function resolve(Token $token): Result
    {
        // Token is a string, either a field placeholder [[Field name]] or a string starting with "
        $token_value = $token->getValue();
        if (str_starts_with($token_value, '"')) { // this is a "simple" string
            return new StringResult(strip_tags(trim($token_value, '"')));
        }

        if (str_starts_with($token_value, Tokenizer::FIELD_OPENER)) { // this is a field placeholder
            return new StringResult(trim(strip_tags($this->substitution->substituteFieldValue($token_value))));
        }

        throw new \ilException("Unrecognized string token: '$token_value'");
    }
}
