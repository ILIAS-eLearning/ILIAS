<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Table;


/**
 * Class HintTableFieldOrderNumber
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class HintTableFieldOrderNumber
{
    const VAR_HINT_ORDER_NUMBERS = "hint_order_number";

    /**
     * HintFieldPointsDeduction constructor.
     *
     * @param float $points_deduction
     */
    public function __construct(int $current_order_number, int $new_order_number) {
        $this->current_order_number = $current_order_number;
        $this->new_order_number = $new_order_number;
    }


    public function getFieldAsHtml(): string {
        global $DIC;

        $field_order_number = '<input type="text" name="'.self::VAR_HINT_ORDER_NUMBERS.'['.$this->current_order_number.']" value="'.$this->new_order_number.'" maxlength="3" style="width:30px" />';

        return $field_order_number;
    }

    public static function getValueFromPost() {
        return filter_input(INPUT_POST, self::VAR_HINT_ORDER_NUMBERS, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
    }
}