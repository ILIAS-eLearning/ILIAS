<?php declare(strict_types=1);

/* Copyright (c) 2017 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes Textarea inputs.
 */
interface Textarea extends FormInput
{
    /**
     * set maximum number of characters
     */
    public function withMaxLimit(int $max_limit) : Textarea;

    /**
     * get maximum limit of characters
     * @return mixed
     */
    public function getMaxLimit();

    /**
     * set minimum number of characters
     */
    public function withMinLimit(int $min_limit) : Textarea;

    /**
     * get minimum limit of characters
     * @return mixed
     */
    public function getMinLimit();

    /**
     * bool if textarea has max or min number of character limit.
     */
    public function isLimited() : bool;
}
