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

namespace ILIAS\Refinery;

use Closure;
use ArrayAccess;
use Countable;

class KeyValueAccess implements ArrayAccess, Countable
{
    private array $raw_values;
    private Transformation $trafo;

    public function __construct(array $raw_values, Transformation $trafo)
    {
        $this->trafo = $trafo;
        $this->raw_values = $raw_values;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->raw_values[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return is_array($this->raw_values[$offset])
            ? array_map($this->getApplicator(), $this->raw_values[$offset])
            : $this->getApplicator()($this->raw_values[$offset]);
    }

    private function getApplicator() : Closure
    {
        return function ($value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $value[$k] = $this->getApplicator()($v);
                }
                return $value;
            }
            return $this->trafo->transform($value);
        };
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value) : void
    {
        $this->raw_values[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset) : void
    {
        if ($this->offsetExists($offset)) {
            unset($this->raw_values[$offset]);
        }
    }

    /**
     * @inheritDoc
     */
    public function count() : int
    {
        return count($this->raw_values);
    }
}
