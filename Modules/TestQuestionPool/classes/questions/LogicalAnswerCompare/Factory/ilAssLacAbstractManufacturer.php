<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Factory/ilAssLacManufacturerInterface.php";

/**
 * Class AbstractManufacturer
 *
 * Date: 26.03.13
 * Time: 15:13
 * @author Thomas Joußen <tjoussen@databay.de>
 */
abstract class ilAssLacAbstractManufacturer implements ilAssLacManufacturerInterface
{

    /**
     * Matches a delivered string with a the pattern returned by getPattern implemented in the explicit Manufacturer
     *
     * @param string $subject
     * @throws ilAssLacUnableToParseCondition
     *
     * @see ManufacturerInterface::getPattern()
     * @return array
     */
    public function match($subject)
    {
        $matches = array();
        $num_matches = preg_match_all($this->getPattern(), $subject, $matches);

        if ($num_matches == 0) {
            require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacUnableToParseCondition.php';
            throw new ilAssLacUnableToParseCondition($subject);
        }
        // Trims each element in the matches array
        $matches = array_map(function ($element) {
            return trim($element);
        }, $matches[0]);

        return $matches;
    }
}
