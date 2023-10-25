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

namespace ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Result;

use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Operators;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Functions;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Result\Result\IntegerResult;

class ResultFormatter
{
    use \ilDclDatetimeRecordDateFormatter;

    private const N_DECIMALS = 1;
    private const SCIENTIFIC_NOTATION_UPPER = 1000000000000;
    private const SCIENTIFIC_NOTATION_LOWER = 0.000000001;
    private \ilLanguage $lng;
    private \ilObjUser $user;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
    }

    public function format(Result\Result $result): string
    {
        return match (true) {
            $result instanceof Result\DateResult => $this->formatDateFromString($result),
            $result instanceof Result\IntegerResult => $this->formatScientific($result),
            $result instanceof Result\StringResult => $this->formatString($result),
            default => $result->getValue()
        };
    }

    protected function formatString(Result\StringResult $result): string
    {
        return $result->getValue();
    }

    protected function getUserDateFormat(): string
    {
        return (string) $this->user->getDateFormat();
    }

    protected function formatDateFromString(Result\DateResult $result): string
    {
        // depending on the function, we need to format the date differently or return nothing
        $calculated_timestamp = (int) $result->getValue();
        switch ($result->getFromFunction()) {
            case Functions::MAX:
            case Functions::MIN:
            case Functions::AVERAGE:
                return $this->formatDateFromInt($calculated_timestamp);
            case Functions::SUM:
                return '';
        }

        // depending on the operator, we need to format the date differently or return nothing
        switch ($result->getFromOperator()) {
            case Operators::SUBTRACTION:
                $presentation = function ($value, $factor, $unit) {
                    return $this->formatScientific(
                        new IntegerResult((string) round($value / $factor, 0))
                    ) . ' ' . $this->lng->txt($unit);
                };

                $prefix = '';
                $value = (int) $calculated_timestamp;
                if ($value < 0) {
                    $prefix = '-';
                    $value *= -1;
                }

                switch (true) {
                    case $value < 60:
                        $value = $presentation($value, 1, 'seconds');
                        break;
                    case $value < 3600:
                        $value = $presentation($value, 60, 'minutes');
                        break;
                    case $value < 24 * 3600:
                        $value = $presentation($value, 3600, 'hours');
                        break;
                    default:
                        $value = $presentation($value, 24 * 3600, 'days');
                        break;
                }
                return $prefix . $value;
            case Operators::ADDITION:
            case Operators::MULTIPLICATION:
            case Operators::DIVISION:
            case Operators::POWER:
                return ''; // currently no output for these operators
            default:
                return $calculated_timestamp;
        }
    }

    protected function formatScientific(Result\IntegerResult $result): string
    {
        $value = (int) $result->getValue();
        if (abs($value) >= self::SCIENTIFIC_NOTATION_UPPER) {
            return sprintf("%e", $value);
        }
        if (abs($value) <= self::SCIENTIFIC_NOTATION_LOWER && $value != 0) {
            return sprintf("%e", $value);
        }

        // format numbers bigger than 1000 with thousand separator
        if (abs($value) >= 1000) {
            if (is_float($value)) {
                $decimals = self::N_DECIMALS;
            } else {
                $decimals = 0;
            }
            return number_format(
                $value,
                $decimals,
                $this->lng->txt('lang_sep_decimal'),
                $this->lng->txt('lang_sep_thousand')
            );
        }

        return (string) $value;
    }
}
