<?php
require_once('./Services/Database/interfaces/interface.ilTableLockInterface.php');

/**
 * Class ilTableLock
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilTableLock implements ilTableLockInterface
{

    /**
     * @var string
     */
    protected $table_name = '';
    /**
     * @var bool
     */
    protected $lock_sequence = false;
    /**
     * @var string
     */
    protected $alias = '';
    /**
     * @var int
     */
    protected $lock_level = ilAtomQuery::LOCK_WRITE;
    /**
     * @var bool
     */
    protected $checked = false;
    /**
     * @var ilDBInterface
     */
    protected $ilDBInstance;


    /**
     * ilTableLock constructor.
     *
     * @param string $table_name
     */
    public function __construct($table_name, ilDBInterface $ilDBInterface)
    {
        $this->table_name = $table_name;
        $this->ilDBInstance = $ilDBInterface;
    }


    /**
     * @throws \ilAtomQueryException
     */
    public function check()
    {
        if (!in_array($this->getLockLevel(), array( ilAtomQuery::LOCK_READ, ilAtomQuery::LOCK_WRITE ))) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_LOCK_WRONG_LEVEL);
        }
        if (!$this->getTableName() || !$this->ilDBInstance->tableExists($this->getTableName())) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_LOCK_TABLE_NONEXISTING);
        }

        $this->setChecked(true);
    }


    /**
     * @param bool $lock_bool
     * @return $this
     */
    public function lockSequence($lock_bool)
    {
        $this->setLockSequence($lock_bool);

        return $this;
    }


    /**
     * @param $alias_name
     * @return $this
     */
    public function aliasName($alias_name)
    {
        $this->setAlias($alias_name);

        return $this;
    }


    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }


    /**
     * @param string $table_name
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }


    /**
     * @return boolean
     */
    public function isLockSequence()
    {
        return $this->lock_sequence;
    }


    /**
     * @param boolean $lock_sequence
     */
    public function setLockSequence($lock_sequence)
    {
        $this->lock_sequence = $lock_sequence;
    }


    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }


    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }


    /**
     * @return int
     */
    public function getLockLevel()
    {
        return $this->lock_level;
    }


    /**
     * @param int $lock_level
     */
    public function setLockLevel($lock_level)
    {
        $this->lock_level = $lock_level;
    }


    /**
     * @return boolean
     */
    public function isChecked()
    {
        return $this->checked;
    }


    /**
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }
}
