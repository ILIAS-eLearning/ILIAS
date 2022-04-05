<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

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
