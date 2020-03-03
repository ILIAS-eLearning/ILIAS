<?php

require_once 'Services/Database/interfaces/interface.ilDBStatement.php';

/**
 * Class ilPDOStatement is a Wrapper Class for PDOStatement
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPDOStatement implements ilDBStatement
{

    /**
     * @var PDOStatement
     */
    protected $pdo_statement;


    /**
     * @param $pdo_statement PDOStatement The PDO Statement to be wrapped.
     */
    public function __construct(PDOStatement $pdo_statement)
    {
        $this->pdo_statement = $pdo_statement;
    }


    /**
     * @param int $fetch_mode
     * @return mixed
     * @throws ilDatabaseException
     */
    public function fetchRow($fetch_mode = ilDBConstants::FETCHMODE_ASSOC)
    {
        if ($fetch_mode == ilDBConstants::FETCHMODE_ASSOC) {
            return $this->pdo_statement->fetch(PDO::FETCH_ASSOC);
        } elseif ($fetch_mode == ilDBConstants::FETCHMODE_OBJECT) {
            return $this->pdo_statement->fetch(PDO::FETCH_OBJ);
        } else {
            throw new ilDatabaseException("No valid fetch mode given, choose ilDBConstants::FETCHMODE_ASSOC or ilDBConstants::FETCHMODE_OBJECT");
        }
    }


    /**
     * @param int $fetch_mode
     * @return mixed|void
     */
    public function fetch($fetch_mode = ilDBConstants::FETCHMODE_ASSOC)
    {
        return $this->fetchRow($fetch_mode);
    }


    /**
     * Pdo allows for a manual closing of the cursor.
     */
    public function closeCursor()
    {
        $this->pdo_statement->closeCursor();
    }


    /**
     * @return int
     */
    public function rowCount()
    {
        return $this->pdo_statement->rowCount();
    }


    /**
     * @return stdClass
     */
    public function fetchObject()
    {
        return $this->fetch(ilDBConstants::FETCHMODE_OBJECT);
    }


    /**
     * @return array
     */
    public function fetchAssoc()
    {
        return $this->fetch(ilDBConstants::FETCHMODE_ASSOC);
    }


    /**
     * @return int
     */
    public function numRows()
    {
        return $this->pdo_statement->rowCount();
    }


    /**
     * @inheritdoc
     */
    public function execute($a_data = null)
    {
        $this->pdo_statement->execute($a_data);

        return $this;
    }

    /**
     * @return string
     */
    public function errorCode()
    {
        return $this->pdo_statement->errorCode();
    }

    /**
     * @return array
     */
    public function errorInfo()
    {
        return $this->pdo_statement->errorInfo();
    }
}
