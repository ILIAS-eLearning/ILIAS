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

namespace ILIAS\TestQuestionPool;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

/**
 * @deprecated This class is no longer needed when using the ks input components.
 */
class ilTestLegacyFormsHelper
{
    protected Refinery $refinery;

    public function __construct(

    ) {
        global $DIC;
        $this->refinery = $DIC['refinery'];
    }

    /**
     * Checks if the provides points of an input are valid. The function returns the language key of the error message
     * if either the input is not an array or one of the points are not numeric. If the input is valid, the function
     * returns the points as an array of floats.
     *
     * @return string|float[]
     */
    public function checkPointsInput($data, bool $required, string $key = 'points'): string|array
    {
        if (!is_array($data) || !$this->inArray($data, $key)) {
            return $required ? 'msg_input_is_required' : [];
        }

        try {
            $points = $this->refinery->to()->listOf($this->refinery->kindlyTo()->float())->transform($data[$key]);
        } catch (ConstraintViolationException $e) {
            return 'form_msg_numeric_value_required';
        }

        if (count($points) === 0) {
            return $required ? 'msg_input_is_required' : [];
        }

        return $points;
    }

    /**
     * Extends the checkPointsInput function by checking if at least one point is greater than 0.0. If not, the function
     * returns the language key of the error message.
     *
     * @return string|float[]
     */
    public function checkPointsInputEnoughPositive($data, bool $required, string $key = 'points'): string|array
    {
        $points = $this->checkPointsInput($data, $required, $key);
        if (!is_array($points)) {
            return $points;
        }

        return max($points) <= 0 ? 'enter_enough_positive_points' : $points;
    }

    public function transformArray($data, string $key, Transformation $transformation): array
    {
        if (!$this->inArray($data, $key)) {
            return [];
        }

        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->listOf($transformation),
            $this->refinery->always([])
        ])->transform($data[$key]);
    }

    /**
     * @return array<float>
     */
    public function transformPoints($data, string $key = 'points'): array
    {
        if (!$this->inArray($data, $key)) {
            return [];
        }

        return array_map(
            fn($v): ?float => $this->refinery->byTrying([$this->refinery->kindlyTo()->float(), $this->refinery->always(null)])->transform($v),
            $data[$key]
        );
    }

    /**
     * Returns true if the given key is set in the array and the value is not empty.
     */
    public function inArray($array, $key): bool
    {
        return is_array($array) && array_key_exists($key, $array) && !empty($array[$key]);
    }
}
