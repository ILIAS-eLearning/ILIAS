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
 * Class StringResultExpression for the expression ~TEXT~
 *
 *
 * Date: 25.03.13
 * Time: 16:41
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacStringResultExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    /**
     * The pattern <b>"/~.*?~/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>~TEXT~</b>	"TEXT" is a Placeholder for string value
     * </pre>
     * It is used to create a StringResultExpression

     * @see StringResultExpression
     * @var string
     */
    public static $pattern = "/~.*?~/";

    /**
     * @var string
     */
    public static $identifier = "~TEXT~";

    /**
     * A text value which should be compared
     *
     * @var string
     */
    protected $text;

    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    public function getPattern(): string
    {
        return '/~(.*)~/';
    }

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    protected function setMatches($matches): void
    {
        $this->text = $matches[1][0];
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue(): string
    {
        return "~" . $this->text . '~';
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription(): string
    {
        return $this->text . " beantwortet ";
    }

    /**
     * @param ilUserQuestionResult $result
     * @param string $comperator
     * @param int $index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null): bool
    {
        $isTrue = false;
        if ($index == null) {
            $values = $result->getUserSolutionsByIdentifier("value");

            foreach ($values as $value) {
                $isTrue = $isTrue || $this->compare($comperator, $value);
            }
        } else {
            $solution = $result->getSolutionForKey($index);
            $isTrue = $this->compare($comperator, $solution["value"]);
        }

        return $isTrue;
    }

    /**
     * @param string $comperator
     * @param mixed $value
     *
     * @return bool
     */
    private function compare($comperator, $value): bool
    {
        switch ($comperator) {
            case "=":
                return $this->getText() == $value;
                break;
            case "<>":
                return $this->getText() != $value;
                break;
            default:
                return false;
        }
    }
}
