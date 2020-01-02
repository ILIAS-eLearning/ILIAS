<?php
/* Copyright (c) 2018 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;

/**
 * Class IsArrayOf
 *
 * @package ILIAS\Validation\Constraints
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class IsArrayOf extends Custom implements Constraint
{

    /**
     * IsArrayOf constructor.
     *
     * @param Data\Factory $data_factory
     * @param Constraint   $on_element
     */
    public function __construct(Data\Factory $data_factory, Constraint $on_element, \ilLanguage $lng)
    {
        parent::__construct(
            function ($value) use ($on_element) {
                if (!is_array($value)) {
                    return false;
                }
                foreach ($value as $item) {
                    if (!$on_element->accepts($item)) {
                        return false;
                    }
                }

                return true;
            },
            function ($txt, $value) use ($on_element) {
                if (!is_array($value)) {
                    return $txt("not_an_array", gettype($value));
                }
                $sub_problems = [];
                foreach ($value as $item) {
                    $sub_problem = $on_element->problemWith($item);
                    if ($sub_problem !== null) {
                        $sub_problems[] = $sub_problem;
                    }
                }
                return $txt("not_an_array_of", implode(" ", $sub_problems));
            },
            $data_factory,
            $lng
        );
    }
}
