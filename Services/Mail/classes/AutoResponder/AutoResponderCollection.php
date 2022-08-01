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

namespace ILIAS\Services\Mail\AutoResponder;

use InvalidArgumentException;

interface AutoResponderCollection
{
    public function add(AutoResponder $element) : void;
    /** @throws InvalidArgumentException */
    public function remove($key) : void;
    /** @throws InvalidArgumentException */
    public function removeElement($element) : void;
    public function containsKey($key) : bool;
    public function getKey($element) : int;
    public function clear() : void;
    public function contains($element) : bool;
    public function get($key);
    public function set($key, $value) : void;
    public function isEmpty() : bool;
    public function getKeys() : array;
    public function getValues() : array;
    public function filter(callable $callable) : self;
    public function slice(int $offset, int $length = null) : self;
    public function toArray() : array;
    public function equals($other) : bool;
}
