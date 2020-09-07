<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('class.ilDBPdoMySQL.php');

/**
 * Class ilDBPdoMySQLInnoDB
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLInnoDB extends ilDBPdoMySQL implements ilDBInterface
{

    /**
     * @var string
     */
    protected $storage_engine = 'InnoDB';


    /**
     * @return bool
     */
    public function supportsFulltext()
    {
        return false;
    }


    /**
     * @return bool
     */
    public function supportsTransactions()
    {
        return false;
    }


    /**
     * @param $table_name
     * @param $afields
     * @param string $a_name
     * @return bool
     */
    public function addFulltextIndex($table_name, $afields, $a_name = 'in')
    {
        return false; // NOT SUPPORTED
    }
}
