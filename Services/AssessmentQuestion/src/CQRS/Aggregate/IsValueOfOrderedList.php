<?php

namespace ILIAS\AssessmentQuestion\CQRS\Aggregate;

/**
 * Interface IsRevisable
 *
 * Generates Revision safe Revision id for IsRevisable object
 *
 * @package ILIAS\AssessmentQuestion\Common
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface IsValueOfOrderedList {
    public static function createWithNewOrderNumber(IsValueOfOrderedList $item, $order_number);
}