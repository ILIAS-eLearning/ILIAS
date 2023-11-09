<?php

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
 * Class ActiveRecord
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @experimental
 * @description
 * @version 2.0.7
 */
abstract class ActiveRecord
{
    protected bool $ar_safe_read = true;
    protected string $connector_container_name = '';
    protected bool $is_new = true;

    public function getArConnector(): \arConnector
    {
        return arConnectorMap::get($this);
    }

    public function getArFieldList(): \arFieldList
    {
        return arFieldCache::get($this);
    }

    /**
     * @return never
     * @throws \arException
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        throw new arException(
            arException::UNKNONWN_EXCEPTION,
            'Implement getConnectorContainerName in your child-class'
        );
    }

    /**
     * @description Return the Name of your Connector Table
     */
    public function getConnectorContainerName(): string
    {
        // WILL BE ABSTRACT TO REPLACE returnDbTableName() IN NEXT VERSION
        if ($this->connector_container_name !== '' && $this->connector_container_name !== '0') {
            return $this->connector_container_name;
        }

        $ar = self::getCalledClass();

        return $ar::returnDbTableName();
    }

    public function setConnectorContainerName(string $connector_container_name): void
    {
        $this->connector_container_name = $connector_container_name;
    }

    /**
     * @return mixed
     */
    public function getPrimaryFieldValue()
    {
        $primary_fieldname = arFieldCache::getPrimaryFieldName($this);

        return $this->{$primary_fieldname};
    }

    /**
     * @param $value
     */
    public function setPrimaryFieldValue($value): void
    {
        $primary_fieldname = arFieldCache::getPrimaryFieldName($this);

        $this->{$primary_fieldname} = $value;
    }

    public function __construct(mixed $primary_key = 0)
    {
        $arFieldList = arFieldCache::get($this);

        $primaryFieldName = $arFieldList->getPrimaryFieldName();
        $this->{$primaryFieldName} = $primary_key;
        if ($primary_key === 0) {
            return;
        }
        if ($primary_key === null) {
            return;
        }
        if ($primary_key === false) {
            return;
        }
        $this->read();
    }

    public function storeObjectToCache(): void
    {
        arObjectCache::store($this);
    }

    public function asStdClass(): \stdClass
    {
        $return = new stdClass();
        foreach ($this->getArFieldList()->getFields() as $arField) {
            $fieldname = $arField->getName();
            $return->{$fieldname} = $this->{$fieldname};
        }

        return $return;
    }

    /**
     * @return array<string, mixed>
     */
    public function asArray(): array
    {
        $return = [];
        foreach ($this->getArFieldList()->getFields() as $arField) {
            $fieldname = $arField->getName();
            $return[$fieldname] = $this->{$fieldname};
        }

        return $return;
    }

    public function buildFromArray(array $array): static
    {
        $class = $this::class;
        $primary = $this->getArFieldList()->getPrimaryFieldName();
        $primary_value = $array[$primary];
        if ($primary_value && arObjectCache::isCached($class, $primary_value)) {
            return arObjectCache::get($class, $primary_value);
        }
        foreach ($array as $field_name => $value) {
            $waked = $this->wakeUp($field_name, $value);
            $this->{$field_name} = $waked ?? $value;
        }
        arObjectCache::store($this);

        return $this;
    }

    /**
     * @param $field_name
     * @param $value
     * @return string|mixed
     * @noinspection NullPointerExceptionInspection
     */
    public function fixDateField($field_name, $value)
    {
        if ($this->getArFieldList()->getFieldByName($field_name)->isDateField()) {
            return $this->getArConnector()->fixDate($value);
        }

        return $value;
    }

    /**
     * @param $field_name
     * @return null
     */
    public function sleep($field_name)
    {
        return null;
    }

    /**
     * @param $field_name
     * @param $field_value
     * @return null
     */
    public function wakeUp($field_name, $field_value)
    {
        return null;
    }

    /**
     * @return array<string, mixed[]>
     */
    final public function getArrayForConnector(): array
    {
        $data = [];
        foreach ($this->getArFieldList()->getFields() as $arField) {
            $field_name = $arField->getName();
            $sleeped = $this->sleep($field_name);
            $var = $sleeped ?? ($this->{$field_name});
            $data[$field_name] = [$arField->getFieldType(), $var];
        }

        return $data;
    }




    //
    // Collector Modifications
    //
    /**
     * @description Returns an instance of the instatiated calling active record (needs to be done in static methods)
     * @TODO        : This should be cached somehow
     */
    protected static function getCalledClass(): \ActiveRecord
    {
        $class = static::class;

        return arCalledClassCache::get($class);
    }

    /**
     * @deprecated Do not use in Core DB-update. Please generate the manual installation script by using:
     *             $arBuilder = new arBuilder(new ilYourARBasedClass());
     *             $arBuilder->generateDBUpdateForInstallation();
     */
    final public static function installDB(): bool
    {
        return self::getCalledClass()->installDatabase();
    }

    /**
     * @deprecated Do not use in Core DB-update.
     */
    public function installConnector(): bool
    {
        return $this->installDatabase();
    }

    /**
     * @param $old_name
     * @param $new_name
     */
    final public static function renameDBField(string $old_name, string $new_name): bool
    {
        return self::getCalledClass()->getArConnector()->renameField(self::getCalledClass(), $old_name, $new_name);
    }

    final public static function tableExists(): bool
    {
        return self::getCalledClass()->getArConnector()->checkTableExists(self::getCalledClass());
    }

    /**
     * @param $field_name
     */
    final public static function fieldExists(string $field_name): bool
    {
        return self::getCalledClass()->getArConnector()->checkFieldExists(self::getCalledClass(), $field_name);
    }

    /**
     * @deprecated never use in ILIAS Core, Plugins only
     */
    final public static function removeDBField(string $field_name): bool
    {
        return self::getCalledClass()->getArConnector()->removeField(self::getCalledClass(), $field_name);
    }

    /**
     * @deprecated never use in ILIAS Core, Plugins only
     */
    final protected function installDatabase(): bool
    {
        if (!self::tableExists()) {
            $fields = [];
            foreach ($this->getArFieldList()->getFields() as $arField) {
                $fields[$arField->getName()] = $arField->getAttributesForConnector();
            }

            return $this->getArConnector()->installDatabase($this, $fields);
        }

        return $this->getArConnector()->updateDatabase($this);
    }

    /**
     * @deprecated never use in ILIAS Core, Plugins only
     */
    final public static function updateDB(): bool
    {
        if (!self::tableExists()) {
            self::getCalledClass()->installDatabase();

            return true;
        }

        return self::getCalledClass()->getArConnector()->updateDatabase(self::getCalledClass());
    }

    /**
     * @deprecated never use in ILIAS Core, Plugins only
     */
    final public static function resetDB(): bool
    {
        return self::getCalledClass()->getArConnector()->resetDatabase(self::getCalledClass());
    }

    /**
     * @deprecated never use in ILIAS Core, Plugins only
     */
    final public static function truncateDB(): void
    {
        self::getCalledClass()->getArConnector()->truncateDatabase(self::getCalledClass());
    }

    /**
     * @depracated never use in ILIAS Core, Plugins only
     */
    final public static function flushDB(): void
    {
        self::truncateDB();
    }

    //
    // CRUD
    //
    public function store(): void
    {
        $primary_fieldname = arFieldCache::getPrimaryFieldName($this);
        $primary_value = $this->getPrimaryFieldValue();

        if (!self::where([$primary_fieldname => $primary_value])->hasSets()) {
            $this->create();
        } else {
            $this->update();
        }
    }

    public function save(): void
    {
        $this->store();
    }

    public function create(): void
    {
        if ($this->getArFieldList()->getPrimaryField()->getSequence()) {
            $primary_fieldname = arFieldCache::getPrimaryFieldName($this);
            $this->{$primary_fieldname} = $this->getArConnector()->nextID($this);
        }

        $this->getArConnector()->create($this);
        arObjectCache::store($this);
    }

    /**
     * @throws arException
     */
    public function copy(int $new_id = 0): self
    {
        if (self::where([$this->getArFieldList()->getPrimaryFieldName() => $new_id])->hasSets()) {
            throw new arException(arException::COPY_DESTINATION_ID_EXISTS);
        }
        $new_obj = clone($this);
        $new_obj->setPrimaryFieldValue($new_id);

        return $new_obj;
    }

    public function afterObjectLoad(): void
    {
    }

    /**
     * @throws arException
     */
    public function read(): void
    {
        $records = $this->getArConnector()->read($this);
        if ($this->ar_safe_read && is_array($records) && $records === []) {
            throw new arException(arException::RECORD_NOT_FOUND, $this->getPrimaryFieldValue());
        }
        if (!$this->ar_safe_read && is_array($records) && $records === []) {
            $this->is_new = true;
        }
        $records = is_array($records) ? $records : [];
        foreach ($records as $record) {
            foreach (array_keys($this->getArrayForConnector()) as $k) {
                $waked = $this->wakeUp($k, $record->{$k});
                $this->{$k} = $waked ?? $record->{$k};
            }
            arObjectCache::store($this);
        }
    }

    public function update(): void
    {
        $this->getArConnector()->update($this);
        arObjectCache::store($this);
    }

    public function delete(): void
    {
        $this->getArConnector()->delete($this);
        arObjectCache::purge($this);
    }



    //
    // Collection
    //
    /**
     * @return ActiveRecord[]
     */
    public static function preloadObjects(): array
    {
        return self::get();
    }

    /**
     * @return $this
     */
    public static function additionalParams(array $additional_params): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());
        $activeRecordList->additionalParams($additional_params);

        return $activeRecordList;
    }

    /**
     * @param       $primary_key
     */
    public static function find($primary_key, array $add_constructor_args = []): ?\ActiveRecord
    {
        /**
         * @var $obj ActiveRecord
         */
        try {
            $class_name = static::class;
            if (!arObjectCache::isCached($class_name, $primary_key)) {
                $obj = arFactory::getInstance($class_name, $primary_key, $add_constructor_args);
                $obj->storeObjectToCache();

                return $obj;
            }
        } catch (arException) {
            return null;
        }

        try {
            $obj = arObjectCache::get($class_name, $primary_key);
        } catch (arException) {
            return null;
        }

        return $obj;
    }

    /**
     * Tries to find the object and throws an Exception if object is not found, instead of returning null
     * @param $primary_key
     * @throws arException
     */
    public static function findOrFail($primary_key, array $add_constructor_args = []): \ActiveRecord
    {
        $obj = self::find($primary_key, $add_constructor_args);
        if (is_null($obj)) {
            throw new arException(arException::RECORD_NOT_FOUND);
        }

        return $obj;
    }

    /**
     * @param       $primary_key
     * @description Returns an existing Object with given primary-key or a new Instance with given primary-key set but not yet created
     * @return \ActiveRecord|object|void
     */
    public static function findOrGetInstance($primary_key, array $add_constructor_args = []): \ActiveRecord
    {
        $obj = self::find($primary_key, $add_constructor_args);
        if ($obj !== null) {
            return $obj;
        }

        $class_name = static::class;
        $obj = arFactory::getInstance($class_name, 0, $add_constructor_args);
        $obj->setPrimaryFieldValue($primary_key);
        $obj->is_new = true;
        $obj->storeObjectToCache();

        return $obj;
    }

    /**
     * @param      $where
     * @param null $operator
     */
    public static function where($where, $operator = null): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());
        $activeRecordList->where($where, $operator);

        return $activeRecordList;
    }

    /**
     * @param              $on_this
     * @param              $on_external
     * @return $this
     */
    public static function innerjoinAR(
        ActiveRecord $activeRecord,
        $on_this,
        $on_external,
        array $fields = ['*'],
        string $operator = '=',
        bool $both_external = false
    ): \ActiveRecordList {
        return self::innerjoin(
            $activeRecord->getConnectorContainerName(),
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
     * @return $this
     */
    public static function innerjoin(
        $tablename,
        $on_this,
        $on_external,
        array $fields = ['*'],
        string $operator = '=',
        bool $both_external = false
    ): \ActiveRecordList {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->innerjoin($tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }

    /**
     * @param        $tablename
     * @param        $on_this
     * @param        $on_external
     * @return $this
     */
    public static function leftjoin(
        $tablename,
        $on_this,
        $on_external,
        array $fields = ['*'],
        string $operator = '=',
        bool $both_external = false
    ): \ActiveRecordList {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->leftjoin($tablename, $on_this, $on_external, $fields, $operator, $both_external);
    }

    /**
     * @param        $orderBy
     */
    public static function orderBy(string $orderBy, string $orderDirection = 'ASC'): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());
        $activeRecordList->orderBy($orderBy, $orderDirection);

        return $activeRecordList;
    }

    public static function dateFormat(string $date_format = 'd.m.Y - H:i:s'): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());
        $activeRecordList->dateFormat($date_format);

        return $activeRecordList;
    }

    /**
     * @param $start
     * @param $end
     */
    public static function limit(int $start, int $end): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());
        $activeRecordList->limit($start, $end);

        return $activeRecordList;
    }

    public static function affectedRows(): int
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->affectedRows();
    }

    public static function count(): int
    {
        return self::affectedRows();
    }

    /**
     * @return ActiveRecord[]
     */
    public static function get(): array
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->get();
    }

    public static function debug(): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->debug();
    }

    public static function first(): ?\ActiveRecord
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->first();
    }

    public static function getCollection(): \ActiveRecordList
    {
        return new ActiveRecordList(self::getCalledClass());
        ;
    }

    public static function last(): ?\ActiveRecord
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->last();
    }

    /**
     * @deprecated
     */
    public static function getFirstFromLastQuery(): ?\ActiveRecord
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->getFirstFromLastQuery();
    }

    public static function connector(arConnector $arConnector): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->connector($arConnector);
    }

    public static function raw(bool $set_raw = true): \ActiveRecordList
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->raw($set_raw);
    }

    /**
     * @param null $values
     * @return mixed[]|mixed[][]|int[]|string[]|null[]
     */
    public static function getArray(?string $key = null, $values = null): array
    {
        $activeRecordList = new ActiveRecordList(self::getCalledClass());

        return $activeRecordList->getArray($key, $values);
    }

    //
    // Magic Methods & Helpers
    //
    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @noinspection PhpInconsistentReturnPointsInspection since this a dynamic call
     */
    public function __call($name, $arguments)
    {
        // Getter
        if (preg_match("/get([a-zA-Z]*)/u", $name, $matches) && (is_countable($arguments) ? count(
            $arguments
        ) : 0) === 0) {
            return $this->{self::fromCamelCase($matches[1])};
        }
        // Setter
        if (!preg_match("/set([a-zA-Z]*)/u", $name, $matches)) {
            return;
        }
        if (count($arguments) !== 1) {
            return;
        }
        $this->{self::fromCamelCase($matches[1])} = $arguments[0];
    }

    public static function _toCamelCase(string $str, bool $capitalise_first_char = false): ?string
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }

        return preg_replace_callback('/_([a-z])/', fn ($c): string => strtoupper($c[1]), $str);
    }

    protected static function fromCamelCase(string $str): ?string
    {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback('/([A-Z])/', fn ($c): string => "_" . strtolower($c[1]), $str);
    }
}
