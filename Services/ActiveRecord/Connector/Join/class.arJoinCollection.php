<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arJoin.php');

/**
 * Class arJoinCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arJoinCollection extends arStatementCollection
{

    /**
     * @var array
     */
    protected $table_names = array();


    /**
     * @param arJoin $statement
     *
     * @return string
     */
    public function getSaveTableName(arJoin $statement)
    {
        $table_name = $statement->getTableName();
        if (in_array($table_name, $this->table_names)) {
            $vals = array_count_values($this->table_names);
            $next = $vals[$table_name] + 1;
            $statement->setFullNames(true);
            $statement->setIsMapped(true);

            return $table_name . '_' . $next;
        } else {
            return $table_name;
        }
    }


    /**
     * @param arStatement $statement
     */
    public function add(arStatement $statement)
    {
        $statement->setTableNameAs($this->getSaveTableName($statement));
        $this->table_names[] = $statement->getTableName();
        parent::add($statement);
    }


    /**
     * @return string
     */
    public function asSQLStatement()
    {
        $return = '';
        if ($this->hasStatements()) {
            foreach ($this->getJoins() as $join) {
                $return .= $join->asSQLStatement($this->getAr());
            }
        }

        return $return;
    }



    /**
     * @return arJoin[]
     */
    public function getJoins()
    {
        return $this->statements;
    }
}
