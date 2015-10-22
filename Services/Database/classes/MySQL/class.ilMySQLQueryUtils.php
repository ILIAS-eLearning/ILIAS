<?php


//TODO....

require_once 'Services/Database/classes/interface.ilQueryUtils.php';
require_once 'Services/PEAR/lib/MDB2/Driver/Datatype/mysqli.php';

class ilMySQLQueryUtils implements ilQueryUtils {

    protected static $instance = null;

    /**
     * @return ilMySQLQueryUtils
     */
    public static function getInstance() {
        if(!self::$instance)
            self::createInstance();
        return self::$instance;
    }

    protected static function createInstance() {
        self::$instance = new \ilMySQLQueryUtils();
    }

    protected function __construct() {
    }

    /**
     * @param string $field
     * @param string[] $values
     * @param bool $negate
     * @param string $type
     * @return string
     */
    public function in($field, $values, $negate = false, $type = "")
    {
        if (count($values) == 0)
        {
            // BEGIN fixed mantis #0014191:
            //return " 1=2 ";		// return a false statement on empty array
            return $negate ? ' 1=1 ' : ' 1=2 ';
            // END fixed mantis #0014191:
        }
        if ($type == "")		// untyped: used ? for prepare/execute
        {
            $str = $field.(($negate) ? " NOT" : "")." IN (?".str_repeat(",?", count($values) - 1).")";
        }
        else					// typed, use values for query/manipulate
        {
            $str = $field.(($negate) ? " NOT" : "")." IN (";
            $sep = "";
            foreach ($values as $v)
            {
                $str.= $sep.$this->quote($v, $type);
                $sep = ",";
            }
            $str.= ")";
        }

        return $str;
    }

    /**
     * @param $query mixed
     * @param $type string
     * @return string
     */
    public function quote($query, $type) {
        //TODO ACTUALLY ESCAPE.
        if($type = 'text')
            return "'$query'";
        return $query;
    }
}