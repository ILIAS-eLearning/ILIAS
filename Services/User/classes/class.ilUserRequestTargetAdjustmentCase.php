<?php declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilUserRequestTargetAdjustmentCase
 */
abstract class ilUserRequestTargetAdjustmentCase
{
    /**
     * @return bool
     */
    abstract public function shouldStoreRequestTarget() : bool;

    /**
     * @return bool
     */
    abstract public function shouldAdjustRequest() : bool;

    /**
     * @return bool
     */
    abstract public function isInFulfillment() : bool;

    /**
     * @return void
     */
    abstract public function adjust() : void;
}
