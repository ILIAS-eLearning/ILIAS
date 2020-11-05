<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\LockHandler;

/**
 * Interface LockingRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface LockingRepository
{

    public function getNameForLocking() : string;
}
