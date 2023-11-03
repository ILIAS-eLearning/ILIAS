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

namespace ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Substitution;

use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Token\Tokenizer;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Token\Token;

class FieldSubstitution
{
    private static array $field_cache = [];
    private \ilLanguage $lng;

    public function __construct(
        private \ilDclBaseRecordModel $record,
        private \ilDclBaseFieldModel $field
    ) {
        global $DIC;
        $this->lng = $DIC->language();
    }

    public function getFieldId(): int
    {
        return (int) $this->field->getId();
    }

    public function substituteFieldValue(string $placeholder): string
    {
        if (isset(self::$field_cache[$placeholder])) {
            $field = self::$field_cache[$placeholder];
        } else {
            $field = $this->getFieldFromPlaceholder($placeholder);
        }

        return $this->record->getRecordFieldFormulaValue($field->getId());
    }

    public function substituteFieldValues(array $tokens): array
    {
        $replaced = [];
        foreach ($tokens as $token) {
            if ($token instanceof Token) {
                $token = $token->getValue();
            }
            if (str_starts_with($token, Tokenizer::FIELD_OPENER)) {
                $replaced[] = new Token($this->substituteFieldValue($token));
            } else {
                $replaced[] = new Token($token);
            }
        }

        return $replaced;
    }

    public function getFieldFromPlaceholder(string $placeholder): \ilDclBaseFieldModel
    {
        $table = \ilDclCache::getTableCache($this->record->getTableId()); // TODO May need caching per table in future
        $field_title = preg_replace('#^\[\[(.*)\]\]#', "$1", $placeholder);
        $field = $table->getFieldByTitle($field_title);
        if ($field === null) {
            // Workaround for standardfields - title my be ID
            $field = $table->getField($field_title);
            if ($field === null) {
                throw new \ilException(sprintf($this->lng->txt('dcl_err_formula_field_not_found'), $field_title));
            }
        }
        self::$field_cache[$placeholder] = $field;
        return $field;
    }
}
