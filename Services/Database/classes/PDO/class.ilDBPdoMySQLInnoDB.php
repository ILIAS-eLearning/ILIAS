<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBPdoMySQLInnoDB
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLInnoDB extends ilDBPdoMySQL
{

    /**
     * @var string
     */
    protected $storage_engine = 'InnoDB';


    public function supportsFulltext() : bool
    {
        return false;
    }


    public function supportsTransactions() : bool
    {
        return false;
    }


    public function addFulltextIndex(string $table_name, array $afields, string $a_name = 'in') : bool
    {
        return false; // NOT SUPPORTED
    }
}
