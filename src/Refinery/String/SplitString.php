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

namespace ILIAS\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use InvalidArgumentException;

/**
 * Split a string by delimiter into array
 */
class SplitString implements Transformation
{
    use DeriveInvokeFromTransform;

    private string $delimiter;
    private Factory $factory;

    public function __construct(string $delimiter, Factory $factory)
    {
        $this->delimiter = $delimiter;
        $this->factory = $factory;
    }

    /**
     * @inheritDoc
     * @return string[]
     */
    public function transform($from): array
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        return explode($this->delimiter, $from);
    }

    /**
     * @inheritDoc
     */
    public function applyTo(Result $result): Result
    {
        $dataValue = $result->value();
        if (false === is_string($dataValue)) {
            $exception = new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
            return $this->factory->error($exception);
        }

        $value = explode($this->delimiter, $dataValue);
        return $this->factory->ok($value);
    }
}
