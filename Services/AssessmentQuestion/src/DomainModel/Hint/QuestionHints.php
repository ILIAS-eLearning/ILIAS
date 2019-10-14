<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Hint;

use ilAsqException;

/**
 * Class Hints
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionHints
{


    /**
     * @var Hint[]
     */
    private $hints;


    /**
     * QuestionHints constructor.
     *
     * @param array Hint[]
     *
     * @throws ilAsqException
     */
    public function __construct(?array $hints)
    {
        $this->hints = $hints;
        $this->validate();
    }


    public function addHint(?Hint $hint)
    {
        $this->hints[] = $hint;
        $this->validate();
    }


    /**
     * @return Hint[]
     */
    public function getHints() : array
    {
        return $this->hints;
    }


    /**
     * @param int $order_number
     *
     * @return Hint
     * @throws ilAsqException
     */
    public function getSpecificHint(int $order_number) : Hint
    {
        foreach ($this->hints as $hint) {
            if ((int) $hint->getOrderNumber() === (int) $order_number) {
                return $hint;
            }
        }

        return new Hint(0, '', 0);
    }


    /**
     * @param string $json_data
     *
     * @return QuestionHints
     * @throws ilAsqException
     */
    public static function deserialize(string $json_data) : QuestionHints
    {
        $data = json_decode($json_data);
        $hints = [];

        foreach ($data as $hint) {
            $a_hint = new Hint($hint->order_number, $hint->content, $hint->point_deduction);
            $a_hint->deserialize($hint);
            $hints[] = $a_hint;
        }

        return new QuestionHints($hints);
    }


    /**
     * @param QuestionHints $other
     *
     * @return bool
     */
    public function equals(QuestionHints $other) : bool
    {
        return !is_null($other)
            && count($this->hints) === count($other->hints)
            && $this->hintsAreEqual($other);
    }


    public function hintsAreEqual(QuestionHints $other) : bool
    {
        /** @var Hint $my_hint */
        foreach ($this->hints as $my_hint) {
            $found = false;

            /** @var Hint $other_hint */
            foreach ($other->hints as $other_hint) {
                if ($my_hint->equals($other_hint)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws ilAsqException
     */
    public function validate():void {
        if(count($this->hints) > 0) {
            $order_numbers = [];
            $min_hint = 0;
            $max_hint = 0;

            foreach($this->getHints() as $hint) {
                //More than one hint with the smae order number?
                if(in_array($hint->getOrderNumber(), $order_numbers)) {
                    throw new ilAsqException('Property hint_order_number - '.$hint->getOrderNumber().' - is not valid. There is a second hint with the same order number');
                }
                $order_numbers[] = $hint->getOrderNumber();

                if($hint->getOrderNumber() < $min_hint || $min_hint == 0) {
                    $min_hint = $hint->getOrderNumber();
                }

                if($hint->getOrderNumber() > $max_hint || $max_hint == 0) {
                    $max_hint = $hint->getOrderNumber();
                }
            }

            if($min_hint != Hint::ORDER_GAP) {
                throw new ilAsqException('Property hint_order_number - '.$min_hint.' - is not valid. This is the minimum Order. Expected would be 10');
            }

            if($max_hint != (Hint::ORDER_GAP * count($this->getHints()))) {
                throw new ilAsqException('Property hint_order_number - '.$max_hint.' - is not valid. This is the maximum Order. Expected would be  '.Hint::ORDER_GAP * count($this->getHints()));
            }
        }

    }
}
