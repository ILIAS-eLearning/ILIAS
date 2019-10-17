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
class HintTableFieldSelectHint
{
    const VAR_HINTS_BY_ORDER_NUMBER = "hints_by_number";

    /**
     * HintFieldPointsDeduction constructor.
     *
     * @param float $points_deduction
     */
    public function __construct(int $order_number) {
        $this->order_number = $order_number;
    }


    public function getFieldAsHtml(): string {
        global $DIC;

        $field_select_hint = '<input type="checkbox" name="'.self::VAR_HINTS_BY_ORDER_NUMBER.'[]" value="'.$this->order_number.'" id="chb_'. $this->order_number.'" />';

        return $field_select_hint;
    }

    public static function getValueFromPost() {
        return filter_input(INPUT_POST, self::VAR_HINTS_BY_ORDER_NUMBER, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
    }
}