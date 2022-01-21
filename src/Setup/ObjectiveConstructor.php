<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use Closure;

/**
 * Class ObjectiveConstructor
 * @package ILIAS\Setup
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ObjectiveConstructor
{
    private string $description;

    private Closure $objectiveCreationClosure;

    public function __construct(string $description, Closure $objectiveCreationClosure)
    {
        $this->description = $description;
        $this->objectiveCreationClosure = $objectiveCreationClosure;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function create() : Objective
    {
        return ($this->objectiveCreationClosure)();
    }
}
