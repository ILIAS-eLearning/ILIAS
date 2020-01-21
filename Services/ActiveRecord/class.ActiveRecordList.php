<?php
require_once(dirname(__FILE__) . '/Connector/Join/class.arJoinCollection.php');
require_once(dirname(__FILE__) . '/Connector/Where/class.arWhereCollection.php');
require_once(dirname(__FILE__) . '/Connector/Limit/class.arLimitCollection.php');
require_once(dirname(__FILE__) . '/Connector/Order/class.arOrderCollection.php');
require_once(dirname(__FILE__) . '/Connector/Concat/class.arConcatCollection.php');
require_once(dirname(__FILE__) . '/Connector/Select/class.arSelectCollection.php');
require_once(dirname(__FILE__) . '/Connector/Having/class.arHavingCollection.php');

/**
 * Class ActiveRecordList
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @description
 *
 * @version 2.0.7
 */
class ActiveRecordList
{

    /**
     * @var arWhereCollection
     */
    protected $arWhereCollection;
    /**
     * @var arJoinCollection
     */
    protected $arJoinCollection;
    /**
     * @var arOrderCollection
     */
    protected $arOrderCollection;
    /**
     * @var arLimitCollection
     */
    protected $arLimitCollection;
    /**
     * @var arConcatCollection
     */
    protected $arConcatCollection;
    /**
     * @var arSelectCollection
     */
    protected $arSelectCollection;
    /**
     * @var arHavingCollection
     */
    protected $arHavingCollection;
    /**
     * @var bool
     */
    protected $loaded = false;
    /**
     * @var string
     */
    protected $class = '';
    /**
     * @var ActiveRecord[]
     */
    protected $result = array();
    /**
     * @var array
     */
    protected $result_array = array();
    /**
     * @var bool
     */
    protected $debug = false;
    /**
     * @var null
     */
    protected $date_format = null;
    /**
     * @var array
     */
    protected $addidtional_parameters = array();
    /**
     * @var string
     */
    protected static $last_query;
    /**
     * @var arConnector
     */
    protected $connector;
    /**
     * @var ActiveRecord
     */
    protected $ar;
    /**
     * @var bool
     */
    protected $raw = false;


    /**
     * @param ActiveRecord $ar
     */
    public function __construct(ActiveRecord $ar)
    {
        $this->class = get_class($ar);
        $this->setAR($ar);
        $this->arWhereCollection = arWhereCollection::getInstance($this->getAR());
        $this->arJoinCollection = arJoinCollection::getInstance($this->getAR());
        $this->arLimitCollection = arLimitCollection::getInstance($this->getAR());
        $this->arOrderCollection = arOrderCollection::getInstance($this->getAR());
        $this->arConcatCollection = arConcatCollection::getInstance($this->getAR());
        $this->arSelectCollection = arSelectCollection::getInstance($this->getAR());
        $this->arHavingCollection = arHavingCollection::getInstance($this->getAR());

        $arSelect = new arSelect();
        $arSelect->setTableName($ar->getConnectorContainerName());
        $arSelect->setFieldName('*');
        $this->getArSelectCollection()->add($arSelect);
        //		if ($ar->getArConnector() == NULL) {
//			$this->connector = new arConnectorDB($this);
//		} else {
//			$this->connector = $ar->getArConnector();
//		}
    }


    /**
     * @return arConnector
     */
    protected function getArConnector()
    {
        return arConnectorMap::get($this->getAR());
    }

    //
    // Parameters for Instance
    //
    /**
     * @param array $additional_params
     *
     * @return $this
     */
    public function additionalParams(array $additional_params)
    {
        $this->setAddidtionalParameters($additional_params);

        return $this;
    }


    //
    // Statements
    //

    /**
     * @param      $where
     * @param null $operator
     *
     * @return $this
     * @throws Exception
     */
    public function where($where, $operator = null)
    {
        $this->loaded = false;
        if (is_string($where)) {
            $arWhere = new arWhere();
            $arWhere->setType(arWhere::TYPE_STRING);
            $arWhere->setStatement($where);
            $this->getArWhereCollection()->add($arWhere);

            return $this;
        } elseif (is_array($where)) {
            foreach ($where as $field_name => $value) {
                $arWhere = new arWhere();
                $arWhere->setFieldname($field_name);
                $arWhere->setValue($value);
                if ($operator) {
                    if (is_array($operator)) {
                        $arWhere->setOperator($operator[$field_name]);
                    } else {
                        $arWhere->setOperator($operator);
                    }
                }
                $this->getArWhereCollection()->add($arWhere);
            }

            return $this;
        } else {
            throw new Exception('Wrong where Statement, use strings or arrays');
        }
    }


    /**
     * @param        $order_by
     * @param string $order_direction
     *
     * @return $this
     * @throws arException
     */
    public function orderBy($order_by, $order_direction = 'ASC')
    {
        if (!$this->getAR()->getArFieldList()->isField($order_by)) {
            //			throw new arException(arException::LIST_ORDER_BY_WRONG_FIELD, $order_by); // Due to Bugfix with Joins
        }
        $arOrder = new arOrder();
        $arOrder->setFieldname($order_by);
        $arOrder->setDirection($order_direction);
        $this->getArOrderCollection()->add($arOrder);

        return $this;
    }


    /**
     * @param $start
     * @param $end
     *
     * @return $this
     * @throws arException
     */
    public function limit($start, $end)
    {
        $arLimit = new arLimit();
        $arLimit->setStart($start);
        $arLimit->setEnd($end);

        $this->getArLimitCollection()->add($arLimit);

        return $this;
    }


    /**
     * @param ActiveRecord $ar
     * @param              $on_this
     * @param              $on_external
     * @param array        $fields
     * @param string       $operator
     * @param bool         $both_external
     *
     * @return $this
     */
    public function innerjoinAR(ActiveRecord $ar, $on_this, $on_external, $fields = array( '*' ), $operator = '=', $both_external = false)
    {
        return $this->innerjoin($ar->getConnectorContainerName(), $on_this, $on_external, $fields, $operator, $both_external);
    }


    /**
     * @param string $type
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     * @param array  $fields
     * @param string $operator
     * @param bool   $both_external
     *
     * @return $this
     * @throws arException
     */

    protected function join($type = arJoin::TYPE_INNER, $tablename, $on_this, $on_external, $fields = array( '*' ), $operator = '=', $both_external = false)
    {
        if (!$this->getAR()->getArFieldList()->isField($on_this) and !$both_external) {
            throw new arException(arException::LIST_JOIN_ON_WRONG_FIELD, $on_this);
        }
        $full_names = false;
        foreach ($fields as $field_name) {
            if ($this->getAR()->getArFieldList()->isField($field_name)) {
                $full_names = true;
                break;
            }
        }

        $arJoin = new arJoin();
        $arJoin->setType($type);
        $arJoin->setFullNames($full_names);
        $arJoin->setTableName($tablename);
        $arJoin->setOnFirstField($on_this);
        $arJoin->setOnSecondField($on_external);
        $arJoin->setOperator($operator);
        $arJoin->setFields($fields);
        $arJoin->setBothExternal($both_external);
        $this->getArJoinCollection()->add($arJoin);

        foreach ($fields as $field) {
            $arSelect = new arSelect();
            $arSelect->setTableName($arJoin->getTableNameAs());
            $arSelect->setFieldName($field);
            $arSelect->setAs($arJoin->getTableNameAs() . '_' . $field);
            $this->getArSelectCollection()->add($arSelect);
        }

        return $this;
    }


    /**
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     * @param array  $fields
     * @param string $operator
     *
     * @param bool   $both_external
     *
     * @return $this
     */
    public function leftjoin($tablename, $on_this, $on_external, $fields = array( '*' ), $operator = '=', $both_external = false)
    {
        return $this->join(arJoin::TYPE_LEFT, $tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }


    /**
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     * @param array  $fields
     * @param string $operator
     *
     * @param bool   $both_external
     *
     * @return $this
     */
    public function innerjoin($tablename, $on_this, $on_external, $fields = array( '*' ), $operator = '=', $both_external = false)
    {
        return $this->join(arJoin::TYPE_INNER, $tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }


    /**
     * @param array $fields
     * @param       $as
     *
     * @return $this
     */
    public function concat(array $fields, $as)
    {
        $con = new arConcat();
        $con->setAs($as);
        $con->setFields($fields);
        $this->getArConcatCollection()->add($con);

        return $this;
    }

    //
    // Statement Collections
    //

    /**
     * @return arWhereCollection
     */
    public function getArWhereCollection()
    {
        return $this->arWhereCollection;
    }


    /**
     * @return arJoinCollection
     */
    public function getArJoinCollection()
    {
        return $this->arJoinCollection;
    }


    /**
     * @return arOrderCollection
     */
    public function getArOrderCollection()
    {
        return $this->arOrderCollection;
    }


    /**
     * @return arLimitCollection
     */
    public function getArLimitCollection()
    {
        return $this->arLimitCollection;
    }


    /**
     * @return arConcatCollection
     */
    public function getArConcatCollection()
    {
        return $this->arConcatCollection;
    }


    /**
     * @return arSelectCollection
     */
    public function getArSelectCollection()
    {
        return $this->arSelectCollection;
    }

    /**
     * @return arHavingCollection
     */
    public function getArHavingCollection()
    {
        return $this->arHavingCollection;
    }


    /**
     * @param arHavingCollection $arHavingCollection
     */
    public function setArHavingCollection($arHavingCollection)
    {
        $this->arHavingCollection = $arHavingCollection;
    }

    //
    // Collection Functions
    //

    /**
     * @param string $date_format
     *
     * @return $this
     */
    public function dateFormat($date_format = 'd.m.Y - H:i:s')
    {
        $this->loaded = false;
        $this->setDateFormat($date_format);

        return $this;
    }


    /**
     * @return $this
     */
    public function debug()
    {
        $this->loaded = false;
        $this->debug = true;

        return $this;
    }


    /**
     * @param arConnector $connector
     *
     * @return $this
     */
    public function connector(arConnector $connector)
    {
        $this->connector = $connector;

        return $this;
    }


    /**
     * @param bool $set_raw
     *
     * @return $this
     */
    public function raw($set_raw = true)
    {
        $this->setRaw($set_raw);

        return $this;
    }


    /**
     * @return bool
     */
    public function hasSets()
    {
        return ($this->affectedRows() > 0) ? true : false;
    }


    /**
     * @return int
     */
    public function affectedRows()
    {
        return $this->getArConnector()->affectedRows($this);
    }


    /**
     * @return int
     */
    public function count()
    {
        return $this->affectedRows();
    }


    /**
     * @return $this
     */
    public function getCollection()
    {
        return $this;
    }


    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }


    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }


    /**
     * @return array
     */
    public function get()
    {
        $this->load();

        return $this->result;
    }


    /**
     * @return ActiveRecord
     * @deprecated
     */
    public function getFirstFromLastQuery()
    {
        $this->loadLastQuery();

        $result = array_values($this->result);

        return array_shift($result);
    }


    /**
     * @return ActiveRecord
     */
    public function first()
    {
        $this->load();

        $result = array_values($this->result);

        return array_shift($result);
    }


    /**
     * @return ActiveRecord
     */
    public function last()
    {
        $this->load();

        $result = array_values($this->result);

        return array_pop($result);
    }


    /**
     * @param string       $key    shall a specific value be used as a key? if null then the 1. array key is just increasing from 0.
     * @param string|array $values which values should be taken? if null all are given. If only a string is given then the result is an 1D array!
     *
     * @return array
     */
    public function getArray($key = null, $values = null)
    {
        $this->load();

        return $this->buildArray($key, $values);
    }


    /**
     * @param $key
     * @param $values
     *
     * @return array
     * @throws Exception
     */
    protected function buildArray($key, $values)
    {
        if ($key === null and $values === null) {
            return $this->result_array;
        }
        $array = array();
        foreach ($this->result_array as $row) {
            if ($key) {
                if (!array_key_exists($key, $row)) {
                    throw new Exception("The attribute $key does not exist on this model.");
                }
                $array[$row[$key]] = $this->buildRow($row, $values);
            } else {
                $array[] = $this->buildRow($row, $values);
            }
        }

        return $array;
    }


    /**
     * @param $row
     * @param $values
     *
     * @return array
     */
    protected function buildRow($row, $values)
    {
        if ($values === null) {
            return $row;
        } else {
            $array = array();
            if (!is_array($values)) {
                return $row[$values];
            }
            foreach ($row as $key => $value) {
                if (in_array($key, $values)) {
                    $array[$key] = $value;
                }
            }

            return $array;
        }
    }


    protected function load()
    {
        if ($this->loaded) {
            return;
        } else {
            $records = $this->getArConnector()->readSet($this);
            /**
             * @var $obj ActiveRecord
             */
            //			$class = get_class($this->getAR());
            //			$obj = arFactory::getInstance($class, NULL, $this->getAddidtionalParameters());
            $primaryFieldName = $this->getAR()->getArFieldList()->getPrimaryFieldName();
            $class_name = get_class($this->getAR());
            foreach ($records as $res) {
                $primary_field_value = $res[$primaryFieldName];
                if (!$this->getRaw()) {

//					$obj = arFactory::getInstance($class_name, NULL, $this->getAddidtionalParameters());
                    $obj = new $class_name(0, $this->getArConnector(), $this->getAddidtionalParameters());
                    $this->result[$primary_field_value] = $obj->buildFromArray($res);
                }
                $res_awake = array();
                if (!$this->getRaw()) {
                    foreach ($res as $key => $value) {
                        $arField = $obj->getArFieldList()->getFieldByName($key);
                        if ($arField !== null) {
                            if ($arField->isDateField() and $this->getDateFormat()) {
                                $res_awake[$key . '_unformatted'] = $value;
                                $res_awake[$key . '_unix'] = strtotime($value);
                                $value = date($this->getDateFormat(), strtotime($value));
                            }
                        }
                        $waked = $this->getAR()->wakeUp($key, $value);
                        $res_awake[$key] = ($waked === null) ? $value : $waked;
                    }
                    $this->result_array[$res_awake[$primaryFieldName]] = $res_awake;
                } else {
                    $this->result_array[$primary_field_value] = $res;
                }
            }
            $this->loaded = true;
        }
    }


    /**
     * @deprecated
     */
    protected function loadLastQuery()
    {
        // $this->readFromDb(self::$last_query);
    }

    //
    // Setters & Getters
    //

    /**
     * @param ActiveRecord $ar
     */
    public function setAR($ar)
    {
        $this->ar = $ar;
    }


    /**
     * @return ActiveRecord
     */
    public function getAR()
    {
        return $this->ar;
    }


    /**
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debug;
    }


    /**
     * @param null $date_format
     */
    public function setDateFormat($date_format)
    {
        $this->date_format = $date_format;
    }


    /**
     * @return null
     */
    public function getDateFormat()
    {
        return $this->date_format;
    }


    /**
     * @param string $last_query
     */
    public static function setLastQuery($last_query)
    {
        self::$last_query = $last_query;
    }


    /**
     * @return string
     */
    public static function getLastQuery()
    {
        return self::$last_query;
    }


    /**
     * @param array $addidtional_parameters
     */
    public function setAddidtionalParameters($addidtional_parameters)
    {
        $this->addidtional_parameters = $addidtional_parameters;
    }


    /**
     * @return array
     */
    public function getAddidtionalParameters()
    {
        return $this->addidtional_parameters;
    }


    /**
     * @param boolean $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }


    /**
     * @return boolean
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
