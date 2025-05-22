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

namespace ILIAS\HTTP\Wrapper;

use ILIAS\Refinery\Transformation;

/**
 * Interface RequestWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface RequestWrapper
{
    /**
     *
     * @return mixed
     */
    public function retrieve(string $key, Transformation $transformation);


    public function has(string $key): bool;

    /**
     * Get all keys from the request
     *
     * @return array<string|int>
     */
    public function keys(): array;
}
