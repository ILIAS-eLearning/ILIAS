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

    private Closure $objectiveCollectionClosure;

    public function __construct(string $description, Closure $objectiveCollectionClosure)
    {
        $this->description = $description;
        $this->objectiveCollectionClosure = $objectiveCollectionClosure;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function create() : ObjectiveCollection
    {
        return $this->getObjectiveCollectionClosure()();
    }

    private function getObjectiveCollectionClosure() : Closure
    {
        return $this->objectiveCollectionClosure;
    }
}
