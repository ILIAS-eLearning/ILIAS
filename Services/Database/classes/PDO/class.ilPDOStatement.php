<?php


require_once 'Services/Database/classes/interface.ilDBStatement.php';

/**
 * Class ilPDOStatement is a Wrapper Class for PDOStatement
 */
class ilPDOStatement implements ilDBStatement {

    /**
     * @var PDOStatement
     */
    protected $pdo;

    /**
     * @param $pdoStatement PDOStatement The PDO Statement to be wrapped.
     */
    public function __construct($pdoStatement) {
        $this->pdo = $pdoStatement;
    }


    /**
     * @param int $fetchMode
     * @return mixed
     * @throws ilDatabaseException
     */
    function fetchRow($fetchMode = DB_FETCHMODE_ASSOC) {
        if($fetchMode == DB_FETCHMODE_ASSOC) {
            return $this->pdo->fetch(PDO::FETCH_ASSOC);
        } elseif ($fetchMode == DB_FETCHMODE_OBJECT) {
            return $this->pdo->fetch(PDO::FETCH_OBJ);
        } else {
            throw new ilDatabaseException("No valid fetch mode given, choose DB_FETCHMODE_ASSOC or DB_FETCHMODE_OBJECT");
        }
    }

    /**
     * @param int $fetchMode
     * @return mixed|void
     */
    function fetch($fetchMode = DB_FETCHMODE_ASSOC) {
        return $this->fetchRow($fetchMode);
    }

    /**
     * Pdo allows for a manual closing of the cursor.
     */
    public function closeCursor() {
        $this->pdo->closeCursor();
    }

    /**
     * @return int
     */
    function rowCount()
    {
        return $this->pdo->rowCount();
    }

    /**
     * @return stdClass
     */
    function fetchObject() {
        return $this->fetch(DB_FETCHMODE_OBJECT);
    }

    /**
     * @return int
     */
    function numRows()
    {
        return $this->pdo->rowCount();
    }
}