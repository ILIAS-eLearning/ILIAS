<?php

/**
 * Class ilGSStorageCache
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class arConnectorCache extends arConnector
{

    /**
     * @var int
     */
    private $ttl;
    /**
     * @var arConnector
     */
    private $arConnectorDB;
    /**
     * @var \ilGlobalCache
     */
    private $cache;
    const CACHE_TTL_SECONDS = 180;


    /**
     * ilGSStorageCache constructor.
     *
     * @param arConnector $arConnectorDB
     * @param int         $ttl
     */
    public function __construct(arConnector $arConnectorDB, int $ttl = self::CACHE_TTL_SECONDS)
    {
        $this->ttl = $ttl;
        $this->arConnectorDB = $arConnectorDB;
        $this->cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return mixed
     */
    public function nextID(ActiveRecord $ar)
    {
        return $this->arConnectorDB->nextID($ar);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return mixed
     */
    public function checkConnection(ActiveRecord $ar)
    {
        return $this->arConnectorDB->checkConnection($ar);
    }


    /**
     * @param ActiveRecord  $ar
     * @param               $fields
     *
     * @return bool
     */
    public function installDatabase(ActiveRecord $ar, $fields)
    {
        return $this->arConnectorDB->installDatabase($ar, $fields);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return bool
     */
    public function updateDatabase(ActiveRecord $ar)
    {
        return $this->arConnectorDB->updateDatabase($ar);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return true
     */
    public function resetDatabase(ActiveRecord $ar)
    {
        return $this->arConnectorDB->resetDatabase($ar);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return void
     */
    public function truncateDatabase(ActiveRecord $ar)
    {
        $this->arConnectorDB->truncateDatabase($ar);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return mixed
     *
     */
    public function checkTableExists(ActiveRecord $ar)
    {
        return $this->arConnectorDB->checkTableExists($ar);
    }


    /**
     * @param ActiveRecord  $ar
     * @param               $field_name
     *
     * @return mixed
     */
    public function checkFieldExists(ActiveRecord $ar, $field_name)
    {
        return $this->arConnectorDB->checkFieldExists($ar, $field_name);
    }


    /**
     * @param ActiveRecord  $ar
     * @param               $field_name
     *
     * @return bool
     * @throws arException
     */
    public function removeField(ActiveRecord $ar, $field_name)
    {
        return $this->arConnectorDB->removeField($ar, $field_name);
    }


    /**
     * @param ActiveRecord  $ar
     * @param               $old_name
     * @param               $new_name
     *
     * @return bool
     * @throws arException
     */
    public function renameField(ActiveRecord $ar, $old_name, $new_name)
    {
        return $this->arConnectorDB->renameField($ar, $old_name, $new_name);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return void
     */
    public function create(ActiveRecord $ar)
    {
        $this->arConnectorDB->create($ar);
        $this->storeActiveRecordInCache($ar);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return array
     */
    public function read(ActiveRecord $ar)
    {
        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $cached_value = $this->cache->get($key);
            if (is_array($cached_value)) {
                return $cached_value;
            }

            if ($cached_value instanceof stdClass) {
                return [$cached_value];
            }
        }

        $results = $this->arConnectorDB->read($ar);

        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();

            $this->cache->set($key, $results, self::CACHE_TTL_SECONDS);
        }

        return $results;
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return void
     */
    public function update(ActiveRecord $ar)
    {
        $this->arConnectorDB->update($ar);
        $this->storeActiveRecordInCache($ar);
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return void
     */
    public function delete(ActiveRecord $ar)
    {
        $this->arConnectorDB->delete($ar);

        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $this->cache->delete($key);
        }
    }


    /**
     * @param ActiveRecordList $arl
     *
     * @return mixed
     */
    public function readSet(ActiveRecordList $arl)
    {
        return $this->arConnectorDB->readSet($arl);
    }


    /**
     * @param ActiveRecordList $arl
     *
     * @return int
     */
    public function affectedRows(ActiveRecordList $arl)
    {
        return $this->arConnectorDB->affectedRows($arl);
    }


    /**
     * @param $value
     * @param $type
     *
     * @return string
     */
    public function quote($value, $type)
    {
        return $this->arConnectorDB->quote($value, $type);
    }


    /**
     * @param ActiveRecord $ar
     */
    public function updateIndices(ActiveRecord $ar)
    {
        $this->arConnectorDB->updateIndices($ar);
    }


    /**
     * Stores an active record into the Cache.
     *
     * @param ActiveRecord $ar
     *
     * @return void
     */
    private function storeActiveRecordInCache(ActiveRecord $ar)
    {
        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $value = $ar->__asStdClass();

            $this->cache->set($key, $value, self::CACHE_TTL_SECONDS);
        }
    }
}
