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

namespace ILIAS\Mail\Autoresponder;

use InvalidArgumentException;

interface AutoresponderCollection
{
    public function add(AutoresponderDto $element) : void;
    /**
     * @param string|int $key
     * @throws InvalidArgumentException
     */
    public function remove($key) : void;
    /** @throws InvalidArgumentException */
    public function removeElement(AutoresponderDto $element) : void;
    /** @param int|string $key */
    public function containsKey($key) : bool;
    public function getKey(AutoresponderDto $element) : int;
    public function clear() : void;
    public function contains(AutoresponderDto $element) : bool;
    /** @param int|string $key */
    public function get($key) : ?AutoresponderDto;
    /** @param int|string $key */
    public function set($key, AutoresponderDto $value) : void;
    public function isEmpty() : bool;
    /** @return array<int|string> $key */
    public function getKeys() : array;
    /** @return AutoresponderDto[] $*/
    public function getValues() : array;
    public function filter(callable $callable) : self;
    public function slice(int $offset, int $length = null) : self;
    /** @return AutoresponderDto[]|array<int|string, AutoresponderDto> */
    public function toArray() : array;
    public function equals($other) : bool;
}
