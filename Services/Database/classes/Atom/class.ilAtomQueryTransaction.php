<?php
require_once('./Services/Database/exceptions/exception.ilAtomQueryException.php');
require_once('./Services/Database/interfaces/interface.ilAtomQuery.php');

/**
 * Class ilAtomQueryTransaction
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 *
 *         Implements Atom-Queries with Transactions, currently used in ilDbPdoGalery
 */
class ilAtomQueryTransaction extends ilAtomQueryBase implements ilAtomQuery
{

    /**
     * Fire your Queries
     *
     * @throws \ilAtomQueryException
     */
    public function run()
    {
        $this->checkBeforeRun();
        $this->runWithTransactions();
    }


    /**
     * @throws \ilAtomQueryException
     */
    protected function runWithTransactions()
    {
        $i = 0;
        do {
            $e = null;
            try {
                $this->ilDBInstance->beginTransaction();
                $this->runQueries();
                $this->ilDBInstance->commit();
            } catch (ilDatabaseException $e) {
                $this->ilDBInstance->rollback();
                if ($i >= self::ITERATIONS - 1) {
                    throw $e;
                }
            }
            $i++;
        } while ($e instanceof ilDatabaseException);
    }
}
