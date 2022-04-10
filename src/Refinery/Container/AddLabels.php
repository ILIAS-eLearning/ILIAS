<?php declare(strict_types=1);

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

namespace ILIAS\Refinery\Container;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use InvalidArgumentException;

/**
 * Adds to any array keys for each value
 */
class AddLabels implements Transformation
{
    use DeriveInvokeFromTransform;

    /** @var string[]|int[] */
    private array $labels;
    private Factory $factory;

    /**
     * @param string[]|int[] $labels
     * @param Factory $factory
     */
    public function __construct(array $labels, Factory $factory)
    {
        $this->labels = $labels;
        $this->factory = $factory;
    }

    /**
     * @inheritDoc
     * @return array<int|string, mixed>
     */
    public function transform($from) : array
    {
        if (!is_array($from)) {
            throw new InvalidArgumentException(__METHOD__ . " argument is not an array.");
        }

        if (count($from) !== count($this->labels)) {
            throw new InvalidArgumentException(__METHOD__ . " number of items in arrays are not equal.");
        }

        return array_combine($this->labels, $from);
    }

    /**
     * @inheritDoc
     */
    public function applyTo(Result $result) : Result
    {
        $dataValue = $result->value();
        if (false === is_array($dataValue)) {
            $exception = new InvalidArgumentException(__METHOD__ . " argument is not an array.");
            return $this->factory->error($exception);
        }

        if (count($dataValue) !== count($this->labels)) {
            $exception = new InvalidArgumentException(__METHOD__ . " number of items in arrays are not equal.");
            return $this->factory->error($exception);
        }

        $value = array_combine($this->labels, $dataValue);
        $result = $this->factory->ok($value);

        return $result;
    }
}
