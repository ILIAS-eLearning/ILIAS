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
     * @param string $subject
     * @return array
     *@throws ilAssLacUnableToParseCondition
     * @see ManufacturerInterface::getPattern()
     */
    public function match(string $subject): array
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
