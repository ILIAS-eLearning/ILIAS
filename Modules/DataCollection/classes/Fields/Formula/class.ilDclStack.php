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
 ********************************************************************
 */

/**
 * Class ilDclStack
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclStack
{
    protected array $stack = array();

    /**
     * @param float|int|string $elem
     */
    public function push($elem)
    {
        $this->stack[] = $elem;
    }

    /**
     * @return ?float|int|string
     */
    public function pop()
    {
        if (!$this->isEmpty()) {
            $last_index = count($this->stack) - 1;
            $elem = $this->stack[$last_index];
            unset($this->stack[$last_index]);
            $this->stack = array_values($this->stack); // re-index

            return $elem;
        }

        return null;
    }

    /**
     * @return ?float|int|string
     */
    public function top()
    {
        if (!$this->isEmpty()) {
            return $this->stack[count($this->stack) - 1];
        }

        return null;
    }

    public function isEmpty() : bool
    {
        return !(bool) count($this->stack);
    }

    public function reset() : void
    {
        $this->stack = array();
    }

    public function count() : int
    {
        return count($this->stack);
    }

    public function debug() : void
    {
        echo "<pre>" . print_r($this->stack, 1) . "</pre>";
    }
}
