<?php /******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/** @noinspection NullPointerExceptionInspection */
/** @noinspection NullPointerExceptionInspection */
/** @noinspection NullPointerExceptionInspection */
/** @noinspection NullPointerExceptionInspection */
/**
 * Class ActiveRecordList
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @description
 * @version 2.0.7
 */
class ActiveRecordList
{
    protected \arWhereCollection $arWhereCollection;
    protected \arJoinCollection $arJoinCollection;
    protected \arOrderCollection $arOrderCollection;
    protected \arLimitCollection $arLimitCollection;
    protected \arConcatCollection $arConcatCollection;
    protected \arSelectCollection $arSelectCollection;
    protected \arHavingCollection $arHavingCollection;
    protected bool $loaded = false;
    protected string $class = '';
    /**
     * @var ActiveRecord[]
     */
    protected array $result = array();
    protected array $result_array = array();
    protected bool $debug = false;
    protected ?string $date_format = null;
    protected array $addidtional_parameters = array();
    protected static ?string $last_query = null;
    protected ?\arConnector $connector = null;
    protected ?\ActiveRecord $ar = null;
    protected bool $raw = false;

    /**
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
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
    }

    protected function getArConnector() : \arConnector
    {
        return arConnectorMap::get($this->getAR());
    }

    public function additionalParams(array $additional_params) : self
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
     * @return $this|void
     * @throws Exception
     */
    public function where($where, $operator = null) : self
    {
        $this->loaded = false;
        if (is_string($where)) {
            $arWhere = new arWhere();
            $arWhere->setType(arWhere::TYPE_STRING);
            $arWhere->setStatement($where);
            $this->getArWhereCollection()->add($arWhere);

            return $this;
        }

        if (is_array($where)) {
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
        }

        throw new Exception('Wrong where Statement, use strings or arrays');
    }

    /**
     * @param        $order_by
     * @throws arException
     */
    public function orderBy(string $order_by, string $order_direction = 'ASC') : self
    {
        $arOrder = new arOrder();
        $arOrder->setFieldname($order_by);
        $arOrder->setDirection($order_direction);
        $this->getArOrderCollection()->add($arOrder);

        return $this;
    }

    /**
     * @param $start
     * @param $end
     * @throws arException
     */
    public function limit(int $start, int $end) : self
    {
        $arLimit = new arLimit();
        $arLimit->setStart($start);
        $arLimit->setEnd($end);

        $this->getArLimitCollection()->add($arLimit);

        return $this;
    }

    /**
     * @param              $on_this
     * @param              $on_external
     */
    public function innerjoinAR(
        ActiveRecord $ar,
        $on_this,
        $on_external,
        array $fields = array('*'),
        string $operator = '=',
        bool $both_external = false
    ) : self {
        return $this->innerjoin(
            $ar->getConnectorContainerName(),
            $on_this,
            $on_external,
            $fields,
            $operator,
            $both_external
        );
    }

    /**
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     * @throws arException
     */
    protected function join(
        string $type,
        string $tablename,
        $on_this,
        string $on_external,
        array $fields = array('*'),
        string $operator = '=',
        bool $both_external = false
    ) : self {
        if (!$both_external && !$this->getAR()->getArFieldList()->isField($on_this)) {
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
     */
    public function leftjoin(
        $tablename,
        $on_this,
        $on_external,
        array $fields = array('*'),
        string $operator = '=',
        bool $both_external = false
    ) : self {
        return $this->join(arJoin::TYPE_LEFT, $tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }

    /**
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     */
    public function innerjoin(
        $tablename,
        $on_this,
        $on_external,
        array $fields = array('*'),
        string $operator = '=',
        bool $both_external = false
    ) : self {
        return $this->join(arJoin::TYPE_INNER, $tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }

    /**
     * @param       $as
     */
    public function concat(array $fields, string $as) : self
    {
        $con = new arConcat();
        $con->setAs($as);
        $con->setFields($fields);
        $this->getArConcatCollection()->add($con);

        return $this;
    }

    public function getArWhereCollection() : \arWhereCollection
    {
        return $this->arWhereCollection;
    }

    public function getArJoinCollection() : \arJoinCollection
    {
        return $this->arJoinCollection;
    }

    public function getArOrderCollection() : \arOrderCollection
    {
        return $this->arOrderCollection;
    }

    public function getArLimitCollection() : \arLimitCollection
    {
        return $this->arLimitCollection;
    }

    public function getArConcatCollection() : \arConcatCollection
    {
        return $this->arConcatCollection;
    }

    public function getArSelectCollection() : \arSelectCollection
    {
        return $this->arSelectCollection;
    }

    public function getArHavingCollection() : \arHavingCollection
    {
        return $this->arHavingCollection;
    }

    public function setArHavingCollection(\arHavingCollection $arHavingCollection) : void
    {
        $this->arHavingCollection = $arHavingCollection;
    }

    public function dateFormat(string $date_format = 'd.m.Y - H:i:s') : self
    {
        $this->loaded = false;
        $this->setDateFormat($date_format);

        return $this;
    }

    public function debug() : self
    {
        $this->loaded = false;
        $this->debug = true;

        return $this;
    }

    public function connector(arConnector $connector) : self
    {
        $this->connector = $connector;

        return $this;
    }

    public function raw(bool $set_raw = true) : self
    {
        $this->setRaw($set_raw);

        return $this;
    }

    public function hasSets() : bool
    {
        return $this->affectedRows() > 0;
    }

    public function affectedRows() : int
    {
        return $this->getArConnector()->affectedRows($this);
    }

    public function count() : int
    {
        return $this->affectedRows();
    }

    public function getCollection() : self
    {
        return $this;
    }

    public function setClass(string $class) : void
    {
        $this->class = $class;
    }

    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * @return \ActiveRecord[]
     */
    public function get() : array
    {
        $this->load();

        return $this->result;
    }

    /**
     * @deprecated
     */
    public function getFirstFromLastQuery() : ?\ActiveRecord
    {
        $this->loadLastQuery();

        $result = array_values($this->result);

        return array_shift($result);
    }

    public function first() : ?\ActiveRecord
    {
        $this->load();

        $result = array_values($this->result);

        return array_shift($result);
    }

    public function last() : ?\ActiveRecord
    {
        $this->load();

        $result = array_values($this->result);

        return array_pop($result);
    }

    /**
     * @param string       $key    shall a specific value be used as a key? if null then the 1. array key is just increasing from 0.
     * @param string|array $values which values should be taken? if null all are given. If only a string is given then the result is an 1D array!
     */
    public function getArray(string $key = null, $values = null) : array
    {
        $this->load();

        return $this->buildArray($key, $values);
    }

    /**
     * @param int|string|array|null $values
     * @throws Exception
     */
    protected function buildArray(?string $key, $values) : array
    {
        if ($key === null && $values === null) {
            return $this->result_array;
        }
        $array = [];
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
     * @param string|array|null $values
     * @return string|int|null|array
     */
    protected function buildRow(?array $row, $values)
    {
        if ($values === null) {
            return $row;
        }
    
        if (!is_array($values)) {
            return $row[$values];
        }
        
        $array = [];
        foreach ($row as $key => $value) {
            if (in_array($key, $values)) {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    protected function load() : void
    {
        if ($this->loaded) {
            return;
        }

        $records = $this->getArConnector()->readSet($this);
        /**
         * @var $obj ActiveRecord
         */
        $primaryFieldName = $this->getAR()->getArFieldList()->getPrimaryFieldName();
        /** @noinspection GetClassUsageInspection */
        $class_name = get_class($this->getAR());
        foreach ($records as $res) {
            $primary_field_value = $res[$primaryFieldName];
            if (!$this->getRaw()) {
                $obj = new $class_name(0, $this->getArConnector(), $this->getAddidtionalParameters());
                $this->result[$primary_field_value] = $obj->buildFromArray($res);
            }
            $res_awake = array();
            if (!$this->getRaw()) {
                foreach ($res as $key => $value) {
                    $arField = $obj->getArFieldList()->getFieldByName($key);
                    if ($arField !== null) {
                        if ($arField->isDateField() && $this->getDateFormat()) {
                            $res_awake[$key . '_unformatted'] = $value;
                            $res_awake[$key . '_unix'] = strtotime($value);
                            $value = date($this->getDateFormat(), strtotime($value));
                        }
                    }
                    $waked = $this->getAR()->wakeUp($key, $value);
                    $res_awake[$key] = $waked ?? $value;
                }
                $this->result_array[$res_awake[$primaryFieldName]] = $res_awake;
            } else {
                $this->result_array[$primary_field_value] = $res;
            }
        }
        $this->loaded = true;
    }

    /**
     * @deprecated
     */
    protected function loadLastQuery() : void
    {
        // $this->readFromDb(self::$last_query);
    }

    public function setAR(\ActiveRecord $ar) : void
    {
        $this->ar = $ar;
    }

    public function getAR() : \ActiveRecord
    {
        return $this->ar;
    }

    public function getDebug() : bool
    {
        return $this->debug;
    }
    
    public function setDateFormat(string $date_format) : void
    {
        $this->date_format = $date_format;
    }

    public function getDateFormat() : string
    {
        return $this->date_format ?? '';
    }

    public static function setLastQuery(string $last_query) : void
    {
        self::$last_query = $last_query;
    }

    public static function getLastQuery() : ?string
    {
        return self::$last_query;
    }

    /**
     * @param mixed[] $addidtional_parameters
     */
    public function setAddidtionalParameters(array $addidtional_parameters) : void
    {
        $this->addidtional_parameters = $addidtional_parameters;
    }

    /**
     * @return mixed[]
     */
    public function getAddidtionalParameters() : array
    {
        return $this->addidtional_parameters;
    }

    public function setRaw(bool $raw) : void
    {
        $this->raw = $raw;
    }

    public function getRaw() : bool
    {
        return $this->raw;
    }
}
