<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Lock;

/**
 * Interface LockHandlerResult
 * @package ILIAS\ResourceStorage
 */
interface LockHandlerResult
{

    public function runAndUnlock() : void;
}
