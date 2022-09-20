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
 * Class ilPDOStatement is a Wrapper Class for PDOStatement
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPDOStatement implements ilDBStatement
{
    protected \PDOStatement $pdo_statement;


    /**
     * @param $pdo_statement PDOStatement The PDO Statement to be wrapped.
     */
    public function __construct(PDOStatement $pdo_statement)
    {
        $this->pdo_statement = $pdo_statement;
    }


    /**
     * @return mixed
     * @throws ilDatabaseException
     */
    public function fetchRow(int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC)
    {
        if ($fetch_mode === ilDBConstants::FETCHMODE_ASSOC) {
            return $this->pdo_statement->fetch(PDO::FETCH_ASSOC);
        }

        if ($fetch_mode === ilDBConstants::FETCHMODE_OBJECT) {
            return $this->pdo_statement->fetch(PDO::FETCH_OBJ);
        }

        throw new ilDatabaseException("No valid fetch mode given, choose ilDBConstants::FETCHMODE_ASSOC or ilDBConstants::FETCHMODE_OBJECT");
    }


    /**
     * @return mixed
     */
    public function fetch(int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC)
    {
        return $this->fetchRow($fetch_mode);
    }


    /**
     * Pdo allows for a manual closing of the cursor.
     */
    public function closeCursor(): void
    {
        $this->pdo_statement->closeCursor();
    }


    public function rowCount(): int
    {
        return $this->pdo_statement->rowCount();
    }


    public function fetchObject(): ?stdClass
    {
        return $this->fetch(ilDBConstants::FETCHMODE_OBJECT) ?: null;
    }


    public function fetchAssoc(): ?array
    {
        return $this->fetch(ilDBConstants::FETCHMODE_ASSOC);
    }


    public function numRows(): int
    {
        return $this->pdo_statement->rowCount();
    }


    /**
     * @inheritdoc
     */
    public function execute(array $a_data = null): ilDBStatement
    {
        $this->pdo_statement->execute($a_data);

        return $this;
    }

    public function errorCode(): string
    {
        return $this->pdo_statement->errorCode();
    }

    public function errorInfo(): array
    {
        return $this->pdo_statement->errorInfo();
    }
}
