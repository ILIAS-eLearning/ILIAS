<?php declare(strict_types=1);

/**
 * Interface ilDBPdoManagerInterface
 * All these methods are not in MDB 2 will be moved to a seperate interface file
 */
interface ilDBPdoManagerInterface
{
    public function getIndexName(string $idx) : string;

    public function getSequenceName(string $sqn) : string;
}
