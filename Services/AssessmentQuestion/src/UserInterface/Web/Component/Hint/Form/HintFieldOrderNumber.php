<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Form;

use ilFormPropertyGUI;
use ilHiddenInputGUI;
use ILIAS\AssessmentQuestion\DomainModel\Hint\Hint;
use ilNumberInputGUI;

/**
 * Class HintFieldOrderNumber
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class HintFieldOrderNumber
{
    const VAR_HINT_ORDER_NUMBER = "hint_order_number";

    /**
     * HintFieldPointsDeduction constructor.
     *
     * @param float $points_deduction
     */
    public function __construct(int $order_number) {
        $this->order_number = $order_number;
    }


    public function getField(): ilFormPropertyGUI {
        global $DIC;

        $field_order_number = new ilHiddenInputGUI(self::VAR_HINT_ORDER_NUMBER);
        $field_order_number->setValue($this->order_number);

        return $field_order_number;
    }

    public  static function getValueFromPost() {
        return filter_input(INPUT_POST, self::VAR_HINT_ORDER_NUMBER);
    }
}