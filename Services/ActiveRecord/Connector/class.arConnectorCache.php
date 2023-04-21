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

use ILIAS\Cache\Container\Container;
use ILIAS\Cache\Container\Request;

/**
 * Class ilGSStorageCache
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class arConnectorCache extends arConnector implements Request
{
    private \arConnector $arConnectorDB;
    private Container $cache_container;

    /**
     * ilGSStorageCache constructor.
     * @param int $ttl
     */
    public function __construct(arConnector $arConnectorDB)
    {
        global $DIC;
        $this->arConnectorDB = $arConnectorDB;
        $this->cache_container = $DIC->globalCache()->get($this);
    }

    /**
     * @param ActiveRecord $ar
     * @return string
     */
    protected function buildCacheKey(ActiveRecord $ar): string
    {
        return $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
    }

    public function getContainerKey(): string
    {
        return 'ar_cache';
    }

    public function isForced(): bool
    {
        return false;
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
        $key = $this->buildCacheKey($ar);
        if ($this->cache_container->has($key)) {
            $cached_value = $this->cache_container->get($key, new Transformation(function ($value) {
                return is_array($value) ? $value : null;
            }));
            if (is_array($cached_value)) {
                return $cached_value;
            }
        }

        $results = $this->arConnectorDB->read($ar);

        $this->cache_container->set($key, $results);

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
        $key = $this->buildCacheKey($ar);
        $this->cache_container->delete($key);
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
        $key = $this->buildCacheKey($ar);
        $value = $ar->asArray();

        $this->cache_container->set($key, $value);
    }
}
