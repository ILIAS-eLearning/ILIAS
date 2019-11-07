<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Hint;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\IsValueOfOrderedList;
use JsonSerializable;
use ilAsqException;
use stdClass;

/**
 * Interface Hint
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Hint implements JsonSerializable, IsValueOfOrderedList
{
    const ORDER_GAP = 10;

    const VAR_HINT_INT_ID = "hint_int_id";
    const VAR_HINT_ORDER_NUMBER = "hint_order_number";
    const VAR_HINT_ORDER_NUMBERS = "hint_order_numbers";
    const VAR_HINT_CONTENT = "hint_content";
    const VAR_HINT_POINTS_DEDUCTION = "hint_points";


    /**
     * @var integer
     */
    private $order_number = 0;
    /**
     * @var string
     */
    private $content;
    /**
     * @var float
     */
    private $point_deduction;


    /**
     * Hint constructor.
     *
     * @param int    $order_number
     * @param string $content
     * @param float  $point_deduction
     *
     * @throws ilAsqException
     */
    public function __construct(int $order_number, string $content, float $point_deduction)
    {
        $this->order_number = $order_number;
        $this->content = $content;
        $this->point_deduction = $point_deduction;

        $this->validate();
    }


    /**
     * @param IsValueOfOrderedList $hint
     * @param                      $order_number
     *
     * @return Hint
     * @throws ilAsqException
     */
    public static function createWithNewOrderNumber(IsValueOfOrderedList $hint, $order_number) {
        /** @var Hint $hint */
        return new Hint($order_number, $hint->getContent(), $hint->getPointDeduction());
    }



    /**
     * @return int
     */
    public function getOrderNumber() : int
    {
        return $this->order_number;
    }


    /**
     * @return string
     */
    public function getContent() : string
    {
        return $this->content;
    }


    /**
     * @return float
     */
    public function getPointDeduction() : float
    {
        return $this->point_deduction;
    }


    public function equals(Hint $other) : bool
    {
        if ($this->order_number !== $other->order_number
        || $this->content !== $other->content
        || $this->point_deduction !== $other->point_deduction) {
            return false;
        }

        return true;
    }


    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }


    public function deserialize(stdClass $data)
    {
        return new Hint(
            $data->order_number,
            $data->content,
            $data->point_deduction);
    }



    /**
     * @throws ilAsqException
     */
    public function validate():void {
        if ($this->getOrderNumber() % self::ORDER_GAP != 0) {
            throw new ilAsqException('Property hint_order_number - '.$this->getOrderNumber().' - is not valid. It hast be a multiple of '.self::ORDER_GAP);
        }
    }

    public static function getValueFromPost():Hint {
        return new Hint( intval(filter_input(INPUT_POST, self::VAR_HINT_ORDER_NUMBER, FILTER_VALIDATE_INT)),
                         strval(filter_input(INPUT_POST, self::VAR_HINT_CONTENT, FILTER_SANITIZE_STRING)),
                         floatval(filter_input(INPUT_POST, self::VAR_HINT_POINTS_DEDUCTION)));
    }



}
