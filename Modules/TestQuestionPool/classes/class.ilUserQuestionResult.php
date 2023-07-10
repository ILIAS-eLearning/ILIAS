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
 * Class ilUserQuestionResult
 *
 * Date: 10.01.14
 * Time: 10:03
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilUserQuestionResult
{
    public static $USER_SOLUTION_IDENTIFIER_KEY = "key";
    public static $USER_SOLUTION_IDENTIFIER_VALUE = "value";


    /**
     * @var iQuestionCondition
     */
    protected $question;

    /**
     * @var int
     */
    protected $active_id;

    /**
     * @var int
     */
    protected $pass;

    /**
     * @var int
     */
    protected $reached_percentage;

    /**
     * @var array
     */
    protected $solutions = array();

    public function __construct($question, $active_id, $pass)
    {
        $this->question = $question;
        $this->active_id = $active_id;
        $this->pass = $pass;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function addKeyValue($key, $value): void
    {
        $this->solutions[] = array(
            self::$USER_SOLUTION_IDENTIFIER_KEY => $key,
            self::$USER_SOLUTION_IDENTIFIER_VALUE => $value
        );
    }

    /**
     * @param string $key
     */
    public function removeByKey($key): void
    {
        foreach ($this->solutions as $array_key => $solution) {
            if ($solution[self::$USER_SOLUTION_IDENTIFIER_KEY] == $key) {
                unset($this->solutions[$array_key]);
                break;
            }
        }
    }

    /**
     * @param string $identifier
     *
     * @return array
     * @throws Exception
     */
    public function getUserSolutionsByIdentifier($identifier): array
    {
        if (
            $identifier != self::$USER_SOLUTION_IDENTIFIER_KEY &&
            $identifier != self::$USER_SOLUTION_IDENTIFIER_VALUE
        ) {
            throw new Exception(sprintf("Unkown Identifier %s", $identifier));
        }

        $solutions = array();
        foreach ($this->solutions as $solution) {
            $solutions[] = $solution[$identifier];
        }
        return $solutions;
    }

    /**
     * @return array
     */
    public function getSolutions(): array
    {
        return $this->solutions;
    }

    /**
     * @param int $key
     *
     * @return array
     */
    public function getSolutionForKey($key): ?array
    {
        foreach ($this->solutions as $solution) {
            if ($solution[self::$USER_SOLUTION_IDENTIFIER_KEY] == $key) {
                return $solution;
            }
        }
        return null;
    }

    /**
     * @param int $reached_percentage
     */
    public function setReachedPercentage($reached_percentage): void
    {
        $this->reached_percentage = $reached_percentage;
    }

    /**
     * @return int
     */
    public function getReachedPercentage(): int
    {
        return $this->reached_percentage;
    }

    /**
     * @return boolean
     */
    public function hasSolutions(): bool
    {
        return count($this->solutions) > 0;
    }
}
