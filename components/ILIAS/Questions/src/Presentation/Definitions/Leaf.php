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
 * Class ArrayBasedRequestWrapper
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ArrayBasedRequestWrapper implements RequestWrapper
{
    /**
     * GetRequestWrapper constructor.
     * @param mixed[] $raw_values
     */
    public function __construct(private array $raw_values)
    {
    }


    /**
     * @inheritDoc
     */
    public function retrieve(string $key, Transformation $transformation)
    {
        return $transformation->transform($this->raw_values[$key] ?? null);
    }


    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return isset($this->raw_values[$key]);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return array_keys($this->raw_values);
    }
}
