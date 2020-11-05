<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Lock;

/**
 * Interface LockHandler
 * @package ILIAS\ResourceStorage
 */
class LockHandlerResultilDB implements LockHandlerResult
{
    /**
     * @var \ilAtomQuery
     */
    protected $atom;

    /**
     * LockHandlerResultilDB constructor.
     * @param \ilAtomQuery $atom
     */
    public function __construct(\ilAtomQuery $atom)
    {
        $this->atom = $atom;
    }

    public function runAndUnlock() : void
    {
        $this->atom->run();
    }

}
