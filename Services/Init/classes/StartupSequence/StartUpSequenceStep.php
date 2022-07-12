<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Init\StartupSequence;

/**
 * Class StartUpSequenceStep
 * @package ILIAS\Init\StartupSequence
 * @author  Michael Jansen <mjansen@databay.de>
 */
abstract class StartUpSequenceStep
{
    /**
     * @return bool
     */
    abstract public function shouldStoreRequestTarget() : bool;

    /**
     * @return bool
     */
    abstract public function shouldInterceptRequest() : bool;

    /**
     * @return bool
     */
    abstract public function isInFulfillment() : bool;

    /**
     * @return void
     */
    abstract public function execute() : void;
}
