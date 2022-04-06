<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBPdoMySQLInnoDB
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLGalera extends ilDBPdoMySQLInnoDB
{
    public function supportsTransactions() : bool
    {
        return true;
    }

    public function buildAtomQuery() : ilAtomQuery
    {
        return new ilAtomQueryTransaction($this);
    }
}
