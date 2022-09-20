<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function run(): void
    {
        $this->checkBeforeRun();
        $this->runWithTransactions();
    }


    /**
     * @throws \ilAtomQueryException
     */
    protected function runWithTransactions(): void
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
