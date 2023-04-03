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
namespace ILIAS\GlobalScreen\ScreenContext\AdditionalData;

use LogicException;

/**
 * Class Collection
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Collection
{
    /**
     * @var mixed[]
     */
    private $values = [];

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->values;
    }

    /**
     * @param string $key
     * @param        $value
     */
    public function add(string $key, $value) : void
    {
        if ($this->exists($key)) {
            throw new LogicException("Key $key already exists.");
        }
        $this->values[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->values[$key];
    }

    /**
     * @param string $key
     * @param        $expected_value
     * @return bool
     */
    public function is(string $key, $expected_value) : bool
    {
        return ($this->exists($key) && $this->get($key) === $expected_value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key) : bool
    {
        return isset($this->values[$key]);
    }

    /**
     * @param string $key
     * @param        $value
     */
    public function replace(string $key, $value) : void
    {
        if (!$this->exists($key)) {
            throw new LogicException("Key $key does not exists.");
        }
        $this->values[$key] = $value;
    }
}
