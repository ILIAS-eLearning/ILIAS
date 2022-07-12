<?php declare(strict_types=1);

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
 
class ilDBStepReader
{
    /**
     * Get the number of the latest database step in this class.
     */
    public function getLatestStepNumber(string $step_class_name, string $step_prefix) : int
    {
        $steps = $this->readStepNumbers($step_class_name, $step_prefix);
        return $steps[count($steps) - 1];
    }

    /**
     * Get a list of all steps in this class.
     */
    public function readStepNumbers(string $step_class_name, string $step_prefix) : array
    {
        $step_numbers = [];
        foreach (get_class_methods($step_class_name) as $method) {
            if (stripos($method, $step_prefix) !== 0) {
                continue;
            }

            $number = substr($method, strlen($step_prefix));

            if (!preg_match("/^[1-9]\d*$/", $number)) {
                throw new LogicException("Method $method seems to be a step but has an odd looking number");
            }

            $step_numbers[] = (int) $number;
        }

        sort($step_numbers);
        return $step_numbers;
    }
}
