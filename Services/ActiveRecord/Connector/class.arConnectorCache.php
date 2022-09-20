<?php

/******************************************************************************
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
/**
 * Class ilGSStorageCache
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class arConnectorCache extends arConnector
{
    private \arConnector $arConnectorDB;
    private \ilGlobalCache $cache;
    public const CACHE_TTL_SECONDS = 180;

    /**
     * ilGSStorageCache constructor.
     * @param int $ttl
     */
    public function __construct(arConnector $arConnectorDB)
    {
        $this->arConnectorDB = $arConnectorDB;
        $this->cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
    }

    /**
     * @return mixed
     */
    public function nextID(ActiveRecord $ar)
    {
        return $this->arConnectorDB->nextID($ar);
    }

    public function checkConnection(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->checkConnection($ar);
    }

    public function installDatabase(ActiveRecord $ar, array $fields): bool
    {
        return $this->arConnectorDB->installDatabase($ar, $fields);
    }

    public function updateDatabase(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->updateDatabase($ar);
    }

    public function resetDatabase(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->resetDatabase($ar);
    }

    public function truncateDatabase(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->truncateDatabase($ar);
    }

    public function checkTableExists(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->checkTableExists($ar);
    }

    public function checkFieldExists(ActiveRecord $ar, string $field_name): bool
    {
        return $this->arConnectorDB->checkFieldExists($ar, $field_name);
    }

    public function removeField(ActiveRecord $ar, string $field_name): bool
    {
        return $this->arConnectorDB->removeField($ar, $field_name);
    }

    public function renameField(ActiveRecord $ar, string $old_name, string $new_name): bool
    {
        return $this->arConnectorDB->renameField($ar, $old_name, $new_name);
    }

    public function create(ActiveRecord $ar): void
    {
        $this->arConnectorDB->create($ar);
        $this->storeActiveRecordInCache($ar);
    }

    public function read(ActiveRecord $ar): array
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

    public function update(ActiveRecord $ar): void
    {
        $this->arConnectorDB->update($ar);
        $this->storeActiveRecordInCache($ar);
    }

    public function delete(ActiveRecord $ar): void
    {
        $this->arConnectorDB->delete($ar);

        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $this->cache->delete($key);
        }
    }

    public function readSet(ActiveRecordList $arl): array
    {
        return $this->arConnectorDB->readSet($arl);
    }

    public function affectedRows(ActiveRecordList $arl): int
    {
        return $this->arConnectorDB->affectedRows($arl);
    }

    /**
     * @param        $value
     */
    public function quote($value, string $type): string
    {
        return $this->arConnectorDB->quote($value, $type);
    }

    public function updateIndices(ActiveRecord $ar): void
    {
        $this->arConnectorDB->updateIndices($ar);
    }

    /**
     * Stores an active record into the Cache.
     */
    private function storeActiveRecordInCache(ActiveRecord $ar): void
    {
        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $value = $ar->asStdClass();

            $this->cache->set($key, $value, self::CACHE_TTL_SECONDS);
        }
    }
}
