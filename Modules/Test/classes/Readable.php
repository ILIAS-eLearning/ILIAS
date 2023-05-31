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

declare(strict_types=1);

namespace ILIAS\Modules\Test;

use Closure;
use ILIAS\DI\Container;
use ilObject;

class Readable
{
    /** @var Container */
    private $container;
    /** @var Closure(int): int[] */
    private $references_of;
    /** @var Incident */
    private $incident;

    public function __construct(
        Container $container,
        $references_of = [ilObject::class, '_getAllReferences'],
        Incident $incident = null
    ) {
        $this->container = $container;
        $this->references_of = Closure::fromCallable($references_of);
        $this->incident = $incident ?? new Incident();
    }

    /**
     * @param int[] $references
     */
    public function references(array $references): bool
    {
        return $this->incident->any(function (int $ref_id): bool {
            return $this->container->access()->checkAccess('read', '', $ref_id);
        }, $references);
    }

    public function objectId(int $obj_id): bool
    {
        return $this->references(array_map('intval', ($this->references_of)($obj_id)));
    }
}
