<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\LockHandler;

/**
 * Interface LockHandlerResult
 * @package ILIAS\ResourceStorage
 */
interface LockHandlerResult
{

    public function runAndUnlock() : void;
}
