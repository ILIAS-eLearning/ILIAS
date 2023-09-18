<?php

declare(strict_types=1);
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function create(): Objective
    {
        return ($this->objectiveCreationClosure)();
    }
}
