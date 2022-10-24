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
 * Class NumberOfResultExpression fot the expression +n+
 *
 * Date: 25.03.13
 * Time: 16:41
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacNumberOfResultExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    /**
     * The pattern <b>"/\\+[0-9]+\\+/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>+n+</b>	"n" is a Placeholder for a numeric value
     * </pre>
     * It is used to create a NumberOfResultExpression

     * @see NumberOfResultExpression
     * @var string
     */
    public static $pattern = "/\\+[0-9]+\\+/";

    /**
     * @var string
     */
    public static $identifier = "+n+";

    /**
     * A numeric value to identify a specific answer which should be compared
     *
     * @var int
     */
    protected $numeric_value;

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    protected function setMatches($matches): void
    {
        $this->numeric_value = $matches[0][0];
    }

    /**
     * @return int
     */
    public function getNumericValue(): int
    {
        return $this->numeric_value;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue(): string
    {
        return '+' . $this->numeric_value . "+";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription(): string
    {
        return "Anwort " . $this->numeric_value . " beantwortet ";
    }

    /**
     * @param ilUserQuestionResult $result
     * @param string $comperator
     * @param null|int $index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null): bool
    {
        $isTrue = false;
        if ($index == null) {
            $values = $result->getUserSolutionsByIdentifier("key");

            foreach ($values as $value) {
                $isTrue = $isTrue || $this->compare($comperator, $value);
            }
        } else {
            $solution = $result->getSolutionForKey($index);
            $isTrue = $this->compare($comperator, $solution["value"]);
        }

        return $isTrue;
    }

    private function compare($comperator, $value): bool
    {
        switch ($comperator) {
            case "=":
                return $value == $this->getNumericValue();
                break;
            case "<>":
                return $value != $this->getNumericValue();
                break;
            default:
                return false;
        }
    }
}
